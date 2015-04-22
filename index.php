<?php require_once('Connections/tams.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}

require_once('functions/function.php');
require_once('param/param.php');
require('param/site.php');
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
?>
<?php
include_once('functions/function.php');
// *** Validate request to login to this site.

$loginState = false;
$loginFormAction = $_SERVER['PHP_SELF'];
if (isset($_GET['accesscheck'])) {
  $_SESSION['PrevUrl'] = $_GET['accesscheck'];
}
 
mysql_select_db($database_tams, $tams);

if (isset($_POST['username']) && strlen($_POST['username'])  <= 20) {
  $loginUsername=$_POST['username'];
  $password=$_POST['password'];
  $MM_fldUserAuthorization = "access";
  $MM_redirecttoReferrer = false;
  
  if( $_POST['who'] != -1 )
  	$loginState = doLogin( $_POST['who'], $loginUsername, $password, $tams);
  else
   if( $_POST['who'] == -1 )
   $loginState = true;
}

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );  
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
<script src="/tams/SpryAssets/SpryValidationSelect.js" type="text/javascript"></script>
<script src="/tams/SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
<script src="SpryAssets/SpryValidationPassword.js" type="text/javascript"></script>
<link href="/tams/SpryAssets/SpryValidationSelect.css" rel="stylesheet" type="text/css" />
<link href="/tams/SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
<link href="SpryAssets/SpryValidationPassword.css" rel="stylesheet" type="text/css" />
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Login<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include 'include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
      <tr>
        <td><form id="form1" name="form1" method="POST" action="<?php echo $loginFormAction; ?>">
          <p>&nbsp;</p>
          <table width="497" border="0" align="center">
           <?php if( $loginState ){?> <tr>
              <td colspan="3"> Username or Password incorrect. Please try again with your correct details!</td>
              
            </tr> <?php } ?>
            <tr>
              <td width="87" height="24">User Name</td>
              <td colspan="2"><label for="username"></label>
                <span id="sprytextfield1">
                <label for="username"></label>
                <input name="username" type="text" id="username" size="30" maxlength="20" />
                <span class="textfieldRequiredMsg">Enter a valid username.</span></span></td>
            </tr>
            <tr>
              <td height="30">Password</td>
              <td colspan="2"><span id="sprypassword">
                <label for="password"></label>
                <input name="password" type="password" id="password" size="30" maxlength="15" />
                <span class="passwordRequiredMsg">Enter a valid password.</span></span></td>
            </tr>
            <tr>
              <td>Login As</td>
              <td colspan="2" align="left">
                <span id="spryselect1">
                <label for="who"></label>
                <select name="who" id="who">
                  <option value="-1">... Login As ...</option>
                  <option value="1">Prospective Student</option>
                  <option value="2">Returning Student</option>
                  <option value="3">Academic Staff</option>
                </select>
                <span class="selectRequiredMsg">Please select an item.</span></span></td>
            </tr>            
            <tr>
              <td colspan="3"><a href="reset_password.php"></a> <!--<a href="prospective/crtacct.php">New Admission? Apply !</a>--></td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td width="86"><input type="submit" name="login" id="login" value="Login" /></td>
              <td width="310"><a href="reset_password.php">Forgot Password?</a></td>
            </tr>
        </table>
        </form></td>
      </tr>
    </table>
    <script type="text/javascript">
var spryselect1 = new Spry.Widget.ValidationSelect("spryselect1");
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var sprypassword = new Spry.Widget.ValidationPassword("sprypassword");
    </script>
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