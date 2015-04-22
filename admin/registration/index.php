<?php require_once('../../Connections/tams.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
require_once('../../param/param.php');
require_once('../../functions/function.php');

$MM_authorizedUsers = "1";
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

$MM_restrictGoTo = "../../login.php";
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

mysql_select_db($database_tams, $tams);
$query_sess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,1";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);


mysql_select_db($database_tams, $tams);
$query_col = "SELECT colid, coltitle FROM college";
$col = mysql_query($query_col, $tams) or die(mysql_error());
$row_col = mysql_fetch_assoc($col);
$totalRows_col = mysql_num_rows($col);

$colname_dept = "-1";
if (isset($row_col['colid'])) {
  $colname_dept = $row_col['colid'];
}

if (isset($_GET['cid'])) {
  $colname_dept = $_GET['cid'];
}

mysql_select_db($database_tams, $tams);
$query_dept = sprintf("SELECT deptid, deptname, coltitle FROM department d, college c WHERE d.colid = c.colid AND d.colid = %s",
						 GetSQLValueString($colname_dept, "int"));
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);

$colname_prog = "-1";
if (isset($row_dept['deptid'])) 
  $colname_prog = $row_dept['deptid'];
	
if (isset($_GET['did']))
  $colname_prog = $_GET['did'];

mysql_select_db($database_tams, $tams);
$query_prog = sprintf("SELECT progid, progname, deptname FROM programme p, department d WHERE d.deptid = p.deptid AND p.deptid = %s", GetSQLValueString($colname_prog, "int"));
$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);
$totalRows_prog = mysql_num_rows($prog);

$colname_studs = "-1";
if (isset($row_prog['progid'])) {
  $colname_studs = $row_prog['progid'];
}
if (isset($_GET['pid'])) {
  $colname_studs = $_GET['pid'];
}

$lvl = ( isset($_GET['lvl']) ) ? $_GET['lvl']: "";
$colname1_studs = -1;
if ( isset($_GET['lvl']) ) {
  $colname1_studs = "AND level=".$lvl;
}
mysql_select_db($database_tams, $tams);
$query_studs = sprintf("SELECT stdid, fname, lname FROM student s WHERE progid = %s %s", 
						GetSQLValueString($colname_studs, "int"), 
						GetSQLValueString($colname1_studs, "undefined", $colname1_studs));
$studs = mysql_query($query_studs, $tams) or die(mysql_error());
$row_studs = mysql_fetch_assoc($studs);
$totalRows_studs = mysql_num_rows($studs);
	
$name = "";
if( isset($_GET['did']) ){
	$name = $row_prog['deptname'];
}else{
	$name = $row_dept['coltitle'];
}

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
<?php require('../../param/site.php'); ?>
<title><?php echo $university ?> </title>
<script type="text/javascript" src="../../scripts/jquery.js"></script>
<script type="text/javascript" src="../../scripts/tams.js"></script>
<!-- InstanceEndEditable -->
<link href="../../css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
<link href="../../css/menulink.css" rel="stylesheet" type="text/css" />
<link href="../../css/footer.css" rel="stylesheet" type="text/css" />
<link href="../../css/sidemenu.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div class="container">
  <div class="header">
    <!-- end .header -->
</div>
  <div class="topmenu">
<?php include '../../include/topmenu.php'; ?>
  </div>
  <!-- end .topmenu --> 
  
  <div class="loginuser">
  <?php include '../../include/loginuser.php'; ?>
  
  <!-- end .loginuser --></div>
  <div class="pagetitle">
    <table width="600">
      <tr>
        <td><!-- InstanceBeginEditable name="pagetitle" -->Course Registration for Students<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">		
      <form id="form1" name="form1" method="post" action="<?php echo $editFormAction?>">
      <tr>
        <td width="100">Choose College<br/>
          <label for="colid"></label>
          <select name="colid" id="colid" onchange="colfilt(this)">
            <?php
			do {  
			?>
						<option value="<?php echo $row_col['colid']?>"<?php if (!(strcmp($row_col['colid'], $colname_dept))) {echo "selected=\"selected\"";} ?>><?php echo $row_col['coltitle']?></option>
						<?php
			} while ($row_col = mysql_fetch_assoc($col));
			  $rows = mysql_num_rows($col);
			  if($rows > 0) {
				  mysql_data_seek($col, 0);
				  $row_col = mysql_fetch_assoc($col);
			  }
			?>
          </select>
        </td>
        <td width="278">Choose Department<br/>
          <select name="deptid" id="deptid" onchange="deptfilt(this)">
            <?php
			do {  
			?>
            <option value="<?php echo $row_dept['deptid']?>"<?php if (!(strcmp($row_dept['deptid'], $colname_prog))) {echo "selected=\"selected\"";} ?>><?php echo $row_dept['deptname']?></option>
            <?php
			} while ($row_dept = mysql_fetch_assoc($dept));
			
			mysql_free_result($dept);			  
			?>
          </select></td>
        <td width="147">
        	Select Programme<br/>            
            <select name="progid" id="progid" onchange="progfilt(this)">
              <?php
            do {  
            ?>
              <option value="<?php echo $row_prog['progid']?>"<?php if (!(strcmp($row_prog['progid'], $colname_studs))) {echo "selected=\"selected\"";} ?>><?php echo $row_prog['progname']?></option>
              <?php
            } while ($row_prog = mysql_fetch_assoc($prog));
              $rows = mysql_num_rows($prog);
              if($rows > 0) {
                  mysql_data_seek($prog, 0);
                  $row_prog = mysql_fetch_assoc($prog);
              }
            ?>
			</select>
		</td>
        <td width="148">
        	Select Level<br/>
            <select name="level" onchange="lvlfilt(this)">
            	<?php for( $i = 100, $value = 1; $i<=600; $i+=100, $value++){ ?>
                	<option value="<?php echo $value?>" <?php if (!(strcmp($value, $lvl))) {echo "selected=\"selected\"";} ?>><?php echo $i?></option>
                <?php echo $lvl;}?>
            </select>
        </td>
      </tr>
      </form>
      
      <tr>
        <td colspan="4">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="4">        
            <table width="683" border="0">
              <?php if ($totalRows_studs > 0) { // Show if recordset not empty ?>
			  <?php do{?>
                <tr>
                  <td width="120"><a href="../../student/profile.php?stid=<?php echo $row_studs['stdid'];?>"><?php echo $row_studs['stdid'];?></a></td>
                  <td width="325"><?php echo $row_studs['lname'].", ".$row_studs['fname']?></td>
                  <td width="116"><a href="editform.php?stid=<?php echo $row_studs['stdid']?>">Add/Delete</a></td>
                  <td width="106"><a href="viewform.php?stid=<?php echo $row_studs['stdid']?>">View Form</a></td>
                </tr>
                <?php }while ($row_studs = mysql_fetch_assoc($studs));?>
                <?php } // Show if recordset not empty ?>
            </table>
        </td>
      </tr>
    </table>
  <!-- InstanceEndEditable --></div>
<div class="footer">
    <p><!-- end .footer -->   
    
    <?php require '../../include/footer.php'; ?>
	
   </p>
  </div>
  <!-- end .container -->
</div>
</body>
<!-- InstanceEnd --></html>