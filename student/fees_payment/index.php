<?php 

require_once('../../Connections/tams.php');

if (!isset($_SESSION)) {
  session_start();
}
require_once('../../param/param.php');
require_once('../../functions/function.php');

$MM_authorizedUsers = "10";
$MM_donotCheckaccess = "false";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
  // For security, start by assuming the visitor is NOT authorized. 
  $isValid = False; 

  // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
  // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
  if (!empty($UserName)) { 
    // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
    // Parse the strings into arrays. 
    $arrUsers = Explode(",", $strUsers); 
    $arrGroups = Explode(",", $strGroups); 
    if (in_array($UserName, $arrUsers)) { 
      $isValid = true; 
    } 
    // Or, you may restrict access to only certain users based on their username. 
    if (in_array($UserGroup, $arrGroups)) { 
      $isValid = true; 
    } 
    if (($strUsers == "") && false) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "../index.php";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
}

if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

//Get current session 
mysql_select_db($database_tams, $tams);
$query = sprintf("SELECT * "
                    . "FROM session "
                    . "ORDER BY sesid DESC LIMIT 2");
$session = mysql_query($query, $tams) or die(mysql_error());
$row_session = mysql_fetch_assoc($session);
$prevSes = mysql_fetch_assoc($session);
$totalRows_session = mysql_num_rows($session);

$_SESSION['payment']['sesid'] = $row_session['sesid'];
$_SESSION['payment']['additions'] = false;

$query_info = sprintf("SELECT *  
                        FROM student 
                        WHERE stdid = %s",
                        GetSQLValueString(getSessionValue('MM_Username'), "text"));
$info = mysql_query($query_info, $tams) or die(mysql_error());
$row_info = mysql_fetch_assoc($info);
$totalRows_info = mysql_num_rows($info);

$_SESSION['payment']['name'] =  $row_info['lname'].' '.$row_info['fname'].' '.$row_info['mname'];

$query_paySes = sprintf("SELECT * FROM session "
                    . "WHERE sesid BETWEEN %s AND %s "
                    . "ORDER BY sesid DESC",
                        GetSQLValueString($row_info['sesid'], "int"),
                        GetSQLValueString($prevSes['sesid'], "int"));
$paySes = mysql_query($query_paySes, $tams) or die(mysql_error());
$totalRows_paySes = mysql_num_rows($paySes);


$owing = array();
$prevCleared = true;
$percent = 100;
$level = $row_info['level'];
$status = $row_info['stid'] == 27? 'Indigene': 'Nonindigene';
$extra_msg = NULL;

$query_curSchedule = sprintf("SELECT *  
                            FROM payschedule  
                            WHERE sesid = %s 
                            AND level = %s 
                            AND status = %s 
                            AND entrymode = %s",
                            GetSQLValueString($row_session['sesid'], "int"),
                            GetSQLValueString($level, "text"),
                            GetSQLValueString($status, "text"),
                            GetSQLValueString($row_info['admode'], "text"));
$curSchedule = mysql_query($query_curSchedule, $tams) or die(mysql_error());
$row_curSchedule = mysql_fetch_assoc($curSchedule);
$totalRows_curSchedule = mysql_num_rows($curSchedule);
    
$_SESSION['payment']['penalty'] = $row_curSchedule['penalty'];
$_SESSION['payment']['level'] = $row_curSchedule['level'];

// Get information for previous session
for($idx = 1; $row_paySes = mysql_fetch_assoc($paySes); $idx++) {    
     --$level;
    
    if($level <= 0)
        break;
    
    $amount = 0;
    $query_prevSchedule = sprintf("SELECT * 
                            FROM payschedule 
                            WHERE sesid = %s 
                            AND level = %s 
                            AND status = %s 
                            AND entrymode = %s",
                            GetSQLValueString($row_paySes['sesid'], "int"),
                            GetSQLValueString($level, "text"),
                            GetSQLValueString($status, "text"),
                            GetSQLValueString($row_info['admode'], "text"));
    $prevSchedule = mysql_query($query_prevSchedule, $tams) or die(mysql_error());
    $row_prevSchedule = mysql_fetch_assoc($prevSchedule);
    $totalRows_prevSchedule = mysql_num_rows($prevSchedule);

    $query_prevPay = sprintf("SELECT *  
                            FROM schfee_transactions 
                            WHERE scheduleid = %s 
                            AND matric_no = %s 
                            AND status = 'APPROVED'",
                            GetSQLValueString($row_prevSchedule['scheduleid'], "int"),
                            GetSQLValueString(getSessionValue('MM_Username'), "text"));
    $prevPay = mysql_query($query_prevPay, $tams) or die(mysql_error());
    $totalRows_prevPay = mysql_num_rows($prevPay);
    
    for(; $row_prevPay = mysql_fetch_assoc($prevPay); ) {
        $amount += doubleval(str_replace(',', '', substr($row_prevPay['amt'], 3)));
    }

    if($row_prevSchedule['amount'] > $amount) {
        $prevCleared = false;        
        $_SESSION['payment']['prev_ses'] = true;
        $_SESSION['payment']['sesname'] = $owing[$row_paySes['sesid']]['sesname'] = $row_paySes['sesname'];
        $_SESSION['payment']['amount'] = $owing[$row_paySes['sesid']]['amount'] = $row_prevSchedule['amount'] - $amount;
        $_SESSION['payment']['scheduleid'] = $owing[$row_paySes['sesid']]['$scheduleid'] = $row_prevSchedule['scheduleid'];
        $_SESSION['payment']['revhead'] = $row_prevSchedule['revhead'];
        $_SESSION['payment']['level'] = $row_prevSchedule['level'];
        $_SESSION['payment']['sesid'] = $row_paySes['sesid'];
        $owing[$row_paySes['sesid']]['last'] = $idx == $totalRows_paySes? true: false;
    }
    
}

