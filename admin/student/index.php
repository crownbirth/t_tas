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

$MM_restrictGoTo = "../../index.php";
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

//Upload File
$rsinsert;
$uploadstat = "";
$insert_row = 0;
$insert_error = array();
if ((isset($_POST["submit"])) && ($_POST["submit"] == "Upload Students")) {
  if(is_uploaded_file($_FILES['filename']['tmp_name'])){
		//Import uploaded file to Database	
		$handle = fopen($_FILES['filename']['tmp_name'], "r");
		while (($data = fgetcsv($handle, 1500, ",")) !== FALSE) 
		{
							
			$insert_query = sprintf("INSERT INTO student (stdid, fname, lname, mname, progid, phone, email, addr, sex, dob, sesid, `level`, admode, password, status, `access`, credit, profile) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($data[0], "text"),
                       GetSQLValueString($data[1], "text"),
                       GetSQLValueString($data[2], "text"),
                       GetSQLValueString($data[3], "text"),
                       GetSQLValueString($data[4], "int"),
                       GetSQLValueString($data[5], "text"),
                       GetSQLValueString($data[6], "text"),
                       GetSQLValueString($data[7], "text"),
                       GetSQLValueString($data[8], "text"),
                       GetSQLValueString($data[9], "date"),
                       GetSQLValueString($data[10], "int"),
                       GetSQLValueString($data[11], "int"),
                       GetSQLValueString($data[12], "text"),
                       GetSQLValueString(md5($data[13]), "text"),
                       GetSQLValueString($data[14], "text"),
                       GetSQLValueString($data[15], "int"),
                       GetSQLValueString($data[16], "int"),
                       GetSQLValueString($data[17], "text"));
			mysql_select_db($database_tams, $tams);	
			/*$rsinsert = mysql_query($insert_query, $tams);
			echo mysql_info($tams);
			list($f,$s,$t) = explode(":", mysql_info($tams));					   
			$insert = strpos($s,"1");*/
			
			$rsinsert1 = mysql_query($insert_query, $tams);
			list($f,$s,$t) = explode(":", mysql_info($tams));					   
			$update1 = strpos($s,"1");
			if( $update1 ){
				$insert_row++;
							
			}else{
				$insert_error[] = $data[0];
				
			}
		}
		if( count($insert_error) > 0 ){
			$uploadstat = "Upload Unsuccessful! The following results could not be uploaded:<br/>";
			foreach( $insert_error as $error){
				$uploadstat .= $error."<br/>";
			}
		}else{
			$uploadstat = "Upload Successful! ".$insert_row." results uploaded.";
		}
		fclose($handle);
	}
}


if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO student (stdid, fname, lname, mname, progid, phone, email, addr, sex, dob, sesid, `level`, admode, password, status, `access`, credit, profile) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['stdid'], "text"),
                       GetSQLValueString($_POST['fname'], "text"),
                       GetSQLValueString($_POST['lname'], "text"),
                       GetSQLValueString($_POST['mname'], "text"),
                       GetSQLValueString($_POST['progid'], "int"),
                       GetSQLValueString($_POST['phone'], "text"),
                       GetSQLValueString($_POST['email'], "text"),
                       GetSQLValueString($_POST['addr'], "text"),
                       GetSQLValueString($_POST['sex'], "text"),
                       GetSQLValueString($_POST['dob'], "date"),
                       GetSQLValueString($_POST['sesid'], "int"),
                       GetSQLValueString($_POST['level'], "int"),
                       GetSQLValueString($_POST['admode'], "text"),
                       GetSQLValueString(md5($_POST['password']), "text"),
                       GetSQLValueString($_POST['status'], "text"),
                       GetSQLValueString($_POST['access'], "int"),
                       GetSQLValueString($_POST['credit'], "int"),
                       GetSQLValueString($_POST['profile'], "text"));

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
$query_prog = "SELECT progid, progname FROM programme";
$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);
$totalRows_prog = mysql_num_rows($prog);

mysql_select_db($database_tams, $tams);
$query_sess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,1";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

