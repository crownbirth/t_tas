<?php require_once('../Connections/tams.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
require_once('../param/param.php');
require_once('../functions/function.php');

$MM_authorizedUsers = "1,2,3,4,5,6,10,20";
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
?>
<?php
$acl = array(2,3);

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

mysql_select_db($database_tams, $tams);
$query_rssess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,1";
$rssess = mysql_query($query_rssess, $tams) or die(mysql_error());
$row_rssess = mysql_fetch_assoc($rssess);
$totalRows_rssess = mysql_num_rows($rssess);


$query = '';
if(getAccess() == 3) {
    $query = "AND p.deptid = ".  GetSQLValueString(getSessionValue('did'), 'int');
}

if(getAccess() == 2) {
    $query = "AND d.colid = ".  GetSQLValueString(getSessionValue('cid'), 'int');
}

// Recordset to populate programme dropdown
$query_prog = sprintf("SELECT p.progid, p.progname, d.colid, p.deptid "
                        . "FROM programme p, department d "
                        . "WHERE d.deptid = p.deptid %s", 
                        GetSQLValueString($query, "defined", $query));
$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);
$totalRows_prog = mysql_num_rows($prog);

$level = 1;
$prg = $row_prog['progid'];

if(isset($_GET['lvl'])) {
    $level = $_GET['lvl'];
}

if(isset($_GET['pid'])) {
    $prg = $_GET['pid'];
}

$colname_stud = "-1";
if (isset($_GET['stid'])) {
  $colname_stud = $_GET['stid'];
}

if (getAccess() < 7 && isset($_GET['stid'])) {
  $colname_stud = $_GET['stid'];
}

if (getAccess() < 7 && !isset($_GET['stid'])) {
    $query_std = sprintf("SELECT s.stdid, s.progid, colid, p.deptid, fname, lname, level "
                            . "FROM student s, programme p, department d "
                            . "WHERE s.progid = p.progid AND d.deptid = p.deptid "
                            . "AND s.progid = %s AND s.level = %s", 
                            GetSQLValueString($prg, "text"), 
                            GetSQLValueString($level, "text"));
    $std = mysql_query($query_std, $tams) or die(mysql_error());
    $row_std = mysql_fetch_assoc($std);
    $totalRows_std = mysql_num_rows($std);
    
    if($totalRows_std > 0) {
        $colname_stud = $row_std['stdid'];
    }
}

$query_stud = sprintf("SELECT s.stdid, s.fname, s.lname, s.level, s.progid, p.progname, d.deptname "
        . "FROM student s, programme p, department d "
        . "WHERE s.progid = p.progid "
        . "AND p.deptid = d.deptid "
        . "AND stdid = %s", GetSQLValueString($colname_stud, "text"));
$stud = mysql_query($query_stud, $tams) or die(mysql_error());
$row_stud = mysql_fetch_assoc($stud);
$totalRows_stud = mysql_num_rows($stud);

if ( getAccess() < 10 ) {
    $prg = ($row_stud['progid'] != null)? $row_stud['progid']: $prg;
    $level = ($row_stud['level'] != null)? $row_stud['level']: $level;
}

$query_studs = sprintf("SELECT stdid, fname, lname "
        . "FROM student "
        . "WHERE level = %s "
        . "AND progid = %s"
                        , GetSQLValueString($level, "int")
                        , GetSQLValueString($prg, "int"));
$studs = mysql_query($query_studs, $tams) or die(mysql_error());
$row_studs = mysql_fetch_assoc($studs);
$total = $totalRows_studs = mysql_num_rows($studs);