if($prevCleared) {    
    $_SESSION['payment']['prev_ses'] = false;
    $_SESSION['payment']['sesname'] = $owing[$row_session['sesid']]['sesname'] = $row_session['sesname'];
    $_SESSION['payment']['amount'] = $owing[$row_session['sesid']]['amount'] = $row_curSchedule['amount'];
    $_SESSION['payment']['scheduleid'] = $owing[$row_session['sesid']]['$scheduleid'] = $row_curSchedule['scheduleid'];
    $_SESSION['payment']['revhead'] = $row_curSchedule['revhead'];
    $_SESSION['payment']['level'] = $row_curSchedule['level'];
    $_SESSION['payment']['percent'] =  100;
    
    $query_curPay = sprintf("SELECT *  
                            FROM schfee_transactions 
                            WHERE scheduleid = %s 
                            AND matric_no = %s 
                            AND status = 'APPROVED'",
                            GetSQLValueString($row_curSchedule['scheduleid'], "int"),
                            GetSQLValueString(getSessionValue('MM_Username'), "text"));
    $curPay = mysql_query($query_curPay, $tams) or die(mysql_error());
    $firstTran = $row_curPay = mysql_fetch_assoc($curPay);
    $totalRows_curPay = mysql_num_rows($curPay);
    $curAmount = 0;
    $totalPercent = 0;
    
    for($idx = 0; $idx < $totalRows_curPay; $row_curPay = mysql_fetch_assoc($curPay), $idx++) {
        $curAmount += doubleval(str_replace(',', '', substr($row_curPay['amt'], 3)));
        $totalPercent += $row_curPay['percentPaid'];
    }
    
    $_SESSION['payment']['installment'] = 'none';
    
    // Check that payschedule amount is equal to paid amount
    if($_SESSION['payment']['amount'] > $curAmount) {
        $_SESSION['payment']['additions'] = true;
    }
    
    if($totalRows_curPay == 0) {
        $_SESSION['payment']['additions'] = false;
    }elseif($totalRows_curPay == 1) {
         
        if($firstTran['percentPaid'] == 100) {
            
            $_SESSION['payment']['installment'] = 'complete';
            $owing[$row_session['sesid']]['amount'] = $_SESSION['payment']['amount'] -= $curAmount;
            
           //means this candidate has paid
          // header('Location: payhistory.php');
         
        }elseif($firstTran['percentPaid'] == 60) {
            $_SESSION['payment']['percent'] =  40;
            $_SESSION['payment']['installment'] = 'incomplete';
            $_SESSION['payment']['additions'] = false;
            
            // Get outstanding payment.
            $owe = $_SESSION['payment']['amount'] - $curAmount;
            
            // Increase amount to make 40% equal to outstanding + addition
            $_SESSION['payment']['amount'] = $owing[$row_session['sesid']]['amount'] = $owe * 2.5;
        }
    }elseif($totalRows_curPay > 1) {
        
        if($totalPercent >= 100) {
            $_SESSION['payment']['installment'] = 'complete';
            $owing[$row_session['sesid']]['amount'] = $_SESSION['payment']['amount'] -= $curAmount;
            //means this candidate has paid
            // header('Location: payhistory.php');
        }
    }
    
}