mysql_select_db($database_tams, $tams);
$query_prog1 = (isset($_GET['cid']))?"SELECT progid, progname FROM programme p, department d WHERE d.deptid = p.deptid AND colid = ".$_GET['cid']." ORDER BY progname ASC":"SELECT progid, progname FROM programme WHERE  deptid= 0 ORDER BY progname ASC";
$prog1 = mysql_query($query_prog1, $tams) or die(mysql_error());
$row_prog1 = mysql_fetch_assoc($prog1);
$totalRows_prog1 = mysql_num_rows($prog1);

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
 
$level = '-1';
if( isset($_GET['lvl']) ){
	$level = $_GET['lvl'];	
}

$sub = false;
$path = pathinfo( $_SERVER['SCRIPT_FILENAME'] );

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
<!-- InstanceEndEditable -->
<link href="../../css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<script src="../../scripts/tams.js" type="text/javascript"></script>
<script src="../../SpryAssets/SpryCollapsiblePanel.js" type="text/javascript"></script>
<link href="../../SpryAssets/SpryCollapsiblePanel.css" rel="stylesheet" type="text/css" />
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
        <td><!-- InstanceBeginEditable name="pagetitle" --> Students in the University<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
    <tr>
    	<td colspan="5"><?php echo $uploadstat?></td>
    </tr>
      <tr>
        <td colspan="5"><div id="CollapsiblePanel1" class="CollapsiblePanel">
          <div class="CollapsiblePanelTab" tabindex="0">Add New Student</div>
          <div class="CollapsiblePanelContent">
            <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
              <table align="center">
              	<tr valign="baseline">
                  <td nowrap="nowrap" align="right">Matric No.:</td>
                  <td><input type="text" name="stdid" value="" size="32" /></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">First Name:</td>
                  <td><input type="text" name="fname" value="" size="32" /></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Last Name:</td>
                  <td><input type="text" name="lname" value="" size="32" /></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Middle Name:</td>
                  <td><input type="text" name="mname" value="" size="32" /></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Programme:</td>
                  <td><select name="progid">
                    <?php
do {  
?>
                    <option value="<?php echo $row_prog['progid']?>"><?php echo $row_prog['progname']?></option>
                    <?php
} while ($row_prog = mysql_fetch_assoc($prog));
  $rows = mysql_num_rows($prog);
  if($rows > 0) {
      mysql_data_seek($prog, 0);
	  $row_prog = mysql_fetch_assoc($prog);
  }
?>
                  </select></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Phone:</td>
                  <td><input type="text" name="phone" value="" size="32" /></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Email:</td>
                  <td><input type="text" name="email" value="" size="32" /></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right" valign="top">Address:</td>
                  <td><textarea name="addr" cols="50" rows="5"></textarea></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Sex:</td>
                  <td><select name="sex">
                    <option value="M">Male</option>
                    <option value="F">Female</option>
                  </select></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Dob:</td>
                  <td><input type="text" name="dob" value="" size="32" /></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Session:</td>
                  <td><select name="sesid">
                    <?php
do {  
?>
                    <option value="<?php echo $row_sess['sesid']?>"><?php echo $row_sess['sesname']?></option>
                    <?php
} while ($row_sess = mysql_fetch_assoc($sess));
  $rows = mysql_num_rows($sess);
  if($rows > 0) {
      mysql_data_seek($sess, 0);
	  $row_sess = mysql_fetch_assoc($sess);
  }
