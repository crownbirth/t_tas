<?php require_once('../Connections/tams.php');
require_once('../param/param.php');
require_once('../functions/function.php');

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
$query_cat = sprintf("SELECT catid, catname FROM category WHERE type=1");
$cat = mysql_query($query_cat, $tams) or die(mysql_error());
$row_cat = mysql_fetch_assoc($cat);
$totalRows_cat = mysql_num_rows($cat);

$colname_courses = "-1";
if( isset($row_cat['catid']) ){
	$colname_courses = $row_cat['catid']; 
} 

if( isset($_GET['catid']) ){
	$colname_courses = $_GET['catid']; 
} 

mysql_select_db($database_tams, $tams);
$query_courses = sprintf("SELECT csid, csname FROM course c, category ct WHERE c.catid = ct.catid AND ct.catid = %s ORDER BY csid ASC", GetSQLValueString($colname_courses, "int"));
$courses = mysql_query($query_courses, $tams) or die(mysql_error());
$row_courses = mysql_fetch_assoc($courses);
$totalRows_courses = mysql_num_rows($courses);


/*mysql_select_db($database_tams, $tams);
$query_col = "SELECT colid, coltitle FROM college";
$col = mysql_query($query_col, $tams) or die(mysql_error());
$row_col = mysql_fetch_assoc($col);
$totalRows_col = mysql_num_rows($col);
 
 $filtername = "The University";
 if( isset($_GET['filter'])){
 	if( $_GET['filter'] == "dept" || ( $_GET['filter'] == "cat" && isset($_GET['did']) ) )			
		do { 
			if( $_GET['did'] == $row_dept['deptid'] )
			$filtername = $row_dept['deptname'];
		} while ($row_dept = mysql_fetch_assoc($dept)); 
	elseif( $_GET['filter'] == "col" || ( $_GET['filter'] == "cat" && isset($_GET['cid']) ) )
		do { 
			if( $_GET['cid'] == $row_col['colid'] )
			$filtername = $row_col['coltitle'];
		} while ($row_col = mysql_fetch_assoc($col));
		
	$filtername = ( isset( $filtername ) ) ? $filtername : "The University";
 }
 */
 
//Fill an array with valid lecturer ids to view registered students.if(){}
$did = "-1";
if( isset($_GET['did']) ){
	$did = $_GET['did']; 
} 

$cid= "-1";
if( isset($_GET['cid']) ){
	$cid = $_GET['cid']; 
} 
$acl = "-1";
if( getAccess() == 1 || (getAccess() == 2 && getSessionValue('cid') == $cid) || (getAccess() == 3 && getSessionValue('did') == $did) || (getAccess() == 4) || (( getAccess() == 5) && getSessionValue('did') == $did)){
	$acl = getSessionValue('lid');
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
<script src="../SpryAssets/SpryCollapsiblePanel.js" type="text/javascript"></script>
<script src="../SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
<script src="../SpryAssets/SpryValidationSelect.js" type="text/javascript"></script>
<script src="../scripts/jquery.js" type="text/javascyript"></script>
<script src="../scripts/tams.js" type="text/javascript"></script>
<link href="../SpryAssets/SpryCollapsiblePanel.css" rel="stylesheet" type="text/css" />
<link href="../SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
<link href="../SpryAssets/SpryValidationSelect.css" rel="stylesheet" type="text/css" />
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->General University Courses<?php //echo $filtername;?><!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
    
      <tr>
        <td colspan="2">Choose Category<br/>
          <label for="catid"></label>
          <select name="catid" id="catid" onchange="catFilter(this)" style="width:150px;">
            <option value="-1" <?php if (isset($_GET['catid']))if (!(strcmp(-1, $_GET['catid']))) {echo "selected=\"selected\"";} ?>>---Select A Category---</option>
            <?php	
			do {  
			?>
            <option value="<?php echo $row_cat['catid']?>" <?php if (isset($_GET['catid']))if (!(strcmp($row_cat['catid'], $_GET['catid']))) {echo "selected=\"selected\"";} ?>><?php echo $row_cat['catname']?></option>
            <?php
			} while ($row_cat = mysql_fetch_assoc($cat));
			  
			?>
        </select></td>
        
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td colspan="3"></td>
      </tr>
      <tr>
        <td width="41">&nbsp;</td>
        <td width="127">&nbsp;</td>
        <td colspan="3">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="3">
            <table width="683" border="0">
              <?php if ($totalRows_courses > 0) { // Show if recordset not empty ?>
                <?php do { ?>
                <tr>
                  <td width="50"><?php echo $row_courses['csid']; ?></td>
                  <td width="364"><a href="course.php?csid=<?php echo $row_courses['csid'];?>"><?php echo $row_courses['csname']; ?></a></td>
                  <td width="105">
				  	<?php if( !strcmp(getSessionValue('lid'), $acl) ){  ?> 
                    	<a href="../registration/coursereg.php?csid=<?php echo $row_courses['csid']?>">Registered Students</a>
					<?php }?>
                  </td>
                </tr>
                <?php } while ($row_courses = mysql_fetch_assoc($courses)); ?>
                <?php mysql_free_result($courses);	} // Show if recordset not empty ?>
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
mysql_free_result($cat);
?>
