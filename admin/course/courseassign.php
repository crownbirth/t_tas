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

$MM_restrictGoTo = "../../index.php";
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

require_once('../../param/param.php');
require_once('../../functions/function.php');

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
$prog = "";
$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	
	$deleteSQL = sprintf("DELETE FROM department_course WHERE progid=%s AND csid NOT IN ( SELECT r.csid FROM result r, student s WHERE r.stdid = s.stdid AND s.progid=%s)",
                       GetSQLValueString($_POST['progid'], "int"),
                       GetSQLValueString($_POST['progid'], "int"));

	mysql_select_db($database_tams, $tams);
	$Result1 = mysql_query($deleteSQL, $tams) or die(mysql_error());
	
	//var_dump($_POST['status']);
	$prog = $_POST['progid'];
	for( $i = 0; $i < count($_POST['course']); $i++ ){
		$crs = $_POST['course'][$i];
		$sts = $_POST['status'][$i];
		$unt = $_POST['unit'][$i];
		
		//delete existing entry in department_course. Note: should fail if registered for by student already
		$updateSQL = sprintf("UPDATE department_course SET status=%s, unit=%s WHERE progid=%s AND csid = %s",
                       GetSQLValueString($sts, "text"),
                       GetSQLValueString($unt, "int"),
                       GetSQLValueString($_POST['progid'], "int"),
                       GetSQLValueString($crs, "text"));
		
		mysql_select_db($database_tams, $tams);
		$Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());
		$update_info = mysql_info($tams);
		list($f,$s,$t) = explode(":", $update_info);
		
		if(  strpos($s,"0")  ){ //insert new entry into department_course
			$insertSQL = sprintf("INSERT INTO department_course (deptid, progid, csid, status, unit) VALUES (%s, %s, %s, %s, %s)",
						   GetSQLValueString($_POST['deptid'], "int"),
						   GetSQLValueString($_POST['progid'], "int"),
						   GetSQLValueString($crs, "text"),
						   GetSQLValueString($sts, "text"),
						   GetSQLValueString($unt, "int"));
	
			mysql_select_db($database_tams, $tams);
			$Result1 = mysql_query($insertSQL, $tams) or die(mysql_error());
		}
	}
}

mysql_select_db($database_tams, $tams);
$query_college = "SELECT colid, coltitle FROM college";
$college = mysql_query($query_college, $tams) or die(mysql_error());
$row_college = mysql_fetch_assoc($college);
$totalRows_college = mysql_num_rows($college);

$colname_dept = "-1";
if ( isset($row_college['colid']) ) {
  $colname_dept = $row_college['colid'];
}

if (isset($_GET['cid'])) {
  $colname_dept = $_GET['cid'];
}

mysql_select_db($database_tams, $tams);
$query_dept = sprintf("SELECT deptid, deptname FROM department WHERE colid = %s", GetSQLValueString($colname_dept, "int"));
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);

//var_dump($_SESSION);
$colname_courses = "-1";
if (isset($row_dept['deptid'])) {
  $colname_courses = $row_dept['deptid'];
}

if (isset($_GET['did'])) {
  $colname_courses = $_GET['did'];
}


mysql_select_db($database_tams, $tams);
$query_courses = sprintf("SELECT c.csid, c.csname, d.colid, ct.catname FROM course c, category ct, department d WHERE d.deptid = c.deptid AND ct.catid = c.catid AND (c.type = 'College' AND d.colid = %s OR c.deptid = %s OR c.type = 'General') ORDER BY c.csid",
			GetSQLValueString($colname_dept,"int"),
			GetSQLValueString($colname_courses,"int"));
			
$courses = mysql_query($query_courses, $tams) or die(mysql_error());
$row_courses = mysql_fetch_assoc($courses);
$totalRows_courses = mysql_num_rows($courses);

$colname_prog = "-1";
if (isset($colname_courses)) {
  $colname_prog= $colname_courses;
}

mysql_select_db($database_tams, $tams);
$query_prog = sprintf("SELECT * FROM programme WHERE deptid = %s", GetSQLValueString($colname_prog, "int"));
$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);
$totalRows_prog = mysql_num_rows($prog);

$colname_deptcrs = "-1";
if ( isset($row_prog['progid']) ) {
  $colname_deptcrs= $row_prog['progid'];
}

if ( isset($_GET['pid']) ) {
  $colname_deptcrs = $_GET['pid'];
}

mysql_select_db($database_tams, $tams);
$query_deptcrs = sprintf("SELECT dc.*, c.csname FROM department_course dc, course c WHERE dc.csid = c.csid AND dc.deptid=%s AND progid=%s", 
					GetSQLValueString($colname_courses,"int"),
					GetSQLValueString($colname_deptcrs,"int"));
$deptcrs = mysql_query($query_deptcrs, $tams) or die(mysql_error());
$row_deptcrs = mysql_fetch_assoc($deptcrs);
$totalRows_deptcrs = mysql_num_rows($deptcrs);

