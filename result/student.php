<?php require_once('../Connections/tams.php');  
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
$query_dept = (isset($_GET['cid']))?"SELECT deptid, deptname FROM department WHERE colid = ".$_GET['cid']." ORDER BY deptname ASC":"SELECT deptid, deptname FROM department ORDER BY deptname ASC";
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);

mysql_select_db($database_tams, $tams);
$query_col = "SELECT colid, coltitle FROM college";
$col = mysql_query($query_col, $tams) or die(mysql_error());
$row_col = mysql_fetch_assoc($col);
$totalRows_col = mysql_num_rows($col);

$totalRows_student = "";
$student ="";
if( isset( $_GET['filter'] ) && $_GET['filter'] != "col"){
mysql_select_db($database_tams, $tams);
$query_student = createFilter("stud");
$student = mysql_query($query_student, $tams) or die(mysql_error());
$row_student = mysql_fetch_assoc($student);
$totalRows_student = mysql_num_rows($student);
}
 
if (!isset($_SESSION)) {
  session_start();
}

$sub = false;
$path = pathinfo( $_SERVER['SCRIPT_FILENAME']);

if( $path['dirname'] != $_SERVER['DOCUMENT_ROOT'].$site_root )
	$sub = true;
	

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );  
}

 $level = "";
 $filtername = "The University";
 if( isset($_GET['filter'])){
 	if( $_GET['filter'] == "dept")	{		
		do { 
			if( $_GET['did'] == $row_dept['deptid'] )
			$filtername = $row_dept['deptname'];
		} while ($row_dept = mysql_fetch_assoc($dept)); 	
	}
	
	if( $_GET['filter'] == "lvl" ){
		$level = $_GET['lvl'];
		if( isset( $_GET['did'] ) )	{		
			do { 
				if( $_GET['did'] == $row_dept['deptid'] )
				$filtername = $row_dept['deptname'];
			} while ($row_dept = mysql_fetch_assoc($dept)); 
			$filtername .= " (".$_GET['lvl']."00 Level)";	
		}
		
		}
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
<script src="../scripts/tams.js" type="text/javascript"></script>
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Students In <?php echo $filtername;?><!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
      <tr>
        <td colspan="5"></td>
      </tr>
      <tr>
        <td colspan="5"></td>
      </tr>
      <tr>
        <td colspan="2">Choose College<br/>
          <select name="col2" id="col" onchange="colFilter(this)">
            <option value="-1" <?php if (isset($_GET['cid']))if (!(strcmp(-1, $_GET['cid']))) {echo "selected=\"selected\"";} ?>>---Select A College---</option>
            <?php
			  $rows = mysql_num_rows($col);
			  if($rows > 0) {
				  mysql_data_seek($col, 0);
				  $row_col = mysql_fetch_assoc($col);
			  }
				do {  
			?>
            <option value="<?php echo $row_col['colid']?>" <?php if (isset($_GET['cid']))if (!(strcmp($row_col['colid'], $_GET['cid']))) {echo "selected=\"selected\"";} ?>><?php echo $row_col['coltitle']?></option>
            <?php
} while ($row_col = mysql_fetch_assoc($col));
  
?>
        </select></td>        
        <td>View By Department<br/>
          <label for="dept2"></label>
          <select name="dept2" id="dept2" onchange="deptFilter(this)">
            <option value="-1" <?php if (isset($_GET['did']))if(!(strcmp(-1, $_GET['did']))) {echo "selected=\"selected\"";} ?>>---Select A Department---</option>
            <?php
			$rows = mysql_num_rows($dept);
  if($rows > 0) {
      mysql_data_seek($dept, 0);
	  $row_dept = mysql_fetch_assoc($dept);
  }
do {  
?>
            <option value="<?php echo $row_dept['deptid']?>"<?php if (isset($_GET['did']))if (!(strcmp($row_dept['deptid'], $_GET['did']))) {echo "selected=\"selected\"";} ?>><?php echo $row_dept['deptname']?></option>
            <?php
} while ($row_dept = mysql_fetch_assoc($dept));
?>
        </select></td>
        <td>Choose Level<br/>
          <select name="level" id="level" onchange="lvlFilter(this)">
          	<option value="-1" <?php if (!(strcmp(-1, $level))) {echo "selected=\"selected\"";} ?>>--Level--</option>
            <option value="1" <?php if (!(strcmp(1, $level))) {echo "selected=\"selected\"";} ?>>100</option>
            <option value="2" <?php if (!(strcmp(2, $level))) {echo "selected=\"selected\"";} ?>>200</option>
            <option value="3" <?php if (!(strcmp(3, $level))) {echo "selected=\"selected\"";} ?>>300</option>
            <option value="4" <?php if (!(strcmp(4, $level))) {echo "selected=\"selected\"";} ?>>400</option>
        </select></td>
        <td valign="bottom"><?php echo $totalRows_student." students"?></td>
      </tr>
      <tr>
        <td width="69"><br/></td>
        <td width="121">&nbsp;</td>
        <td width="227">&nbsp;</td>
        <td width="170">&nbsp;</td>
        <td width="79" valign="bottom">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="5"></td>
      </tr>
    </table>
    <table width="682" border="0" align="center">
      <?php if ($totalRows_student > 0) { // Show if recordset not empty ?>
        <?php do { ?>
          <tr>
            <td width="139"><?php echo $row_student['stdid']; ?></td>
            <td width="292"><a href="../student/profile.php?stid=<?php echo $row_student['stdid']; ?>"><?php echo $row_student['fname']; ?>, <?php echo ucwords(strtolower($row_student['lname'])); ?></a></td>
            <td width="237"><a href="transcript.php?stid=<?php echo $row_student['stdid']; ?>">Transcript</a></td>
          </tr>
          <?php } while ($row_student = mysql_fetch_assoc($student));?>
          
        <?php mysql_free_result($student);	} // Show if recordset not empty ?>
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
