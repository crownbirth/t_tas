<?php require_once('../Connections/tams.php'); ?>
<?php
 
if (!isset($_SESSION)) {
  session_start();
}
require_once('../param/param.php');
require_once('../functions/function.php');

$MM_authorizedUsers = "1,2,3,4,5,6, 20, 21, 22, 23";
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

if (isset($_GET['lid'])) {
  $colname_staff = $_GET['lid'];
}else {
    $colname_staff = getSessionValue('lid');
}
mysql_select_db($database_tams, $tams);
$query_staff = sprintf("SELECT l.*, d.deptname, c.colname , c.colid FROM lecturer l, department d, college c WHERE d.deptid = l.deptid AND d.colid = c.colid AND lectid = %s", 
					GetSQLValueString($colname_staff, "text"));
$staff = mysql_query($query_staff, $tams) or die(mysql_error());
$row_staff = mysql_fetch_assoc($staff);
$totalRows_staff = mysql_num_rows($staff);


$sub = false;
$path = pathinfo( $_SERVER['SCRIPT_FILENAME']);

if( $path['dirname'] != $_SERVER['DOCUMENT_ROOT'].$site_root )
	$sub = true;
	

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );  
}

$staff = ($colname_staff)? $colname_staff:"";
$filename = "profile.png";
$dh = opendir('../images/staff/');
$excl = array('.','..');
while ($file = readdir($dh)){
    $len = strlen( $file ) - (strlen( $file ) - strpos( $file, '.'));
    if( !in_array($file,$excl) && substr( $file, 0, $len ) == $staff ){
        $filename = $file;
        break;
    }
}
closedir($dh);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/template.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php require('../param/site.php'); ?>
<title><?php echo $university ?> </title>
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
        <td><!-- InstanceBeginEditable name="pagetitle" --><?php echo $row_staff['lname']." ".$row_staff['fname']."'s"?> Profile<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
    
       <tr>
       <td colspan="3">
       	<table width="670" cellspacing="5" cellpadding="5">
          <tr>
            <td width="429">&nbsp;</td>
            <td width="114"><a href="../teaching/coursehist.php?lid=<?php echo  $row_staff['lectid']?>">Teaching History</a></td>
            <td width="75">       
               <?php if( getSessionValue('lid') == $colname_staff ){?>
                <a href="editprofile.php?lid=<?php echo $row_staff['lectid']; ?>" >Edit Profile</a>    
                <?php }?>
          </tr>
        </table>
        </td>
      </tr>   
      
      <tr>
        <td width="176" rowspan="5" align="center"><img src="../images/staff/<?php echo $filename;?>" alt="" name="profile_image" width="150" height="150" id="profile_image" /></td>
        <td width="84" height="27"><strong>Name:</strong></td>
        <td width="414"><?php echo $row_staff['title']." ". $row_staff['lname']; ?> <?php echo $row_staff['fname']; ?></td>
      </tr>
      <tr>
        <td height="31"><strong>Department: </strong></td>
        <td height="31"><a href="../department/department.php?did=<?php echo $row_staff['deptid']; ?>"><?php echo $row_staff['deptname']; ?></a></td>
      </tr>
      <tr>
        <td height="24"><strong>College: </strong></td>
        <td height="24"><a href="../college/college.php?cid=<?php echo $row_staff['colid']; ?>"><?php echo $row_staff['colname']; ?></a></td>
      </tr>
      <tr>
        <td height="24"><strong>Phone: </strong></td>
        <td height="24"><?php echo $row_staff['phone']; ?></td>
      </tr>
      <tr>
        <td height="30"><strong>Email:</strong></td>
        <td height="30"><?php echo $row_staff['email']; ?></td>
      </tr>
      <tr>
        <td colspan="3">Research Area</td>
      </tr>
      <tr>
        <td colspan="3"><?php echo $row_staff['profile']; ?></td>
      </tr>
      <tr>
        <td colspan="3">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="3">
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
//mysql_free_result($staff);
?>
