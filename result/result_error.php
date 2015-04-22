<?php require_once('../Connections/tams.php'); ?>
<?php
require_once('../param/param.php');
require_once('../functions/function.php');

if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "2,3,4,5,6";
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
if (!((isset($_SESSION['MM_Username'])) && 
        (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
}
?>
<?php
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

mysql_select_db($database_tams, $tams);

if( isset($_POST['submit']) && $_POST['submit'] == "") { 
    
}

$query_sess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,1";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

$csid = -1;
if(isset($_GET['csid'])) {
    $csid = $_GET['csid'];
}

$sesid = $row_sess['sesid'];
if(isset($_GET['sid'])) {
    $sesid = $_GET['sid'];
}

$status = '';
if( isset($_POST['restore']) && $_POST['restore'] == "Restore Results") { 
    if(isset($_POST['stdid']) && is_array($_POST['stdid'])) {        
        mysql_query("BEGIN", $tams);
        $insert_string = sprintf("INSERT INTO result (stdid, csid, sesid, tscore, escore) "
                                    . "SELECT stdid, csid, sesid, tscore, escore "
                                    . "FROM result_error "
                                    . "WHERE sesid = %s "
                                    . "AND csid = %s "
                                    . "AND stdid IN ('%s')",
                                    GetSQLValueString($sesid, "int"),
                                    GetSQLValueString($csid, "text"),
                                    GetSQLValueString('stdid', "defined", implode("','", $_POST['stdid'])));
        $ret = mysql_query($insert_string, $tams);
        
        if($ret) {
            $delete_string = sprintf("DELETE FROM result_error "
                                    . "WHERE sesid = %s "
                                    . "AND csid = %s "
                                    . "AND stdid IN ('%s')",
                                    GetSQLValueString($sesid, "int"),
                                    GetSQLValueString($csid, "text"),
                                    GetSQLValueString('stdid', "defined", implode("','", $_POST['stdid'])));
            $ret2 = mysql_query($delete_string, $tams);
            
            if($ret2) {
                mysql_query("COMMIT", $tams);
                $status = count($_POST['stdid']).' result(s) restored successfully!';
            }else {
                mysql_query("ROLLBACK", $tams);
                $status = 'There was a problem restoring the result(s) selected!';
            }
            
        }else {
            $status = 'There was a problem restoring the result(s) the selected!';
        }
        
    }else {
        $status = 'You did not select any result to restore!';
    }         
}

$query_course = sprintf("SELECT SUM(percentPaid) AS total, c.csname, c.csid, s.lname, s.fname, r.stdid, r.date, "
                        . "r.tscore, r.escore "
                        . "FROM course c "
                        . "JOIN result_error r ON c.csid = r.csid AND r.csid = %s AND r.sesid = %s "
                        . "LEFT JOIN student s ON s.stdid = r.stdid "
                        . "LEFT JOIN schfee_transactions st ON r.stdid = st.matric_no AND st.sesid = r.sesid "
                        . "AND st.status = 'APPROVED' GROUP BY r.stdid", 
                        GetSQLValueString($csid, "text"), 
                        GetSQLValueString($sesid, "int"));
$course = mysql_query($query_course, $tams) or die(mysql_error());
$row_course = mysql_fetch_assoc($course);
$totalRows_course = mysql_num_rows($course);

$checks = [];
$paidCount = 0;
if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")) {
    doLogout( $site_root );  
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" ng-app="ngTams">
    <!-- InstanceBegin template="/Templates/template.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php require('../param/site.php'); ?>
<title><?php echo $university ?> </title>
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/tams.js"></script>
<script type="text/javascript" src="../scripts/angular/angular.min.js"></script>
<script src="../scripts/bootstrap-modal.js"></script>
<!-- InstanceEndEditable -->
<link href="../css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
<link href="../css/menulink.css" rel="stylesheet" type="text/css" />
<link href="../css/footer.css" rel="stylesheet" type="text/css" />
<link href="../css/sidemenu.css" rel="stylesheet" type="text/css" />
</head>

<body>
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Result Error (<?php echo $csid?>)<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
      <form action="<?php echo $editFormAction?>" method="post">
          <p><?php echo $status?></p>
          <table width="690" class="table table-striped" ng-controller="CheckController">
        <tr>
            <td colspan="6"></td>
        </tr>
        <thead>
            <tr>
                <th>Matric No.</th>
                <th>Name</th>
                <th>Test</th>
                <th>Exam</th>
                <th>Date</th>                
                <th><input type="checkbox" ng-model="checkAll" ng-click="notify()"/></th>
            </tr>
        </thead>
        <?php 
            if($totalRows_course > 0) :
                for($idx = 0; $idx < $totalRows_course; $idx++, $row_course = mysql_fetch_assoc($course)):
        ?>
        <tr>
            <td><?php echo $row_course['stdid']?></td>
            <td><?php echo $row_course['lname'].' '.$row_course['fname']?></td>
            <td><?php echo $row_course['tscore']?></td>
            <td><?php echo $row_course['escore']?></td>
            <td><?php echo date('D jS M, Y H:ia', strtotime($row_course['date']))?></td>
            <td>
                <?php if($row_course['total'] != NULL && $row_course['total'] == 100): array_push($checks, 'false')?>
                <input type="checkbox" ng-model="checks[<?php echo $paidCount++?>]" 
                       name="stdid[]" value="<?php echo $row_course['stdid']?>"/>           
                <?php endif?>
            </td>
        </tr>
        <?php endfor;?>        
        <tr>
            <td colspan="6" align="center"><input type="submit" name="restore" value="Restore Results"/></td>
        </tr>
        <?php else:?>
        <tr>
            <td colspan="5">There are no errors uploaded for this course!</td>
        </tr>
        <?php endif;?>
    </table>
      </form>
  <!-- InstanceEndEditable --></div>
<div class="footer">
    <p><!-- end .footer -->   
    
    <?php require '../include/footer.php'; ?>
	
   </p>
  </div>
  <!-- end .container -->
</div>
    
</body>
<script type="text/javascript">
    
    angular.module('ngTams', []).controller('CheckController', function($scope) {
        $scope.checkAll = false;
        $scope.checks = [<?php echo implode(',', $checks) ?>];
        
        $scope.notify = function() {
            angular.forEach($scope.checks, function(value, key) {
                this[key] = !$scope.checkAll;
            }, $scope.checks);
        };
        
    });
</script>
<!-- InstanceEnd --></html>
