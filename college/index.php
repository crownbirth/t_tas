<?php require_once('../Connections/tams.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
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

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form2")) {
  $insertSQL = sprintf("INSERT INTO college (colid, colname, colcode, coltitle, remark) VALUES (%s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['colid'], "int"),
                       GetSQLValueString($_POST['colname'], "text"),
                       GetSQLValueString($_POST['colcode'], "text"),
                       GetSQLValueString($_POST['coltitle'], "text"),
                       GetSQLValueString($_POST['remark'], "text"));

  mysql_select_db($database_tams, $tams);
  $Result1 = mysql_query($insertSQL, $tams) or die(mysql_error());
  
  $insertGoTo = "index.php";
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

$maxRows_rscol = 10;
$pageNum_rscol = 0;
if (isset($_GET['pageNum_rscol'])) {
  $pageNum_rscol = $_GET['pageNum_rscol'];
}
$startRow_rscol = $pageNum_rscol * $maxRows_rscol;

mysql_select_db($database_tams, $tams);
$query_rscol = "SELECT * FROM college";
$query_limit_rscol = sprintf("%s LIMIT %d, %d", $query_rscol, $startRow_rscol, $maxRows_rscol);
$rscol = mysql_query($query_limit_rscol, $tams) or die(mysql_error());
$row_rscol = mysql_fetch_assoc($rscol);

if (isset($_GET['totalRows_rscol'])) {
  $totalRows_rscol = $_GET['totalRows_rscol'];
} else {
  $all_rscol = mysql_query($query_rscol);
  $totalRows_rscol = mysql_num_rows($all_rscol);
}
$totalPages_rscol = ceil($totalRows_rscol/$maxRows_rscol)-1;
?>
<?php 
require_once('../param/param.php');
require_once('../functions/function.php');
//session_start();
 
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
<script src="../SpryAssets/SpryCollapsiblePanel.js" type="text/javascript"></script>
<link href="../SpryAssets/SpryCollapsiblePanel.css" rel="stylesheet" type="text/css" />
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
        <td><!-- InstanceBeginEditable name="pagetitle" --><?php echo $college_name;?> in the University<!-- InstanceEndEditable --></td>
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
        <p>
        <?php echo $college_page_top_content; ?> </p>
        <br />
        </td>
    </tr>
      <tr>
        <td>
          <?php if ($totalRows_rscol > 0) { // Show if recordset not empty ?>
          <ul>
  <?php do { ?>
  <li>
    <a href="college.php?cid=<?php echo $row_rscol['colid']; ?>"><?php echo $row_rscol['colname']; ?></a>
		  <?php $access = array(1,2);if( in_array(getAccess(),$access) && ( getAccess() == 1 || getSessionValue('cid') == $row_rscol['colid']) ){?>
          |<a href="coledit.php?cid=<?php echo $row_rscol['colid'];?>"> Edit</a>
          </li>
          <?php } ?>
    <?php } while ($row_rscol = mysql_fetch_assoc($rscol)); ?>
    </ul>
            <?php ;} // Show if recordset not empty ?>
        </td>
      </tr>
      <tr>
    	<td> 
        <?php echo $college_page_bottom_content; ?>
        </td>
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
mysql_free_result($rscol);
?>
