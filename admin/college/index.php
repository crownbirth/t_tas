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
require_once('../../param/param.php');
require_once('../../functions/function.php');
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
<?php require('../../param/site.php'); ?>
<title><?php echo $university ?> </title>
<script src="../../SpryAssets/SpryCollapsiblePanel.js" type="text/javascript"></script>
<script src="../../SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
<link href="../../SpryAssets/SpryCollapsiblePanel.css" rel="stylesheet" type="text/css" />
<!-- InstanceEndEditable -->
<link href="../../css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Update <?php echo $college_name;?> in the University<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
      <tr>
        <td>&nbsp;
          <div id="CollapsiblePanel1" class="CollapsiblePanel">
            <div class="CollapsiblePanelTab" tabindex="0">Create New <?php echo $college_name;?></div>
            <div class="CollapsiblePanelContent">
                <form action="<?php echo $editFormAction; ?>" method="post" name="form2" id="form2">
                  <table align="center">
                    <tr valign="baseline">
                      <td colspan="2" nowrap="nowrap">&nbsp;
                      	<?php 					  		
                       		statusMsg();
                      ?>
                      </td>
                    </tr>
                    <tr valign="baseline">
                      <td nowrap="nowrap" align="right"><?php echo $college_name;?> Name:</td>
                      <td><span id="sprytextfield1">
                        <label for="colname"></label>
                        <input name="colname" type="text" id="colname" size="50" />
                      <span class="textfieldRequiredMsg">Enter a valid name.</span></span></td>
                    </tr>
                    <tr valign="baseline">
                      <td nowrap="nowrap" align="right"><?php echo $college_name;?> Code:</td>
                      <td><span id="sprytextfield2">
                        <label for="colcode"></label>
                        <input name="colcode" type="text" id="colcode" size="15" />
                      <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                    </tr>
                    <tr valign="baseline">
                      <td nowrap="nowrap" align="right"><?php echo $college_name;?> Title:</td>
                      <td><span id="sprytextfield3">
                      <label for="coltitle"></label>
                        <input type="text" name="coltitle" id="coltitle" />
                      <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                    </tr>
                    <tr valign="baseline">
                      <td nowrap="nowrap" align="right" valign="top">Remark:</td>
                      <td><textarea name="remark" cols="50" rows="5"></textarea></td>
                    </tr>
                    <tr valign="baseline">
                      <td colspan="2" align="center" nowrap="nowrap"><input type="submit" value="Add <?php echo $college_name;?>" /></td>
                    </tr>
                  </table>
                  <input type="hidden" name="colid" value="" />
                  <input type="hidden" name="MM_insert" value="form2" />
              </form>
                <p>&nbsp;</p>
            </div>
          </div>
        <p>&nbsp;</p></td>
      </tr>
      <tr>
        <td><table width="683" border="0">
          <tr>
            <td width="40">Code</td>
            <td width="364" class="colspace">Name</td>
            <td width="115">&nbsp;</td>
            <td width="44">&nbsp;</td>
            <td width="58">&nbsp;</td>
          </tr>
          <?php if ($totalRows_rscol > 0) { // Show if recordset not empty ?>
  <?php do { ?>
    <tr>
      <td><?php echo $row_rscol['colcode']; ?></td>
      <td><?php echo $row_rscol['colname']; ?></td>
      <td> <a href="../department/?cid=<?php echo $row_rscol['colid']; ?>">Add Department</a></td>
      <td><a href="college.php?cid=<?php echo $row_rscol['colid']; ?>">Edit</a></td>
      <td>Delete</td>
    </tr>
    <?php } while ($row_rscol = mysql_fetch_assoc($rscol)); ?>
            <?php } // Show if recordset not empty ?>
        </table></td>
      </tr>
    </table>
    <script type="text/javascript">
var CollapsiblePanel1 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel1", {contentIsOpen:false});
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2");
var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3");
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
mysql_free_result($rscol);
?>
