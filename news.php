<?php require_once('Connections/tams.php'); ?>
<?php define('UPLOAD_DIR','images/news/');?>
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

$maxRows_rsNews = 3;
$pageNum_rsNews = 0;
if (isset($_GET['pageNum_rsNews'])) {
  $pageNum_rsNews = $_GET['pageNum_rsNews'];
}
$startRow_rsNews = $pageNum_rsNews * $maxRows_rsNews;

mysql_select_db($database_tams, $tams);
$query_rsNews = "SELECT * FROM news ORDER BY id DESC";
$query_limit_rsNews = sprintf("%s LIMIT %d, %d", $query_rsNews, $startRow_rsNews, $maxRows_rsNews);
$rsNews = mysql_query($query_limit_rsNews, $tams) or die(mysql_error());
$row_rsNews = mysql_fetch_assoc($rsNews);

if (isset($_GET['totalRows_rsNews'])) {
  $totalRows_rsNews = $_GET['totalRows_rsNews'];
} else {
  $all_rsNews = mysql_query($query_rsNews);
  $totalRows_rsNews = mysql_num_rows($all_rsNews);
}
$totalPages_rsNews = ceil($totalRows_rsNews/$maxRows_rsNews)-1;

$queryString_rsNews = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsNews") == false && 
        stristr($param, "totalRows_rsNews") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsNews = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsNews = sprintf("&totalRows_rsNews=%d%s", $totalRows_rsNews, $queryString_rsNews);
 session_start(); 
require('param/site.php'); 
require_once('param/param.php');
require_once('functions/function.php');
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->News Update<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">

    <?php include 'include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
    <tr>
    <?php if( getAccess() == 1){?>
    <td align="right"><a href="admin/news/index.php">Add/Edit News</a>
    <?php }?>
    </td>
    </tr>
      <tr>
        <td><?php do { ?>
            <div id="split">
              <table width="680" border="0">
                <tr>
                  <td colspan="2" align="left"><p class="newstitle"><strong><?php echo $row_rsNews['title']; ?></strong></p></td>
                </tr>
                <tr>
                  <td width="137" valign="top"><table width="137" border="0">
                      <tr>
                        <td width="131"><img src="<?php echo UPLOAD_DIR.$row_rsNews['image']; ?>" alt="" width="132" height="130" class="newsimg" /></td>
                      </tr>
                  </table></td>
                  <td valign="top"><?php echo $row_rsNews['article']; ?></td>
                </tr>
                <tr>
                  <td colspan="2"><em>Posted:<?php echo $row_rsNews['date']; ?></em>
                  <hr /></td>
                </tr>
              </table>
            </div>
            <?php } while ($row_rsNews = mysql_fetch_assoc($rsNews)); ?></td>
      </tr>
    </table>
    <table align="center"><tr>
          <td colspan="8" align="center"><a href="<?php printf("%s?pageNum_rsNews=%d%s", $currentPage, max(0, $pageNum_rsNews - 1), $queryString_rsNews); ?>">Previous News</a> | <a href="<?php printf("%s?pageNum_rsNews=%d%s", $currentPage, min($totalPages_rsNews, $pageNum_rsNews + 1), $queryString_rsNews); ?>">Next News</a></td>
          </tr></table>
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
mysql_free_result($rsNews);
?>
