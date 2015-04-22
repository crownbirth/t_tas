<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "20,21,22,24";
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

$MM_restrictGoTo = "index.php";
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
<?php require_once('../../Connections/tams.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$rsstdnt = TRUE;
$seed = "";
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



$colname_rsstdnt = "-1";
if (isset($_POST['search']) && $_POST['search'] != NULL) {
  $colname_rsstdnt = $_POST['search'];
  $seed =  $colname_rsstdnt;

mysql_select_db($database_tams, $tams);
$query_rsstdnt = "SELECT jambregid, lname, fname 
					FROM prospective
					 WHERE lname LIKE '%".$seed."%'
					 OR fname LIKE '%".$seed."%'
					 OR jambregid LIKE '%".$seed."%'";

$rsstdnt = mysql_query($query_rsstdnt, $tams) or die(mysql_error());

$row_rsstdnt = mysql_fetch_assoc($rsstdnt);
$totalRows_rsstdnt = mysql_num_rows($rsstdnt);
}

require_once('../../param/param.php');
require_once('../../functions/function.php');


$sub = false;
$path = pathinfo( $_SERVER['SCRIPT_FILENAME']);

if( $path['dirname'] != $_SERVER['DOCUMENT_ROOT'].$site_root )
	$sub = true;
	

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );  
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/icttemplate.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php require('../../param/site.php'); ?>
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Search And Edit Applicant Record <!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->

    <table width="690">
      <tr>
        <td height="37">
            <form id="form1" name="form1" method="post" action="">
            <table width="639" border="0" align="center" class="table table-bordered table-condensed table-hover table-striped">
              <tr>
                <td width="200" height="30" align="center">Search By Name UTME No. or Form No. </td>
                <td width="371" align="center"><input name="search" type="text" id="search" size="55" /></td>
                <td width="81" align="center"><input type="submit" name="submit" id="submit" value="Search" /></td>
              </tr>
            </table>
        </form>
            <table width="626" align="center" class="table table-bordered table-condensed table-hover table-striped">
        	<tr align="center">
            	<th width="71">S/n</th>
            	<th width="150">UTME No.</th>
                <th width="275">Full Name</th>
                <th width="110">Actions</th>
            </tr>
             <?php
	   if(!empty($row_rsstdnt)){
	   $i = 1; do {
	   ?>
            <tr align="center" >
            	<td><?php echo $i;?></td>	
            	<td><?php echo $row_rsstdnt['jambregid']?></td>
                <td><?php echo $row_rsstdnt['fname']." ".$row_rsstdnt['lname']?></td>
                <td><a href="editapplicant1.php?jambreg=<?php echo $row_rsstdnt['jambregid']; ?>">Edit</a></td>
            </tr>
             <?php $i++; } while ($row_rsstdnt = mysql_fetch_assoc($rsstdnt));
		}else {
			?>
            
            <tr >
            	<td style="color:#F00" colspan="5" align="center"><Strong>SORRY !!</Strong> NO Record Available Search by Name or Matric No </td>
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
if(isset($_POST['search'])){
mysql_free_result($rsstdnt);
}
?>
