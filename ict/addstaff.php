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
$query_Rsict = "SELECT stfid, title, lname, fname, mname, `access` FROM ictstaff";
$Rsict = mysql_query($query_Rsict, $tams) or die(mysql_error());
$row_Rsict = mysql_fetch_assoc($Rsict);
$totalRows_Rsict = mysql_num_rows($Rsict);

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
	
    $ictQuery = "SELECT stfid FROM ictstaff ORDER BY stfid DESC LIMIT 0, 1";
    mysql_select_db($database_tams, $tams);
    $Result = mysql_query($ictQuery, $tams) or die(mysql_error());
    $ictid_row = mysql_fetch_assoc($Result);
    $totalRows_ictid = mysql_num_rows($Result);
    
    if($totalRows_ictid > 0) {
        $ictid_row['stfid'];
        $lastid = substr($ictid_row['stfid'],3);
        $lastid++;
        $newid = "ICT".str_pad($lastid, 4, '0', STR_PAD_LEFT);
    } else {
        $newid = "ICT0001";
    }
	
    $insertSQL = sprintf("INSERT INTO ictstaff (stfid,title, lname, fname, mname, phone, dob, email, addr, sex, `access`, password, profile) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                                                 GetSQLValueString($newid, "text"),
                        GetSQLValueString($_POST['title'], "text"),
                        GetSQLValueString($_POST['lname'], "text"),
                        GetSQLValueString($_POST['fname'], "text"),
                        GetSQLValueString($_POST['mname'], "text"),
                        GetSQLValueString($_POST['phone'], "text"),
                        GetSQLValueString($_POST['dob'], "text"),
                        GetSQLValueString($_POST['email'], "text"),
                        GetSQLValueString($_POST['addr'], "text"),
                        GetSQLValueString($_POST['sex'], "text"),
                        GetSQLValueString($_POST['access'], "int"),
                        GetSQLValueString(md5($_POST['password']), "text"),
                        GetSQLValueString($_POST['profile'], "text"));

    mysql_select_db($database_tams, $tams);
    $Result1 = mysql_query($insertSQL, $tams) or die(mysql_error());

    unset($_POST['MM_insert']);
   
    $params['entid'] = $newid;
    $params['enttype'] = 'ictstaff';
    $params['action'] = 'create';
    $params['cont'] = json_encode($_POST);
    audit_log($params);
    $insertGoTo = "addstaff.php";
    
    if(isset($_SERVER['QUERY_STRING'])) {
        $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
        $insertGoTo .= $_SERVER['QUERY_STRING'];
    }
    
    header(sprintf("Location: %s", $insertGoTo));
}

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout($site_root.'/ict');  
}

function getAccessByName($val){
	if ($val==20){
		echo "Admin";
	}
	else if($val==21){
		echo "Unit Head";
		}
	else if($val==22){
		echo "Staff";;
		}
        else{
            "";
        }        
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/icttemplate.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php require('../param/site.php'); ?>
<title><?php echo $university ?> </title>
<!-- InstanceEndEditable -->
<link href="css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<script src="../SpryAssets/SpryCollapsiblePanel.js" type="text/javascript"></script>
<link href="../SpryAssets/SpryCollapsiblePanel.css" rel="stylesheet" type="text/css" />
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Add new Staff<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include 'include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
      <tr>
        <td><div id="CollapsiblePanel1" class="CollapsiblePanel">
          <div class="CollapsiblePanelTab" tabindex="0">Add new Staff</div>
          <div class="CollapsiblePanelContent">
            <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
              <table align="center">
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
                  <td nowrap="nowrap" align="right">Last Name:</td>
                  <td><input type="text" name="lname" value="" size="32" /></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">First Name:</td>
                  <td><input type="text" name="fname" value="" size="32" /></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Middle Name:</td>
                  <td><input type="text" name="mname" value="" size="32" /></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Phone:</td>
                  <td><input type="text" name="phone" value="" size="32" /></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Date of birth:</td>
                  <td><input type="text" name="dob" value="" size="32" /></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">Email:</td>
                  <td><input type="text" name="email" value="" size="32" /></td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right" valign="top">Addr:</td>
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
                  <td nowrap="nowrap" align="right">Access:</td>
                  <td><select name="access">
                    <option value="20">Admin</option>
                    <option value="21">Unit Head</option>
                    <option value="22">Staff</option>
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
                  <td><input type="submit" value="Add new Staff" /></td>
                </tr>
              </table>
              <input type="hidden" name="MM_insert" value="form1" />
            </form>
            <p>&nbsp;</p>
          </div>
        </div>          <p>&nbsp;</p>
        <table width="632" border="0" align="center">
          <tr align="center">
            <th width="82">Staff ID </th>
            <th width="242">Full Name</th>
            <th width="123">Access Lvl</th>
            <th width="167" colspan="2">Action </th>
            </tr>
            <?php if ($totalRows_Rsict > 0) { // Show if recordset not empty ?>
          <?php do { ?>
  <tr align="center">
    <td><?php echo $row_Rsict['stfid']; ?></td>
    <td><?php echo $row_Rsict['title']; ?> <?php echo $row_Rsict['fname']; ?> <?php echo $row_Rsict['lname']; ?></td>
    <td><?php getAccessByName($row_Rsict['access']); ?></td>
    <td><a href="editstaff.php?stfid=<?php echo $row_Rsict['stfid']; ?>">Edit</a></td>
    <td><a href="#">Delete</a></td>
  </tr>
<?php } while ($row_Rsict = mysql_fetch_assoc($Rsict)); ?>
 <?php } else{ ?>
 		<tr>
        	<td colspan="5" style="color:#F00" align="center"> No Record Available </td>
        </tr>
 <?php }?>
        </table>
        <p>&nbsp;</p></td>
      </tr>
    </table>
    <script type="text/javascript">
var CollapsiblePanel1 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel1", {contentIsOpen:false});
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
mysql_free_result($Rsict);
?>
