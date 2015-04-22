<?php require_once('../../Connections/tams.php'); ?>
<?php
// *** Validate request to login to this site.
if (!isset($_SESSION)) {
  session_start();
}

//echo date('Y-m-d H:i:s');



$reroot = 'treated2.php';
require_once('../../param/param.php');
require_once('../../functions/function.php');

$MM_authorizedUsers = "20, 22";
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
mysql_select_db($database_tams, $tams);
$query = sprintf("SELECT * FROM session ORDER BY sesid DESC");
$session = mysql_query($query, $tams) or die(mysql_error());
$row_session = mysql_fetch_assoc($session);

mysql_select_db($database_tams, $tams);
$query = sprintf("SELECT * FROM programme WHERE continued = 'Yes' ORDER BY progname ASC");
$prog = mysql_query($query, $tams) or die(mysql_error());
$row_proramme = mysql_fetch_assoc($prog);


mysql_select_db($database_tams, $tams);
$query_limit_verify = sprintf("SELECT ol.*, s.sesname "
                            . "FROM olevel_veri_data ol, session s "
                            . "WHERE ol.treated = 'Yes' "
                            . "AND ol.sesid = s.sesid "
                            . "AND ol.level <> 'UTME' "
                            . "ORDER BY date_treated "
                            . "DESC ");
$verify = mysql_query($query_limit_verify, $tams) or die(mysql_error());
$verify_row = mysql_fetch_assoc($verify);
$verify_row_num = mysql_num_rows($verify);
            

            
    $arr = array();
    empty($arr);
    do{
        array_push($arr, $verify_row);
        
    }while($verify_row = mysql_fetch_assoc($verify));


//var_dump($arr);

$json = json_encode($arr);    



        
       
        
        if( isset($_POST['MM_treat'])  && $_POST['MM_treat'] == 'form2' ){
            
            $cur_detais = $_GET['id'];
            $cur_ordid = $_GET['ordid'];
            $msg = '';
            
            if($_POST['submit'] == 'Yes'){
                $msg = "<p style='color:green'>Your Olevel Result has been PRINTED by the ICT <br/> and it is being forwarded to Admission's Office for Verification .</p>";
            }else{
                $msg = "<p style='color:red'>ICT could NOT PRINT your O'Level Result  <br/>Your Card details may be wrong or maximum use reached. Please re-submit.</p>";
            }
            
            
            mysql_select_db($database_tams, $tams);
            $query = sprintf("UPDATE `olevel_veri_data` SET `treated` = 'Yes', approve = %s, return_msg = %s, date_treated=%s, who=%s WHERE id = %s", 
                                        GetSQLValueString($_POST['submit'], 'text'),
                                        GetSQLValueString($msg, 'text'),
                                        GetSQLValueString(date('Y-m-d H:i:s'), 'date'),
                                        GetSQLValueString($_SESSION['MM_Username'], 'text'),
                                        GetSQLValueString($cur_detais, 'text'));

            $verify = mysql_query($query, $tams) or die(mysql_error());
            
            
            header("Location: ". $reroot); 
            exit;  
        }
       
  
    
if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
    doLogout($site_root.'/ict');  
}
?>
<!DOCTYPE html>
<html ng-App="app"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require('../../param/site.php'); ?>
    <title><?php echo $university ?> </title>
    <!-- InstanceEndEditable -->
    <link href="../../css/template.css" rel="stylesheet" type="text/css" />
    <!-- InstanceBeginEditable name="head" -->
    <!-- InstanceEndEditable -->
    <link href="../css/menulink.css" rel="stylesheet" type="text/css" />
    <link href="../css/footer.css" rel="stylesheet" type="text/css" />
    <link href="../css/sidemenu.css" rel="stylesheet" type="text/css" />
   <link href="../css/datepicker.css" rel="stylesheet" type="text/css" />
    <script src="js/angular.js"></script>
    <script src="js/datepickr.js"></script>
    
</head>

    <body >
        <script type="text/javascript">
            
            var data = <?php echo $json?>;
            app = angular.module('app', []);
            
            app.controller('firstCtrl',function($scope){
                $scope.verify = data;
                
                $scope.click = function(){
                    location.href = "index.php";
                };
            });
           
        </script>
<div class="container">
  <div class="header">
    <!-- end .header -->
    </div>
  <div class="topmenu">
