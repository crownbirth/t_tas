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

$maxRows_dept = 10;
$pageNum_dept = 0;
if (isset($_GET['pageNum_dept'])) {
  $pageNum_dept = $_GET['pageNum_dept'];
}
$startRow_dept = $pageNum_dept * $maxRows_dept;

$colname_dept = "-1";
if (isset($_GET['cid'])) {
  $colname_dept = $_GET['cid'];
}
mysql_select_db($database_tams, $tams);
$query_dept = sprintf("SELECT deptid, deptname, colid FROM department WHERE colid = %s ORDER BY deptname ASC", GetSQLValueString($colname_dept, "int"));
$query_limit_dept = sprintf("%s LIMIT %d, %d", $query_dept, $startRow_dept, $maxRows_dept);
$dept = mysql_query($query_limit_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);

if (isset($_GET['totalRows_dept'])) {
  $totalRows_dept = $_GET['totalRows_dept'];
} else {
  $all_dept = mysql_query($query_dept);
  $totalRows_dept = mysql_num_rows($all_dept);
}
$totalPages_dept = ceil($totalRows_dept/$maxRows_dept)-1;

$colname_col = "-1";
if (isset($_GET['cid'])) {
  $colname_col = $_GET['cid'];
}
mysql_select_db($database_tams, $tams);
$query_col = sprintf("SELECT colid, colname, colcode, coltitle, page_up, page_down FROM college WHERE colid = %s", GetSQLValueString($colname_col, "int"),GetSQLValueString($colname_col, "int"));
$col = mysql_query($query_col, $tams) or die(mysql_error());
$row_col = mysql_fetch_assoc($col);
$totalRows_col = mysql_num_rows($col);
?>
<?php 
require_once('../param/param.php');
require_once('../functions/function.php');

$sub = false;
$path = pathinfo( $_SERVER['SCRIPT_FILENAME']);

if( $path['dirname'] != $_SERVER['DOCUMENT_ROOT'].$site_root )
	$sub = true;
	

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true"))
	doLogout( $site_root );

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
        <td><!-- InstanceBeginEditable name="pagetitle" --><?php echo $row_col['colname'];?><!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
    <tr>
      	<td align="right"><?php $access = array(1,2);if( in_array(getAccess(),$access) && ( getAccess() == 1 || getSessionValue('cid') == $colname_col) ){?>
          <a href="coledit.php?cid=<?php echo $colname_col;?>"> Edit Page</a>
          </li>
          <?php } ?></td>
      </tr>
     <tr>
      	<td width="690"><?php echo $row_col['page_up'];?></td>
      </tr>
      <tr>
      	<td width="690"></td>
      </tr>       
      <tr>
      	<td>
              <?php if ($totalRows_dept > 0) { // Show if recordset not empty ?>
              <ul>
  <?php do { ?>
    <li>
        <a href="../department/department.php?did=<?php echo $row_dept['deptid']?>"> 
		<?php echo $row_dept['deptname']; ?>
        </a>
        
		<?php $access = array(1,2,3);if( in_array(getAccess(),$access) && ( getAccess() == 1 || getAccess() == 2 && getSessionValue('cid') == $row_dept['colid'] ) || ( getAccess() == 3 && getSessionValue('cid') == $row_dept['deptid'] ) ){?>
        
        |<a href="../department/deptedit.php?did=<?php echo $row_dept['deptid']?>"> Edit</a>
		<?php }?>
        </li>
     
    <?php } while ($row_dept = mysql_fetch_assoc($dept)); ?>
               </ul> <?php } // Show if recordset not empty ?>
          </td>
      </tr>
       <tr>
      	<td><?php echo $row_col['page_down'];?></td>
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
mysql_free_result($dept);

mysql_free_result($col);
?>
