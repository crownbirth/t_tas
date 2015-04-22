<?php require_once('../../Connections/tams.php'); ?>
<?php
$acl = array(1);

if (!isset($_SESSION)) {
  session_start();
}
	
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form2")) {
	if( isset($_POST['ref'])){
		
		mysql_select_db($database_tams, $tams);
		 $deleteSQL = sprintf("DELETE FROM result WHERE stdid = %s AND sesid = %s",
						   GetSQLValueString($_POST['stid'], "text"),
						   GetSQLValueString($_POST['sid'], "int"));
		$Result = mysql_query($deleteSQL, $tams) or die(mysql_error());
		
		mysql_select_db($database_tams, $tams);
		 $deleteSQL = sprintf("DELETE FROM result_buffer WHERE stdid = %s AND sesid = %s",
						   GetSQLValueString($_POST['stid'], "text"),
						   GetSQLValueString($_POST['sid'], "int"));
		$Result = mysql_query($deleteSQL, $tams) or die(mysql_error());

		foreach($_POST['ref'] AS $ref) {
			$ref= htmlentities($ref);						
			
		  $insertSQL = sprintf("INSERT INTO result (stdid, csid, sesid) VALUES (%s, %s, %s)",
							   GetSQLValueString($_POST['stid'], "text"),
							   GetSQLValueString($ref, "text"),
							   GetSQLValueString($_POST['sid'], "int"));
		
		  $Result = mysql_query($insertSQL, $tams) or die(mysql_error());
		  
		  //insert for result_buffer		  
		  $insertSQL = sprintf("INSERT INTO result_buffer (stdid, csid, sesid) VALUES (%s, %s, %s)",
							   GetSQLValueString($_POST['stid'], "text"),
							   GetSQLValueString($ref, "text"),
							   GetSQLValueString($_POST['sid'], "int"));
		
		  $Result = mysql_query($insertSQL, $tams) or die(mysql_error());
		}
	}
	
	if( isset($_POST['cur'])){
		
		mysql_select_db($database_tams, $tams);
		 $deleteSQL = sprintf("DELETE FROM result WHERE stdid = %s AND sesid = %s",
						   GetSQLValueString($_POST['stid'], "text"),
						   GetSQLValueString($_POST['sid'], "int"));
		$Result = mysql_query($deleteSQL, $tams) or die(mysql_error());
		
		mysql_select_db($database_tams, $tams);
		 $deleteSQL = sprintf("DELETE FROM result_buffer WHERE stdid = %s AND sesid = %s",
						   GetSQLValueString($_POST['stid'], "text"),
						   GetSQLValueString($_POST['sid'], "int"));
		$Result = mysql_query($deleteSQL, $tams) or die(mysql_error());
		
		foreach($_POST['cur'] AS $cur) {
			$cur = htmlentities($cur);
						
			$insertSQL = sprintf("INSERT INTO result (stdid, csid, sesid) VALUES (%s, %s, %s)",
							   GetSQLValueString($_POST['stid'], "text"),
							   GetSQLValueString($cur, "text"),
							   GetSQLValueString($_POST['sid'], "int"));
		
		  $Result = mysql_query($insertSQL, $tams) or die(mysql_error());
		  
		  //insert for result_buffer			
		  $insertSQL = sprintf("INSERT INTO result_buffer (stdid, csid, sesid) VALUES (%s, %s, %s)",
							   GetSQLValueString($_POST['stid'], "text"),
							   GetSQLValueString($cur, "text"),
							   GetSQLValueString($_POST['sid'], "int"));
		
		  $Result = mysql_query($insertSQL, $tams) or die(mysql_error());
		}
	}
	
	$updateSQL = sprintf("UPDATE registration SET course = %s",
						   GetSQLValueString("Registered", "text"));
	
	  mysql_select_db($database_tams, $tams);
	  $Result = mysql_query($updateSQL, $tams) or die(mysql_error());
}

mysql_select_db($database_tams, $tams);
$query_sess = "SELECT * FROM `session` ORDER BY sesid ASC LIMIT 0,1";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

$colname_stud = "-1";
if (isset($_GET['stid'])) {
  $colname_stud = $_GET['stid'];
}

