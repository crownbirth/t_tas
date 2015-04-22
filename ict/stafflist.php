<?php require_once('../Connections/tams.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "20,21,23";
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
require_once('../param/param.php');
require_once('../functions/function.php');


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

$query_staff = "";
        
if(isset($_GET['did'])) {
    $deptid = $_GET['did'];
    $query_staff = sprintf("SELECT l.lectid, l.fname, l.lname, l.email, l.phone 
                        FROM lecturer l 
                        JOIN department d ON d.deptid = l.deptid 
                        WHERE d.deptid = %s",
                        GetSQLValueString($deptid, "int"));

    
    $query_info = sprintf("SELECT deptname as name
                        FROM department
                        WHERE deptid = %s",
                        GetSQLValueString($deptid, "int"));
}

if(isset($_GET['cid'])) {
    $colid = $_GET['cid'];
    $query_staff = sprintf("SELECT l.lectid, l.fname, l.lname, l.email, l.phone 
                        FROM lecturer l 
                        JOIN department d ON d.deptid = l.deptid 
                        JOIN college c ON c.colid = d.colid 
                        WHERE c.colid = %s",
                        GetSQLValueString($colid, "int"));


    $query_info = sprintf("SELECT colname as name 
                        FROM college
                        WHERE colid = %s",
                        GetSQLValueString($colid, "int"));
}

$staff = mysql_query($query_staff, $tams) or die(mysql_error());
$row_staff = mysql_fetch_assoc($staff);
$totalRows_staff = mysql_num_rows($staff);


$info = mysql_query($query_info, $tams) or die(mysql_error());
$row_info = mysql_fetch_assoc($info);
$totalRows_info = mysql_num_rows($info);

$name = $row_info['name'];

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")) {
	doLogout($site_root.'/ict');  
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/icttemplate.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php require('../param/site.php'); ?>
<title><?php echo $university ?> </title>
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/tams.js"></script>
<!-- InstanceEndEditable -->
<link href="css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
<link href="css/menulink.css" rel="stylesheet" type="text/css" />
<link href="css/footer.css" rel="stylesheet" type="text/css" />
<link href="css/sidemenu.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div class="container">
  <div class="header">
    <!-- end .header -->
</div>
  <div class="topmenu">
<?php include 'include/topmenu.php'; ?>
  </div>
  <!-- end .topmenu --> 
  
  <div class="loginuser">
  <?php include 'include/loginuser.php'; ?>
  
  <!-- end .loginuser --></div>
  <div class="pagetitle">
    <table width="600">
      <tr>
        <td><!-- InstanceBeginEditable name="pagetitle" -->Staff in <?php echo $name?><!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include 'include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
      <table width="679" border="0" class="mytext">
          <tr>
              <td>
                  <table width="670" class="table table-striped">
                      <thead>
                          <tr>
                            <th>S/N</th>
                            <th>Staff Id</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php 
                            if($totalRows_staff > 0){ $i = 1;
                                do{
                          ?>
                          <tr>
                              <td><?php echo $i++;?></td>
                              <td>
                                  <a href="../staff/profile.php?lid=<?php echo $row_staff['lectid']?>">
                                      <?php echo $row_staff['lectid']?>
                                  </a>
                              </td>
                              <td>
                                  <?php echo "{$row_staff['fname']} {$row_staff['lname']}" ;?>
                              </td>
                              <td><?php echo (isset($row_staff['phone']))?  $row_staff['phone'] : '-';?></td>
                              <td><?php echo (isset($row_staff['email']))?  $row_staff['email'] : '-';?></td>
                          </tr>
                          <?php                           
                                  }while($row_staff = mysql_fetch_assoc($staff));
                                  
                            }else{
                          ?>
                          <tr>
                              <td colspan="5">No record available!</td>
                          </tr>
                          <?php 
                            }
                          ?>
                      </tbody>
                  </table>
              </td>
          </tr>
      
    </table>
                     
  <!-- InstanceEndEditable --></div>
<div class="footer">
    <p><!-- end .footer -->   
    
    <?php require 'include/footer.php'; ?>
	
   </p>
  </div>
  <!-- end .container -->
</div>
</body>
<!-- InstanceEnd --></html>