?>
                  </select></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Level:</td>
                  <td><select name="level">
                    <option value="1">100</option>
                    <option value="2">200</option>
                  </select></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Admode:</td>
                  <td><select name="admode">
                    <option value="UTME" >UTME</option>
                    <option value="DE" >DE</option>
                  </select></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Password:</td>
                  <td><input type="text" name="password" value="" size="32" /></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right" valign="top">Profile:</td>
                  <td><textarea name="profile" cols="50" rows="5"></textarea></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">&nbsp;</td>
                  <td><input type="submit" value="Add Student" /></td>
                </tr>
              </table>
              <input type="hidden" name="status" value="Undergrad" />
              <input type="hidden" name="access" value="10" />
              <input type="hidden" name="credit" value="0" />
              <input type="hidden" name="MM_insert" value="form1" />
            </form>
            <p>&nbsp;</p>
          </div>
        </div></td>
      </tr>
      <tr>
        <td colspan="5"><div id="CollapsiblePanel2" class="CollapsiblePanel">
          <div class="CollapsiblePanelTab" tabindex="0">Add New Student from File</div>
          <div class="CollapsiblePanelContent">
          	<form action="<?php echo $editFormAction; ?>" method="post" enctype="multipart/form-data">
          	<table width="683" border="0">
              <tr>
                <td colspan="3">Upload CSV file with no column heading and in the order of: stdid, fname, mname, lname, progid, phone, email, addr, sex, dob, sesid, admode, password, status, acess, credit, profile.</td>
                </tr>
              <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td width="104">Select File</td>
                <td width="427"><input name="filename" type="file" size="55" /></td>
                <td width="138"><input name="submit" type="submit" value="Upload Students" /></td>
              </tr>
            </table>
		  </form>
          </div>
        </div></td>
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
        <td width="254">View By Programme<br/>
          <label for="prog"></label>
          <select name="prog" id="prog" onchange="progFilter(this)">
            <option value="-1" <?php if (isset($_GET['pid']))if(!(strcmp(-1, $_GET['pid']))) {echo "selected=\"selected\"";} ?>>---Select A Programme---</option>
            <?php
			$rows = mysql_num_rows($prog1);
  if($rows > 0) {
      mysql_data_seek($prog, 0);
	  $row_dept = mysql_fetch_assoc($prog1);
  }
do {  
?>
            <option value="<?php echo $row_prog1['progid']?>"<?php if (isset($_GET['pid']))if (!(strcmp($row_prog1['progid'], $_GET['pid']))) {echo "selected=\"selected\"";} ?>><?php echo $row_prog1['progname']?></option>
            <?php
} while ($row_prog1 = mysql_fetch_assoc($prog1));
?>
        </select></td>
        <td width="101">Choose Level<br/>
          <select name="level" id="level" onchange="lvlFilter(this)">
          	<option value="-1" <?php if (!(strcmp(-1, $level))) {echo "selected=\"selected\"";} ?>>--Level--</option>
            <option value="1" <?php if (!(strcmp(1, $level))) {echo "selected=\"selected\"";} ?>>100</option>
            <option value="2" <?php if (!(strcmp(2, $level))) {echo "selected=\"selected\"";} ?>>200</option>
            <option value="3" <?php if (!(strcmp(3, $level))) {echo "selected=\"selected\"";} ?>>300</option>
            <option value="4" <?php if (!(strcmp(4, $level))) {echo "selected=\"selected\"";} ?>>400</option>
        </select></td>
        <td width="29" valign="bottom"><?php echo $totalRows_student." students"?></td>
      </tr>
      <tr>
        <td width="175"  colspan="5">
        	<table width="650" border="0">
			  <?php if ($totalRows_student > 0) { // Show if recordset not empty ?>
                <?php do { ?>
                  <tr>
                    <td width="138"><?php echo $row_student['stdid']; ?></td>
                    <td><a href="../../student/profile.php?stid=<?php echo $row_student['stdid']; ?>"><?php echo $row_student['fname']; ?>, <?php echo ucwords(strtolower($row_student['lname'])); ?></a></td>
                    <td width="130"><a href="../registration/viewform.php?stid=<?php echo $row_student['stdid'];?>"><?php echo "Course Form"?></a></td>
                    <td width="119"><a href="../result/transcript.php?stid=<?php echo $row_student['stdid'];?>"><?php echo "Transcript"?></a></td>
                    <td><a href="editstud.php?stid=<?php echo $row_student['stdid']; ?>">Edit</a></td>
                  </tr>
                  <?php } while ($row_student = mysql_fetch_assoc($student));?>
                  
                <?php } // Show if recordset not empty ?>
            </table></td>
      </tr>
    </table>
    <script type="text/javascript">
var CollapsiblePanel1 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel1", {contentIsOpen:false});
var CollapsiblePanel2 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel2", {contentIsOpen:false});
    </script>
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
<?php
mysql_free_result($prog);

mysql_free_result($sess);

mysql_free_result($student);

mysql_free_result($prog1);
?>
