<?php require_once('../Connections/tams.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
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

$prog = "";
$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
    if(isset($_POST['course'])) {
        for($i = 0; $i < count($_POST['course']); $i++ ) {
            $crs = $_POST['course'][$i];
            $sts = $_POST['status'][$i];
            $unt = $_POST['unit'][$i];
            $lvl = $_POST['level'][$i];

            // Update course table
            $updateSQL = sprintf("UPDATE course SET status=%s, unit=%s, level = %s WHERE deptid = %s AND csid = %s",
                   GetSQLValueString($sts, "text"),
                   GetSQLValueString($unt, "int"),
                   GetSQLValueString($lvl, "int"),
                   GetSQLValueString($_POST['deptid'], "int"),
                   GetSQLValueString($crs, "text"));

            $Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());
            $update_info = mysql_info($tams);
        }
    }
}

$colname_dept = "-1";
if ( getSessionValue('cid') != NULL ) {
  $colname_dept = getSessionValue('cid');
}

$query_dept = sprintf("SELECT deptid, deptname FROM department WHERE colid = %s", GetSQLValueString($colname_dept, "int"));
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);

$colname_courses = "-1";
if (isset($row_dept['deptid'])) {
  $colname_courses = $row_dept['deptid'];
}

if (isset($_GET['did'])) {
  $colname_courses = $_GET['did'];
}

if ( getAccess() == 3 ) {
  $colname_courses = getSessionValue('did');
}

$query_info = sprintf("SELECT MAX(duration) AS max "
        . "FROM programme "
        . "WHERE deptid = %s", GetSQLValueString($colname_courses, "int"));
$info = mysql_query($query_info, $tams) or die(mysql_error());
$row_info = mysql_fetch_assoc($info);
$totalRows_info = mysql_num_rows($info);

$duration = ($totalRows_info > 0)? $row_info['max']: 0;

$query_courses = sprintf("SELECT c.csid, c.csname, d.colid, ct.catname, c.status, c.unit, c.level "
                    . "FROM course c, category ct, department d "
                    . "WHERE d.deptid = c.deptid AND ct.catid = c.catid "
                    . "AND c.deptid = %s "
                    . "ORDER BY c.csid",
                    GetSQLValueString($colname_courses,"int"));

$courses = mysql_query($query_courses, $tams) or die(mysql_error());
$row_courses = mysql_fetch_assoc($courses);
$totalRows_courses = mysql_num_rows($courses);

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
    doLogout( $site_root );  
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <!-- InstanceBegin template="/Templates/template.dwt.php" codeOutsideHTMLIsLocked="false" -->
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Assign Courses to Department<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
    	
      <tr>
        <td colspan="5">
          <table align="center">
          <?php if( getAccess() == 2 ){?>
          	<tr>
          	  <td nowrap="nowrap" align="right">Department</td>
          	  <td><label for="deptid"></label>
          	    <select name="deptid" id="deptid" onchange="deptfilt(this)">
          	      <?php
                        do {  
                      ?>
          	      <option value="<?php echo $row_dept['deptid']?>" 
                          <?php if (!(strcmp($row_dept['deptid'], $colname_courses))) {echo "selected=\"selected\"";}?>>
                                  <?php echo $row_dept['deptname']?>
                      </option>
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
              <?php }?>             
            </table>
          
        <p>&nbsp;</p></td>
      </tr>
        
    <tr>
        <td colspan="5">
            <form name="assignform" action="<?php echo $editFormAction?>" method="post">
                <table class="table table-striped">
                    
                    <tr>
                        <td>
                          <fieldset>
                              <legend>Departmental Courses</legend>
                              <?php if ($totalRows_courses > 0) { // Show if recordset not empty  ?>
                              <?php do{
                                      $stat = $row_courses['status'];
                                      $unit = $row_courses['unit'];
                                      $level = $row_courses['level'];
                              ?>
                              <div style="font-size:inherit">
                                  <p style="float:left; padding: 0 2px">
                                      <?php echo $row_courses['csid']?> 
                                  </p>

                                  <p style="float:right; padding: 0 2px">
                                      <input type="checkbox" class="cbox" name="course[]" value="<?php echo $row_courses['csid']?>"/>
                                  </p>            

                                  <p style="float:right; padding: 0 2px">
                                      <select name="unit[]" >
                                          <option value="1" <?php if($unit == 1) echo "selected"?>>1</option>
                                          <option value="2" <?php if($unit == 2) echo "selected"?>>2</option>
                                          <option value="3" <?php if($unit == 3) echo "selected"?>>3</option>
                                          <option value="4" <?php if($unit == 4) echo "selected"?>>4</option>
                                          <option value="5" <?php if($unit == 5) echo "selected"?>>5</option>
                                          <option value="6" <?php if($unit == 6) echo "selected"?>>6</option>
                                      </select>
                                  </p>

                                  <p style="float:right; padding: 0 2px">
                                      <select name="status[]">
                                          <option value="Compulsory" <?php if($stat == "Compulsory") echo "selected"?>>Compulsory</option>
                                          <option value="Required" <?php if($stat == "Required") echo "selected"?>>Required</option>                
                                          <option value="Elective" <?php if($stat == "Elective") echo "selected"?>>Elective</option>
                                              </select>
                                  </p>

                                  <p style="float:right; padding: 0 2px">
                                      <select name="level[]">
                                          <?php for($idx = 1; $idx <= $duration; $idx++) {?>
                                          <option value="<?php echo $idx?>" <?php if($idx == $level) echo "selected"?>>
                                              <?php echo $idx.'00'?>
                                          </option>
                                          <?php }?>
                                      </select>
                                  </p>
                                  
                                  <p style="float:right; width:53%; padding: 0 2px">
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
                        </td>
                    </tr>     
                </table>
            </form>

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
    <script type="text/javascript">
        $(function(){
            courseassign();	
        });
	
    </script>
<!-- InstanceEnd -->
</html>
<?php
mysql_free_result($courses);

mysql_free_result($dept);
?>
