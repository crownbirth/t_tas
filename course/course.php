<?php require_once('../Connections/tams.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
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

$colname_course = "-1";
if (isset($_GET['csid'])) {
  $colname_course = $_GET['csid'];
}

$colname_course1 = "-1";
if (isset($_GET['pid'])) {
  $colname_course1 = $_GET['pid'];
}

$query = "SELECT c.*, d.deptname FROM course c, department d WHERE c.deptid = d.deptid AND csid = %s";

//echo $_SERVER['HTTP_REFERER'];
if( isset( $_GET['pid'] ) )
	$query = "SELECT c.*, dc.status, dc.unit, d.deptname FROM course c, department d, department_course dc WHERE c.csid = dc.csid AND c.deptid = d.deptid AND dc.progid = ".$colname_course1." AND c.csid = %s";
mysql_select_db($database_tams, $tams);
$query_course = sprintf($query, GetSQLValueString($colname_course, "text"));
$course = mysql_query($query_course, $tams) or die(mysql_error());
$row_course = mysql_fetch_assoc($course);
$totalRows_course = mysql_num_rows($course);

if (!isset($_SESSION)) {
  session_start();
}
require_once('../param/param.php');
require_once('../functions/function.php');


$sub = false;
$path = pathinfo( $_SERVER['SCRIPT_FILENAME']);

if( $path['dirname'] != $_SERVER['DOCUMENT_ROOT'].$site_root )
	$sub = true;
	

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );  
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/template.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php require('../param/site.php'); ?>
<title><?php echo $university ?> </title>
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Course Details<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
      <tr>
        <td align="right"><a href="../course/coursehistory.php?csid=<?php echo $row_course['csid']?>">Teaching History</a></td>
      </tr>
      <tr>
        <td>
        <table width="665" border="0" align="center">
          <tr>
            <td width="178">Course Code</td>
            <td width="470"><?php echo $row_course['csid']; ?></td>
          </tr>
          <tr>
            <td>Course Name</td>
            <td><?php echo $row_course['csname']; ?></td>
          </tr>
          <tr>
            <td>Semester</td>
            <td> <?php echo getSemester($row_course['semester']); ?></td>
          </tr>
          <tr>
            <td>Course Type</td>
            <td><?php echo $row_course['type']; ?></td>
          </tr>
          <tr>
            <td>Host Department</td>
            <td><a href="../department/department.php?did=<?php echo $row_course['deptid']?>"><?php echo $row_course['deptname']; ?></a></td>
          </tr>
          <?php if( isset( $_GET['pid'] ) ){?>          
          <tr>
            <td>Status</td>
            <td><?php echo $row_course['status']; ?></td>
          </tr>
          <tr>
            <td>Unit</td>
            <td><?php echo $row_course['unit']; ?></td>
          </tr>
          <?php }?>
          <tr>
            <td>Course Content</td>
            <td><?php echo $row_course['cscont']; ?></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
        </table></td>
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
</body>
<!-- InstanceEnd --></html>