<?php require_once('../Connections/tams.php');  
if (!isset($_SESSION)) {
  session_start();
}

require_once('../param/param.php');
require_once('../functions/function.php');

$MM_authorizedUsers = "1,2,3,4,5,6,10";
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
$query_prog = (isset($_GET['cid']))?"SELECT progid, progname FROM programme p, department d WHERE d.deptid = p.deptid AND colid = ".$_GET['cid']." ORDER BY progname ASC":"SELECT progid, progname FROM programme WHERE  deptid= 0 ORDER BY progname ASC";
$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);
$totalRows_prog = mysql_num_rows($prog);

mysql_select_db($database_tams, $tams);
$query_col = "SELECT colid, coltitle FROM college";
$col = mysql_query($query_col, $tams) or die(mysql_error());
$row_col = mysql_fetch_assoc($col);
$totalRows_col = mysql_num_rows($col);

$totalRows_student = "";
$student ="";
if( isset( $_GET['filter'] ) && $_GET['filter'] != "col"){
mysql_select_db($database_tams, $tams);
$query_student = createFilter("stud");
$student = mysql_query($query_student, $tams) or die(mysql_error());
$row_student = mysql_fetch_assoc($student);
$totalRows_student = mysql_num_rows($student);
}
 
if (!isset($_SESSION)) {
  session_start();
}

$sub = false;
$path = pathinfo( $_SERVER['SCRIPT_FILENAME']);

if( $path['dirname'] != $_SERVER['DOCUMENT_ROOT'].$site_root )
	$sub = true;
	

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );  
}

 $level = "";
 $filtername = "The University";
 if( isset($_GET['filter'])){
 	if( $_GET['filter'] == "dept")	{		
		do { 
			if( $_GET['did'] == $row_dept['deptid'] )
			$filtername = $row_dept['deptname'];
		} while ($row_dept = mysql_fetch_assoc($dept)); 	
	}
	
	if( $_GET['filter'] == "lvl" ){
		$level = $_GET['lvl'];
		if( isset( $_GET['did'] ) )	{		
			do { 
				if( $_GET['did'] == $row_dept['deptid'] )
				$filtername = $row_dept['deptname'];
			} while ($row_dept = mysql_fetch_assoc($dept)); 
			$filtername .= " (".$_GET['lvl']."00 Level)";	
		}
		
		}
 }

$did = "-1";
if( isset( $_GET['pid'] ) )	
$did = $row_student['deptid'];

$cid = "-1";
if( isset( $_GET['cid'] ) )	
$cid = $_GET['cid'];

$allow = false;
$acl = array(4,5,6);
if( getAccess() == 1 || (getAccess() == 2 && getSessionValue('cid') == $cid) || (getAccess() == 3 && getSessionValue('did') == $did) || (in_array(getAccess(), $acl) && getSessionValue('did') == $did) ){
	 $allow = true;
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Students In <?php echo $filtername;?><!-- InstanceEndEditable --></td>
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
        <td colspan="2">Choose College<br/>
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
        <td>View By Programme<br/>
          <label for="prog"></label>
          <select name="prog" id="prog" onchange="progFilter(this)" style="width:320px;">
            <option value="-1" <?php if (isset($_GET['pid']))if(!(strcmp(-1, $_GET['pid']))) {echo "selected=\"selected\"";} ?>>---Select A Programme---</option>
            <?php
			$rows = mysql_num_rows($prog);
  if($rows > 0) {
      mysql_data_seek($prog, 0);
	  $row_dept = mysql_fetch_assoc($prog);
  }
do {  
?>
            <option value="<?php echo $row_prog['progid']?>"<?php if (isset($_GET['pid']))if (!(strcmp($row_prog['progid'], $_GET['pid']))) {echo "selected=\"selected\"";} ?>><?php echo $row_prog['progname']?></option>
            <?php
} while ($row_prog = mysql_fetch_assoc($prog));
?>
        </select></td>
        <td>Choose Level<br/>
          <select name="level" id="level" onchange="lvlFilter(this)">
          	<option value="-1" <?php if (!(strcmp(-1, $level))) {echo "selected=\"selected\"";} ?>>--Level--</option>
            <option value="1" <?php if (!(strcmp(1, $level))) {echo "selected=\"selected\"";} ?>>100</option>
            <option value="2" <?php if (!(strcmp(2, $level))) {echo "selected=\"selected\"";} ?>>200</option>
            <option value="3" <?php if (!(strcmp(3, $level))) {echo "selected=\"selected\"";} ?>>300</option>
            <option value="4" <?php if (!(strcmp(4, $level))) {echo "selected=\"selected\"";} ?>>400</option>
        </select></td>
        <td valign="bottom"><?php echo $totalRows_student." students"?></td>
      </tr>
      <tr>
        <td width="69"><br/></td>
        <td width="106">&nbsp;</td>
        <td width="242">&nbsp;</td>
        <td width="170">&nbsp;</td>
        <td width="79" valign="bottom">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="5"></td>
      </tr>
    </table>
    <table width="682" border="0" align="center">
      <?php if ($totalRows_student > 0) { // Show if recordset not empty ?>
        <?php do { ?>
          <tr>
            <td width="138"><?php echo $row_student['stdid']; ?></td>
            <td width="277"><a href="profile.php?stid=<?php echo $row_student['stdid']; ?>"><?php echo $row_student['lname']; ?></a><a href="profile.php?stid=<?php echo $row_student['stdid']; ?>">, <?php echo ucwords(strtolower($row_student['fname'])); ?></a></td>
            <td width="148"><?php if($allow){?><a href="../registration/viewform.php?stid=<?php echo $row_student['stdid'];?>"><?php echo "Course Form"?></a><?php }?></td>
            <td width="101"><?php if($allow){?><a href="../result/transcript.php?stid=<?php echo $row_student['stdid'];?>"><?php echo "Transcript"?></a><?php }?></td>
          </tr>
          <?php } while ($row_student = mysql_fetch_assoc($student));?>
          
        <?php mysql_free_result($student);} // Show if recordset not empty ?>
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

mysql_free_result($col);

?>