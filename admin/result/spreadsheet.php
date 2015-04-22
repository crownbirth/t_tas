<?php require_once('../../Connections/tams.php'); ?>
<?php 
require_once('../../param/param.php');
require_once('../../functions/function.php'); 
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
$did = $_GET['pid'];

$pid = "-1";
if( isset( $_GET['pid'] ) )	
$pid = $_GET['pid'];

$sid = "-1";
if( isset( $_GET['sid'] ) )	
$sid = $_GET['sid'];

$cid = "-1";
if( isset( $_GET['cid'] ) )	
$cid = $_GET['cid'];

$allow = false;
$acl = array(4,5);
if( getAccess() == 1 || (getAccess() == 2 && getSessionValue('cid') == $cid) || (getAccess() == 3 && getSessionValue('did') == $did) || (in_array(getAccess(), $acl) && getSessionValue('did') == $did) ){
	 $allow = true;
}

mysql_select_db($database_tams, $tams);
$query_Rssess = "SELECT * FROM `session` ORDER BY sesname DESC";
$Rssess = mysql_query($query_Rssess, $tams) or die(mysql_error());
$row_Rssess = mysql_fetch_assoc($Rssess);
$totalRows_Rssess = mysql_num_rows($Rssess);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/template.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php require('../../param/site.php'); ?>
<script src="../../scripts/tams.js" type="text/javascript"></script>
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Broadsheet <!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
      <form name="broadsheet" method="post" action="smprint.php" target="_blank">
    <table width="690">
   
      <tr>
        <td colspan="4"></td>
      </tr>
      <tr>
        <td colspan="4"></td>
      </tr>
      <tr>
        <td height="56" colspan="2" align="center">Choose College<br/>
        
          <select name="col" id="col" onchange="colFilter(this)">
            <option value="-1" <?php if (!(strcmp(-1, $cid))) {echo "selected=\"selected\"";} ?> >---Select A College---</option>
            <?php
do {  
?>
<option value="<?php echo $row_col['colid']?>" <?php if (!(strcmp($row_col['colid'], $cid))) {echo "selected=\"selected\"";}?>><?php echo $row_col['coltitle']?></option>
<?php
} while ($row_col = mysql_fetch_assoc($col));
  $rows = mysql_num_rows($col);
  if($rows > 0) {
      mysql_data_seek($col, 0);
	  $row_col = mysql_fetch_assoc($col);
  }
?>
          </select></td>        
        <td width="448" align="center">Choose Programme<br/>
         
          <select name="prog" id="prog" onchange="progFilter(this)" style="width:200px">
            <option value="-1" <?php if(!(strcmp(-1, $pid))) {echo "selected=\"selected\"";} ?>>---Select A Programme---</option>
            <?php
			$rows = mysql_num_rows($prog);
  if($rows > 0) {
      mysql_data_seek($prog, 0);
	  $row_dept = mysql_fetch_assoc($prog);
  }
do {  
?>
            <option value="<?php echo $row_prog['progid']?>" <?php if (!(strcmp($row_prog['progid'], $pid))) {echo "selected=\"selected\"";} ?>><?php echo $row_prog['progname']?></option>
            <?php
} while ($row_prog = mysql_fetch_assoc($prog));
?>
          </select></td>
        </tr>
      
    </table>
    <table width="668" align="center">
    <tr><td width="146" height="59"><table width="625" border="0" align="center">
      <tr>
        <td width="120" height="59" align="center">Choose session
          <select name="session" id="session" onchange="sesFilter(this)">
          <option value="-1">--Session--</option>
          <?php
do {  
?>
          <option value="<?php echo $row_Rssess['sesid']?>" <?php if (!(strcmp($row_Rssess['sesid'], $sid))) {echo "selected=\"selected\"";} ?>><?php echo $row_Rssess['sesname']?></option>
          <?php
} while ($row_Rssess = mysql_fetch_assoc($Rssess));
  $rows = mysql_num_rows($Rssess);
  if($rows > 0) {
      mysql_data_seek($Rssess, 0);
	  $row_Rssess = mysql_fetch_assoc($Rssess);
  }
?>
        </select></td>
        <td width="101" align="center">Choose Level
          <select name="level" id="level">
          <option value="-1">--Level--</option>
          <option value="1">100</option>
          <option value="2">200</option>
          <option value="3">300</option>
          <option value="4">400</option>
        </select></td>
        <td width="156" align="center">
        Choose Semester
        <select name="semester" id="semester2">
          <option value="-1">--Semester--</option>
          <option value="F">First</option>
          <option value="S">Second</option>
        </select>
          </td>
        <td width="207" align="center"><input type="submit" name="submit" id="submit" value="Process Broadsheet" /></td>
        </tr>
    </table></td></tr>
    
    </table>
    <input type="hidden" name="sid" value="<?php echo $sid?>" />
    <input type="hidden" name="pid" value="<?php echo $pid?>" />
    <input type="hidden" name="cid" value="<?php echo $cid?>" />
    </form>
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

mysql_free_result($prog);

mysql_free_result($col);

mysql_free_result($Rssess);

//mysql_free_result($deptcrs);


?>