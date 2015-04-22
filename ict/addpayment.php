<?php require_once('../Connections/tams.php'); 

if (!isset($_SESSION)) {
  session_start();
}

$MM_authorizedUsers = "20";
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
if (!((isset($_SESSION['MM_Username'])) && 
        (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
}

require_once('../param/param.php');
require_once('../functions/function.php');	

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

$query_rssess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,1";
$rssess = mysql_query($query_rssess, $tams) or die(mysql_error());
$row_rssess = mysql_fetch_assoc($rssess);
$totalRows_rssess = mysql_num_rows($rssess);

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$msg = null;

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	
	$query_studentChk = sprintf("SELECT * FROM student WHERE stdid = %s",
                                    GetSQLValueString($_POST['stdid'], "text"));
	$studentChk = mysql_query($query_studentChk, $tams) or die(mysql_error());
	$row_studentChk = mysql_fetch_assoc($studentChk);
	$totalRows_studentChk = mysql_num_rows($studentChk);
	if($totalRows_studentChk > 0) {  
            if(isset($_POST['amount']) && $_POST['amount'] != '') {
                $status = 'Paid';
                $sesid = $row_rssess['sesid'];
                $insertSQL = sprintf("INSERT "
                                        . "INTO payhistory "
                                        . "(stdid, amount, sesid, status) "
                                        . "VALUES (%s, %s, %s, %s)",
                                        GetSQLValueString($_POST['stdid'], "text"),
                                        GetSQLValueString($_POST['amount'], "int"),
                                        GetSQLValueString($sesid, "int"),
                                        GetSQLValueString($status, "text"));

                $Result = mysql_query($insertSQL, $tams) or die(mysql_error());
                unset($_POST['MM_insert']);
                
                $_POST['sesid'] = $sesid;
                $_POST['status'] = $status;
                $params['entid'] = '';
                $params['enttype'] = 'payment';
                $params['action'] = 'create';
                $params['cont'] = json_encode($_POST);
                audit_log($params);
            }else {
                $msg = 'You cannot specify an empty amount!'; 
            }
	} else {
            $msg = 'The matric number you specified does not exist!'; 
	}

    
}

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout($site_root.'/ict');  
}

function getAccessByName($val){
	if ($val==20){
		echo "Admin Staff";
	}
	else if($val==21){
		echo "Regular Staff";
		}
	else{
		"";
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <!-- InstanceBegin template="/Templates/icttemplate.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php require('../param/site.php'); ?>
<title><?php echo $university ?> </title>
<!-- InstanceEndEditable -->
<link href="css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<script src="../SpryAssets/SpryCollapsiblePanel.js" type="text/javascript"></script>
<link href="../SpryAssets/SpryCollapsiblePanel.css" rel="stylesheet" type="text/css" />
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Add New Payment<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include 'include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
        <?php if($msg) {?>
      <tr>
        <td><?php echo $msg?></td>
      </tr>
        <?php }?>
      <tr>
        <td>
            <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
              <table align="center">                
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Matric:</td>
                  <td><input type="text" name="stdid" value="" size="32" /></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Amount:</td>
                  <td><input type="text" name="amount" value="" size="32" /></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">&nbsp;</td>
                  <td><input type="submit" value="Add new Payment" /></td>
                </tr>
              </table>
              <input type="hidden" name="MM_insert" value="form1" />
            </form>
            <p>&nbsp;</p>
            
        <p>&nbsp;</p></td>
      </tr>
    </table>
    <script type="text/javascript">
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

