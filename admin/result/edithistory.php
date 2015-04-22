<?php require_once('../../Connections/tams.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "1";
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
?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
require_once('../../param/param.php');
require_once('../../functions/function.php');


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

$sesid = '';
if(isset($_GET['sid'])) {
    $sesid = $_GET['sid'];
}

$csid = '';
if(isset($_GET['csid'])) {
    $csid = $_GET['csid'];
}

$stid = '';
if(isset($_GET['stdid'])) {
    $stid = $_GET['stdid'];
}

$state = false;
$edited = false;

if($sesid && $csid && $stid) {
    $state = true;
    
    $query_crs = sprintf("SELECT csname "
                            . "FROM course "
                            . "WHERE csid=%s", 
                            GetSQLValueString($csid, "text"));
    $crs = mysql_query($query_crs, $tams) or die(mysql_error());
    $row_crs = mysql_fetch_assoc($crs);
    $totalRows_crs = mysql_num_rows($crs);
    
    $query_stud = sprintf("SELECT stdid, lname, fname "
                            . "FROM student "
                            . "WHERE stdid=%s", 
                            GetSQLValueString($stid, "text"));
    $stud = mysql_query($query_stud, $tams) or die(mysql_error());
    $row_stud = mysql_fetch_assoc($stud);
    $totalRows_stud = mysql_num_rows($stud);
    
    $query_ses = sprintf("SELECT * "
                            . "FROM session "
                            . "WHERE sesid=%s", 
                            GetSQLValueString($sesid, "text"));
    $ses = mysql_query($query_ses, $tams) or die(mysql_error());
    $row_ses = mysql_fetch_assoc($ses);
    $totalRows_ses = mysql_num_rows($ses);
    
    
    $query_edit = sprintf("SELECT * "
                            . "FROM result_log rl, result r, lecturer l "
                            . "WHERE l.lectid = rl.lectid "
                            . "AND r.csid = rl.csid "
                            . "AND r.stdid = rl.stdid "
                            . "AND r.sesid = rl.sesid "
                            . "AND r.csid=%s "
                            . "AND r.stdid=%s "
                            . "AND r.sesid=%s  "
                            . "AND r.edited = 'TRUE'", 
                            GetSQLValueString($csid, "text"),							 
                            GetSQLValueString($stid, "text"), 
                            GetSQLValueString($sesid, "int"));
    $edit = mysql_query($query_edit, $tams) or die(mysql_error());
    $row_edit = mysql_fetch_assoc($edit);
    $totalRows_edit = mysql_num_rows($edit);
    
    if($totalRows_edit > 0) {
        $edited = true;
    }
}

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );  
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/template.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php require('../../param/site.php'); ?>
<title><?php echo $university ?> </title>
<script type="text/javascript" src="../../scripts/jquery.js"></script>
<script type="text/javascript" src="../../scripts/tams.js"></script>
<!-- InstanceEndEditable -->
<link href="../../css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<style>
    
    .table thead th{
        vertical-align: top;
        
        border-left: 1px solid #ddd;
        border-right:1px solid #ddd;
    }
    
    .table thead th{
        vertical-align: top;
        
        border-left: 1px solid #ddd;
        border-right:1px solid #ddd;
    }
</style>
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Edit History<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
      <div>
          <strong>Course:</strong> <?php echo $row_crs['csname'];?> <br/>
          <strong>Student:</strong> <?php echo $row_stud['fname'].' '.$row_stud['lname'];?> - <?php echo $row_stud['stdid']?> <br/>
          <strong>Session:</strong> <?php echo $row_ses['sesname'];?> <br/>
      </div>
    <table width="679" border="0" class="mytext table table-striped">
        <thead>
            <tr>
                <th rowspan="2">S/N</th>
                <th rowspan="2">Lecturer Name</th>
                <th colspan="2">Old Scores</th>
                <th colspan="2">New Score</th>
                <th rowspan="2">Date</th>         
            </tr>
            <tr>
                <th>Test</th>                
                <th>Exam</th>
                <th>Test</th>
                <th>Exam</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                if($state) {
                    if($edited) {
                        $idx = 1;
                        do{
            ?>
            <tr>
                <td><?php echo $idx?></td>
                <td><?php echo '<a href=\'../../staff/profile.php?lectid='.$row_edit['lectid'].'\'>'.$row_edit['fname'].' '.$row_edit['lname'].'</a>'?></td>
                <td><?php echo $row_edit['old_test']?></td>
                <td><?php echo $row_edit['old_exam']?></td>
                <td><?php echo $row_edit['new_test']?></td>
                <td><?php echo $row_edit['new_exam']?></td>
                <td><?php echo date('jS F, Y', strtotime($row_edit['date']))?></td>
            </tr>
            <?php 
                            $idx++;
                        }while($row_edit = mysql_fetch_assoc($edit));
                    }else{
            ?>
            <tr>
                <td colspan="7">No edited entry for the specified parameters!</td>
            </tr>
            <?php         
                    }              
                }else {
            ?>
            <tr>
                <td colspan="7">Invalid parameters!</td>
            </tr>
            <?php }?>
        </tbody>
    </table>
  </div>
<div class="footer">
    <p><!-- end .footer -->   
    
    <?php require '../../include/footer.php'; ?>
	
   </p>
  </div>
  <!-- end .container -->
</div>
</body>
<!-- InstanceEnd --></html>