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

$colname_dept = "-1";
if (isset($_GET['did'])) {
  $colname_dept = $_GET['did'];
}
mysql_select_db($database_tams, $tams);
$query_dept = sprintf("SELECT * FROM department WHERE deptid = %s", GetSQLValueString($colname_dept, "int"));
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);

$maxRows_deptprog = 10;
$pageNum_deptprog = 0;
if (isset($_GET['pageNum_deptprog'])) {
  $pageNum_deptprog = $_GET['pageNum_deptprog'];
}
$startRow_deptprog = $pageNum_deptprog * $maxRows_deptprog;

$colname_deptprog = "-1";
if (isset($_GET['did'])) {
  $colname_deptprog = $_GET['did'];
}
mysql_select_db($database_tams, $tams);
$query_deptprog = sprintf("SELECT progid, progname, programme.deptid, colid FROM programme, department WHERE programme.deptid = department.deptid AND programme.deptid = %s", GetSQLValueString($colname_deptprog, "int"));
$query_limit_deptprog = sprintf("%s LIMIT %d, %d", $query_deptprog, $startRow_deptprog, $maxRows_deptprog);
$deptprog = mysql_query($query_limit_deptprog, $tams) or die(mysql_error());
$row_deptprog = mysql_fetch_assoc($deptprog);

if (isset($_GET['totalRows_deptprog'])) {
  $totalRows_deptprog = $_GET['totalRows_deptprog'];
} else {
  $all_deptprog = mysql_query($query_deptprog);
  $totalRows_deptprog = mysql_num_rows($all_deptprog);
}
$totalPages_deptprog = ceil($totalRows_deptprog/$maxRows_deptprog)-1;

mysql_select_db($database_tams, $tams);
$query_col = "SELECT colid, coltitle FROM college";
$col = mysql_query($query_col, $tams) or die(mysql_error());
$row_col = mysql_fetch_assoc($col);
$totalRows_col = mysql_num_rows($col);
$collegename = ""
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
        <td><!-- InstanceBeginEditable name="pagetitle" --><?php echo $row_dept['deptname'];?> Department<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
       <tr>
      	<td align="right">
        	<?php $access = array(1,2,3);if( in_array(getAccess(),$access) && ( getAccess() == 1 || getAccess() == 2 && getSessionValue('cid') == $row_dept['colid'] ) || ( getAccess() == 3 && getSessionValue('did') == $row_dept['deptid'] ) ){?>
        
        <a href="../department/deptedit.php?did=<?php echo $colname_dept?>"> Edit Page</a>
		<?php }?>
        </td>
      </tr>
      <tr>
      	<td><?php echo $row_dept['page_up'];?></td>
      </tr>
       <tr>
      	<td></td>
      </tr>
      <tr>
      	<td>
            <?php if ($totalRows_deptprog > 0) { // Show if recordset not empty ?>
            <ul class="courselist">
  <?php do { ?>
   
      <li>
		  <a href="../programme/programme.php?pid=<?php echo $row_deptprog['progid']?>">
		  	<?php echo $row_deptprog['progname']; ?> 
          </a>
          
          <?php $access = array(1,2,3);if( in_array(getAccess(),$access) && ( getAccess() == 1 ||  getAccess() == 2 && getSessionValue('cid') == $row_dept['colid'] ) || ( getAccess() == 3 && getSessionValue('did') == $row_dept['deptid'] ) ){?>
          	|<a href="../programme/progedit.php?pid=<?php echo $row_deptprog['progid']?>"> Edit</a>
			<?php }?>
      </li>
      
    <?php } while ($row_deptprog = mysql_fetch_assoc($deptprog)); ?>
    		</ul>
              <?php } // Show if recordset not empty ?>
          </td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
       <tr>
      	<td><?php echo $row_dept['page_down'];?></td>
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

mysql_free_result($deptprog);

mysql_free_result($col);
?>
