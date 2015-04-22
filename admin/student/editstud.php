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



$colname_editstud = "-1";
if (isset($_GET['stid'])) {
  $colname_editstud = $_GET['stid'];
}
mysql_select_db($database_tams, $tams);
$query_editstud = sprintf("SELECT * FROM student WHERE stdid = %s", GetSQLValueString($colname_editstud, "text"));
$editstud = mysql_query($query_editstud, $tams) or die(mysql_error());
$row_editstud = mysql_fetch_assoc($editstud);
$totalRows_editstud = mysql_num_rows($editstud);

mysql_select_db($database_tams, $tams);
$query_sess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,6";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

mysql_select_db($database_tams, $tams);
$query_prog = "SELECT progid, progname FROM programme";
$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);
$totalRows_prog = mysql_num_rows($prog);


$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
	$password = htmlentities($row_editstud['password']);
	if(isset($_POST['password'])){
		$password = md5($_POST['password']);
		}
  $updateSQL = sprintf("UPDATE student SET fname=%s, lname=%s, mname=%s, progid=%s, phone=%s, email=%s, addr=%s, sex=%s, dob=%s, sesid=%s, `level`=%s, admode=%s, password=%s, status=%s, `access`=%s, credit=%s, profile=%s WHERE stdid=%s",
                       GetSQLValueString($_POST['fname'], "text"),
                       GetSQLValueString($_POST['lname'], "text"),
                       GetSQLValueString($_POST['mname'], "text"),
                       GetSQLValueString($_POST['progid'], "int"),
                       GetSQLValueString($_POST['phone'], "text"),
                       GetSQLValueString($_POST['email'], "text"),
                       GetSQLValueString($_POST['addr'], "text"),
                       GetSQLValueString($_POST['sex'], "text"),
                       GetSQLValueString($_POST['dob'], "date"),
                       GetSQLValueString($_POST['sesid'], "int"),
                       GetSQLValueString($_POST['level'], "int"),
                       GetSQLValueString($_POST['admode'], "text"),
                       GetSQLValueString($_POST['password'], "text"),
                       GetSQLValueString($_POST['status'], "text"),
                       GetSQLValueString($_POST['access'], "int"),
                       GetSQLValueString($_POST['credit'], "int"),
                       GetSQLValueString($_POST['profile'], "text"),
                       GetSQLValueString($_POST['stdid'], "text"));

  mysql_select_db($database_tams, $tams);
  $Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());
}


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
<script src="../../scripts/widgEditor.js" type="text/javascript"></script>
<link href="../../css/widgEditor.css" rel="stylesheet" type="text/css" />
<!-- InstanceEndEditable -->
<link href="../../css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Edit Student<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
      <tr>
        <td><form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
          <table align="center">
            <tr valign="baseline">
              <td nowrap="nowrap" align="right">Matric No.:</td>
              <td><?php echo $row_editstud['stdid']; ?></td>
            </tr>
            <tr valign="baseline">
              <td nowrap="nowrap" align="right">First Name:</td>
              <td><input type="text" name="fname" value="<?php echo htmlentities($row_editstud['fname'], ENT_COMPAT, 'utf-8'); ?>" size="32" /></td>
            </tr>
            <tr valign="baseline">
              <td nowrap="nowrap" align="right">Last Name:</td>
              <td><input type="text" name="lname" value="<?php echo htmlentities($row_editstud['lname'], ENT_COMPAT, 'utf-8'); ?>" size="32" /></td>
            </tr>
            <tr valign="baseline">
              <td nowrap="nowrap" align="right">Middle Name:</td>
              <td><input type="text" name="mname" value="<?php echo htmlentities($row_editstud['mname'], ENT_COMPAT, 'utf-8'); ?>" size="32" /></td>
            </tr>
            <tr valign="baseline">
              <td nowrap="nowrap" align="right">Programme:</td>
              <td>
              <select name="progid">
                <?php
				do {  
				?>
                <option value="<?php echo $row_prog['progid']?>" <?php if (!(strcmp($row_prog['progid'], htmlentities($row_editstud['progid'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>><?php echo $row_prog['progname']?></option>
                <?php
				} while ($row_prog = mysql_fetch_assoc($prog));
				  $rows = mysql_num_rows($prog);
				  if($rows > 0) {
					  mysql_data_seek($prog, 0);
					  $row_prog = mysql_fetch_assoc($prog);
				  }
				?>
              </select>
              </td>
            </tr>
            <tr valign="baseline">
              <td nowrap="nowrap" align="right">Phone:</td>
              <td><input type="text" name="phone" value="<?php echo htmlentities($row_editstud['phone'], ENT_COMPAT, 'utf-8'); ?>" size="32" /></td>
            </tr>
            <tr valign="baseline">
              <td nowrap="nowrap" align="right">Email:</td>
              <td><input type="text" name="email" value="<?php echo htmlentities($row_editstud['email'], ENT_COMPAT, 'utf-8'); ?>" size="32" /></td>
            </tr>
            <tr valign="baseline">
              <td nowrap="nowrap" align="right" valign="top">Address:</td>
              <td>
              <textarea  name="addr" cols="50" rows="5" class="widgEditor nothing">
              	<?php echo htmlentities($row_editstud['addr'], ENT_COMPAT, 'utf-8'); ?>
              </textarea>              
              </td>
            </tr>
            <tr valign="baseline">
              <td nowrap="nowrap" align="right">Sex:</td>
              <td><select name="sex">
                <option value="M" <?php if (!(strcmp("M", htmlentities($row_editstud['sex'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>Male</option>
                <option value="F" <?php if (!(strcmp("F", htmlentities($row_editstud['sex'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>Female</option>
              </select></td>
            </tr>
            <tr valign="baseline">
              <td nowrap="nowrap" align="right">Date of Birth:</td>
              <td><input type="text" name="dob" value="<?php echo htmlentities($row_editstud['dob'], ENT_COMPAT, 'utf-8'); ?>" size="32" /></td>
            </tr>
            <tr valign="baseline">
              <td nowrap="nowrap" align="right">Session:</td>
              <td><select name="sesid">
                <?php
				do {  
				?>
                <option value="<?php echo $row_sess['sesid']?>" <?php if (!(strcmp($row_sess['sesid'], htmlentities($row_editstud['sesid'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>><?php echo $row_sess['sesname']?></option>
                <?php
				} while ($row_sess = mysql_fetch_assoc($sess));
				  $rows = mysql_num_rows($sess);
				  if($rows > 0) {
					  mysql_data_seek($sess, 0);
					  $row_sess = mysql_fetch_assoc($sess);
				  }
				?>
              </select></td>
            </tr>
            <tr valign="baseline">
              <td nowrap="nowrap" align="right">Level:</td>
              <td>
              <select name="level">
                <option value="1" <?php if (!(strcmp("1", htmlentities($row_editstud['level'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>100</option>
                <option value="2" <?php if (!(strcmp("2", htmlentities($row_editstud['level'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>200</option>
                <option value="3" <?php if (!(strcmp("3", htmlentities($row_editstud['level'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>300</option>
                <option value="4" <?php if (!(strcmp("4", htmlentities($row_editstud['level'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>400</option>
                <option value="5" <?php if (!(strcmp("5", htmlentities($row_editstud['level'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>500</option>
                <option value="6" <?php if (!(strcmp("6", htmlentities($row_editstud['level'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>600</option>
              </select>
              </td>
            </tr>
            <tr valign="baseline">
              <td nowrap="nowrap" align="right">Admode:</td>
              <td><select name="admode">
                <option value="UTME" <?php if (!(strcmp("UTME", htmlentities($row_editstud['admode'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>UTME</option>
                <option value="DE" <?php if (!(strcmp("DE", htmlentities($row_editstud['admode'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>Direct Entry</option>
              </select></td>
            </tr>
            <tr valign="baseline">
              <td nowrap="nowrap" align="right">Password:</td>
              <td><input type="text" name="password" value="<?php //echo htmlentities($row_editstud['password'], ENT_COMPAT, 'utf-8'); ?>" size="32" /></td>
            </tr>
            <tr valign="baseline">
              <td nowrap="nowrap" align="right">Status:</td>
              <td><select name="status">
                <option value="Undergrad" <?php if (!(strcmp("Undergrad", htmlentities($row_editstud['status'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>Undergraduate</option>
                <option value="Graduate" <?php if (!(strcmp("Graduate", htmlentities($row_editstud['status'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>Graduate</option>
              </select></td>
            </tr>
            <tr valign="baseline">
              <td nowrap="nowrap" align="right">Credit:</td>
              <td><input type="text" name="credit" value="<?php echo htmlentities($row_editstud['credit'], ENT_COMPAT, 'utf-8'); ?>" size="32" /></td>
            </tr>
            <tr valign="baseline">
              <td nowrap="nowrap" valign="top" align="right">Profile:</td>
              <td>
              <textarea  name="profile" cols="50" rows="5" class="widgEditor nothing">
              	<?php echo htmlentities($row_editstud['profile'], ENT_COMPAT, 'utf-8'); ?>
              </textarea>
              </td>
            </tr>
            <tr valign="baseline">
              <td nowrap="nowrap" align="right">&nbsp;</td>
              <td><input type="submit" value="Edit Student" /></td>
            </tr>
          </table>
          <input type="hidden" name="access" value="<?php echo htmlentities($row_editstud['access'], ENT_COMPAT, 'utf-8'); ?>" />
          <input type="hidden" name="MM_update" value="form1" />
          <input type="hidden" name="stdid" value="<?php echo $row_editstud['stdid']; ?>" />
        </form>
        <p>&nbsp;</p></td>
      </tr>
    </table>
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
mysql_free_result($editstud);

mysql_free_result($sess);

mysql_free_result($prog);
?>
