<?php require_once('Connections/tams.php'); ?>
<?php require_once('Connections/tams.php'); ?>
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

$currentPage = $_SERVER["PHP_SELF"];

$maxRows_recal = 1;
$pageNum_recal = 0;
if (isset($_GET['pageNum_recal'])) {
  $pageNum_recal = $_GET['pageNum_recal'];
}
$startRow_recal = $pageNum_recal * $maxRows_recal;

mysql_select_db($database_tams, $tams);
$query_recal = "SELECT caltitle, calbody FROM calendar";
$query_limit_recal = sprintf("%s LIMIT %d, %d", $query_recal, $startRow_recal, $maxRows_recal);
$recal = mysql_query($query_limit_recal, $tams) or die(mysql_error());
$row_recal = mysql_fetch_assoc($recal);

if (isset($_GET['totalRows_recal'])) {
  $totalRows_recal = $_GET['totalRows_recal'];
} else {
  $all_recal = mysql_query($query_recal);
  $totalRows_recal = mysql_num_rows($all_recal);
}
$totalPages_recal = ceil($totalRows_recal/$maxRows_recal)-1;

$queryString_recal = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_recal") == false && 
        stristr($param, "totalRows_recal") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_recal = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_recal = sprintf("&totalRows_recal=%d%s", $totalRows_recal, $queryString_recal);
 session_start(); 

require('param/site.php'); 
require_once('functions/function.php');
require_once('param/param.php');
if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout(); 
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/template.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $university ?> </title>
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Calendar<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include 'include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
      <tr>
        <td align="center"><?php do { ?>
            <table width="600" border="0" align="center">
              <tr>
                <td class="newstitle"><?php echo $row_recal['caltitle']; ?></td>
              </tr>
              <tr>
                <td><?php echo $row_recal['calbody']; ?></td>
              </tr>
            </table>
          <a href="<?php printf("%s?pageNum_recal=%d%s", $currentPage, max(0, $pageNum_recal - 1), $queryString_recal); ?>">Previous</a> | <a href="<?php printf("%s?pageNum_recal=%d%s", $currentPage, min($totalPages_recal, $pageNum_recal + 1), $queryString_recal); ?>">Next</a><br />
          <?php } while ($row_recal = mysql_fetch_assoc($recal)); ?></td>
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
<?php
mysql_free_result($recal);


?>
