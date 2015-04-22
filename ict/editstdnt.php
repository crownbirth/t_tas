<?php require_once('../Connections/tams.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}

require_once('../param/param.php');
require_once('../functions/function.php');

$MM_authorizedUsers = "20,21,22";
$MM_donotCheckaccess = "false";

define ('MAX_FILE_SIZE', 2048 * 1536);
define('UPLOAD_DIR', '../images/student/');



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

mysql_select_db($database_tams, $tams);

$colname_editstud = "-1";
if (isset($_GET['stid'])) {
  $colname_editstud = $_GET['stid'];
}


if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form1")) {
            
    $query_editstud = sprintf("SELECT * FROM student WHERE stdid = %s", GetSQLValueString($colname_editstud, "text"));
    $editstud = mysql_query($query_editstud, $tams) or die(mysql_error());
    $row_editstud = mysql_fetch_assoc($editstud);
    $totalRows_editstud = mysql_num_rows($editstud);
    
    $edit = array();
    $fields = array_keys($row_editstud);       
    foreach($_POST as $key => $fld) {
        if(in_array($key, $fields)) {            
            if(trim($fld) != trim($row_editstud[$key]))
                $edit[$key] = array('old' => trim($row_editstud[$key]), 'new' => trim($fld));
        }
    }
    
    unset($edit['password']);
    
    $password = '`password`='.GetSQLValueString($row_editstud['password'], "text").',';	
    if(isset($_POST['password']) && ($_POST['password'] != '')){
            $password = '`password`='.GetSQLValueString(md5($_POST['password']), "text").',';
            $edit['password'] = array('old' => '', 'new' => '');
    }	
//    var_dump($edit);
//    exit;
    $updateSQL = sprintf("UPDATE student SET stdid= %s, fname=%s, lname=%s, mname=%s, progid=%s, phone=%s, email=%s, addr=%s, sex=%s, dob=%s, sesid=%s, `level`=%s, `stid`=%s, admode=%s, %s status=%s, `access`=%s, credit=%s, profile=%s WHERE stdid=%s",
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
                        GetSQLValueString($_POST['stid'], "int"),
                        GetSQLValueString($_POST['admode'], "text"),
                        GetSQLValueString($password, "defined", $password),
                        GetSQLValueString($_POST['status'], "text"),
                        GetSQLValueString($_POST['access'], "int"),
                        GetSQLValueString($_POST['credit'], "int"),
                        GetSQLValueString($_POST['profile'], "text"),
                        GetSQLValueString($colname_editstud, "text"));

    $Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());
    $entid = mysql_insert_id();

    $upload = "";
    if($Result1){
         $upload = uploadFile(UPLOAD_DIR, "student", MAX_FILE_SIZE);
    }

    $params['entid'] = $colname_editstud;
    $params['enttype'] = 'student';
    $params['action'] = 'edit';
    $params['cont'] = json_encode($edit);
    audit_log($params);

    $insertGoTo = "srchstdnt.php";
    if (isset($_SERVER['QUERY_STRING'])) {
      $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
      $insertGoTo .= $_SERVER['QUERY_STRING'];
    }
  header(sprintf("Location: %s", $insertGoTo));
}

$query_editstud = sprintf("SELECT * FROM student WHERE stdid = %s", GetSQLValueString($colname_editstud, "text"));
$editstud = mysql_query($query_editstud, $tams) or die(mysql_error());
$row_editstud = mysql_fetch_assoc($editstud);
$totalRows_editstud = mysql_num_rows($editstud);

$query_sess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,6";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

$query_prog = "SELECT progid, progname FROM programme";
$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);
$totalRows_prog = mysql_num_rows($prog);

