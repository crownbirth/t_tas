<?php 
require_once('../../Connections/tams.php');
if (!isset($_SESSION)) {
  session_start();
}
require_once('../../param/param.php');
require_once('../../functions/function.php');

$MM_authorizedUsers = "11";
$MM_donotCheckaccess = "true";

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
    if (($strUsers == "") && true) { 
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
    function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = ""){
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

$std = getSessionValue('MM_Username');

mysql_select_db($database_tams, $tams);
$query = sprintf("SELECT * "
                    . "FROM session "
                    . "ORDER BY sesid DESC LIMIT 2");
$session = mysql_query($query, $tams) or die(mysql_error());
$row_session = mysql_fetch_assoc($session);
$totalRows_session = mysql_num_rows($session);

$query_student = sprintf("SELECT * FROM prospective WHERE jambregid=%s",
                         GetSQLValueString($_SESSION['MM_Username'], 'text'));
$student = mysql_query($query_student, $tams) or die(mysql_error());
$row_student = mysql_fetch_assoc($student);
$veri_data_row_num = mysql_num_rows($student);

$status = $row_student['stid'] == 27? 'Indigene': 'Nonindigene';
$level = $row_student['admtype'] == 'DE'? 2: 1;

$query_curSchedule = sprintf("SELECT *  
                            FROM payschedule  
                            WHERE sesid = %s 
                            AND level = %s 
                            AND status = %s 
                            AND entrymode = %s",
                            GetSQLValueString($row_session['sesid'], "int"),
                            GetSQLValueString($level, "text"),
                            GetSQLValueString($status, "text"),
                            GetSQLValueString($row_student['admtype'], "text"));
$curSchedule = mysql_query($query_curSchedule, $tams) or die(mysql_error());
$row_curSchedule = mysql_fetch_assoc($curSchedule);
$totalRows_curSchedule = mysql_num_rows($curSchedule);

$query_verify = sprintf("SELECT * FROM verification WHERE stdid = %s",
                        GetSQLValueString($_SESSION['MM_Username'], 'text'));
$verify = mysql_query($query_verify, $tams) or die(mysql_error());
$row_verify = mysql_fetch_assoc($verify);
$totalRows_verify = mysql_num_rows($verify);

$query_paid = sprintf("SELECT * "
                    . "FROM schfee_transactions "
                    . "WHERE scheduleid = %s "
                    . "AND status = 'APPROVED' "
                    . "AND can_no = %s",
                    GetSQLValueString($row_curSchedule['scheduleid'], 'text'),
                    GetSQLValueString($_SESSION['MM_Username'], 'text'));
$paid = mysql_query($query_paid, $tams) or die(mysql_error());
$total_paid= mysql_num_rows($paid);

$amount = 0;
for(; $row_paid = mysql_fetch_assoc($paid); ) {
    $amount += doubleval(str_replace(',', '', substr($row_paid['amt'], 3)));
}

// Incomplete payment
if ($row_curSchedule['amount'] <= $amount) {
    
//    echo $row_curSchedule['amount'];
//    echo $amount;
    header('Location: ../status.php');
}
      
// ensure only admitted and olevel verified students can pay school fees
if($row_student['adminstatus']=='No' || $row_verify['verified'] =='FALSE'){
    header("Location: ../status.php"); 
    exit;
}

$_SESSION['payment']['paid'] = false;
$_SESSION['payment']['verified'] = true;
$_SESSION['payment']['sesid'] = $row_session['sesid'];
$_SESSION['payment']['sesname'] = $row_session['sesname'];
$_SESSION['payment']['amount'] = $row_curSchedule['amount'] - $amount;
$_SESSION['payment']['scheduleid'] = $row_curSchedule['scheduleid'];
$_SESSION['payment']['revhead'] = $row_curSchedule['revhead'];
$_SESSION['payment']['level'] = $level;
$_SESSION['payment']['percent'] = 100;
$_SESSION['payment']['jambregid'] = $row_student['jambregid'];
$_SESSION['payment']['name'] = $row_student['lname'].' '.$row_student['fname'].' '.$row_student['mname'];
$_SESSION['payment']['prg'] = $row_student['progofferd'];
$_SESSION['payment']['admtype'] = $row_student['admtype'];

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root.'/prospective' );   
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/template.dwt.php" codeOutsideHTMLIsLocked="false" -->
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Payment Instruction <!-- InstanceEndEditable --></td>
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
                    <p>
                        Your SCHOOL FEES is to be paid by selecting a card type below and using our webpay platform.
                    </p>
                    
            <p>
                Payment will be made using Debit/Credit Cards (ATM Card)<br>
                Your Card can be from <u>any of the Nigerian Banks</u>
                <br>Ensure that your card has been enabled for internet transactions
                by your bank (kindly enquire from your bank if you must).
            </p> 
            <p>
                <b style="color :red">Fees paid to Tai Solarin University of Education are non-refundable</b>
                <h4>Are you using Internet explorer browser?</h4>
                Avoid browser issues, uncheck support for Use SSL2.0 by following the steps below:<br/>
                1. Click on Tool option on the menu bar<br/>
                2. Select Internet Options<br/>
                3. Click Advance tab<br/>
                4. Scroll down to Security option and uncheck Use SSL 2.0<br/>
            </p>
                </td> 
            </tr>
            <tr>
                <td >
                    <table width="400" align="center" class="table table-bordered table-striped table-condensed">
                        <tr>
                            <th colspan="2">Select a payment method to continue</th>
                        </tr>
                         <tr>
                             <td align="center" width="50%" style=" "><a href="mastercard/mastercard.php"><img src="img/mastercard.png"></a></td>
                             <td align="center" width="50%"><a href="visa/visa.php"><img src="img/visa.jpg"></a></td>
                        </tr>
                    </table>
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
<!-- InstanceEnd --></html>