$checked = array();
do{
	$checked[] = $row_deptcrs['csid'];
	$checked[$row_deptcrs['csid']]['status'] = $row_deptcrs['status'];
	$checked[$row_deptcrs['csid']]['unit'] = $row_deptcrs['unit'];
}while( $row_deptcrs = mysql_fetch_assoc($deptcrs) );

	  
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Assign/Load Courses to Department<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
    	
      <tr>
        <td colspan="5"><form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
          <table align="center">
          	<tr>
          	  <td nowrap="nowrap" align="right">College</td>
          	  <td><label for="colid"></label>
          	    <select name="colid" id="colid" onchange="colfilt(this)">
          	      <?php
do {  
?>
          	      <option value="<?php echo $row_college['colid']?>" <?php if (!(strcmp($row_college['colid'], $colname_dept))) {echo "selected=\"selected\"";} ?>><?php echo $row_college['coltitle']?></option>
          	      <?php
} while ($row_college = mysql_fetch_assoc($college));
  $rows = mysql_num_rows($college);
  if($rows > 0) {
      mysql_data_seek($college, 0);
	  $row_college = mysql_fetch_assoc($college);
  }
?>
                </select></td>
        	  </tr>
          	<tr>
          	  <td nowrap="nowrap" align="right">Department</td>
          	  <td><label for="deptid"></label>
          	    <select name="deptid" id="deptid" onchange="deptfilt(this)">
          	      <?php
do {  
?>
          	      <option value="<?php echo $row_dept['deptid']?>" <?php if (!(strcmp($row_dept['deptid'], $colname_courses))) {echo "selected=\"selected\"";} ?>><?php echo $row_dept['deptname']?></option>
          	      <?php
} while ($row_dept = mysql_fetch_assoc($dept));
  $rows = mysql_num_rows($dept);
  if($rows > 0) {
      mysql_data_seek($dept, 0);
	  $row_dept = mysql_fetch_assoc($dept);
  }
?>
                </select></td>
        	  </tr>
          	<tr>
            	<td nowrap="nowrap" align="right">Programme:</td>
                <td>
                    <select name="progid" onchange="progfilt(this)">
                      <?php
						do {  
						?>
                      <option value="<?php echo $row_prog['progid']?>" <?php if (!(strcmp($row_prog['progid'], $colname_deptcrs))) {echo "selected=\"selected\"";} ?>><?php echo $row_prog['progname']?></option>
                      <?php
} while ($row_prog = mysql_fetch_assoc($prog));
  $rows = mysql_num_rows($prog);
  if($rows > 0) {
      mysql_data_seek($prog, 0);
	  $row_prog = mysql_fetch_assoc($prog);
  }
?>
                    </select>
                </td>
        	</tr>
            </table>
          
        <p>&nbsp;</p>
      
      
       <tr>
      	<td colspan="5">
        	<form name="assignform" action="<?php echo $editFormAction?>" method="post">
            <fieldset>
            	<legend>Departmental Courses</legend>
                <?php if ($totalRows_courses > 0) { // Show if recordset not empty  ?>
                <?php do{
					$stat = "";
					$unit = "";
					$check = "";
					if( in_array($row_courses['csid'],$checked)){
						$stat = $checked[$row_courses['csid']]['status'];
						$unit = $checked[$row_courses['csid']]['unit'];
						$check = true;
					}
				?>
                <div style="font-size:inherit">
                	<p style="float:left;">
						<?php echo $row_courses['csid']?> 
                    </p>
                    
                    <p style="float:right;">
                    	<input type="checkbox" class="cbox" name="course[]" value="<?php echo $row_courses['csid']?>" <?php if( $check ) echo "checked"?>/>
                    </p>            
                    
                    <p style="float:right;">
                        <select name="unit[]" >
                            <option value="1" <?php if($unit == 1) echo "selected"?>>1</option>
                            <option value="2" <?php if($unit == 2) echo "selected"?>>2</option>
                            <option value="3" <?php if($unit == 3) echo "selected"?>>3</option>
                            <option value="4" <?php if($unit == 4) echo "selected"?>>4</option>
                            <option value="5" <?php if($unit == 5) echo "selected"?>>5</option>
                            <option value="6" <?php if($unit == 6) echo "selected"?>>6</option>
                        </select>
                    </p>
                    
                    <p style="float:right;">
                    	<select name="status[]">
                            <option value="Compulsory" <?php if($stat == "Compulsory") echo "selected"?>>Compulsory</option>
                            <option value="Required" <?php if($stat == "Required") echo "selected"?>>Required</option>                
                            <option value="Elective" <?php if($stat == "Elective") echo "selected"?>>Elective</option>
              			</select>
                    </p>
                    
                    <p style="float:right; width:45%;">
                    	<?php echo ucwords(strtolower($row_courses['csname']))?>
                    </p>
                    <div style="clear:both;"></div>
                    
                </div>
                
                <?php }while( $row_courses = mysql_fetch_assoc($courses) );?>
                <?php }?>
            </fieldset>
            <p style="padding:0 260px"><input type="submit" name="submit" value="Assign Courses" /></p>
           <input type="hidden" name="deptid" value="<?php echo $colname_courses?>" />
          	<input type="hidden" name="MM_insert" value="form1" />
        </form>
        </td>
      </tr>      
    </table>
    <script type="text/javascript">
		$(function(){
			courseaassign();	
		});
	
	</script>
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

mysql_free_result($college);

mysql_free_result($dept);

mysql_free_result($deptcrs);
?>