mysql_select_db($database_tams, $tams);
$query_stud = sprintf("SELECT s.stdid, s.fname, s.lname, s.level, s.progid, p.progname, d.deptname FROM student s, programme p, department d WHERE s.progid = p.progid AND p.deptid = d.deptid AND stdid = %s", GetSQLValueString($colname_stud, "text"));
$stud = mysql_query($query_stud, $tams) or die(mysql_error());
$row_stud = mysql_fetch_assoc($stud);
$totalRows_stud = mysql_num_rows($stud);

$colname_ref = "-1";
if (isset($_GET['stid'])) {
  $colname_ref = $_GET['stid'];
}

$colname_regStatus = "-1";
if (isset($colname_stud)) {
  $colname_regStatus = $colname_stud;
}
$colname_regStatus1 = "-1";
if (isset($row_sess['sesid'])) {
  $colname_regStatus1 = $row_sess['sesid'];
}

mysql_select_db($database_tams, $tams);
$query_regStatus = sprintf("SELECT * FROM registration WHERE stdid = %s AND sesid = %s", GetSQLValueString($colname_regStatus, "text"), GetSQLValueString($colname_regStatus1, "int"));
$regStatus = mysql_query($query_regStatus, $tams) or die(mysql_error());
$row_regStatus = mysql_fetch_assoc($regStatus);
$totalRows_regStatus = mysql_num_rows($regStatus);

mysql_select_db($database_tams, $tams);
$query_course = sprintf("SELECT r.csid, c.semester, c.csname, d.status, d.unit FROM result r, course c, department_course d WHERE r.stdid = %s AND c.csid = r.csid AND d.csid = r.csid AND r.sesid = %s ORDER BY r.csid, c.semester ASC", GetSQLValueString($colname_regStatus, "text"),GetSQLValueString($colname_regStatus1, "int"));
$course = mysql_query($query_course, $tams) or die(mysql_error());
$row_course = mysql_fetch_assoc($course);
$totalRows_course = mysql_num_rows($course);

$sesReg = false;
$row_regStatus['status'];
if($row_regStatus['status'] == "Registered" )
	$sesReg = true;

$crsReg = false;
if($row_regStatus['course'] == "Registered" )
	$crsReg = true;

mysql_select_db($database_tams, $tams);
$query_ref = sprintf("SELECT DISTINCT r.csid, d.status, unit FROM `result` r, department_course d, student s WHERE d.csid = r.csid AND r.stdid = s.stdid AND d.progid = s.progid AND tscore + escore < 40 AND r.stdid = %s AND r.sesid <> %s AND r.csid NOT IN ( SELECT csid FROM result WHERE stdid = %s AND tscore + escore > 39 AND sesid <> %s )", 
										GetSQLValueString($colname_stud, "text"), 
										GetSQLValueString($colname_regStatus1, "int"),
										GetSQLValueString($colname_stud, "text"), 
										GetSQLValueString($colname_regStatus1, "int"));
$ref = mysql_query($query_ref, $tams) or die(mysql_error());
$row_ref = mysql_fetch_assoc($ref);
$totalRows_ref = mysql_num_rows($ref);

$colname_cur = "-1";
if (isset($row_stud['progid'])) {
  $colname_cur = $row_stud['progid'];
}

$colname_cur1 = "-1";
if (isset($row_stud['level'])) {
  $colname_cur1 = "___".$row_stud['level']."%";
}
mysql_select_db($database_tams, $tams);
$query_cur = sprintf("SELECT csid, status, unit FROM department_course WHERE progid = %s AND csid LIKE %s",
 									GetSQLValueString($colname_cur, "int"),
									GetSQLValueString($colname_cur1, "text"));
$cur = mysql_query($query_cur, $tams) or die(mysql_error());
$row_cur = mysql_fetch_assoc($cur);
$totalRows_cur = mysql_num_rows($cur);

