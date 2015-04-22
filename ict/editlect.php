<?php require_once('../Connections/tams.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}

require_once('../param/param.php');
require_once('../functions/function.php');

define ('MAX_FILE_SIZE', 1024 * 50);
define('UPLOAD_DIR', '../images/staff/');

$colname_editprof = "-1";
if (isset($_GET['lid'])) {
  $colname_editprof = $_GET['lid'];
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
    
    $query_editprof = sprintf("SELECT * FROM lecturer WHERE lectid = %s", GetSQLValueString($colname_editprof, "text"));
    $editprof = mysql_query($query_editprof, $tams) or die(mysql_error());
    $row_editprof = mysql_fetch_assoc($editprof);
    $totalRows_editprof = mysql_num_rows($editprof); 

    $edit = array();
    $fields = array_keys($row_editprof);       
    foreach($_POST as $key => $fld) {
        if(in_array($key, $fields)) {
            if(trim($fld) != trim($row_editprof[$key]))
                $edit[$key] = array('old' => trim($row_editprof[$key]), 'new' => trim($fld));
        }
    }
    
    unset($edit['password']);
    
    $password = '';
    if(isset($_POST['password']) && $_POST['password'] != '') {
        $password = 'password='.GetSQLValueString(md5($_POST['password']), "text").',';
        $edit['password'] = array('old' => '', 'new' => '');
    }
	
    $updateSQL = sprintf("UPDATE lecturer SET title=%s, fname=%s, lname=%s, mname=%s, phone=%s, deptid=%s, email=%s, addr=%s, sex=%s, %s profile=%s WHERE lectid=%s",
                       GetSQLValueString($_POST['title'], "text"),
		       GetSQLValueString($_POST['fname'], "text"),
                       GetSQLValueString($_POST['lname'], "text"),
                       GetSQLValueString($_POST['mname'], "text"),
                       GetSQLValueString($_POST['phone'], "text"),
                       GetSQLValueString($_POST['deptid'], "int"),
                       GetSQLValueString($_POST['email'], "text"),
                       GetSQLValueString($_POST['addr'], "text"),
                       GetSQLValueString($_POST['sex'], "text"),
                       $password,
                       GetSQLValueString($_POST['profile'], "text"),
                       GetSQLValueString($colname_editprof, "text"));

    mysql_select_db($database_tams, $tams);
    $Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());
  
  $upload = "";
  
  if( $Result1 ){
            $upload = uploadFile( UPLOAD_DIR, "staff", MAX_FILE_SIZE);
    }

    $params['entid'] = $colname_editprof;
    $params['enttype'] = 'lecturer';
    $params['action'] = 'edit';
    $params['cont'] = json_encode($edit);
    audit_log($params);
    
  $updateGoTo = "srchstdnt.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
}

$query_editprof = sprintf("SELECT * FROM lecturer WHERE lectid = %s", GetSQLValueString($colname_editprof, "text"));
$editprof = mysql_query($query_editprof, $tams) or die(mysql_error());
$row_editprof = mysql_fetch_assoc($editprof);
$totalRows_editprof = mysql_num_rows($editprof);

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout($site_root.'/ict');  
}

