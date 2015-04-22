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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO programme (progid, progname, deptid, duration, progcode) VALUES (%s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['progid'], "int"),
                       GetSQLValueString($_POST['progname'], "text"),
                       GetSQLValueString($_POST['deptid'], "int"),
                       GetSQLValueString($_POST['duration'], "int"),
                       GetSQLValueString($_POST['progcode'], "text"));

  mysql_select_db($database_tams, $tams);
  $Result1 = mysql_query($insertSQL, $tams) or die(mysql_error());
	
	 $insertGoTo = "../department/index.php";
  if( $Result1 )
  	$insertGoTo = ( isset( $_GET['success'] ) ) ? $insertGoTo : $insertGoTo."?success";
  else
	$insertGoTo = ( isset( $_GET['error'] ) ) ? $insertGoTo : $insertGoTo."?error";

  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
}

$colname_dept = "-1";
if (isset($_GET['did'])) {
  $colname_dept = $_GET['did'];
}
mysql_select_db($database_tams, $tams);
$query_dept = sprintf("SELECT deptid, deptname FROM department WHERE deptid = %s", GetSQLValueString($colname_dept, "int"));
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);

mysql_select_db($database_tams, $tams);
$query_opts = "SELECT deptid, deptname FROM department";
$opts = mysql_query($query_opts, $tams) or die(mysql_error());
$row_opts = mysql_fetch_assoc($opts);
$totalRows_opts = mysql_num_rows($opts);

$colname_deptprog = "-1";
if (isset($_GET['did'])) {
  $colname_deptprog = $_GET['did'];
}
mysql_select_db($database_tams, $tams);
$query_deptprog = sprintf("SELECT progid, progname, progcode FROM programme WHERE deptid = %s", GetSQLValueString($colname_deptprog, "int"));
$deptprog = mysql_query($query_deptprog, $tams) or die(mysql_error());
$row_deptprog = mysql_fetch_assoc($deptprog);
$totalRows_deptprog = mysql_num_rows($deptprog);
?>
<?php
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
<script src="../SpryAssets/SpryCollapsiblePanel.js" type="text/javascript"></script>
<link href="../SpryAssets/SpryCollapsiblePanel.css" rel="stylesheet" type="text/css" />
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Add Programme<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
    <tr>
      <td>
          <table width="690">
            
            <tr>
              <td><table width="683" border="0">
                <tr>
                  <td colspan="4">Programme(s) in <?php echo $row_dept['deptname'];?></td>
                </tr>
                <tr>
                  <td width="40">Code</td>
                  <td width="364" class="colspace">Name</td>
                  <td width="115">&nbsp;</td>
                  <td width="44">&nbsp;</td>
                  </tr>
                <tr>
                  <td><?php echo $row_deptprog['progcode']; ?></td>
                  <td><?php echo $row_deptprog['progname']; ?></td>
                  <td><a href="../course/?pid=<?php echo $row_deptprog['progid']?>">Add Courses</a></td>
                  <td><?php $access = array(1,2); if( in_array(getAccess(),$access)){?><a href="programme.php?pid=<?php echo $row_deptprog['progid']?>&did=<?php echo $_GET['did']?>">Edit</a><?php }?></td>
                  </tr>
              </table></td>
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
<?php
mysql_free_result($dept);

mysql_free_result($opts);

mysql_free_result($deptprog);
?>
