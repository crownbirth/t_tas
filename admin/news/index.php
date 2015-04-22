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

$MM_restrictGoTo = "../../index.php";
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
<?php require_once('../../Connections/tams.php');
require_once('../../param/param.php');
require_once('../../functions/function.php');

define ('MAX_FILE_SIZE', 1024 * 150);
define('UPLOAD_DIR', '../images/news/');


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

$file = "";
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {

  $insertSQL = sprintf("INSERT INTO news (id, `date`, title, article) VALUES (%s, %s, %s, %s)",
                       GetSQLValueString($_POST['id'], "int"),
                       GetSQLValueString($_POST['date'], "text"),
                       GetSQLValueString($_POST['titile'], "text"),
                       GetSQLValueString($_POST['aticle'], "text"));

  mysql_select_db($database_tams, $tams);
  $Result1 = mysql_query($insertSQL, $tams) or die(mysql_error());
  
  $upload = "";
  $id = mysql_insert_id($tams);
  if( $Result1 ){
		$upload = uploadFile( UPLOAD_DIR, "news", MAX_FILE_SIZE, $id);
	}

  $ext = substr( $_FILES['filename']['name'], strrpos($_FILES['filename']['name'],'.') );
  $file = "news_".$id.$ext;
  $insertSQL = sprintf("UPDATE news SET image=%s WHERE id=%s",
                       GetSQLValueString($file, "text"),
                       GetSQLValueString($id, "int"));

  mysql_select_db($database_tams, $tams);
  $Result1 = mysql_query($insertSQL, $tams) or die(mysql_error());

  $insertGoTo = "newsadmin.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
 // header(sprintf("Location: %s", $insertGoTo));
}

mysql_select_db($database_tams, $tams);
$query_Rsnews = "SELECT id, `date`, title FROM news";
$Rsnews = mysql_query($query_Rsnews, $tams) or die(mysql_error());
$row_Rsnews = mysql_fetch_assoc($Rsnews);
$totalRows_Rsnews = mysql_num_rows($Rsnews);

if (!isset($_SESSION)) {
 // session_start();
}

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
<?php require('../../param/site.php'); ?>
<title><?php echo $university ?> </title>
<!-- InstanceEndEditable -->
<link href="../../css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<script src="../../SpryAssets/SpryCollapsiblePanel.js" type="text/javascript"></script>
<link href="../../SpryAssets/SpryCollapsiblePanel.css" rel="stylesheet" type="text/css" />
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Add / Edit News<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
      <tr>
        <td><ul>
          <li><a href="index.php">List News</a></li>
          </ul>
          <div id="CollapsiblePanel1" class="CollapsiblePanel">
            <div class="CollapsiblePanelTab" tabindex="0">Add news</div>
            <div class="CollapsiblePanelContent">
              <h4>Add News</h4>
              <form action="<?php echo $editFormAction; ?>" method="POST" enctype="multipart/form-data" name="form1" id="form1">
                <table width="562" border="0">
                  <tr>
                    <td width="115" align="center">Title:</td>
                    <td width="437"><label for="titile"></label>
                    <input name="titile" type="text" id="titile" size="70" /></td>
                  </tr>
                  <tr>
                    <td align="center">Upload Image:</td>
                    <td><label for="filename"></label>
                    <input name="filename" type="file" id="image" size="60" /></td>
                  </tr>
                  <tr>
                    <td>&nbsp;</td>
                    <td><label for="aticle"></label>
                    <textarea name="aticle" id="aticle" cols="68" rows="5" class="widgEditor nothing"></textarea></td>
                  </tr>
                  <tr>
                    <td><input type="hidden" name="date" id="date" value="<?php echo date('F j,Y g:i a');?>" />
                    <input type="hidden" name="id" value="" />
                    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MAX_FILE_SIZE; ?>" /></td>
                    <td><input type="submit" name="upload" id="upload" value="Submit" /></td>
                  </tr>
                </table>
                <input type="hidden" name="MM_insert" value="form1" />
              </form>
              <p>&nbsp;</p>
            </div>
          </div>
        <p>&nbsp;</p>
        <table width="600" border="1" align="center">
          <tr>
            <td width="130" height="68"><?php if( isset($file) ){?><img src=<?php echo (UPLOAD_DIR . $file);?> class="newsimg" /><?php }?></td>
            <td width="454"><?php if (isset($result)) {
  echo "<p><strong> $upload</strong></p>";
} 
?></td>
          </tr>
        </table>
        <p>&nbsp;</p>
        <table width="680" border="0" align="center">
          <tr>
            <td width="280" align="center"><h3>News Title </h3></td>
            <td width="163" align="center"><h3>Date Posted</h3></td>
            <td width="143" align="center"><h3>Actions</h3></td>
          </tr>
        </table>
        <?php do { ?>
          <div class="split" id="split">
            <table width="680" border="0" align="center">
              <tr align="center">
                <td width="279"><strong><?php echo $row_Rsnews['title']; ?></strong></td>
                <td width="161"><?php echo $row_Rsnews['date']; ?></td>
                <td width="70"><a href="editnews.php?id=<?php echo $row_Rsnews['id'];?>">Edit</a></td>
                <td width="72"><a href="#">Delete</a></td>
              </tr>
            </table>
          </div>
          <?php } while ($row_Rsnews = mysql_fetch_assoc($Rsnews)); ?>
<p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p></td>
      </tr>
    </table>
    <script type="text/javascript">
var CollapsiblePanel1 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel1", {contentIsOpen:false});
    </script>
  <!-- InstanceEndEditable --></div>
<div class="footer">
    <p><!-- end .footer -->   
    
    <?php require '../../include/footer.php'; ?>
	
   </p>
  </div>
  <!-- end .container -->
</div>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($Rsnews);
?>
