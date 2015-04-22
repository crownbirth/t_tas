<?php require_once('Connections/tams.php'); ?>
<?php

//include required function files 
require_once('param/param.php');
require_once('functions/function.php');



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

//Message to be displayed if no username is entered.
$msg = 'Enter a valid username!';

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  
  $userid = '';
  
  //Retrieve user's email and password
  $rsUser = sprintf("SELECT stdid, email, password
					FROM student
					WHERE stdid=%s",
					GetSQLValueString($_POST['username'], "text"));

  mysql_select_db($database_tams, $tams);
  $rsUser = mysql_query($rsUser, $tams) or die(mysql_error());
  $foundUser = mysql_num_rows($rsUser);
  $row_rsUser = mysql_fetch_assoc($rsUser);
  
  //if no user is found in the students table for the entered username, check in the prospective table.
  if(!$foundUser) {
	  $rsUser = sprintf("SELECT pstdid, email, password
						FROM prospective
						WHERE pstdid=%s",
						GetSQLValueString($_POST['username'], "text"));
	  $rsUser = mysql_query($rsUser, $tams) or die(mysql_error());
	  $foundUser = mysql_num_rows($rsUser);
	  $row_rsUser = mysql_fetch_assoc($rsUser);
	  if($foundUser) {
		$userid = $row_rsUser['pstdid'];
	  }
  }else{
	  $userid = $row_rsUser['stdid'];
  }
  
  //Check if a user is found.
  if($foundUser) {
	  
	  //Check if the user has a valid email.
	  if($row_rsUser['email']){
		  //Prepare message and send it.
		  $bodyText = sprintf('Your username is %s, and your password is %s', $userid, $row_rsUser['password']);
		  $mail = @mail($row_rsUser['email'], "Password Recovery", $bodyText, "From:from@email.com\r\n");
		  
		  //Display appropriate message on success or failure of mail delivery.
		  if($mail){
			$msg = "An email with your correct username and password has been sent to your registered email – %s";
			$msg = sprintf($msg, $row_rsUser['email']);
		  }else{
			$msg = "Could not send email to the following address: %s. Please try again!";
			$msg = sprintf($msg, $row_rsUser['email']);
		  }
	  }
  }
  
}//End of $_POST

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );  
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/template.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Tams </title>
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Recover Password<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include 'include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690" align="center">
      <tr>
        <td>&nbsp;
          <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1" class="form-inline form-signin">
          <div class="alert alert-info alert-dismissable">
    Submit your Login Username and your Password will be fowarded to the E-mail Address you Registered during account creation.</div>
            <table align="center">
              <tr valign="baseline">
                <td align="center" colspan="2"><?php if($_POST)echo $msg;?></td>
              </tr>
              <tr valign="baseline">
                <td>&nbsp;</td>
              </tr>
              <tr valign="baseline">
                <td nowrap="nowrap" align="right"><input type="text" name="username" value="" size="32" placeholder="Enter your Username.." /></td>
                <td><input type="submit" value="Recover"  class="btn btn-primary"/></td>
              </tr>
            </table>
            <input type="hidden" name="MM_insert" value="form1" />
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