mysql_select_db($database_tams, $tams);
$query_dept = "SELECT deptid, deptname FROM department";
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/template.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php require('../param/site.php'); ?>
<title><?php echo $university ?> </title>
<script src="scripts/widgEditor.js" type="text/javascript"></script>
<link href="css/widgEditor.css" rel="stylesheet" type="text/css" />
<!-- InstanceEndEditable -->
<link href="css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Edit Profile of <?php echo $row_editprof['lname'].", ".substr($row_editprof['fname'],0,1)?><!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include 'include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
      <tr>
        <td>&nbsp;
          <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1" enctype="multipart/form-data">
            <table align="center">
              <tr valign="baseline">
                <td width="89" align="right" nowrap="nowrap">Select Image:</td>
                <td width="266"><input type="file" name="filename" size="32" /></td>
                <td width="211" rowspan="7"><img src="" alt="" id="placeholder" name="placeholder" width="160" height="160" align="top"/></td>
              </tr>
              <tr valign="baseline">
                <td nowrap="nowrap" align="right">Title:</td>
                <td><select name="title">
                    <option value="Prof" <?php if(!(strcmp("Prof",htmlentities( $row_editprof['title'], ENT_COMPAT, 'utf-8')))) {echo "selected=\"selected\"";} ?>>Prof.</option>
                    <option value="Dr" <?php if(!(strcmp("Dr",htmlentities( $row_editprof['title'], ENT_COMPAT, 'utf-8')))) {echo "selected=\"selected\"";} ?>>Dr.</option>
                    <option value="Mr" <?php if(!(strcmp("Mr",htmlentities( $row_editprof['title'], ENT_COMPAT, 'utf-8')))) {echo "selected=\"selected\"";} ?>>Mr.</option>
                    <option value="Mrs" <?php if(!(strcmp("Mrs",htmlentities( $row_editprof['title'], ENT_COMPAT, 'utf-8')))) {echo "selected=\"selected\"";} ?>>Mrs.</option>
                    <option value="Miss" <?php if(!(strcmp("Miss",htmlentities( $row_editprof['title'], ENT_COMPAT, 'utf-8')))) {echo "selected=\"selected\"";} ?>>Miss</option>
                  </select></td>
              </tr>
              <tr valign="baseline">
                <td nowrap="nowrap" align="right">First Name:</td>
                <td><input type="text" name="fname" value="<?php echo htmlentities($row_editprof['fname'], ENT_COMPAT, 'utf-8'); ?>" size="32" /></td>
              </tr>
              <tr valign="baseline">
                <td nowrap="nowrap" align="right">Last Name:</td>
                <td><input type="text" name="lname" value="<?php echo htmlentities($row_editprof['lname'], ENT_COMPAT, 'utf-8'); ?>" size="32" /></td>
              </tr>
              <tr valign="baseline">
                <td nowrap="nowrap" align="right">Middle Name:</td>
                <td><input type="text" name="mname" value="<?php echo htmlentities($row_editprof['mname'], ENT_COMPAT, 'utf-8'); ?>" size="32" /></td>
              </tr>
              <tr valign="baseline">
                <td nowrap="nowrap" align="right">Department:</td>
                <td><label for="deptid"></label>
                  <select name="deptid" id="deptid">
                    <?php
do {  
?>
                    <option value="<?php echo $row_dept['deptid']?>"<?php if (!(strcmp($row_dept['deptid'],htmlentities( $row_editprof['deptid'], ENT_COMPAT, 'utf-8')))) {echo "selected=\"selected\"";} ?>><?php echo $row_dept['deptname']?></option>
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
                <td nowrap="nowrap" align="right">Phone No.:</td>
                <td><input type="text" name="phone" value="<?php echo htmlentities($row_editprof['phone'], ENT_COMPAT, 'utf-8'); ?>" size="32" /></td>
              </tr>
              <tr valign="baseline">
                <td height="24" align="right" nowrap="nowrap">Email:</td>
                <td><input type="text" name="email" value="<?php echo htmlentities($row_editprof['email'], ENT_COMPAT, 'utf-8'); ?>" size="32" /></td>
              </tr>
              <tr valign="baseline">
                <td nowrap="nowrap" align="right" valign="middle">Address:</td>
                <td colspan="2"><textarea name="addr" cols="50" rows="5"><?php echo htmlentities($row_editprof['addr'], ENT_COMPAT, 'utf-8'); ?></textarea></td>
              </tr>
              <tr valign="baseline">
                <td nowrap="nowrap" align="right">Sex:</td>
                <td colspan="2"><select name="sex">
                  <option value="M" <?php if (!(strcmp("M", htmlentities($row_editprof['sex'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>Male</option>
                  <option value="F" <?php if (!(strcmp("F", htmlentities($row_editprof['sex'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>Female</option>
                </select></td>
              </tr>
              <tr valign="baseline">
                <td nowrap="nowrap" align="right">Password:</td>
                <td colspan="2"><input type="text" name="password" value="<?php //echo htmlentities($row_editprof['password'], ENT_COMPAT, 'utf-8'); ?>" size="32" /></td>
              </tr>
              <tr valign="baseline">
                <td align="right" valign="middle" nowrap="nowrap">Research Area:</td>
                <td colspan="2"><textarea name="profile" cols="50" rows="5" class="widgEditor nothing"><?php echo htmlentities($row_editprof['profile'], ENT_COMPAT, 'utf-8'); ?></textarea></td>
              </tr>
              <tr valign="baseline">
                <td nowrap="nowrap" align="right">&nbsp;</td>
                <td colspan="2"><input type="submit" value="Update Profile" /></td>
              </tr>
            </table>
            
            <input type="hidden" name="MM_update" value="form1" />
            <input type="hidden" name="lectid" value="<?php echo $row_editprof['lectid']; ?>" />
          </form>
        <p>&nbsp;</p></td>
      </tr>
    </table>
    <script type="text/javascript">
    	$(document).ready(function() {
            $('#filename').blur(function() {
                var img = $('#filename').val();
				$('#placeholder').attr('src',img);
            });
        });
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
mysql_free_result($editprof);

mysql_free_result($dept);
?>
