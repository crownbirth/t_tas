<?php require_once('../Connections/tams.php'); ?>
<?php
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
$query_col = "SELECT colid, coltitle FROM college";
$col = mysql_query($query_col, $tams) or die(mysql_error());
$row_col = mysql_fetch_assoc($col);
$totalRows_col = mysql_num_rows($col);

$colname_dept = "-1";
if( isset( $_GET['cid'] ) ){
	$colname_dept = $_GET['cid'];	
}
mysql_select_db($database_tams, $tams);
$query_dept = ( isset( $_GET['filter'] ) )? sprintf("SELECT deptid, deptname, deptcode FROM department WHERE colid=%s ORDER BY deptname ASC",GetSQLValueString( $colname_dept,"int" ) ): "SELECT deptid, deptname, deptcode FROM department ORDER BY deptname ASC";
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);

$staff = array();
$student = array();
do{
	$query_studstat = sprintf("SELECT count(stdid) as cstud FROM student s, programme p WHERE p.progid = s.progid AND p.deptid=%s",GetSQLValueString( $row_dept['deptid'],"int" ) );
	$studstat = mysql_query($query_studstat, $tams) or die(mysql_error());
	$row_studstat = mysql_fetch_assoc($studstat);
	
	$student[] = ( $row_studstat['cstud'] != 0)? $row_studstat['cstud']: "-";
	
	$query_stafstat = sprintf("SELECT count(lectid) as cstaf FROM lecturer l WHERE l.deptid=%s",GetSQLValueString( $row_dept['deptid'],"int" ) );
	$stafstat = mysql_query($query_stafstat, $tams) or die(mysql_error());
	$row_stafstat = mysql_fetch_assoc($stafstat);
	
	$staff[] = ( $row_stafstat['cstaf'] != 0 )? $row_stafstat['cstaf']: "-"; ;
	
}while($row_dept = mysql_fetch_assoc($dept));
$rows = mysql_num_rows($dept);
if($rows > 0) {
	mysql_data_seek($dept, 0);
	$row_dept = mysql_fetch_assoc($dept);
}
		
		
$name = "The University";

if( isset( $_GET['filter'] ) ){
	do{
		if( $_GET['cid'] == $row_col['colid'] )
			$name = $row_col['coltitle'];
	
	} while ($row_col = mysql_fetch_assoc($col));
	$rows = mysql_num_rows($col);
		  if($rows > 0) {
			  mysql_data_seek($col, 0);
			  $row_col = mysql_fetch_assoc($col);
		}
}
?>
<?php 
require_once('../param/param.php');
require_once('../functions/function.php');


$sub = false;
$path = pathinfo( $_SERVER['SCRIPT_FILENAME']);

if( $path['dirname'] != $_SERVER['DOCUMENT_ROOT'].$site_root )
	$sub = true;
	

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/template.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php require('../param/site.php'); ?>
<title><?php echo $university ?> </title>
<!-- InstanceEndEditable -->
<link href="../css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<script src="../SpryAssets/SpryCollapsiblePanel.js" type="text/javascript"></script>
<script src="../SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
<script src="../scripts/tams.js" type="text/javascript"></script>
<link href="../SpryAssets/SpryCollapsiblePanel.css" rel="stylesheet" type="text/css" />
<link href="../SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Departments in <?php echo $name; ?> <!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
    <tr>
    	<td colspan="3"></td>
    </tr>
    <tr>
    	<td colspan="2" align="right">View By College
    	  <select name="cid" onchange="colFilter(this)">
          <option value="-1">---Select A College---</option>
    	  <?php
			$rows = mysql_num_rows($col);
		  if($rows > 0) {
			  mysql_data_seek($col, 0);
			  $row_col = mysql_fetch_assoc($col);
		  }
		  $value =( isset($_GET['cid']) ) ? $_GET['cid'] : "";
do {  
?>
    	  <option value="<?php echo $row_col['colid']?>"<?php if (!(strcmp($row_col['colid'], $value))) {echo "selected=\"selected\"";} ?>><?php echo $row_col['coltitle']?></option>
    	  <?php
} while ($row_col = mysql_fetch_assoc($col));

?>
    	</select></td>
    	<td width="102" align="right">&nbsp;</td>
    </tr>      
    <tr>
    	<td colspan="3">&nbsp;</td>
    </tr>
    <tr>
    	<td colspan="3">&nbsp;</td>
    </tr>
    <tr>
    	<td width="473">&nbsp;</td>
    	<td width="99" align="center">
        	<?php if( getAccess() < 4 && getAccess() > 0){?>
       			Staff No.
            <?php }?>
        </td>
    	<td align="center">
			<?php if( getAccess() < 4 && getAccess() > 0){?>
       			Student No.
            <?php }?>
        </td>
    </tr>
      <tr>
      <td colspan="3">      	
          <?php if ($totalRows_dept > 0) { // Show if recordset not empty ?>
          <ul>
  <?php 
  $count = 0;
  do { ?>
  <li style="list-style-position:outside">
   <div>
   <span style="">
   	<a href="department.php?did=<?php echo $row_dept['deptid'];?>"><?php echo $row_dept['deptname']; ?> </a>
   
   <?php $access = array(3);if( in_array(getAccess(),$access) && ( getAccess() == 2 && $_SESSION['cid'] == $row_dept['colid'] ) || ( getAccess() == 3 && $_SESSION['did'] == $row_dept['deptid'] )){?>
   |<a href="deptedit.php?did=<?php echo $row_dept['deptid'];?><?php  if( isset($_GET['cid']) )echo "&cid=".$_GET['cid'];?>"> Edit</a><?php }?>
   </span>
   <?php if( getAccess() < 4 && getAccess() > 0){?>
   <span style="float:right; width:99px; text-align:center">
   	<?php echo $student[$count]?>
   </span>
   
   <span style="float:right; width:99px; text-align:center">
   	<?php echo $staff[$count]?>
   </span>
   <?php }?>
   <div style="clear:both"></div>
   </div>
   
   </li>
   
    <?php $count++; } while ($row_dept = mysql_fetch_assoc($dept)); ?>
    </ul>
            <?php } // Show if recordset not empty ?>
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

mysql_free_result($col);

mysql_free_result($dept);
?>
