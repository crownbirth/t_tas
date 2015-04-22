<?php require_once('../Connections/tams.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
require_once('../param/param.php');
require_once('../functions/function.php');

$MM_authorizedUsers = "2,3";
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

mysql_select_db($database_tams, $tams);
$query_sess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,2";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

$colname_dept = "-1";
if ( getSessionValue('cid') != NULL ) {
  $colname_dept = getSessionValue('cid');
}
mysql_select_db($database_tams, $tams);
$query_dept = sprintf("SELECT deptid, deptname, coltitle "
                        . "FROM department d, college c "
                        . "WHERE d.colid = c.colid "
                        . "AND d.colid = %s",
                        GetSQLValueString($colname_dept, "int"));
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);


$colname_prog = "-1";
if (isset($row_dept['deptid'])) 
  $colname_prog = $row_dept['deptid'];
	
if (isset($_GET['did']))
  $colname_prog = $_GET['did'];

mysql_select_db($database_tams, $tams);
$query_prog = sprintf("SELECT progid, progname, p.deptid, deptname "
                        . "FROM programme p, department d "
                        . "WHERE d.deptid = p.deptid "
                        . "AND p.deptid = %s", GetSQLValueString($colname_prog, "int"));
$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);
$totalRows_prog = mysql_num_rows($prog);

$colname_deptcrs = "-1";
if (isset($row_prog['deptid'])) {
  $colname_deptcrs = $row_prog['progid'];
}
if (isset($_GET['did'])) {
  $colname_deptcrs = $_GET['did'];
}

$colname1_deptcrs = "-1";
if (isset($row_sess['sesid'])) {
  $colname1_deptcrs = $row_sess['sesid'];
}

if (isset($_GET['sid'])) {
  $colname1_deptcrs = $_GET['sid'];
}

if(getAccess() == 3) {
	$colname_prog = getSessionValue('did');
}

mysql_select_db($database_tams, $tams);
$query_deptcrs = sprintf("SELECT c.csid, csname, upload, approve "
                        . "FROM course c, department_course dc, teaching t "
                        . "WHERE c.csid = dc.csid "
                        . "AND c.csid = t.csid "
                        . "AND dc.csid = t.csid "
                        . "AND dc.deptid = t.deptid "
                        . "AND dc.deptid = %s "
                        . "AND t.sesid = %s "
                        . "ORDER BY csid ASC", 
                        GetSQLValueString($colname_prog, "int"), 
                        GetSQLValueString($colname1_deptcrs, "int"));
$deptcrs = mysql_query($query_deptcrs, $tams) or die(mysql_error());
$row_deptcrs = mysql_fetch_assoc($deptcrs);
$totalRows_deptcrs = mysql_num_rows($deptcrs);

$name = "";
if(getAccess() == 3) {
    $name = $row_prog['deptname'];
}elseif(getAccess() == 2) {
    $name = $row_dept['coltitle'];
}

if(isset($_GET['did']) ) {
	$name = $row_prog['deptname'];
}elseif(isset($_GET['cid']) ) {
	$name = $row_dept['coltitle'];
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
<?php require('../param/site.php'); ?>
<title><?php echo $university ?> </title>
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/tams.js"></script>
<!-- InstanceEndEditable -->
<link href="../css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
<link href="../css/menulink.css" rel="stylesheet" type="text/css" />
<link href="../css/footer.css" rel="stylesheet" type="text/css" />
<link href="../css/sidemenu.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div class="container">
  <div class="header">
    <!-- end .header -->
</div>
  <div class="topmenu">
<?php include '../include/topmenu.php'; ?>
  </div>
  <!-- end .topmenu --> 
  
  <div class="loginuser">
  <?php include '../include/loginuser.php'; ?>
  
  <!-- end .loginuser --></div>
  <div class="pagetitle">
    <table width="600">
      <tr>
        <td><!-- InstanceBeginEditable name="pagetitle" -->Consider Result for <?php echo $name;?><!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">		
      <form id="form1" name="form1" method="post" action="<?php echo $editFormAction?>">
      <tr>
      <?php if( getAccess() == 2){?>
        <td width="315" align="left">
        Choose Department
          <select name="deptid" id="deptid" onchange="deptfilt(this)">
            <?php
			do {  
			?>
            <option value="<?php echo $row_dept['deptid']?>"<?php if (!(strcmp($row_dept['deptid'], $colname_prog))) {echo "selected=\"selected\"";} ?>><?php echo $row_dept['deptname']?></option>
            <?php
			} while ($row_dept = mysql_fetch_assoc($dept));
			
			mysql_free_result($dept);			  
			?>
          </select>
          <?php }?>
        </td>
        <td width="364">
        <?php if( getAccess() == 2 || getAccess() == 3){?>
            Session
            <select name="sesid" id="sesid" onchange="sesfilt(this)">
                <?php
                do {  
                ?>
                <option value="<?php echo $row_sess['sesid']?>"<?php if (!(strcmp($row_sess['sesid'], $colname1_deptcrs))) {echo "selected=\"selected\"";} ?>><?php echo $row_sess['sesname']?></option>
                <?php
                } while ($row_sess = mysql_fetch_assoc($sess));
                  $rows = mysql_num_rows($sess);
                  if($rows > 0) {
                      mysql_data_seek($sess, 0);
                      $row_sess = mysql_fetch_assoc($sess);
                  }
                ?>
              </select>
          </td>
        <?php }?>
        </tr>
      </form>
      
      <tr>
        <td colspan="3">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="3">        
            <table width="683" border="0">
              <?php if ($totalRows_deptcrs > 0) { // Show if recordset not empty ?>
			  <?php do{?>
                <tr>
                
                  <td width="60"><?php echo $row_deptcrs['csid']?></td>
                  <td width="385"><a href="result.php?csid=<?php echo $row_deptcrs['csid']?>&did=<?php echo  $colname_deptcrs?>&sid=<?php echo  $colname1_deptcrs?>"><?php echo ucwords(strtolower($row_deptcrs['csname']))?></a></td>
                  <td width="116"><?php echo getUploadState($row_deptcrs['upload'])?></td>
                  <td width="106"><?php echo getApproveState($row_deptcrs['approve'])?></td>
                </tr>
                <?php }while ($row_deptcrs = mysql_fetch_assoc($deptcrs));?>
                <?php } // Show if recordset not empty ?>
            </table>
        </td>
      </tr>
    </table>
  <!-- InstanceEndEditable --></div>
<div class="footer">
    <p><!-- end .footer -->   
    
    <?php require '../include/footer.php'; ?>
	
   </p>
  </div>
  <!-- end .container -->
</div>
</body>
<!-- InstanceEnd --></html>
<?php

mysql_free_result($prog);

mysql_free_result($deptcrs);

mysql_free_result($sess);
?>