<?php require_once('../Connections/tams.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}

require_once('../param/param.php');
require_once('../functions/function.php');

$MM_authorizedUsers = "20,21";
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

$MM_restrictGoTo = "index.php";
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

$colname_Rsstaf = "-1";
if (isset($_GET['stfid'])) {
  $colname_Rsstaf = $_GET['stfid'];
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
    $query_Rsstaf = sprintf("SELECT * FROM ictstaff WHERE stfid = %s", GetSQLValueString($colname_Rsstaf, "text"));
    $Rsstaf = mysql_query($query_Rsstaf, $tams) or die(mysql_error());
    $row_Rsstaf = mysql_fetch_assoc($Rsstaf);
    $totalRows_Rsstaf = mysql_num_rows($Rsstaf);	

    $edit = array();
    $fields = array_keys($row_Rsstaf);       
    foreach($_POST as $key => $fld) {
        if(in_array($key, $fields)) {            
            if(trim($fld) != trim($row_Rsstaf[$key]))
                $edit[$key] = array('old' => trim($row_Rsstaf[$key]), 'new' => trim($fld));
        }
    }
    
    $updateSQL = sprintf("UPDATE ictstaff SET title=%s, fname=%s, lname=%s, mname=%s, dob=%s, phone=%s, email=%s, addr=%s, sex=%s, `access`=%s, profile=%s WHERE stfid=%s",
                       GetSQLValueString($_POST['title'], "text"),
                       GetSQLValueString($_POST['fname'], "text"),
                       GetSQLValueString($_POST['lname'], "text"),
                       GetSQLValueString($_POST['mname'], "text"),
                       GetSQLValueString($_POST['dob'], "text"),
                       GetSQLValueString($_POST['phone'], "text"),
                       GetSQLValueString($_POST['email'], "text"),
                       GetSQLValueString($_POST['addr'], "text"),
                       GetSQLValueString($_POST['sex'], "text"),
                       GetSQLValueString($_POST['access'], "int"),
                       GetSQLValueString($_POST['profile'], "text"),
                       GetSQLValueString($_POST['stfid'], "text"));

  
    $Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());

    $params['entid'] = $colname_Rsstaf;
    $params['enttype'] = 'ictstaff';
    $params['action'] = 'edit';
    $params['cont'] = json_encode($edit);
    audit_log($params);
    
    $updateGoTo = "addstaff.php?stfid=" . $row_Rsstaf['stfid'] . "";
    if (isset($_SERVER['QUERY_STRING'])) {
      $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
      $updateGoTo .= $_SERVER['QUERY_STRING'];
    }
    header(sprintf("Location: %s", $updateGoTo));
}

