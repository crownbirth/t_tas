<?php require_once('../../Connections/tams.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
require_once('../../param/param.php');
require_once('../../functions/function.php');

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

$MM_restrictGoTo = "../../login.php";
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
$acl = array(1);

if (!isset($_SESSION)) {
  session_start();
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

$colname_stud = "-1";
if (isset($_GET['stid'])) {
  $colname_stud = $_GET['stid'];
}

mysql_select_db($database_tams, $tams);
$query_stud = sprintf("SELECT s.stdid, s.fname, s.lname, s.level, s.progid, p.progname, d.deptname FROM student s, programme p, department d WHERE s.progid = p.progid AND p.deptid = d.deptid AND stdid = %s", GetSQLValueString($colname_stud, "text"));
$stud = mysql_query($query_stud, $tams) or die(mysql_error());
$row_stud = mysql_fetch_assoc($stud);
$totalRows_stud = mysql_num_rows($stud);


mysql_select_db($database_tams, $tams);
$query_regsess = sprintf("SELECT s.* FROM session s, registration r WHERE r.sesid = s.sesid AND r.status=%s AND r.stdid=%s ORDER BY sesname DESC", 
						GetSQLValueString("Registered", "text"), 
						GetSQLValueString($colname_stud, "text"));
$regsess = mysql_query($query_regsess, $tams) or die(mysql_error());
$row_regsess = mysql_fetch_assoc($regsess);
$totalRows_regsess = mysql_num_rows($regsess);

$colname_course = "-1";
if (isset($colname_stud)) {
  $colname_course = $colname_stud;
}

$colname1_course = "-1";
if (isset($row_regsess['sesid'])) {
  $colname1_course = $row_regsess['sesid'];
}

if (isset($_GET['sid'])) {
  $colname1_course = $_GET['sid'];
}

$colname2_course = "-1";
if (isset($row_stud['progid'])) {
  $colname2_course = $row_stud['progid'];
}
mysql_select_db($database_tams, $tams);
$query_course = sprintf("SELECT r.csid, c.semester, c.csname, dc.status, dc.unit FROM result r, course c, department_course dc WHERE c.csid = dc.csid AND c.csid = r.csid AND dc.csid = r.csid AND r.stdid = %s AND r.sesid = %s AND dc.progid = %s ORDER BY c.semester ASC", 
							GetSQLValueString($colname_course, "text"), 
							GetSQLValueString($colname1_course, "int"), 
							GetSQLValueString($colname2_course, "int"));
$course = mysql_query($query_course, $tams) or die(mysql_error());
$row_course = mysql_fetch_assoc($course);
$totalRows_course = mysql_num_rows($course);

$name = ( isset($row_stud['lname']) ) ? "for ".$row_stud['lname']." ".$row_stud['fname']." (".$row_stud['stdid'].")": "";

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
<script type="text/javascript" src="../../scripts/jquery.js"></script>
<script type="text/javascript" src="../../scripts/tams.js"></script>
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Registered Courses <?php echo $name?><!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">     
      <tr>
      	<td>
       	  <table border="0" align="center">
              <tr>
                <td colspan="3" align="right"></td>
              </tr>              
              <tr>
                <td >
                <a href="editform.php?stid=<?php echo $colname_stud?>">Add/Delete</a>
                </td>
                <td align="right">
                <select name="sesid" onchange="sesfilt(this)">
                    <?php
                    do {  
                    ?>
                         <option value="<?php echo $row_regsess['sesid']?>"<?php if (!(strcmp($row_regsess['sesid'], $colname1_course))) {echo "selected=\"selected\"";} ?>><?php echo $row_regsess['sesname']?></option>
                                              <?php
                    } while ($row_regsess = mysql_fetch_assoc($regsess));
                      $rows = mysql_num_rows($regsess);
                      if($rows > 0) {
                          mysql_data_seek($regsess, 0);
                          $row_regsess = mysql_fetch_assoc($regsess);
                      }
                    ?>
                  </select>
                &nbsp;&nbsp; Session
                </td>
              </tr>
              <tr>
                <td colspan="3">
                <table width="680" border="0" id="ctable">
                  <tr>
                    <th width="100" align="center">COURSE CODE</th>
                    <th width="410" align="center">COURSE NAME</th>
                    <th width="80" align="center">STATUS</th>
                    <th width="30">UNIT</th>
                    <th width="70" align="center">SEMESTER</th>
                  </tr>
                  <?php 
				  	$tunits = 0;
				  	if ($totalRows_course > 0) { // Show if recordset not empty  ?>
				  <?php                  		
						do { 
							 ?>
                    <tr>
                      <td><div align="center"><?php echo $row_course['csid']; ?></div></td>
                      <td><?php echo ucwords(strtolower($row_course['csname'])); ?></td>
                      <td><div align="center"><?php echo $row_course['status']; ?></div></td>
                      <td><div align="center"><?php echo $row_course['unit'];$tunits += $row_course['unit'];?></div></td>
                      <td><div align="center"><?php echo (strtolower($row_course['semester']) == "f")? "First": "Second" ;?></div></td>
                    </tr>
                    <?php } while ($row_course = mysql_fetch_assoc($course)); ?>
                                    <?php } // Show if recordset not empty ?>
                	<tr>
                    <td colspan="3" align="right" >Total Units</td>
                        <td align="center"><?php echo $tunits;?></td>
                        <td></td>
                    </tr>
                </table></td>
              </tr>
              <tr>
                <td colspan="3">&nbsp;</td>
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
<?php
mysql_free_result($stud);

mysql_free_result($regsess);

mysql_free_result($course);
?>