$query_state = "SELECT * FROM `state` ";
$state = mysql_query($query_state, $tams) or die(mysql_error());
$row_state = mysql_fetch_assoc($state);
$totalRows_state = mysql_num_rows($state);

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$image_url = '../images/student/profile.png';
$image = array("../images/student/{$colname_editstud}.jpg", "../images/student/{$colname_editstud}.png");
if(realpath("{$image[0]}")) {
    $image_url = $image[0];
}elseif(realpath("{$image[1]}")){
    $image_url = $image[1];
}

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout($site_root.'/ict');  
}
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Edit Student<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include 'include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
      <tr>
        <td><form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1" enctype="multipart/form-data">
          <table align="center">
            <tr valign="baseline">
                <td width="89" align="left" nowrap="nowrap">Select Image:</td>
                <td width="266" align="left">
                    <input type="file" name="filename" size="32" /></td>
                <td width="211"  align="left" rowspan="6">
                    <img src="<?php echo $image_url;?>" alt="" id="placeholder" name="placeholder" 
                         width="160" height="160" align="top"/>
                </td>
              </tr>  
            <tr valign="baseline">
              <td nowrap="nowrap" align="left">Matric No.:</td>
              <td colspan="2" align="left">
                  <input type="text" name="stdid" 
                         value="<?php echo $row_editstud['stdid']; ?>" 
                             <?php echo (getIctAccess() > 20)? "readonly=\"readonly\"": "" ?> />
              </td>
            </tr>
            <tr valign="baseline">
              <td nowrap="nowrap" align="left">First Name:</td>
              <td colspan="2" align="left">
                  <input type="text" name="fname" 
                         value="<?php echo htmlentities($row_editstud['fname'], ENT_COMPAT, 'utf-8'); ?>" size="32" />
              </td>
            </tr>
            <tr valign="baseline">
              <td nowrap="nowrap" align="left">Last Name:</td>
              <td colspan="2" align="left">
                  <input type="text" 
                         name="lname" 
                         value="<?php echo htmlentities($row_editstud['lname'], ENT_COMPAT, 'utf-8'); ?>" size="32" />
              </td>
            </tr>
            <tr valign="baseline">
              <td nowrap="nowrap" align="left">Middle Name:</td>
              <td colspan="2" align="left"><input type="text" name="mname" value="<?php echo htmlentities($row_editstud['mname'], ENT_COMPAT, 'utf-8'); ?>" size="32" /></td>
            </tr>
            
            <tr valign="baseline">
              <td nowrap="nowrap" align="left">Phone:</td>
              <td colspan="2" align="left"><input type="text" name="phone" value="<?php echo htmlentities($row_editstud['phone'], ENT_COMPAT, 'utf-8'); ?>" size="32" /></td>
            </tr>
            <tr valign="baseline">
              <td nowrap="nowrap" align="left">Email:</td>
              <td colspan="2" align="left"><input type="text" name="email" value="<?php echo htmlentities($row_editstud['email'], ENT_COMPAT, 'utf-8'); ?>" size="32" /></td>
            </tr>
             <tr valign="baseline">
              <td nowrap="nowrap" align="left">Programme:</td>
              <td colspan="2" align="left">
              <select name="progid" style="width: 200px">
                <?php
				do {  
				?>
                <option value="<?php echo $row_prog['progid']?>" <?php if (!(strcmp($row_prog['progid'], htmlentities($row_editstud['progid'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>><?php echo $row_prog['progname']?></option>
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
            </tr> 
            <tr valign="baseline">
              <td nowrap="nowrap" align="left" valign="top">Address:</td>
              <td colspan="2" align="left">
              <textarea  name="addr" cols="50" rows="5">
                  <?php echo htmlentities($row_editstud['addr'], ENT_COMPAT, 'utf-8'); ?>
              </textarea>              
              </td>
            </tr>
            <tr valign="baseline">
              <td nowrap="nowrap" align="left">Sex:</td>
              <td colspan="2" align="left"><select name="sex">
                <option value="M" <?php if (!(strcmp("M", htmlentities($row_editstud['sex'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>Male</option>
                <option value="F" <?php if (!(strcmp("F", htmlentities($row_editstud['sex'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>Female</option>
              </select></td>
            </tr>
            <tr valign="baseline">
              <td nowrap="nowrap" align="left">Date of Birth:</td>
              <td colspan="2" align="left"><input type="text" name="dob" value="<?php echo htmlentities($row_editstud['dob'], ENT_COMPAT, 'utf-8'); ?>" size="32" /></td>
            </tr>
            <tr valign="baseline">
              <td nowrap="nowrap" align="left">Session:</td>
              <td colspan="2" align="left"><select name="sesid">
                <?php
				do {  
				?>
                <option value="<?php echo $row_sess['sesid']?>" <?php if (!(strcmp($row_sess['sesid'], htmlentities($row_editstud['sesid'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>><?php echo $row_sess['sesname']?></option>
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
              <td nowrap="nowrap" align="left">Level:</td>
              <td colspan="2" align="left">
              <select name="level">
                <option value="1" <?php if (!(strcmp("1", htmlentities($row_editstud['level'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>100</option>
                <option value="2" <?php if (!(strcmp("2", htmlentities($row_editstud['level'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>200</option>
                <option value="3" <?php if (!(strcmp("3", htmlentities($row_editstud['level'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>300</option>
                <option value="4" <?php if (!(strcmp("4", htmlentities($row_editstud['level'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>400</option>
                <option value="5" <?php if (!(strcmp("5", htmlentities($row_editstud['level'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>500</option>
                <option value="6" <?php if (!(strcmp("6", htmlentities($row_editstud['level'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>600</option>
              </select>
              </td>
            </tr>
            <?php if(getIctAccess()== 20){?> 
                <tr valign="baseline">
                    <td nowrap="nowrap" align="left">State :</td>
                    <td colspan="2" align="left"><select name="stid">
                       <?php do{?>     
                            <option value="<?php echo $row_state['stid']?>" <?php if (!(strcmp($row_state['stid'], htmlentities($row_editstud['stid'], ENT_COMPAT, 'utf-8')))){echo "SELECTED";}?>><?php echo $row_state['stname']?></option>
                       <?php }while ($row_state = mysql_fetch_assoc($state))?>
                    </select></td>
                </tr> 
            <?php }?>
            <tr valign="baseline">
              <td nowrap="nowrap" align="left">Admode:</td>
              <td colspan="2" align="left"><select name="admode">
                <option value="UTME" <?php if (!(strcmp("UTME", htmlentities($row_editstud['admode'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>UTME</option>
                <option value="DE" <?php if (!(strcmp("DE", htmlentities($row_editstud['admode'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>Direct Entry</option>
              </select></td>
            </tr>
            <tr valign="baseline">
              <td nowrap="nowrap" align="left">Password:</td>
              <td colspan="2" align="left"><input type="text" name="password" value="" size="32" /></td>
            </tr>
            <tr valign="baseline">
              <td nowrap="nowrap" align="left">Status:</td>
              <td colspan="2" align="left"><select name="status">
                <option value="Undergrad" <?php if (!(strcmp("Undergrad", htmlentities($row_editstud['status'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>Undergraduate</option>
                <option value="Graduate" <?php if (!(strcmp("Graduate", htmlentities($row_editstud['status'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>>Graduate</option>
              </select></td>
            </tr>
            <tr valign="baseline">
              <td nowrap="nowrap" align="left">Credit:</td>
              <td colspan="2" align="left"><input type="text" name="credit" value="<?php echo htmlentities($row_editstud['credit'], ENT_COMPAT, 'utf-8'); ?>" size="32" /></td>
            </tr>
            <tr valign="baseline">
              <td nowrap="nowrap" valign="top" align="left">Profile:</td>
              <td colspan="2" align="left">
              <textarea  name="profile" cols="50" rows="5">
              	<?php echo htmlentities($row_editstud['profile'], ENT_COMPAT, 'utf-8'); ?>
              </textarea>
              </td>
            </tr>
            <tr valign="baseline">
              <td nowrap="nowrap" align="left">&nbsp;</td>
              <td colspan="2" align="left"><input type="submit" value="Update Student" /></td>
            </tr>
          </table>
          <input type="hidden" name="access" value="<?php echo htmlentities($row_editstud['access'], ENT_COMPAT, 'utf-8'); ?>" />
          <input type="hidden" name="MM_update" value="form1" />
        </form>
        <p>&nbsp;</p></td>
      </tr>
    </table>
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
mysql_free_result($editstud);

mysql_free_result($sess);

mysql_free_result($prog);
?>
