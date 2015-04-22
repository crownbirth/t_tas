<?php require_once('../Connections/tams.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "4";
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
$prog = "";
$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	
	$prog = 0;
	//delete existing entry in department_course. Note: should fail if registered for by student already
	$deleteSQL = sprintf("DELETE FROM department_course WHERE progid=%s AND csid NOT IN ( SELECT DISTINCT r.csid FROM result r, student s WHERE r.stdid = s.stdid)",
                       GetSQLValueString($prog, "int"));

	mysql_select_db($database_tams, $tams);
	$Result1 = mysql_query($deleteSQL, $tams) or die(mysql_error());
	
	if( isset($_POST['course']) )
	for( $i = 0; $i < count($_POST['course']); $i++ ){
		$crs = $_POST['course'][$i];
		$sts = $_POST['status'][$i];
		$unt = $_POST['unit'][$i];
		$dpt = $_POST['dept'][$i];

		$updateSQL = sprintf("UPDATE department_course SET status=%s, unit=%s WHERE progid=%s AND csid = %s",
                       GetSQLValueString($sts, "text"),
                       GetSQLValueString($unt, "int"),
                       GetSQLValueString($prog, "int"),
                       GetSQLValueString($crs, "text"));
		
		mysql_select_db($database_tams, $tams);
		$Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());
		$update_info = mysql_info($tams);
		list($f,$s,$t) = explode(":", $update_info);
		
		if(  strpos($s,"0")  ){ //insert new entry into department_course
			$insertSQL = sprintf("INSERT INTO department_course (deptid, progid, csid, status, unit) VALUES (%s, %s, %s, %s, %s)",
						   GetSQLValueString($dpt, "int"),
						   GetSQLValueString($prog, "int"),
						   GetSQLValueString($crs, "text"),
						   GetSQLValueString($sts, "text"),
						   GetSQLValueString($unt, "int"));
	
			mysql_select_db($database_tams, $tams);
			$Result1 = mysql_query($insertSQL, $tams) or die(mysql_error());
		}
	}
}


mysql_select_db($database_tams, $tams);
$query_courses = sprintf("SELECT c.csid, c.csname, c.deptid FROM course c, category ct WHERE c.catid=ct.catid AND c.type = 'General' AND ct.type=1 ORDER BY c.csid");		
$courses = mysql_query($query_courses, $tams) or die(mysql_error());
$row_courses = mysql_fetch_assoc($courses);
$totalRows_courses = mysql_num_rows($courses);

$colname_deptcrs = "-1";
if ( isset($row_prog['progid']) ) {
  $colname_deptcrs= $row_prog['progid'];
}

if ( isset($_GET['pid']) ) {
  $colname_deptcrs = $_GET['pid'];
}

mysql_select_db($database_tams, $tams);
$query_deptcrs = sprintf("SELECT * FROM department_course dc WHERE progid=0");
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Assign Unit/Status to General Courses<!-- InstanceEndEditable --></td>
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
      	<td colspan="5">
        	<form name="assignform" action="<?php echo $editFormAction?>" method="post">
            <fieldset>
            	<legend>Courses</legend>
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
                    
                    <input type="text"  style="display:none" name="dept[]" class="dept" value="<?php echo $row_courses['deptid']?>" <?php if(!$check)echo "disabled";?>/>
                    <div style="clear:both;"></div>                 
                </div>
                
                <?php }while( $row_courses = mysql_fetch_assoc($courses) );?>
                <?php }?>
            </fieldset>
            <p style="padding:0 260px"><input type="submit" name="submit" value="Assign Courses" /></p>
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
    
    <?php require '../include/footer.php'; ?>
	
   </p>
  </div>
  <!-- end .container -->
</div>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($courses);

mysql_free_result($deptcrs);
?>