$query_regsess = sprintf("SELECT s.* FROM session s, registration r "
                            . "WHERE r.sesid = s.sesid "
                            . "AND r.status=%s "
                            . "AND r.stdid=%s "
                            . "ORDER BY sesname DESC", 
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

$query_cursess = sprintf("SELECT * FROM `session` WHERE sesid=%s", GetSQLValueString($colname1_course, "int"));
$cursess = mysql_query($query_cursess, $tams) or die(mysql_error());
$row_cursess = mysql_fetch_assoc($cursess);
$totalRows_cursess = mysql_num_rows($cursess);

$colname2_course = "-1";
if (isset($row_stud['progid'])) {
  $colname2_course = $row_stud['progid'];
}

$query_course = sprintf("SELECT r.csid, c.semester, c.csname, c.status, c.unit "
        . "FROM result r, course c "
        . "WHERE r.cleared = 'TRUE' "
        . "AND c.csid = r.csid "
        . "AND r.stdid = %s "
        . "AND r.sesid = %s "
        . "ORDER BY c.semester ASC", 
                        GetSQLValueString($colname_course, "text"), 
                        GetSQLValueString($colname1_course, "int"));
$course = mysql_query($query_course, $tams) or die(mysql_error());
$row_course = mysql_fetch_assoc($course);
$totalRows_course = mysql_num_rows($course);

$query_reg = sprintf("SELECT r.stdid "
        . "FROM registration r, student s "
        . "WHERE s.stdid = r.stdid "
        . "AND r.sesid = %s "
        . "AND s.level = %s "
        . "AND s.progid = %s "
        . "AND course = 'Registered'",  
                        GetSQLValueString($row_rssess['sesid'], "int"), 
                        GetSQLValueString($level, "int"), 
                        GetSQLValueString($prg, "int"));
$reg = mysql_query($query_reg, $tams) or die(mysql_error());
$row_reg = mysql_fetch_assoc($reg);
$totalReg = $totalRows_reg = mysql_num_rows($reg); 

$query_appr = sprintf("SELECT r.stdid "
        . "FROM registration r, student s "
        . "WHERE s.stdid = r.stdid "
        . "AND r.sesid = %s "
        . "AND s.level = %s "
        . "AND s.progid = %s "
        . "AND approved = 'TRUE'",  
                        GetSQLValueString($row_rssess['sesid'], "int"), 
                        GetSQLValueString($level, "int"), 
                        GetSQLValueString($prg, "int"));
$appr = mysql_query($query_appr, $tams) or die(mysql_error());
$row_appr = mysql_fetch_assoc($appr);
$totalApprd = $totalRows_appr = mysql_num_rows($appr);

$approve = false;
$query_approved = sprintf("SELECT * "
        . "FROM registration "
        . "WHERE stdid = %s "
        . "AND sesid = %s "
        . "AND status = 'Registered'",
                        GetSQLValueString($colname_course, "text"), 
                        GetSQLValueString($colname1_course, "int"));
$approved = mysql_query($query_approved, $tams) or die(mysql_error());
$row_approved = mysql_fetch_assoc($approved);
$totalRows_approved = mysql_num_rows($approved);

$registered = false;
if (isset($row_approved['status']) ) {
  $registered = true;
}

if(($row_approved['approved'] == 'TRUE' && $colname1_course == $row_rssess['sesid']) 
        || ($colname1_course > 0 && $colname1_course != $row_rssess['sesid'])) {
    $approve = true;
}

$name = ( isset($row_stud['lname']) ) ? "for ".$row_stud['lname']." ".$row_stud['fname']." (".$row_stud['stdid'].")": "";

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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Registered Courses <?php echo $name?><!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690"> 
      <tr>
      <?php if(getSessionValue("MM_UserGroup") < 7 ){?>
        <td align="right">
            <table>
                <tr>
                    <?php if(getSessionValue("MM_UserGroup") < 4 ){?>
                    <td>
                        <select onChange="progfilt(this)" style="width:200px">
                            <?php
                                do {  
                            ?>
                            <option <?php if($prg == $row_prog['progid'])echo "selected";?> value="<?php echo $row_prog['progid']?>"><?php echo $row_prog['progname']?></option>
                            <?php
                                } while ($row_prog = mysql_fetch_assoc($prog));
                            ?>
                          </select>
                    </td>
                    <?php }?>
                    <td>
                        <select onChange="lvlfilt(this)">
                            <option value="1" <?php if($level == 1) echo 'selected';?>>100</option>
                            <option value="2" <?php if($level == 2) echo 'selected';?>>200</option>
                            <option value="3" <?php if($level == 3) echo 'selected';?>>300</option>
                            <option value="4" <?php if($level == 4) echo 'selected';?>>400</option>
                        </select>
                    </td>
                    <td>
                        <select onChange="studfilt(this)" name="stdid">
                            <?php
                                do {  
                            ?>
                            <option <?php if($colname_stud == $row_studs['stdid'])echo "selected";?> value="<?php echo $row_studs['stdid']?>"><?php echo ucwords(strtolower($row_studs['lname']." ".$row_studs['fname']))." (".$row_studs['stdid'].")"?></option>
                            <?php
                                } while ($row_studs = mysql_fetch_assoc($studs));
                            ?>
                          </select>
                    </td>
                </tr>
            </table>
        </td>
       </tr>
       <?php }?>
        
      <tr>
      	<td>
       	  <table border="0" width="670">
              <tr>
                <td colspan="3" align="right"></td>
              </tr>   
              <tr>
              	<td>
                    <?php 
                        if($approve){ 
                            if( getAccess() < 4 || getAccess() == 10 ){
                    ?>
                    <a href="editform.php?stid=<?php echo $colname_stud?>">Add/Delete</a>
                    <?php } }?>
                </td>
                <td>
                    <?php
                        //if($approve){ 
                            if( getAccess() < 4 ){
                    ?>
                    Population: <?php echo $total?>&nbsp;&nbsp;
                    Registered: <?php echo $totalReg?>&nbsp;&nbsp;
                    Cleared: <?php echo $totalApprd?>&nbsp;&nbsp;                  
                    
                    <?php } ?>
                </td>
                <td align="right">
                  <select name="sesid" onchange="sesfilt(this)">
                    <?php
                    do {  
                    ?>
                      <option value="<?php echo $row_regsess['sesid']?>"
                          <?php if (!(strcmp($row_regsess['sesid'], $colname1_course))) {echo "selected=\"selected\"";} ?>>
                              <?php echo $row_regsess['sesname']?>
                      </option>
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
              <?php 
                if($registered) {
                        //if($approve){
                        if(true){
                ?>
              <tr>
                <td colspan="3">
                    <table width="680" border="0" class="table table-striped" style="font-weight: normal">                  
                  <tr>
                    <th width="100" align="center">COURSE CODE</th>
                    <th width="410" align="center">COURSE NAME</th>
                    <th width="80" align="center">STATUS</th>
                    <th width="30">UNIT</th>
                    <th width="70" align="center">SEMESTER</th>
                  </tr>
                  <?php 
                        $tunits = 0;
                        if ($totalRows_course > 0) { // Show if recordset not empty                  		
                            do { 
                  ?>
                    <tr>
                      <td><div align="center"><?php echo strtoupper($row_course['csid']); ?></div></td>
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
               <?php }else{?>
              <tr>
                  <td colspan="3">Your course form is awaiting your course adviser's approval!</td>
              </tr>
                <?php }}else {?>
                <tr>
                  <td colspan="3">You have not registered for this session <?php echo $row_cursess['sesname']?>!</td>
              </tr>
                <?php }?>
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
mysql_free_result($stud);

mysql_free_result($regsess);

mysql_free_result($course);

mysql_free_result($approved);
?>