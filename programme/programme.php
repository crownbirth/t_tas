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

$colname_prog = "-1";
if (isset($_GET['pid'])) {
  $colname_prog = $_GET['pid'];
}
mysql_select_db($database_tams, $tams);
$query_prog = sprintf("SELECT p.*, d.colid FROM programme p, department d WHERE d.deptid = p.deptid AND progid=%s", GetSQLValueString($colname_prog, "int"));
$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);
$totalRows_prog = mysql_num_rows($prog);

$colname_progcrs = "-1";
if (isset($row_prog['deptid'])) {
  $colname_progcrs = $row_prog['deptid'];
}

mysql_select_db($database_tams, $tams);
$query_progcrs = sprintf("SELECT c.csid, c.csname FROM course c, department_course dc WHERE c.csid=dc.csid AND c.deptid = %s AND dc.progid=%s AND c.type <> 'General'", 
						GetSQLValueString($colname_progcrs, "int"), 
						GetSQLValueString($colname_prog, "int"));
$progcrs = mysql_query($query_progcrs, $tams) or die(mysql_error());
$row_progcrs = mysql_fetch_assoc($progcrs);
$totalRows_progcrs = mysql_num_rows($progcrs);

mysql_select_db($database_tams, $tams);
$query_dept = "SELECT deptid, deptname FROM department ORDER BY deptname ASC";
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);
 
if (!isset($_SESSION)) {
  session_start();
}
require_once('../param/param.php');
require_once('../functions/function.php');


$sub = false;
$path = pathinfo( $_SERVER['SCRIPT_FILENAME']);

if( $path['dirname'] != $_SERVER['DOCUMENT_ROOT'].$site_root )
	$sub = true;
	

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );  
}
$deptname = "";
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
        <td><!-- InstanceBeginEditable name="pagetitle" --><?php echo $row_prog['progname'];?><!-- InstanceEndEditable --></td>
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
        	<?php $access = array(1,2,3);if( in_array(getAccess(),$access) && ( getAccess() == 1 ||  getAccess() == 2 && getSessionValue('cid') == $row_prog['colid'] ) || ( getAccess() == 3 && getSessionValue('did') == $row_prog['deptid'] ) ){?>
          	<a href="../programme/progedit.php?pid=<?php echo $colname_prog;?>"> Edit Page</a>
			<?php }?>
        </td>
      </tr> 
      <tr>
        <td><?php echo $row_prog['page_up']; ?></td>
      </tr>
      <tr>
        <td></td>
      </tr>      
      <tr>
        <td>For the award of a <?php echo $degree?> degree in  <?php echo $row_prog['progname']; ?>, prospective Students are required to attend lectures and be examined in the courses listed below.</td>
      </tr>
      <tr>
        <td>Apart from the departmental courses, students are expected to offer some courses in other departments within their college and some general <a href="../course/generalcourse.php">university course</a>.</td>
      </tr>
      <tr>
        <td>       	 
		  <?php if ($totalRows_progcrs > 0) { // Show if recordset not empty ?>
          <ul class="courselist">
            <?php do { ?>
            
				<li>
					 <a href="../course/course.php?csid=<?php echo $row_progcrs['csid']?>&pid=<?php echo $colname_prog;?>"><?php echo ucwords(strtolower($row_progcrs['csname'])); ?></a>
                </li>
            <?php } while ($row_progcrs = mysql_fetch_assoc($progcrs)); ?>
            </ul>
            <?php } // Show if recordset not empty ?>
          </td>
      </tr>
      <tr>
        <td><?php echo $row_prog['page_down']; ?></td>
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
mysql_free_result($prog);

mysql_free_result($progcrs);

mysql_free_result($dept);
?>