$colname_reg = "-1";
if (isset($colname_regStatus1)) {
  $colname_reg = $colname_regStatus1;
}
mysql_select_db($database_tams, $tams);
$query_reg = sprintf("SELECT r.csid, dc.unit FROM `result` r, department_course dc WHERE r.stdid=%s AND dc.csid = r.csid AND sesid = %s AND dc.progid=%s", 
			GetSQLValueString($colname_stud, "text"), 
			GetSQLValueString($colname_reg, "int"), 
			GetSQLValueString($row_stud['progid'], "int"));
$reg = mysql_query($query_reg, $tams) or die(mysql_error());
$row_reg = mysql_fetch_assoc($reg);
$totalRows_reg = mysql_num_rows($reg);

$tunit= 0;
$checked = array();
do{
	$checked[] =  $row_reg['csid'];
	$tunit += $row_reg['unit'];
}while( $row_reg = mysql_fetch_assoc($reg) );

$name = ( isset($row_stud['stdid']) ) ? "for ".$row_stud['lname']." ".$row_stud['fname']." - ".$row_sess['sesname']." Session": "";

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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Add/Delete Course <?php echo $name?><!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
      <tr>
      	 <td>&nbsp;</td>
      </tr>
      
      <tr>
        <td>
            <form action="<?php echo $editFormAction; ?>" method="post" name="form2" id="form2">
                <table width="644">
                  <tr>
                    <td colspan="3" valign="top">
                    	Max Unit Allowed: <span id="max"><?php echo $row_sess['tnumax']?></span><br/>
                        Min Unit Allowed: <span id="min"><?php echo $row_sess['tnumin']?></span><br/>
                        Registered Units: <span id="reg"><?php echo $tunit?></span><br/>
                        Remaining Units: <span id="rem"><?php echo $row_sess['tnumax']-$tunit?></span><br/>
                    </td>
                  </tr>
                  <tr>
                    <td width="290" valign="top">
                    	<fieldset>
                        	<legend>Carry Over</legend>
                            <?php if ($totalRows_ref > 0) { // Show if recordset not empty ?>
  <?php do{?>
                              <div>
                                <p style="float:left;"><?php echo $row_ref['csid']?> (<span><?php echo $row_ref['unit']?></span> Units, <?php echo $row_ref['status'];?>)</p>
                                 <p style="float:right;"><input type="checkbox" class="cbox" name="ref[]" value="<?php echo $row_ref['csid']?>" <?php if( in_array($row_ref['csid'], $checked) ) echo "checked";?>/></p>
                                <div style="clear:both;"></div>
                              </div>
                              <?php }while( $row_ref = mysql_fetch_assoc($ref) );?>
                              <?php }else{
								  	echo "There are no carry over courses.";
								  } // Show if recordset not empty ?>
                        </fieldset>
                    </td>
                    <td width="45" valign="top">&nbsp;</td>
                    <td width="293" valign="top">
                    	<fieldset>
                        	<legend>Current</legend>
                             <?php if ($totalRows_cur > 0) { // Show if recordset not empty ?>
                            <?php do{?>
                            <div>
								<p style="float:left;"><?php echo $row_cur['csid']?> (<span><?php echo $row_cur['unit']?></span> Units, <?php echo $row_cur['status']?>)</p>
                                <p style="float:right;"><input type="checkbox" class="cbox" name="cur[]" value="<?php echo $row_cur['csid']?>" <?php if( in_array($row_cur['csid'], $checked) ) echo "checked";?>/></p>
                                <div style="clear:both;"></div>
                            </div>
                             <?php }while( $row_cur = mysql_fetch_assoc($cur) );?>
                              <?php }else{
								  	echo "";
								  } // Show if recordset not empty ?>

                        </fieldset></td>
                  </tr>
                  <tr>
                    <td colspan="3" align="center">&nbsp;</td>
                  </tr>
                  <tr>
                    <td colspan="3" align="center">
                    <input type="submit" name="submit" id="submit" value="Register Courses" disabled
                    />
                    </td>
                  </tr>
                </table>
                
          <input name="stid" type="hidden" value="<?php echo $colname_stud?>" />
          <input name="sid" type="hidden" value="<?php echo $row_sess['sesid']?>" />
          <input type="hidden" name="MM_insert" value="form2" />
             </form>
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
mysql_free_result($sess);

mysql_free_result($reg);
?>
