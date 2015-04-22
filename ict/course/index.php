<?php require_once('../../Connections/tams.php');?>
<?php
if (!isset($_SESSION)) {
  session_start();
}

require_once('../../param/param.php');
require_once('../../functions/function.php');

$MM_authorizedUsers = "20";
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

//Upload File
$rsinsert;
$insert_row = 0;
$insert_error;
if ((isset($_POST["submit"])) && ($_POST["submit"] == "Upload Courses")) {
  if(is_uploaded_file($_FILES['filename']['tmp_name'])){
		//Import uploaded file to Database	
		$handle = fopen($_FILES['filename']['tmp_name'], "r");
		while (($data = fgetcsv($handle, 1500, ",")) !== FALSE) 
		{
							
			$insert_query = sprintf("INSERT INTO course (csid, csname, semester, catid, deptid, cscont) VALUES (%s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($data[0], "text"),
                       GetSQLValueString($data[1], "text"),
                       GetSQLValueString($data[2], "text"),
                       GetSQLValueString($data[3], "int"),
                       GetSQLValueString($data[4], "text"),
                       GetSQLValueString($data[5], "text"));
			mysql_select_db($database_Tsdb, $Tsdb);	
			$rsinsert = mysql_query($insert_query);
			$insert_error = mysql_error();
			$insert_row++;
		}
		
		fclose($handle);
	}
}



$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO course (csid, csname, semester, type, catid, deptid, cscont) VALUES (%s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['csid'], "text"),
                       GetSQLValueString($_POST['csname'], "text"),
                       GetSQLValueString($_POST['semester'], "text"),
                       GetSQLValueString($_POST['type'], "text"),
                       GetSQLValueString($_POST['catid'], "int"),
                       GetSQLValueString($_POST['deptid'], "int"),
                       GetSQLValueString($_POST['cscont'], "text"));

  mysql_select_db($database_tams, $tams);
  $Result1 = mysql_query($insertSQL, $tams) or die(mysql_error());

  $insertGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
}

mysql_select_db($database_tams, $tams);
$query_cat = "SELECT * FROM category";
$cat = mysql_query($query_cat, $tams) or die(mysql_error());
$row_cat = mysql_fetch_assoc($cat);
$totalRows_cat = mysql_num_rows($cat);mysql_select_db($database_tams, $tams);

mysql_select_db($database_tams, $tams);
$query_dept = ( isset($_GET['cid']) )?"SELECT deptid, deptname FROM department WHERE colid = ".$_GET['cid']." ORDER BY deptname ASC":"SELECT deptid, deptname FROM department ORDER BY deptname ASC";
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);


$courses = "";
$totalRows_courses = "";
if( isset( $_GET['filter'] ) && $_GET['filter'] != "col"){
mysql_select_db($database_tams, $tams);
$query_courses = createFilter("course");
$courses = mysql_query($query_courses, $tams) or die(mysql_error());
$row_courses = mysql_fetch_assoc($courses);
$totalRows_courses = mysql_num_rows($courses);
}

mysql_select_db($database_tams, $tams);
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
	if( $_GET['filter'] == "cat" )
		do { 
			if( $_GET['catid'] == $row_cat['catid'] )
			$filtername .= "(".$row_cat['catname'].")";
		} while ($row_cat = mysql_fetch_assoc($cat));
 }
 
if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout($site_root.'/ict');  
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
<script src="../../SpryAssets/SpryCollapsiblePanel.js" type="text/javascript"></script>
<script src="../../SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
<script src="../../SpryAssets/SpryValidationSelect.js" type="text/javascript"></script>
<script src="../../scripts/jquery.js" type="text/javascyript"></script>
<script src="../../scripts/tams.js" type="text/javascript"></script>
<link href="../../SpryAssets/SpryCollapsiblePanel.css" rel="stylesheet" type="text/css" />
<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
<link href="../../SpryAssets/SpryValidationSelect.css" rel="stylesheet" type="text/css" />
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Courses In <?php echo $filtername;?><!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="667">
      <tr>
        <td colspan="4"><div id="CollapsiblePanel1" class="CollapsiblePanel">
          <div class="CollapsiblePanelTab" tabindex="0">Add A New Course</div>
          <div class="CollapsiblePanelContent">
            <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
              <table width="415" align="center">
                <tr valign="baseline">
                  <td width="153" align="right" nowrap="nowrap">Course Code:</td>
                  <td><span id="sprytextfield1">
                    <label for="csid"></label>
                    <input type="text" name="csid" id="csid" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Course Name:</td>
                  <td><span id="sprytextfield2">
                    <label for="csname"></label>
                    <input type="text" name="csname" id="csname" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Semester:</td>
                  <td><span id="spryselect1">
                    <label for="semester"></label>
                    <select name="semester" id="semester">
                      <option value="F">First</option>
                      <option value="S">Second</option>
                    </select>
                    <span class="selectRequiredMsg">Please select an item.</span></span></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Course Type:</td>
                  <td width="96"><span id="spryselect4">
                    <label for="type"></label>
                    <select name="type" id="type">
                      <option value="General">General</option>
                      <option value="College">College</option>
                      <option value="Departmental">Departmental</option>
                      </select>
                    <span class="selectRequiredMsg">Please select an item.</span></span></td>
                  </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Course Category:</td>
                  <td width="96"><span id="spryselect2">
                    <label for="catid"></label>
                    <select name="catid" id="catid">
                      <?php
					  $rows = mysql_num_rows($cat);
					  if($rows > 0) {
      mysql_data_seek($cat, 0);
	  $row_cat = mysql_fetch_assoc($cat);}
