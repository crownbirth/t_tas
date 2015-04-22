<?php require_once('../Connections/tams.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "20";
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
if (!isset($_SESSION)) {
  session_start();
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
$query_rssess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,6";
$rssess = mysql_query($query_rssess, $tams) or die(mysql_error());
$row_rssess = mysql_fetch_assoc($rssess);
$totalRows_rssess = mysql_num_rows($rssess);

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "purge")) {
    $query_del = sprintf("DELETE FROM result "
                        . "WHERE stdid=%s "
                        . "AND sesid=%s", 
                        GetSQLValueString($_POST['stid'], "text"),
                        GetSQLValueString($row_rssess['sesid'], "int"));
    $del = mysql_query($query_del, $tams) or die(mysql_error());
    
    $query_upd = sprintf("UPDATE registration SET course = 'Unregistered' "
                        . "WHERE stdid=%s "
                        . "AND sesid=%s", 
                        GetSQLValueString($_POST['stid'], "text"),
                        GetSQLValueString($row_rssess['sesid'], "int"));
    $upd = mysql_query($query_upd, $tams) or die(mysql_error());

}

// Recordset to populate programme dropdown
mysql_select_db($database_tams, $tams);
$query_prog = sprintf("SELECT progid, progname "
                        . "FROM programme ORDER BY progname ASC");
$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);
$totalRows_prog = mysql_num_rows($prog);

$level = 1;
$prg = $row_prog['progid'];
$cursess = $ses = $row_rssess['sesid'];

if(isset($_GET['lvl'])) {
    $level = $_GET['lvl'];
}

if(isset($_GET['pid'])) {
    $prg = $_GET['pid'];
}

if(isset($_GET['sid'])) {
    $ses = $_GET['sid'];
}

$sesname = '';
do{
    if($row_rssess['sesid'] == $ses) {
        $sesname = $row_rssess['sesname'];
    }
}while($row_rssess = mysql_fetch_assoc($rssess));
mysql_data_seek($rssess, 0);

$query_list = sprintf("SELECT r.status, r.course, s.stdid, s.fname, s.lname 
                        FROM registration r, student s 
                        WHERE r.stdid = s.stdid 
                        AND s.level = %s 
                        AND s.progid = %s 
                        AND r.sesid = %s", 
        GetSQLValueString($level, "int"), 
        GetSQLValueString($prg, "int"), 
        GetSQLValueString($ses, "int"));
$list = mysql_query($query_list, $tams) or die(mysql_error());
$row_list = mysql_fetch_assoc($list);
$totalRows_list = mysql_num_rows($list);

$query_count = sprintf("SELECT s.stdid, count(rs.csid) as `count` 
                        FROM student s LEFT JOIN result rs 
                        ON rs.stdid = s.stdid 
                        WHERE s.level = %s 
                        AND s.progid = %s 
                        AND rs.sesid = %s 
                        GROUP BY s.stdid", 
        GetSQLValueString($level, "int"), 
        GetSQLValueString($prg, "int"), 
        GetSQLValueString($ses, "int"));
$count = mysql_query($query_count, $tams) or die(mysql_error());
$row_count = mysql_fetch_assoc($count);
$totalRows_count = mysql_num_rows($count);

$stud_count = array();
do{
    $stud_count[$row_count['stdid']] = $row_count['count'];
}while($row_count = mysql_fetch_assoc($count));

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")) {
	doLogout($site_root.'/ict');  
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/icttemplate.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php require('../param/site.php'); ?>
<title><?php echo $university ?> </title>
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/tams.js"></script>
<!-- InstanceEndEditable -->
<link href="css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
<link href="css/menulink.css" rel="stylesheet" type="text/css" />
<link href="css/footer.css" rel="stylesheet" type="text/css" />
<link href="css/sidemenu.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div class="container">
  <div class="header">
    <!-- end .header -->
</div>
  <div class="topmenu">
