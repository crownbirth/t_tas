<?php require_once('../Connections/tams.php');
require_once('../param/param.php');
require_once('../functions/function.php');

if (!isset($_SESSION)) {
  session_start();
}

$MM_authorizedUsers = "1,2,3,4,5,6";
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

mysql_select_db($database_tams, $tams);
$query_col = "SELECT colid, coltitle FROM college";
$col = mysql_query($query_col, $tams) or die(mysql_error());
$row_col = mysql_fetch_assoc($col);
$totalRows_col = mysql_num_rows($col);

mysql_select_db($database_tams, $tams);
$query_dept = "SELECT deptid, deptname FROM department ORDER BY deptname ASC";
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);


mysql_select_db($database_tams, $tams);
$query_staff = ( isset( $_GET['filter'] ) )? createFilter("lect"): "SELECT title, lectid, fname, lname, email FROM lecturer WHERE lectid=0";
$staff = mysql_query($query_staff, $tams) or die(mysql_error());
$row_staff = mysql_fetch_assoc($staff);
$totalRows_staff = mysql_num_rows($staff);

if (!isset($_SESSION)) {
  session_start();
}
require_once('../param/param.php');
require_once('../functions/function.php');


$sub = false;
$path = pathinfo( $_SERVER['SCRIPT_FILENAME']);

if( $path['dirname'] != $_SERVER['DOCUMENT_ROOT'].$site_root )
	$sub = true;
	

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );  
}

 $filtername = "The University";
 if( isset($_GET['filter'])){
 	if( $_GET['filter'] == "dept")			
		do { 
			if( $_GET['did'] == $row_dept['deptid'] )
			$filtername = $row_dept['deptname'];
		} while ($row_dept = mysql_fetch_assoc($dept)); 
	elseif( $_GET['filter'] == "col" )
		do { 
			if( $_GET['cid'] == $row_col['colid'] )
			$filtername = $row_col['coltitle'];
		} while ($row_col = mysql_fetch_assoc($col));
 }
 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/template.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php require('../param/site.php'); ?>
<title><?php echo $university ?> </title>
<!-- InstanceEndEditable -->
<link href="../css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<script src="../scripts/tams.js" type="text/javascript"></script>
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Staff In <?php echo $filtername;?><!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
      <tr>
        <td colspan="5"></td>
      </tr>
      <tr>
        <td colspan="5"></td>
      </tr>
      <tr>
        <td></td>
        <td>View By College<br/>
          <label for="col"></label>
          <select name="col2" id="col" onchange="colFilter(this)">
            <option value="-1" <?php if (isset($_GET['cid']))if (!(strcmp(-1, $_GET['cid']))) {echo "selected=\"selected\"";} ?>>---Select A College---</option>
            <?php
			  $rows = mysql_num_rows($col);
			  if($rows > 0) {
				  mysql_data_seek($col, 0);
				  $row_col = mysql_fetch_assoc($col);
			  }
				do {  
			?>
            <option value="<?php echo $row_col['colid']?>" <?php if (isset($_GET['cid']))if (!(strcmp($row_col['colid'], $_GET['cid']))) {echo "selected=\"selected\"";} ?>><?php echo $row_col['coltitle']?></option>
            <?php
} while ($row_col = mysql_fetch_assoc($col));
  
?>
          </select></td>
        <td>View By Department<br/>
          <label for="dept2"></label>
          <select name="dept2" id="dept2" onchange="deptFilter(this)">
            <option value="-1" <?php if (isset($_GET['did']))if(!(strcmp(-1, $_GET['did']))) {echo "selected=\"selected\"";} ?>>---Select A Department---</option>
            <?php
			$rows = mysql_num_rows($dept);
  if($rows > 0) {
      mysql_data_seek($dept, 0);
	  $row_dept = mysql_fetch_assoc($dept);
  }
do {  
?>
            <option value="<?php echo $row_dept['deptid']?>"<?php if (isset($_GET['did']))if (!(strcmp($row_dept['deptid'], $_GET['did']))) {echo "selected=\"selected\"";} ?>><?php echo $row_dept['deptname']?></option>
            <?php
} while ($row_dept = mysql_fetch_assoc($dept));
?>
          </select></td>
        <td>&nbsp;</td>
        <td valign="bottom"><?php echo $totalRows_staff." staff"?></td>
      </tr>
      <tr>
        <td width="95"><br/></td>
        <td width="285">&nbsp;</td>
        <td width="161">&nbsp;</td>
        <td width="29">&nbsp;</td>
        <td width="96" valign="bottom">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="5"></td>
      </tr>
    </table>
    <table width="682" border="0" align="center">
      <?php if ($totalRows_staff > 0) { // Show if recordset not empty ?>
        <?php do { ?>
          <tr>
            <td width="224"><a href="profile.php?lid=<?php echo $row_staff['lectid']; ?>"><?php echo $row_staff['title']." ".$row_staff['lname'].", ".$row_staff['fname']; ?></a></td>
            <td width="287"><a href="mailto:<?php echo $row_staff['email']; ?>"><?php echo $row_staff['email']; ?></a></td>
            <td width="157"><a href="../teaching/coursehist.php?lid=<?php echo $row_staff['lectid']?>">Teaching History</a></tr>
          <?php } while ($row_staff = mysql_fetch_assoc($staff));?>
        <?php } // Show if recordset not empty ?>
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
mysql_free_result($dept);

mysql_free_result($col);

mysql_free_result($staff);
?>
