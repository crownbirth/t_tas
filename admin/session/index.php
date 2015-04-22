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

if(isset($_GET['action'])) {
    if($_GET['action'] == 'ses') {
        $updateSQL = sprintf("UPDATE session SET status = %s",
			GetSQLValueString("TRUE", "text"));
	
        mysql_select_db($database_tams, $tams);
        $Result = mysql_query($updateSQL, $tams) or die(mysql_error());
    }elseif($_GET['action'] == 'reg') {
        $updateSQL = sprintf("UPDATE session SET registration = %s",
			GetSQLValueString("FALSE", "text"));
	
        mysql_select_db($database_tams, $tams);
        $Result = mysql_query($updateSQL, $tams) or die(mysql_error());
    }else {
        $updateSQL = sprintf("UPDATE session SET registration = %s",
			GetSQLValueString("TRUE", "text"));
	
        mysql_select_db($database_tams, $tams);
        $Result = mysql_query($updateSQL, $tams) or die(mysql_error());
    }
        
    header('Location: index.php');
}
$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO `session` ( sesname, tnumin, tnumax) VALUES ( %s, %s, %s)",
                       GetSQLValueString($_POST['sesname'], "text"),
                       GetSQLValueString($_POST['tnumin'], "int"),
                       GetSQLValueString($_POST['tnumax'], "int"));

  mysql_select_db($database_tams, $tams);
  $Result1 = mysql_query($insertSQL, $tams) or die(mysql_error());

  
  /*$updateSQL = sprintf("UPDATE `student` SET level= level + 1 WHERE level < 4");
  /*
  	check graduation status of students where level is equal to or greater than 4
  
  

  mysql_select_db($database_tams, $tams);
  $Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());
  
  $updateSQL = sprintf("UPDATE `student` SET status = 'Graduate' WHERE level = 6");
  mysql_select_db($database_tams, $tams);
  $Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());
  */
}

mysql_select_db($database_tams, $tams);
$query_sess = "SELECT * FROM `session` ORDER BY sesname DESC";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

$sessname = "";
if( isset($row_sess['sesid']) ){
	list( $fyear,$syear) = explode("/",$row_sess['sesname']);
	$fyear = intval($fyear) +1;
	$syear = intval($syear) +1;
	$sessname = $fyear."/".$syear;;
}

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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Create/Update Session<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
        <?php if($row_sess['status'] == 'TRUE') {?>
        <tr>
          <td colspan="5">&nbsp;
            <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
              <table align="center">
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Session Name:</td>
                  <td><input type="text" name="sesname" value="<?php echo $sessname?>" size="15" /></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Minimum Unit:</td>
                  <td><input type="text" name="tnumin" size="10" value="<?php echo $row_sess['tnumin']?>"/></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Maximum Unit:</td>
                  <td><input type="text" name="tnumax" size="10" value="<?php echo $row_sess['tnumax']?>"/></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">&nbsp;</td>
                  <td><input type="submit" value="Create Session" /></td>
                </tr>
              </table>
              <input type="hidden" name="MM_insert" value="form1" />
            </form>
          <p>&nbsp;</p></td>
        </tr>
        <?php }?>
        <?php if ($totalRows_sess > 0) { // Show if recordset not empty 
                $count = 0;
        ?>                
        <?php do { ?>
          <tr>
            <td width="127"><?php echo $row_sess['sesname']; ?></td>
            <td width="167"><?php echo $row_sess['tnumin']; ?></td>
            <td width="190"><?php echo $row_sess['tnumax']; ?></td>
            <td width="84"><a href="sesedit.php?sid=<?php echo $row_sess['sesid'];?>">Edit</a></td>
            <td width="98">
                <?php 
                    if($count == 0) {
                        if($row_sess['registration'] == 'FALSE'){
                ?> 
                <span><a  href='index.php?action=regc'>Close Registration</a></span>
                <?php                 
                        }else {
                ?>
                
                <span><a  href='index.php?action=reg'>Open Registration</a></span>
                <?php                 
                        }                        
                    }
                ?>
            </td>
            <td width="98"><?php if($count == 0) {?> <span><a href='index.php?action=ses'>Close Session</a></span><?php }?></td>
          </tr>
          <?php $count++;} while ($row_sess = mysql_fetch_assoc($sess)); ?>
        <?php } // Show if recordset not empty ?>
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
mysql_free_result($sess);
?>
