<?php require_once('../Connections/tams.php'); ?>
<?php
require_once('../param/param.php');
require_once('../functions/function.php');

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

mysql_select_db($database_tams, $tams);
$query_sess = "SELECT * FROM `session` ORDER BY sesname DESC";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

$colname_crs = "-1";
if (isset($row_sess['sesid'])) {
  $colname_crs = $row_sess['sesid'];
}

if (isset($_GET['sid'])) {
  $colname_crs = $_GET['sid'];
}

$colname1_crs = "-1";
if (isset($_GET['csid'])) {
  $colname1_crs = $_GET['csid'];
}
mysql_select_db($database_tams, $tams);
$query_crs = sprintf("SELECT r.stdid, s.lname, s.fname FROM `result` r, student s WHERE r.stdid = s.stdid AND r.sesid = %s AND r.csid=%s", 
					GetSQLValueString($colname_crs, "int"), 
					GetSQLValueString($colname1_crs, "text"));
$crs = mysql_query($query_crs, $tams) or die(mysql_error());
$row_crs = mysql_fetch_assoc($crs);
$totalRows_crs = mysql_num_rows($crs);

$sub = false;
$path = pathinfo( $_SERVER['SCRIPT_FILENAME']);

if( $path['dirname'] != $_SERVER['DOCUMENT_ROOT'].$site_root )
	$sub = true;
	

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );  
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/template.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php require('../param/site.php'); ?>
<title><?php echo $university ?> </title>
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/tams.js"></script>
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Registered Students for <?php echo $_GET['csid']?><!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
    	<tr>
          <td colspan="5"></td>
        </tr>
        <tr>
          <td colspan="5">
          	Session &nbsp;&nbsp;            
          	<select name="sesid" onchange="sesfilt(this)">
          	  <?php
do {  
?>
          	  <option value="<?php echo $row_sess['sesid']?>"<?php if (!(strcmp($row_sess['sesid'], $colname_crs))) {echo "selected=\"selected\"";} ?>><?php echo $row_sess['sesname']?></option>
          	  <?php
} while ($row_sess = mysql_fetch_assoc($sess));
  $rows = mysql_num_rows($sess);
  if($rows > 0) {
      mysql_data_seek($sess, 0);
	  $row_sess = mysql_fetch_assoc($sess);
  }
?>
            </select>
            &nbsp;&nbsp;
            <?php echo $totalRows_crs?> registered students
          </td>
        </tr>
      <?php if( $totalRows_crs > 0 ){  //?> 
      <?php do { ?>
        <tr>
          <td><a href="../student/profile.php?stid=<?php echo $row_crs['stdid'];?>"><?php echo $row_crs['stdid'];?></a></td>
          <td><?php echo $row_crs['lname'].", ".$row_crs['fname'];?></td>
          <td><a href="viewform.php?stid=<?php echo $row_crs['stdid'];?>">View Form</a></td>
          <td></td>
          <td>&nbsp;</td>
        </tr>
        <?php } while ($row_crs = mysql_fetch_assoc($crs)); ?>
        <?php }?>
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
mysql_free_result($sess);

mysql_free_result($crs);
?>
