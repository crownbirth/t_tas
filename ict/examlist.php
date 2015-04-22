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

$sesname = $row_rssess['sesname'];

$query_dept = "SELECT * FROM `department`";
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);

$query_duration = "SELECT MAX(duration) as max FROM `programme`";
$duration = mysql_query($query_duration, $tams) or die(mysql_error());
$row_duration = mysql_fetch_assoc($duration);
$totalRows_duration = mysql_num_rows($duration);

$deptid = -1;
if(isset($_GET['did'])) {
    $deptid = $_GET['did'];
}

$level = 1;
if(isset($_GET['lvl'])) {
    $level = $_GET['lvl'];
}
$query_stud = sprintf("SELECT *, sum(percentPaid) as percent FROM student s "
                    . "JOIN programme p ON p.progid = s.progid "
                    . "JOIN department d ON d.deptid = p.deptid "
                    . "JOIN schfee_transactions st ON s.stdid = st.matric_no "
                    . "WHERE d.deptid = %s AND s.level = %s "
                    . "GROUP BY stdid "
                    . "HAVING percent >= 100",
                    GetSQLValueString($deptid, "int"),
                    GetSQLValueString($level, "int"));
$stud = mysql_query($query_stud, $tams) or die(mysql_error());
$row_stud = mysql_fetch_assoc($stud);
$totalRows_stud = mysql_num_rows($stud);

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
<script type="text/javascript" src="scripts/tams.js"></script>
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
        <td><!-- InstanceBeginEditable name="pagetitle" --><?php echo 'Paid/Exam List ('.$sesname.')'?><!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include 'include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
       <table width="679" border="0" class="mytext">
           <tr>
               <td>
                   <select name="did" onchange="deptfilt(this)">
                       <option value="-1">Select a department</option>
                       <?php for($idx = 0; $idx < $totalRows_dept; $idx++, $row_dept = mysql_fetch_assoc($dept)) {?>
                       <option value="<?php echo $row_dept['deptid']?>" 
                               <?php if($deptid == $row_dept['deptid']) echo 'selected'?>>
                                   <?php echo $row_dept['deptname']?>
                       </option>
                      <?php }?>
                   </select>
                   
                   <select name="lvl" onchange="lvlfilt(this)">
                        <?php for($idx = 0; $idx < $row_duration['max']; $idx++) {?>
                       <option value="<?php echo $idx + 1?>" 
                               <?php if($level == $idx + 1) echo 'selected'?>>
                                   <?php echo $idx + 1?>
                       </option>
                        <?php }?>
                    </select>
               </td>
               <td>
                   <form method="post"
                       action="printexamlist.php" target="_blank" >
                       <input type="hidden" name="did" value="<?php echo $deptid?>"/>
                       <input type="hidden" name="lvl" value="<?php echo $level?>"/>
                       <input value='Print List' type="submit"/>&nbsp;&nbsp;
                    </form>
               </td>
           </tr>
           <tr>
               <td colspan="2">
                  <table width="670" class="table table-striped">
                      <thead>
                          <tr>
                            <th>S/N</th>
                            <th>Matric No</th>
                            <th>Name</th>                            
                            <th>Programme</th>
                            <th>Remark</th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php 
                            if($totalRows_stud > 0){
                                for($idx = 0; $idx < $totalRows_stud; $idx++, $row_stud = mysql_fetch_assoc($stud)) {
                          ?>
                          <tr>
                              <td><?php echo $idx + 1;?></td>
                              <td>
                                 <a href="../student/profile.php?stid=<?php echo $row_stud['stdid']?>">
                                      <?php echo $row_stud['stdid']?>
                                  </a>
                              </td>
                              <td>
                                  <?php echo "{$row_stud['fname']} {$row_stud['lname']}" ;?>
                              </td>
                              <td width="250"><?php echo $row_stud['progname'];?></td>
                              <td></td>                             
                          </tr>
                          <?php                           
                                }
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
    
    <?php require 'include/footer.php'; ?>
	
   </p>
  </div>
  <!-- end .container -->
</div>
</body>
<!-- InstanceEnd --></html>