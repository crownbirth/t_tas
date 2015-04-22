<?php require_once('../Connections/tams.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "3";
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

$sesname = $row_rssess['sesname'];
$deptid = getSessionValue('did');

$query_prog = sprintf("SELECT * FROM `programme` WHERE deptid = %s", GetSQLValueString($deptid, 'int'));
$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);
$totalRows_prog = mysql_num_rows($prog);

$level = 'all';
$filter = '';
$pid = 'all';

$ses = $row_rssess['sesid'];

if(isset($_GET['sid'])) {
    $ses = $_GET['sid'];
}

if(isset($_GET['lvl'])) {
    $level = $_GET['lvl'];
    
    if($level != 'all') {
        $filter = 'AND st.level = '.GetSQLValueString($level, 'int');
    }
}

if(isset($_GET['pid'])) {
    $pid = $_GET['pid'];
    
    if($pid != 'all') {
        $filter .= 'AND s.progid = '.GetSQLValueString($pid, 'int');
    }
}
$query_stud = sprintf("SELECT s.stdid, s.fname, s.lname, s.admode, s.phone 
                FROM student s 
                JOIN programme p ON p.progid = s.progid 
                JOIN department d ON d.deptid = p.deptid 
                JOIN schfee_transactions st ON st.matric_no = s.stdid 
                WHERE st.sesid = %s 
                AND st.status = 'APPROVED' 
                AND d.deptid = %s %s ORDER BY s.stdid ASC",
                GetSQLValueString($ses, "int"),
                GetSQLValueString($deptid, "int"),
                GetSQLValueString($filter, "defined", $filter));

$stud = mysql_query($query_stud, $tams) or die(mysql_error());
$row_stud = mysql_fetch_assoc($stud);
$totalRows_stud = mysql_num_rows($stud);

$query_levels = sprintf("SELECT max(duration) as `max` 
                FROM programme p 
                JOIN department d ON d.deptid = p.deptid 
                WHERE  d.deptid = %s",
                GetSQLValueString($deptid, "int"));

$levels = mysql_query($query_levels, $tams) or die(mysql_error());
$row_levels = mysql_fetch_assoc($levels);
$totalRows_levels = mysql_num_rows($levels);

$name = 'Paid students';

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")) {
	doLogout( $site_root );  
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
        <td><!-- InstanceBeginEditable name="pagetitle" --><?php echo $name?> <?php echo '('.$sesname.')'?><!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
       <table width="679" border="0" class="mytext">
           <tr>
                <td>
                    <select name='prog' onchange="progfilt(this)">
                        <option value="all" <?php if($pid == "all") echo 'selected';?>>All</option>
                        <?php for(; $row_prog != false ; $row_prog = mysql_fetch_assoc($prog)){?>
                        <option value="<?php echo $row_prog['progid']?>" <?php if($pid == $row_prog['progid']) echo 'selected'?>>
                            <?php echo $row_prog['progname'];?>
                        </option>
                        <?php }?>
                    </select>
                    <select name='ses' onchange="sesfilt(this)">
                        <?php for(; $row_rssess != false ; $row_rssess = mysql_fetch_assoc($rssess)){?>
                        <option value="<?php echo $row_rssess['sesid']?>" <?php if($ses == $row_rssess['sesid']) echo 'selected'?>>
                            <?php echo $row_rssess['sesname'];?>
                        </option>
                        <?php }?>
                    </select>
                </td>
            </tr>
           <tr>
              <td align="right">
                  <table>
                      <tr>
                          <td>
                              Level
                              <select onChange="lvlfilt(this)">
                                  <option value="all" <?php if($level == "all") echo 'selected';?>>All</option>
                                  <?php 
                                        $count = isset($row_levels['max'])? $row_levels['max']: 0;                                        
                                        for($idx = 1; $idx <= $count;$idx++) {
                                  ?>  
                                            <option value="<?php echo $idx?>" 
                                                <?php if($level == $idx) echo 'selected';?>>
                                                <?php echo $idx.'00';?>
                                            </option>
                                       
                                  <?php }?>
                                  <option value="" <?php if($level == '') echo 'selected';?>>Extra Year 1</option>
                                  <option value="" <?php if($level == '') echo 'selected';?>>Extra Year 2</option>
                              </select>
                          </td>
                      </tr>
                  </table>
              </td>
          </tr>
           
          <tr>
              <td>
                  <table width="670" class="table table-striped">
                      <thead>
                          <tr>
                            <th>S/N</th>
                            <th>Matric</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Admission Mode</th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php 
                            if($totalRows_stud > 0){ $i = 1;
                                do{
                          ?>
                          <tr>
                              <td><?php echo $i++;?></td>
                              <td>
                                 <a href="../student/profile.php?stid=<?php echo $row_stud['stdid']?>">
                                      <?php echo $row_stud['stdid'] ;?>
                                  </a>
                              </td>
                              <td>
                                  <?php echo "{$row_stud['fname']} {$row_stud['lname']}" ;?>
                              </td>
                              <td><?php echo (isset($row_stud['phone']))?  $row_stud['phone'] : '-';?></td>
                              <td><?php echo (isset($row_stud['admode']))?  $row_stud['admode'] : '-';?></td>
                          </tr>
                          <?php                           
                                }while($row_stud = mysql_fetch_assoc($stud));
                            }else{
                          ?>
                          <tr>
                              <td colspan="5">No record available!</td>
                          </tr>
                          <?php 
                            }
                          ?>
                      </tbody>
                  </table>
              </td>
          </tr>
      
    </table>
      
  <!-- InstanceEndEditable -->
  </div>
<div class="footer">
    <p><!-- end .footer -->   
    
    <?php require '../include/footer.php'; ?>
	
   </p>
  </div>
  <!-- end .container -->
</div>
</body>
<!-- InstanceEnd --></html>