<?php include '../include/topmenu.php'; ?>
  </div>
  <!-- end .topmenu --> 
  
  <div class="loginuser">
  <?php include '../include/loginuser.php'; ?>
  
  <!-- end .loginuser --></div>
  <div class="pagetitle">
    <table width="600">
      <tr>
        <td><!-- InstanceBeginEditable name="pagetitle" --> O'Level Verification Page  <!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
 
        <table width="690" class="table table-bordered" ng-controller="firstCtrl">
            <tr>
                <td>
                    <table class="table table-bordered table-condensed">
                        <tr>
                            <td>
                                <table width="345" class="table table-bordered table-condensed table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th colspan="2">Generate report</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <form method="POST" action="printreport.php" target="_blank">
                                            <tr>
                                                <td>Programme </td>
                                                <td>
                                                    <select name="progid" style=" width: 150px" required="required">
                                                        <option value="">-Choose-</option>
                                                        <?php do{?>
                                                        <option value="<?php echo $row_proramme['progid']?>"><?php echo $row_proramme['progname']?></option>
                                                        <?php }while ($row_proramme = mysql_fetch_assoc($prog))?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Level </td>
                                                <td>
                                                    <select name="level" required="required">
                                                        <option value="">-Choose-</option>
                                                        <option value="1">100</option>
                                                        <option value="2">200</option>
                                                        <option value="3">300</option>
                                                        <option value="4">400</option>
                                                        <option value="5">500</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Session </td>
                                                <td>
                                                    <select name="sesid" required="required">
                                                        <option value="">-Choose-</option>
                                                        <?php do{?>
                                                            <option value="<?php echo $row_session['sesid']?>"><?php echo $row_session['sesname']?></option>
                                                        <?php }while ($row_session = mysql_fetch_assoc($session))?>
                                                    </select>
                                                    <input type="submit" name="submit" value="generate" >
                                                </td>
                                            </tr>
                                        </form>    
                                    </tbody>    
                                </table> 
                            </td>
                            <td>
                                <form method="POST" action="printreport2b.php" target="_blank">
                                    <table  width="345" class="table table-bordered table-condensed table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th colspan="2"> Generate By Date </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>From :</td>
                                                <td><input type="text" id="datepick" name="from" style=" width: 100px"/> &nbsp; YYYY-MM-DD</td>
                                            </tr>
                                            <tr>
                                                <td>To :</td>
                                                <td><input type="text" id="datepick2" name="to" style=" width: 100px"/> &nbsp; YYYY-MM-DD</td>
                                            </tr>
                                            <tr>
                                                <td>&nbsp;</td>
                                                <td><input type="submit" name="submit" value="submit"/></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </form>    
                            </td>
                        </tr>
                    </table>
                </td>
            </tr> 
            <tr>
                <td>
                    <table class="table table-bordered table-condensed table-striped table-hover">
                        <tr>
                            <td >
                                Search By any Parameter  <input type="text" name="search" data-ng-model="search" >
                            </td>
                            <td >
                                Enable Re-process  <input type="checkbox" ng-model="active">
                            </td>
                        </tr>
                    </table>
                   
                    <table width="690" class="table table-bordered table-condensed table-striped table-hover">
                      <thead>
                          <tr>
                              <th>Reg No</th>
                              <th>Exam Type</th>
                              <th width="30">Exam Year</th>
                              <th>Exam No</th>
                              <th>Card S/N</th>
                              <th>Session</th>
                              <th>Card Pin</th>
                              <th ng-show="!active">Printed</th>
                              <th ng-show="active">Re-Process</th>
                          </tr>
                      </thead>
                      <tbody >
                          <tr ng-repeat="d in verify | filter:search | limitTo:25">  
                              <td>{{d.stdid}}</td>
                                <td>{{d.exam_type}}</td> 
                                <td>{{d.exam_year}}</td> 
                                <td>{{d.exam_no}}</td> 
                                <td>{{d.card_no}}</td>
                                <td>{{d.sesname}}</td>
                                <td>{{d.card_pin}}</td> 
                                <td ng-show="!active">{{d.approve}}</td>
                                <td ng-show="active" width="90">
                                    <form name='form2' method="POST" action="treated.php?id={{d.id}}">
                                        <input type="submit" name='submit' value="Yes"/>&nbsp;|&nbsp;<input type="submit"  name='submit' value="No"/>
                                        <input type="hidden" name='MM_treat' value="form2"/>
                                    </form>   
                                </td>
                            </tr>
                            <tr ng-hide="!d.$dirty && !d.stdid.$dirty">
                                <td colspan="7" align="center"><p style="color: red">No O'Level Result Card Submitted </p></td>
                            </tr>
                      </tbody>
                    </table>
                </td>
            </tr>
            <tr>
<!--            <td align="center">
                <table  class="table table-bordered table-condensed table-striped">
                    <tr width="50" align="center">
                        <td><p><a href="<?php printf("%s?pageNum_Rsall=%d%s", $currentPage, max(0, $pageNum_Rsall - 1), $queryString_Rsall); ?>"><< Prev</a></p></td>
                        <td><?php echo 'Page '.($pageNum_Rsall + 1) ." of ". ($totalPages_Rsall + 1); ?></td>
                        <td><p><a href="<?php printf("%s?pageNum_Rsall=%d%s", $currentPage, min($totalPages_Rsall, $pageNum_Rsall + 1), $queryString_Rsall); ?>">Next >></a></p></td>
                    </tr>
                </table>
            </td>-->
        </tr>
    </table>
    
  <!-- InstanceEndEditable --></div>
<div class="footer">
    <p><!-- end .footer -->   
    
    <?php require '../include/footer.php'; ?>
	
   </p>
  </div>
  <!-- end .container -->
</div>
    <script type="text/javascript">
        new datepickr('datepick',{
            'dateFormat': 'Y-m-d' 
        });

        new datepickr('datepick2', {
                'dateFormat': 'Y-m-d'
        });

        
    </script>
</body>
</html>