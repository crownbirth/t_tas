<?php 

require_once('../../Connections/tams.php');

if (!isset($_SESSION)) {
  session_start();
}
require_once('../../param/param.php');
require_once('../../functions/function.php');

$MM_authorizedUsers = "10";
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

//Get current session 
mysql_select_db($database_tams, $tams);
$query = sprintf("SELECT * "
                    . "FROM session "
                    . "ORDER BY sesid DESC LIMIT 1");
$session = mysql_query($query, $tams) or die(mysql_error());
$row_session = mysql_fetch_assoc($session);
$totalRows_session = mysql_num_rows($session);

if(checkFees($row_session['sesid'], getSessionValue('stid'))) {
    header('Location: index.php');
}

if($_SESSION['payment']['prev_ses']) {
    $_SESSION['payment']['percent'] = 100;
    //$_SESSION['payment']['percent'] = $_GET['pc'];
}else {
    $validPercent = array(60, 100);
    
    if($_SESSION['payment']['installment'] == 'none') {
        if(isset($_GET['pc']) && in_array($_GET['pc'], $validPercent)) {
            $_SESSION['payment']['percent'] = $_GET['pc'];
        }    
    }elseif($_SESSION['payment']['installment'] == 'incomplete') {
        $_SESSION['payment']['percent'] = 40;
    }
}


if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout($site_root);   
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Payment Instruction<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
      <table width="690" class="table">
            <tr>
                <td colspan="2">
                    <p>
                        Your School fee is to be paid by selecting a card type below and using our webpay platform.
                    </p>
                    
            <p>
                Payment will be made using Debit/Credit Cards (ATM Card)<br>
                Your Card can be from <u>any of the Nigerian Banks</u>
                <br>Ensure that your card has been enabled for internet transactions
                by your bank (kindly enquire from your bank if you must).
            </p> 
            <p>
                <b style="color :red">Fees paid to Tai Solarin University of Education are non-refundable</b>
                <h4>Are you using Internet explorer browser?</h4>
                Avoid browser issues, uncheck support for Use SSL2.0 by following the steps below:<br/>
                1. Click on Tool option on the menu bar<br/>
                2. Select Internet Options<br/>
                3. Click Advance tab<br/>
                4. Scroll down to Security option and uncheck Use SSL 2.0<br/>
            </p>
                </td> 
            </tr>
            <tr>
                <td >
                    <table width="400" align="center" class="table table-bordered table-striped table-condensed">
                        <tr>
                            <th colspan="2">Select a payment method to continue</th>
                        </tr>
                         <tr>
                             <td align="center" width="50%" style=" "><a href="mastercard/mastercard.php"><img src="img/mastercard.png"></a></td>
                             <td align="center" width="50%"><a href="visa/visa.php"><img src="img/visa.jpg"></a></td>
                        </tr>
                    </table>
                </td>  
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