$query_Rsstaf = sprintf("SELECT * FROM ictstaff WHERE stfid = %s", GetSQLValueString($colname_Rsstaf, "text"));
$Rsstaf = mysql_query($query_Rsstaf, $tams) or die(mysql_error());
$row_Rsstaf = mysql_fetch_assoc($Rsstaf);
$totalRows_Rsstaf = mysql_num_rows($Rsstaf);	

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Edit Staff <!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include 'include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
      	
      <tr>
        <td>&nbsp;
          <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
            <table align="center">
            	<tr>
      			<td width="95">ID</td>
       			 <td width="317"><?php echo $row_Rsstaf['stfid']?></td>
      			</tr>
              <tr valign="baseline">
                <td nowrap="nowrap" align="right">Title:</td>
                <td><select name="title">
                    <option value="Prof"<?php if (!(strcmp("Prof", htmlentities($row_Rsstaf['title'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>> Prof.</option>
                    <option value="Dr" <?php if (!(strcmp("Dr", htmlentities($row_Rsstaf['title'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?> >Dr.</option>
                    <option value="Mr" <?php if (!(strcmp("Mr", htmlentities($row_Rsstaf['title'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?> >Mr.</option>
                    <option value="Mrs" <?php if (!(strcmp("Mrs", htmlentities($row_Rsstaf['title'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>Mrs.</option>
                    <option value="Miss" <?php if (!(strcmp("Miss", htmlentities($row_Rsstaf['title'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>Miss</option>
                  </select></td>
              </tr>
              <tr valign="baseline">
                <td nowrap="nowrap" align="right">First Name:</td>
                <td><input type="text" name="fname" value="<?php echo htmlentities($row_Rsstaf['fname'], ENT_COMPAT, 'utf-8'); ?>" size="32" /></td>
              </tr>
              <tr valign="baseline">
                <td nowrap="nowrap" align="right">Last Name:</td>
                <td><input type="text" name="lname" value="<?php echo htmlentities($row_Rsstaf['lname'], ENT_COMPAT, 'utf-8'); ?>" size="32" /></td>
              </tr>
              <tr valign="baseline">
                <td nowrap="nowrap" align="right">Middle Name:</td>
                <td><input type="text" name="mname" value="<?php echo htmlentities($row_Rsstaf['mname'], ENT_COMPAT, 'utf-8'); ?>" size="32" /></td>
              </tr>
              <tr valign="baseline">
                <td nowrap="nowrap" align="right">Date of birth:</td>
                <td><input type="text" name="dob" value="<?php echo htmlentities($row_Rsstaf['dob'], ENT_COMPAT, 'utf-8'); ?>" size="32" /></td>
              </tr>
              <tr valign="baseline">
                <td nowrap="nowrap" align="right">Phone:</td>
                <td><input type="text" name="phone" value="<?php echo htmlentities($row_Rsstaf['phone'], ENT_COMPAT, 'utf-8'); ?>" size="32" /></td>
              </tr>
              <tr valign="baseline">
                <td nowrap="nowrap" align="right">Email:</td>
                <td><input type="text" name="email" value="<?php echo htmlentities($row_Rsstaf['email'], ENT_COMPAT, 'utf-8'); ?>" size="32" /></td>
              </tr>
              <tr valign="baseline">
                <td nowrap="nowrap" align="right" valign="top">Address:</td>
                <td><textarea name="addr" cols="50" rows="5"><?php echo htmlentities($row_Rsstaf['addr'], ENT_COMPAT, 'utf-8'); ?></textarea></td>
              </tr>
              <tr valign="baseline">
                <td nowrap="nowrap" align="right">Sex:</td>
                <td><select name="sex">
                    <option value="M"<?php if (!(strcmp("M", htmlentities($row_Rsstaf['sex'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>Male</option>
                    <option value="F" <?php if (!(strcmp("F", htmlentities($row_Rsstaf['sex'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>Female</option>
                  </select></td>
              </tr>
              <tr valign="baseline">
                <td nowrap="nowrap" align="right">Access:</td>
                <td><select name="access">
                    <option value="20"<?php if (!(strcmp("20", htmlentities($row_Rsstaf['access'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>Admin</option>
                    <option value="21" <?php if (!(strcmp("21", htmlentities($row_Rsstaf['access'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>Unit Head</option>
                     <option value="21" <?php if (!(strcmp("22", htmlentities($row_Rsstaf['access'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>Staff</option>
                  </select></td>
              </tr>
              <tr valign="baseline">
                <td nowrap="nowrap" align="right" valign="top">Profile:</td>
                <td><textarea name="profile" cols="50" rows="5"><?php echo htmlentities($row_Rsstaf['profile'], ENT_COMPAT, 'utf-8'); ?></textarea></td>
              </tr>
              <tr valign="baseline">
                <td nowrap="nowrap" align="right">&nbsp;</td>
                <td><input type="submit" value="Update record" /></td>
              </tr>
            </table>
            <input type="hidden" name="stfid" value="<?php echo $row_Rsstaf['stfid']; ?>" />
            <input type="hidden" name="MM_update" value="form1" />
            <input type="hidden" name="stfid" value="<?php echo $row_Rsstaf['stfid']; ?>" />
          </form>
        <p>&nbsp;</p></td>
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
mysql_free_result($Rsstaf);
?>
