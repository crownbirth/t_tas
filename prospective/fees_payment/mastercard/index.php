<?php 
require_once('../../../Connections/tams.php');
if (!isset($_SESSION)) {
  session_start();
}
require_once('../../../param/param.php');
require_once('../../../functions/function.php');

$reroot2 = "../../status.php";

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
    <table width="600">
      <tr>
        <td><!-- InstanceBeginEditable name="pagetitle" -->Payment Confirmation <!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../../../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
        <table width="690" >
            <tr>
                <td colspan="2">
                    <p>Below is the details of the payment transaction you are
                        about to execute. Click the <strong>Pay Now</strong> 
                        button to continue with your payment or <strong>Cancel</strong> to terminate! 
                    </p>
                    <table width="690" class="table table-striped table-bordered table-condensed">
                        <tr>
                            <th width="150">UTME Reg.No : </th>
                            <td><?php echo $_SESSION['payment']['jambregid'] ?></td>
                        </tr>
                        <tr>
                            <th>Full Name : </th>
                            <td><?php echo strtoupper($_SESSION['payment']['name']) ?></td>
                        </tr>
                        <tr>
                            <th>Application type : </th>
                            <td><?php echo $_SESSION['payment']['admtype']?></td>
                        </tr>
                        <tr>
                            <th>Amount to be paid : </th>
                            <th style="color: #CC0000"><?php echo '=N= '. number_format($_SESSION['payment']['amount']);?></th>
                        </tr>
                        <tr>
                            <td colspan="2">&nbsp; </td>  
                        </tr>
                        <tr>
                            <td width="50%" align="right">
                                <button onclick="location.href = 'processpayment.php'">Pay Now</button>
                            </td>
                            <td width="50%" align="left">
                                <button onclick="location.href = '../index.php'">Cancel</button >
                            </td>
                        </tr>
                    </table>
                </td> 
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