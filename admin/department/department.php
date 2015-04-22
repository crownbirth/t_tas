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

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
  $updateSQL = sprintf("UPDATE department SET deptname=%s, deptcode=%s, colid=%s, remark=%s WHERE deptid=%s",
                       GetSQLValueString($_POST['deptname'], "text"),
                       GetSQLValueString($_POST['deptcode'], "text"),
                       GetSQLValueString($_POST['colid'], "int"),
                       GetSQLValueString($_POST['remark'], "text"),
                       GetSQLValueString($_POST['deptid'], "int"));

  mysql_select_db($database_tams, $tams);
  $Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());
	
   
  $updateGoTo = "department.php";
  if( $Result1 )
  	$updateGoTo = ( isset( $_GET['success'] ) ) ? $updateGoTo : $updateGoTo."?success";
  else
	$updateGoTo = ( isset( $_GET['error'] ) ) ? $updateGoTo : $updateGoTo."?error";

  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
}

$colname_editdept = "-1";
if (isset($_GET['did'])) {
  $colname_editdept = $_GET['did'];
}
mysql_select_db($database_tams, $tams);
$query_editdept = sprintf("SELECT * FROM department WHERE deptid = %s", GetSQLValueString($colname_editdept, "int"));
$editdept = mysql_query($query_editdept, $tams) or die(mysql_error());
$row_editdept = mysql_fetch_assoc($editdept);
$totalRows_editdept = mysql_num_rows($editdept);

$maxRows_coldept = 10;
$pageNum_coldept = 0;
if (isset($_GET['pageNum_coldept'])) {
  $pageNum_coldept = $_GET['pageNum_coldept'];
}
$startRow_coldept = $pageNum_coldept * $maxRows_coldept;

$colname_coldept = "-1";
if (isset($_GET['cid'])) {
  $colname_coldept = $_GET['cid'];
}
mysql_select_db($database_tams, $tams);
$query_coldept = sprintf("SELECT deptid, deptname, deptcode FROM department WHERE colid = %s", GetSQLValueString($colname_coldept, "int"));
$query_limit_coldept = sprintf("%s LIMIT %d, %d", $query_coldept, $startRow_coldept, $maxRows_coldept);
$coldept = mysql_query($query_limit_coldept, $tams) or die(mysql_error());
$row_coldept = mysql_fetch_assoc($coldept);

if (isset($_GET['totalRows_coldept'])) {
  $totalRows_coldept = $_GET['totalRows_coldept'];
} else {
  $all_coldept = mysql_query($query_coldept);
  $totalRows_coldept = mysql_num_rows($all_coldept);
}
$totalPages_coldept = ceil($totalRows_coldept/$maxRows_coldept)-1;

mysql_select_db($database_tams, $tams);
$query_col = "SELECT colid, coltitle FROM college";
$col = mysql_query($query_col, $tams) or die(mysql_error());
$row_col = mysql_fetch_assoc($col);
$totalRows_col = mysql_num_rows($col);
$collegename = ""
?>
<?php 
require_once('../../param/param.php');
require_once('../../functions/function.php');

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
<script src="../../SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Update <?php echo $row_editdept['deptname'];?> Department<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
      <tr>
        <td>
        	<form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
              <table align="center">
                <tr valign="baseline">
                  <td colspan="2" nowrap="nowrap">
                  	<?php 					  		
                       		statusMsg();
                      ?>
                  </td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Department Name:</td>
                  <td><span id="sprytextfield1">
                    <label for="deptname"></label>
                    <input type="text" name="deptname" id="deptname" value="<?php echo htmlentities($row_editdept['deptname'], ENT_COMPAT, 'utf-8'); ?>" size="32" />
                  <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Department Code:</td>
                  <td><span id="sprytextfield2">
                    <label for="deptcode"></label>
                    <input type="text" name="deptcode" id="deptcode" value="<?php echo htmlentities($row_editdept['deptcode'], ENT_COMPAT, 'utf-8'); ?>" size="32"/>
                  <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">College Name:</td>
                  <td><select name="colid">
                    <?php
do {  
?>
                    <option value="<?php echo $row_col['colid']?>" <?php if (!(strcmp($row_col['colid'], $row_editdept['colid']))) {echo "selected=\"selected\""; $collegename = $row_col['coltitle']; } ?>><?php echo $row_col['coltitle']?></option>
                    <?php
} while ($row_col = mysql_fetch_assoc($col));
  $rows = mysql_num_rows($col);
  if($rows > 0) {
      mysql_data_seek($col, 0);
	  $row_col = mysql_fetch_assoc($col);
  }
?>
                  </select></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right" valign="top">Remark:</td>
                  <td><textarea name="remark" cols="50" rows="5"><?php echo htmlentities($row_editdept['remark'], ENT_COMPAT, 'utf-8'); ?></textarea></td>
                </tr>
                <tr valign="baseline">
                  <td colspan="2" align="center" nowrap="nowrap"><input type="submit" value="Update Department" /></td>
                </tr>
              </table>
              <input type="hidden" name="deptid" value="<?php echo $row_editdept['deptid']; ?>" />
              <input type="hidden" name="MM_update" value="form1" />
            </form>
        </td>
      </tr>
      <tr>
      	<td>Department(s) in <?php echo $collegename?></td>
      </tr>
      <tr>
      	<td>
          <table width="683" border="0">
            <tr>
              <td width="40">Code </td>
              <td width="364">Name</td>
              <td width="115">&nbsp;</td>
              <td width="44">&nbsp;</td>
              <td width="58">&nbsp;</td>
            </tr>
            <?php if ($totalRows_coldept > 0) { // Show if recordset not empty ?>
  <?php do { ?>
    <tr>
      <td><?php echo $row_coldept['deptcode']; ?></td>
      <td><?php echo $row_coldept['deptname']; ?></td>
      <td><a href="../programme/?did=<?php echo $row_coldept['deptid']; ?>">Add Programme</a></td>
      <td><a href="department.php?did=<?php echo $row_coldept['deptid']; ?>&cid=<?php echo $_GET['cid'];?>">Edit</a></td>
      <td>Delete</td>
    </tr>
    <?php } while ($row_coldept = mysql_fetch_assoc($coldept)); ?>
              <?php } // Show if recordset not empty ?>
          </table></td>
      </tr>
    </table>
    <script type="text/javascript">
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2");
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
mysql_free_result($editdept);

mysql_free_result($coldept);

mysql_free_result($col);
?>