do {  
?>
                      <option value="<?php echo $row_cat['catid']?>"><?php echo $row_cat['catname']?></option>
                      <?php
} while ($row_cat = mysql_fetch_assoc($cat));
  $rows = mysql_num_rows($cat);
  if($rows > 0) {
      mysql_data_seek($cat, 0);
	  $row_cat = mysql_fetch_assoc($cat);
  }
?>
                      </select>
                    <span class="selectRequiredMsg">Please select an item.</span></span></td>
                  <div id="newCategory">
                        div to create new categorry
                    </div>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Department:</td>
                  <td><span id="spryselect3">
                    <label for="deptid"></label>
                    <select name="deptid" id="deptid">
                      <?php
					  $rows = mysql_num_rows($dept);
						if($rows > 0) {
						  mysql_data_seek($dept, 0);
						  $row_dept = mysql_fetch_assoc($dept);
						}
do {  
?>
                      <option value="<?php echo $row_dept['deptid']?>"><?php echo $row_dept['deptname']?></option>
                      <?php
} while ($row_dept = mysql_fetch_assoc($dept));
  $rows = mysql_num_rows($dept);
  if($rows > 0) {
      mysql_data_seek($dept, 0);
	  $row_dept = mysql_fetch_assoc($dept);
  }
?>
                    </select>
                    <span class="selectRequiredMsg">Please select an item.</span></span></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Course Content:</td>
                  <td><textarea name="cscont"></textarea></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">&nbsp;</td>
                  <td><input type="submit" value="Add Course" /></td>
                </tr>
              </table>
              <input type="hidden" name="MM_insert" value="form1" />
            </form>
            <p>&nbsp;</p>
          </div>
        </div></td>
      </tr>
      <tr>
        <td colspan="4"><div id="CollapsiblePanel2" class="CollapsiblePanel">
          <div class="CollapsiblePanelTab" tabindex="0">Add A New Course from File</div>
          <div class="CollapsiblePanelContent">
          <form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data">
          	<table width="683" border="0">
              <tr>
                <td colspan="3">Upload CSV file with no column heading and in the order of: csid, csname, semester, type, catid, deptid, cscont.</td>
              </tr>
              <tr>
                <td colspan="3">&nbsp;</td>
                </tr>
              <tr>
                <td width="104">Select File</td>
                <td width="427"><input name="filename" type="file" size="55" /></td>
                <td width="138"><input name="submit" type="submit" value="Upload Courses" /></td>
              </tr>
            </table>
		  </form>
          </div>
        </div></td>
      </tr>
      
      <tr>
        <td width="50"><a href="index.php">View All</a><br/></td>
        <td width="150">View By College<br/>
          <label for="col"></label>
          <select name="col" id="col" onchange="colFilter(this)" style="width:150px;">
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
        <td width="250">View By Department<br/>
          <label for="dept"></label>
          <select name="dept" id="dept" onchange="deptFilter(this)" style="width:250px;">
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
  $rows = mysql_num_rows($dept);
  
?>
          </select>
        </td>
        <td width="181">View By Category<br/>
        <select name="cat" onchange="catFilter(this)" style="width:150px;">
          <option value="-1" <?php if (isset($_GET['catid']))if (!(strcmp(-1, $_GET['catid']))) {echo "selected=\"selected\"";} ?>>---Select A Category---</option>
          <?php
		   $rows = mysql_num_rows($cat);
  if($rows > 0) {
      mysql_data_seek($cat, 0);
	  $row_cat = mysql_fetch_assoc($cat);
  }
do {  
?>
          <option value="<?php echo $row_cat['catid']?>"<?php if(isset($_GET['catid']))if (!(strcmp($row_cat['catid'], $_GET['catid']))) {echo "selected=\"selected\"";} ?>><?php echo $row_cat['catname']?></option>
<?php
} while ($row_cat = mysql_fetch_assoc($cat));
  $rows = mysql_num_rows($cat);
  if($rows > 0) {
      mysql_data_seek($cat, 0);
	  $row_cat = mysql_fetch_assoc($cat);
  }
?>
        </select></td>
      </tr>
      <tr>
      <tr>
        <td colspan="4"></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td colspan="4">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="4">
            <table width="683" border="0">
              <?php if ($totalRows_courses > 0) { // Show if recordset not empty ?>
                <?php do { ?>
                <tr>
                  <td width="50"><?php echo $row_courses['csid']; ?></td>
                  <td width="364"><?php echo $row_courses['csname']; ?></td>
                  <td width="105"><?php echo $row_courses['catname']; ?></td>
                  <td width="44"><a href="courseedit.php?csid=<?php echo $row_courses['csid'];?>">Edit</a></td>
                  <td width="58">Delete</td>
                </tr>
                <?php } while ($row_courses = mysql_fetch_assoc($courses)); ?>
                <?php mysql_free_result($courses); 
				} // Show if recordset not empty ?>
            </table>
        </td>
      </tr>
    </table>
    <script type="text/javascript">
var CollapsiblePanel1 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel1", {contentIsOpen:false});
var CollapsiblePanel2 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel2", {contentIsOpen:false});
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2");
var spryselect1 = new Spry.Widget.ValidationSelect("spryselect1");
var spryselect2 = new Spry.Widget.ValidationSelect("spryselect2");
var spryselect4 = new Spry.Widget.ValidationSelect("spryselect4");
var spryselect3 = new Spry.Widget.ValidationSelect("spryselect3");
    </script>
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

mysql_free_result($dept);

mysql_free_result($col);
?>