<?php include 'include/topmenu.php'; ?>
  </div>
  <!-- end .topmenu --> 
  
  <div class="loginuser">
  <?php include 'include/loginuser.php'; ?>
  
  <!-- end .loginuser --></div>
  <div class="pagetitle">
    <table width="600">
      <tr>
        <td><!-- InstanceBeginEditable name="pagetitle" -->Registration List <?php echo '('.$sesname.')'?><!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include 'include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
      <table width="679" border="0" class="mytext">
          <tr>
              <td align="right">
                  <table>
                      <tr>
                          <td>
                              <select onChange="sesfilt(this)" name="sesid" style="width:200px">
                                <?php
                                    do {
                                ?>
                                  <option value="<?php echo $row_rssess['sesid']?>" <?php if($ses == $row_rssess['sesid'])echo "selected";?>><?php echo $row_rssess['sesname']?></option>
                                <?php
                                    } while ($row_rssess = mysql_fetch_assoc($rssess));
                                ?>
                              </select>
                          </td>
                          <td>
                              <select onChange="progfilt(this)" name="stdid" style="width:200px">
                                <?php
                                    do {
                                ?>
                                  <option value="<?php echo $row_prog['progid']?>" <?php if($prg == $row_prog['progid'])echo "selected";?>><?php echo $row_prog['progname']?></option>
                                <?php
                                    } while ($row_prog = mysql_fetch_assoc($prog));
                                ?>
                              </select>
                          </td>
                          <td>
                              <select onChange="lvlfilt(this)">
                                  <option value="1" <?php if($level == 1) echo 'selected';?>>100</option>
                                  <option value="2" <?php if($level == 2) echo 'selected';?>>200</option>
                                  <option value="3" <?php if($level == 3) echo 'selected';?>>300</option>
                                  <option value="4" <?php if($level == 4) echo 'selected';?>>400</option>
                              </select>
                          </td>
                      </tr>
                  </table>
              </td>
          </tr>
          
          <tr>
              <td>
                  <table width="670">
                      <thead>
                          <tr>
                            <th>Matric</th>
                            <th>Name</th>
                            <th>Session</th>
                            <th>Course</th>
                            <th></th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php 
                            if($totalRows_list > 0){
                                do{
                          ?>
                          <tr>
                              <td><a target="_blank" href="../student/profile.php?stid=<?php echo $row_list['stdid']?>"><?php echo $row_list['stdid']?></a></td>
                              <td><?php echo $row_list['fname'].' '.$row_list['lname']?></td>
                              <td><?php echo $row_list['status']?></td>
                              <td>
                                  <?php 
                                    $count = ' ( 0 )';
                                    if(isset($stud_count[$row_list['stdid']]))
                                        $count = "( <a target='_blank' href='../registration/viewform.php?stid={$row_list['stdid']}'>{$stud_count[$row_list['stdid']]}</a> )";
                                    
                                    echo "{$row_list['course']} {$count}"
                                    ?>
                              </td>
                              <td>
                                  <?php 
                                    if($ses == $cursess) {
                                        if($row_list['status'] == 'Registered' && $row_list['course'] == 'Registered' && !isset($stud_count[$row_list['stdid']])) {
                                  ?>
                                  <form method="post">
                                      <button type="submit" name="stid" value="<?php echo $row_list['stdid']?>">Purge</button>
                                      <input type="hidden" name="MM_insert" value="purge" />
                                  </form>
                                  <?php }}?>
                              </td>
                          </tr>
                          <?php }while($row_list = mysql_fetch_assoc($list));}?>
                      </tbody>
                  </table>
              </td>
          </tr>
      
    </table>
                     
  <!-- InstanceEndEditable --></div>
<div class="footer">
    <p><!-- end .footer -->   
    
    <?php require 'include/footer.php'; ?>
	
   </p>
  </div>
  <!-- end .container -->
</div>
</body>
<!-- InstanceEnd --></html>