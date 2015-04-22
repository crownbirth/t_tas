<?php require_once('../Connections/tams.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}

require_once('../param/param.php');
require_once('../functions/function.php');

$MM_authorizedUsers = "20,21";
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

//Upload File
$rsinsert;
$insert_row = 0;
$insert_error;
if ((isset($_POST["submit"])) && ($_POST["submit"] == "Upload Staff")) {
  if(is_uploaded_file($_FILES['filename']['tmp_name'])){
		//Import uploaded file to Database	
		$handle = fopen($_FILES['filename']['tmp_name'], "r");
		while (($data = fgetcsv($handle, 1500, ",")) !== FALSE) 
		{
							
			$insert_query = sprintf("INSERT INTO lecturer (lectid, title, fname, lname, mname, deptid, phone, email, addr, sex, password) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($data[0], "text"),
                       GetSQLValueString($data[1], "text"),
                       GetSQLValueString($data[2], "text"),
                       GetSQLValueString($data[3], "text"),
                       GetSQLValueString($data[4], "text"),
                       GetSQLValueString($data[5], "int"),
                       GetSQLValueString($data[6], "text"),
                       GetSQLValueString($data[7], "text"),
                       GetSQLValueString($data[8], "text"),
                       GetSQLValueString($data[9], "text"),
                       GetSQLValueString(md5($data[3]), "text"));
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
    $insertSQL = sprintf("INSERT INTO lecturer (lectid, title, fname, lname, mname, deptid, phone, email, addr, access, sex, password) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['lectid'], "text"),
                       GetSQLValueString($_POST['title'], "text"),
                       GetSQLValueString($_POST['fname'], "text"),
                       GetSQLValueString($_POST['lname'], "text"),
                       GetSQLValueString($_POST['mname'], "text"),
                       GetSQLValueString($_POST['deptid'], "int"),
                       GetSQLValueString($_POST['phone'], "text"),
                       GetSQLValueString($_POST['email'], "text"),
                       GetSQLValueString($_POST['addr'], "text"),                       
                       GetSQLValueString(5, "int"),
                       GetSQLValueString($_POST['sex'], "text"),
                       GetSQLValueString(md5($_POST['password']), "text"));

    mysql_select_db($database_tams, $tams);
    $Result1 = mysql_query($insertSQL, $tams) or die(mysql_error());

    unset($_POST['MM_insert']);
    
    $params['entid'] = $_POST['lectid'];
    $params['enttype'] = 'lecturer';
    $params['action'] = 'create';
    $params['cont'] = json_encode($_POST);
    audit_log($params);
    
    $insertGoTo = "srchstdnt.php";
    if(isset($_SERVER['QUERY_STRING'])) {
        $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
        $insertGoTo .= $_SERVER['QUERY_STRING'];
    }
    header(sprintf("Location: %s", $insertGoTo));
}

mysql_select_db($database_tams, $tams);
$query_dept = "SELECT deptid, deptname FROM department";
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);

mysql_select_db($database_tams, $tams);
$query_col = "SELECT colid, coltitle FROM college";
$col = mysql_query($query_col, $tams) or die(mysql_error());
$row_col = mysql_fetch_assoc($col);
$totalRows_col = mysql_num_rows($col);

mysql_select_db($database_tams, $tams);
$query_staff = ( isset( $_GET['filter'] ) )? createFilter("lect"): "SELECT lectid, title, fname, lname, mname FROM lecturer";
$staff = mysql_query($query_staff, $tams) or die(mysql_error());
$row_staff = mysql_fetch_assoc($staff);
$totalRows_staff = mysql_num_rows($staff);

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout($site_root.'/ict');  
}

 $filtername = "The University";
 if( isset($_GET['filter'])){
 	if( $_GET['filter'] == "dept")			
		do { 
			if( $_GET['did'] == $row_dept['deptid'] )
			$filtername = $row_dept['deptname'];
		} while ($row_dept = mysql_fetch_assoc($dept)); 
	elseif( $_GET['filter'] == "col" )
		do { 
			if( $_GET['cid'] == $row_col['colid'] )
			$filtername = $row_col['coltitle'];
		} while ($row_col = mysql_fetch_assoc($col));
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
<link href="css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<script src="SpryAssets/SpryCollapsiblePanel.js" type="text/javascript"></script>
<script src="SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
<script src="scripts/tams.js" type="text/javascript"></script>
<link href="SpryAssets/SpryCollapsiblePanel.css" rel="stylesheet" type="text/css" />
<link href="SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
<!-- InstanceEndEditable -->
<link href="css/menulink.css" rel="stylesheet" type="text/css" />
<link href="css/footer.css" rel="stylesheet" type="text/css" />
<link href="css/sidemenu.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div class="container">
  <div class="header">
    <!-- end .header -->
</div>
  <div class="topmenu">
<?php include 'include/topmenu.php'; ?>
  </div>
  <!-- end .topmenu --> 
  
  <div class="loginuser">
  <?php include 'include/loginuser.php'; ?>
  
  <!-- end .loginuser --></div>
  <div class="pagetitle">
    <table width="600">
      <tr>
        <td><!-- InstanceBeginEditable name="pagetitle" -->Staff In <?php echo $filtername;?><!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include 'include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
      <tr>
        <td colspan="5"><div id="CollapsiblePanel1" class="CollapsiblePanel">
          <div class="CollapsiblePanelTab" tabindex="0">Add New Staff</div>
          <div class="CollapsiblePanelContent">
            <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
              <table align="center">
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Staff Id:</td>
                  <td><span id="sprytextfield1">
                    <input type="text" name="lectid" value="" size="32" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Title:</td>
                  <td><select name="title">
                    <option value="Prof">Prof.</option>
                    <option value="Dr">Dr.</option>
                    <option value="Mr" selected>Mr.</option>
                    <option value="Mrs">Mrs.</option>
                    <option value="Miss">Miss</option>
                  </select></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">First Name:</td>
                  <td><span id="sprytextfield2">
                    <input type="text" name="fname" value="" size="32" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Last Name:</td>
                  <td><span id="sprytextfield7">
                    <label for="lname"></label>
                    <input name="lname" type="text" id="lname" size="32" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Middle Name:</td>
                  <td><span id="sprytextfield3">
                    <input type="text" name="mname" value="" size="32" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Department:</td>
                  <td><select name="deptid">
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
                  </select></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Phone No:</td>
                  <td><span id="sprytextfield4">
                    <input type="text" name="phone" value="" size="32" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Email:</td>
                  <td><span id="sprytextfield5">
                    <input type="text" name="email" value="" size="32" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Address:</td>
                  <td><span id="sprytextfield6">
                    <input type="text" name="addr" value="" size="32" />
                    <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Sex:</td>
                  <td><select name="sex">
                    <option value="M">Male</option>
                    <option value="F">Female</option>
                  </select></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Password:</td>
                  <td><input type="text" name="password" value="" size="32" /></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">&nbsp;</td>
                  <td><input type="submit" value="Add Staff" /></td>
                </tr>
              </table>
              <input type="hidden" name="MM_insert" value="form1" />
            </form>
            <p>&nbsp;</p>
          </div>
        </div></td>
      </tr>
       <?php if(5>10){?> 
      <tr>
        <td colspan="5"><div id="CollapsiblePanel2" class="CollapsiblePanel">
          <div class="CollapsiblePanelTab" tabindex="0">Add New Staff from File</div>
          <div class="CollapsiblePanelContent">
          	<form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data">
          	<table width="683" border="0">
              <tr>
                <td colspan="3">Upload CSV file with no column heading and in the order of: lectid, title, fname, lname, mname, deptid, phone, email, addr, sex, access, password, profile.</td>
                </tr>
              <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td width="104">Select File</td>
                <td width="427"><input name="filename" type="file" size="55" /></td>
                <td width="138"><input name="submit" type="submit" value="Upload Staffs" /></td>
              </tr>
            </table>
		  </form>
          </div>
        </div></td>
      </tr>
       <?php }?>
      <tr>
        <td colspan="5"></td>
      </tr>
      <tr>
        <td colspan="5"></td>
      </tr>
      <tr>
        <td>View All<br/></td>
        <td>View By Department<br/>
          <label for="dept2"></label>
          <select name="dept2" id="dept2" onchange="deptFilter(this)">
            <option value="-1" <?php if (isset($_GET['did']))if(!(strcmp(-1, $_GET['did']))) {echo "selected=\"selected\"";} ?>>---Select A Department---</option>
            <?php
do {  
?>
            <option value="<?php echo $row_dept['deptid']?>"<?php if (isset($_GET['did']))if (!(strcmp($row_dept['deptid'], $_GET['did']))) {echo "selected=\"selected\"";} ?>><?php echo $row_dept['deptname']?></option>
            <?php
} while ($row_dept = mysql_fetch_assoc($dept));
  $rows = mysql_num_rows($dept);
  if($rows > 0) {
      mysql_data_seek($dept, 0);
	  $row_dept = mysql_fetch_assoc($dept);
  }
?>
          </select></td>
        <td>View By College<br/>
          <label for="col"></label>
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
  $rows = mysql_num_rows($col);
  if($rows > 0) {
      mysql_data_seek($col, 0);
	  $row_col = mysql_fetch_assoc($col);
  }
?>
          </select></td>
        <td>&nbsp;</td>
        <td valign="bottom"><?php echo $totalRows_staff." staff"?></td>
      </tr>
      <tr>
        <td width="95"><br/></td>
        <td width="285">&nbsp;</td>
        <td width="161">&nbsp;</td>
        <td width="29">&nbsp;</td>
        <td width="96" valign="bottom">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="5"></td>
      </tr>
    </table>
    <table width="683" border="0">
      <?php if ($totalRows_staff > 0) { // Show if recordset not empty ?>
        <?php do { ?>
          <tr>
            <td width="319"><?php echo $row_staff['title']." ".$row_staff['lname'].", ".$row_staff['fname']." ".$row_staff['mname']; ?></td>
            <td width="114"></td>
            <td width="92"><a href="editlect.php?lid=<?php echo $row_staff['lectid']; ?>">Edit</a></td>
            <td width="89">Delete</td>
            <td width="47">&nbsp;</td>
          </tr>
          <?php } while ($row_staff = mysql_fetch_assoc($staff)); ?>
        <?php } // Show if recordset not empty ?>
    </table>
    
    <script type="text/javascript">
var CollapsiblePanel1 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel1", {contentIsOpen:false});
		var CollapsiblePanel2 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel2", {contentIsOpen:false});
		var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
		var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2");
		var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3");
		var sprytextfield4 = new Spry.Widget.ValidationTextField("sprytextfield4");
		var sprytextfield5 = new Spry.Widget.ValidationTextField("sprytextfield5");
		var sprytextfield6 = new Spry.Widget.ValidationTextField("sprytextfield6");
var sprytextfield7 = new Spry.Widget.ValidationTextField("sprytextfield7");
    </script>
  <!-- InstanceEndEditable --></div>
<div class="footer">
    <p><!-- end .footer -->   
    
    <?php require 'include/footer.php'; ?>
	
   </p>
  </div>
  <!-- end .container -->
</div>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($dept);

mysql_free_result($col);

mysql_free_result($staff);
?>
