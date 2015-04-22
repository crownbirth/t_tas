<?php require_once('../Connections/tams.php'); ?>
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

$colname_courselist = "-1";
if (isset($_GET['csid'])) {
  $colname_courselist = $_GET['csid'];
}
mysql_select_db($database_tams, $tams);
$query_courselist = sprintf("SELECT lectid1, lectid2, csname, c.deptid, sesname FROM teaching t, course c, session s WHERE s.sesid = t.sesid AND c.csid = t.csid AND t.csid = %s ORDER BY sesname DESC", GetSQLValueString($colname_courselist, "text"));
$courselist = mysql_query($query_courselist, $tams) or die(mysql_error());
$row_courselist = mysql_fetch_assoc($courselist);
$totalRows_courselist = mysql_num_rows($courselist);

$colname_lect = "-1";
if (isset($row_courselist['deptid'])) {
  $colname_lect = $row_courselist['deptid'];
}
mysql_select_db($database_tams, $tams);
$query_lect = sprintf("SELECT lectid, title, fname, lname FROM lecturer WHERE deptid = %s", GetSQLValueString($colname_lect, "int"));
$lect = mysql_query($query_lect, $tams) or die(mysql_error());
$row_lect = mysql_fetch_assoc($lect);
$totalRows_lect = mysql_num_rows($lect);
 
$lectlist = array();
$count = 0;
do{
	$lectlist[$row_lect['lectid']] = $row_lect['title']." ".$row_lect['lname'].", ".$row_lect['fname'];
	
	$count++;
}while( $row_lect = mysql_fetch_assoc($lect) );

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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Teaching History for <?php echo $colname_courselist?><!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
      <?php if ($totalRows_courselist > 0) { // Show if recordset not empty ?>
      <tr>
        <td width="121"><strong>Session</strong></td>
        <td width="207"><strong>Convener</strong></td>
        <td width="346"><strong>Assistant</strong></td>
      </tr>
     
<?php do{?>
      <tr>
        <td><?php echo $row_courselist['sesname']?></td>
        <td>        	
            <?php 
                if( isset($lectlist[$row_courselist['lectid1']]) )
                    echo "<a href='../staff/profile.php?lid={$row_courselist['lectid1']}'>".$lectlist[$row_courselist['lectid1']].'</a>';
                else
                    echo "-"
            ?>
            
        </td>
        <td>
            <?php 
                if( isset($lectlist[$row_courselist['lectid2']]) )
                    echo "<a href='../staff/profile.php?lid={$row_courselist['lectid2']}'>".$lectlist[$row_courselist['lectid2']].'</a>';
                else
                    echo "-"
            ?>
        </td>
      </tr>
      <?php }while( $row_courselist = mysql_fetch_assoc($courselist) );?>
       <?php }else{ ?>
       No history available
        <?php }?>
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
mysql_free_result($courselist);

mysql_free_result($lect);
?>