if ((isset($_GET['doLogout'])) && ($_GET['doLogout']=="true")){
	doLogout($site_root);   
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" ng-app="tams">
    <!-- InstanceBegin template="/Templates/template.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php require('../../param/site.php'); ?>
<title><?php echo $university ?> </title>
<!-- InstanceEndEditable -->
<link href="../../css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
<link href="../../css/menulink.css" rel="stylesheet" type="text/css" />
<link href="../../css/footer.css" rel="stylesheet" type="text/css" />
<link href="../../css/sidemenu.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div class="container">
  <div class="header">
    <!-- end .header -->
</div>
  <div class="topmenu">
<?php include '../../include/topmenu.php'; ?>
  </div>
  <!-- end .topmenu --> 
  
  <div class="loginuser">
  <?php include '../../include/loginuser.php'; ?>
  
  <!-- end .loginuser --></div>
  <div class="pagetitle">
    <table width="600">
      <tr>
        <td><!-- InstanceBeginEditable name="pagetitle" -->Payment Status <!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690" class="table">
        <tr>
            <td colspan="2">
                <?php if(!$prevCleared) {?>
                <p>
                    You have outstanding payments from previous session(s).
                </p>
                
                <table class="table table-striped">
                    <caption><strong>Outstanding Payment(s)</strong></caption>
                    <thead>
                        <th>Session</th>
                        <th>Amount</th>
                        <th>Penalty</th>
                    </thead>
                    <tbody>
                        <?php foreach($owing as $sesid => $values) {?>
                        <tr>
                            <td>
                                <?php echo $values['sesname']?>
                            </td>
                            <td>
                                <?php echo number_format($values['amount'])?>
                            </td>
                            <td>
                                <?php echo number_format($_SESSION['payment']['penalty'])?>
                            </td>
                            <td>
                                <?php  if($values['last']) {?>
                                <button onclick="location.href = 'paymentinfo.php'">Pay Now</button>
                                
                                <?php }?>
                                <!--TODO: Add pay button from testing 'last' value-->
                            </td>                            
                        </tr>
                        <?php }?>
                        <tr>
                            <td colspan="4" style="text-align: center"> 
                                
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php } else if($totalPercent == 100 && !$_SESSION['payment']['additions']) { ?>

                 <p>
                   You are cleared for the CURRENT SESSION. Click on Payhistroy link to REPRINT your RECEIPTS!
                </p>
                <br/>


                <?php } else {?>
                
                <p>
                   You are cleared for the previous session(s)!
                </p>
                <br/>
                <table class="table table-striped" ng-controller="PayController">
                    <caption><strong>Payment Invoice</strong></caption>
                    <thead>
                        <th>Session</th>
                        <th>Amount</th>
                        <th>Per cent</th>
                    </thead>
                    <tbody>
                        <?php if($_SESSION['payment']['installment'] != 'complete') { // Incomplete payments?>
                        <tr>
                            <td>
                                <?php echo $owing[$row_session['sesid']]['sesname']?>
                            </td>
                            <td ng-bind="dispAmt">
                                <?php echo $owing[$row_session['sesid']]['amount']?>
                            </td>
                            <td>
                                <?php if($_SESSION['payment']['installment'] == 'none') {?>
                                <select ng-model="percent" 
                                        ng-options="values.value as values.name for values in validValues"
                                        ng-click="calcAmt()">
                                </select>
                                <?php }else {?>
                                <span>40%</span>
                                <?php }?>
                            </td>
                        </tr>                        
                        <tr>
                            <td colspan="3" style="text-align: center"> 
                                <button ng-click="processUrl('percent')">Pay Now</button>
                            </td>
                        </tr>
                        <?php }elseif($_SESSION['payment']['additions']){ // Additional payments?>
                        <tr>
                            <td>
                                <?php echo $owing[$row_session['sesid']]['sesname']?>
                            </td>
                            <td>
                                <?php echo $owing[$row_session['sesid']]['amount']?>
                            </td>
                            <td align="center">-</td>
                        </tr>                        
                        <tr>
                            <td colspan="3" style="text-align: center"> 
                                <button ng-click="processUrl('addition')">Pay Now</button>
                            </td>
                        </tr>
                        <?php }else { // Cleared of all payments ?>
                        <tr>
                            <td colspan="3">
                                You are not owing for the current session (<?php echo $owing[$row_session['sesid']]['sesname']?>).
                            </td>
                        </tr>
                        <?php }?>
                    </tbody>
                </table>
                
                <?php }?>
            </td> 
        </tr>
    </table>
  <!-- InstanceEndEditable --></div>
<div class="footer">
    <p><!-- end .footer -->   
    
    <?php require '../../include/footer.php'; ?>
	
   </p>
  </div>
  <!-- end .container -->
</div>
</body>
<script type="text/javascript" src="../../scripts/angular/angular.min.js"></script>
<script>

    angular.module('tams', [])
    
    .controller('PayController', function($scope, $window) {

        $scope.validValues = [
            {'value': 100, 'name': '100%'}//,
            //{'value': 60, 'name': '60%'}
        ];

        $scope.percent = <?php echo $_SESSION['payment']['percent']?>;
        
        $scope.amount = $scope.dispAmt = <?php echo $owing[$row_session['sesid']]['amount']?>;
        
        $scope.calcAmt = function () {
            $scope.dispAmt = $scope.amount * $scope.percent/100;
        };
        
        $scope.calcAmt();
        
        $scope.processUrl = function(type) {
            switch(type) {
                case 'percent':
                    $window.location.href = 'paymentinfo.php?pc='+$scope.percent;
                    break;

                case 'addition':
                    $window.location.href = 'paymentinfo.php?ad';
                    break;

                default:
            }

        };
    });

</script>
<!-- InstanceEnd --></html>