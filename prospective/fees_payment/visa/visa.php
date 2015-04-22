<?php 
if (!isset($_SESSION)) {
  session_start();
}
require_once('../../../param/param.php');
require_once('../../../functions/function.php');


$MM_authorizedUsers = "11";
$MM_donotCheckaccess = "true";

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
    if (($strUsers == "") && true) { 
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

if(!isset($_SESSION['payment']['paid']) || !isset($_SESSION['payment']['verified']) 
        || !$_SESSION['payment']['verified'] || $_SESSION['payment']['paid']) {
    header('Location: ../index.php');
}

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root.'/prospective' );   
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/template.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php require('../../../param/site.php'); ?>
<title><?php echo $university ?> </title>
<!-- InstanceEndEditable -->
<link href="../../../css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
<link href="../../../css/menulink.css" rel="stylesheet" type="text/css" />
<link href="../../../css/footer.css" rel="stylesheet" type="text/css" />
<link href="../../../css/sidemenu.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div class="container">
  <div class="header">
    <!-- end .header -->
</div>
  <div class="topmenu">
<?php include '../../../include/topmenu.php'; ?>
  </div>
  <!-- end .topmenu --> 
  
  <div class="loginuser">
  <?php include '../../../include/loginuser.php'; ?>
  
  <!-- end .loginuser --></div>
  <div class="pagetitle">
      <img src="../img/visa.jpg" width="70px" height="30px" />  Visa Instruction  InstanceEndEditable 
<!--    <table width="600">
      <tr>
          <td> InstanceBeginEditable name="pagetitle"  <img src="img/visa.jpg" width="70px" height="30px" />  Visa Instruction  InstanceEndEditable </td>
      </tr>
    </table>-->
  </div>
<div class="sidebar1">
   
    <?php include '../../../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
        <table width="690" >
            <tr>
                <td colspan="2">
                    <p>
                        <div>
                            <p>
                                This site is protected with Verified by Visa (VbV),
                                    Visa Password-Protected Identity Checking Service,
                                    and requires that the card is enrolled to participate
                                    in the VbV program. If your Visa Card issued by Nigerian
                                    Banks is not enrolled, kindly follow the steps outlined
                                    below.
                            </p>
                            <ol>
                                <li>Locate the nearest VISA/VPAY enabled ATM</li>
                                <li>Insert your card and punch in your PIN</li>
                                <li>Select the PIN change option</li>
                                <li>Select Internet PIN (i-PIN) change option</li>
                                <li>Insert any four - six digits of your choice as your
                                        i-PIN</li>
                                <li>Re-enter the digits entered in step 5</li>
                                <li>If you have done the above correctly, a message is
                                        displayed that your PIN was changed successfully.
                                        This means your card is now enrolled in the VbV program
                                        and you have an Internet PIN (i-PIN) which can be
                                        used for any internet related transaction</li>
                                <li>Note the the word "<strong>i-PIN</strong>","<strong>Password</strong>"
                                        and "<strong>VbV Code</strong>" are the same</li>
                                <li>You
                                            can now visit your favourite VbV enabled site to shop
                                            securely</li>
                                <p>
                                    <strong>Important</strong><br />
                                    Please note that this is only for internet related
                                        transactions and it does not change your regular PIN
                                        on ATM and POS.
                                </p>
                            </ol>
                        </div>
                    </p>
                </td> 
            </tr>
            <tr>
                <td align="center"><input type="button" onclick="javascript:location='index.php'" value="Pay Now"/></td>
                <td align="center"><input type="button" onclick="javascript:location='../../termsandcon.php'" value="Cancle"/></td>
            </tr>
        </table>
  <!-- InstanceEndEditable --></div>
<div class="footer">
    <p><!-- end .footer -->   
    
    <?php require '../../../include/footer.php'; ?>
	
   </p>
  </div>
  <!-- end .container -->
</div>
</body>
<!-- InstanceEnd --></html>