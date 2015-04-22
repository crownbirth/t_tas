<?php require_once('../Connections/tams.php');
//require_once('../Connections/conn_burmas.php');

if (!isset($_SESSION)) {
  session_start();
}

require_once('../param/param.php'); 
require_once('../functions/function.php');

$MM_authorizedUsers = "20";
$MM_donotCheckaccess = "true";

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
    if (($strUsers == "") && true) { 
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

mysql_select_db($database_tams, $tams);

$msg = NULL;
if(isset($_POST['add'])) {
    $msg = 'There was a problem adding the student you specified!';
    if($_POST['stdid'] != '' && $_POST['sesid'] != '' && $_POST['level'] != '') {
        $query_insert = sprintf("REPLACE INTO student_pop VALUES(%s, %s, %s)", 
                                GetSQLValueString($_POST['stdid'], "text"), 
                                GetSQLValueString($_POST['sesid'], "int"), 
                                GetSQLValueString($_POST['level'], "int"));
        $status = mysql_query($query_insert, $tams) or die(mysql_error());
        
        if($status)
            $msg = 'Student added successfully!';
    }else {
        $msg = 'Some required fields are missing!';
    }
}

$query_rssess = "SELECT * FROM `session` ORDER BY sesid DESC LIMIT 0,6";
$rssess = mysql_query($query_rssess, $tams) or die(mysql_error());
$row_rssess = mysql_fetch_assoc($rssess);
$totalRows_rssess = mysql_num_rows($rssess);

$query_duration = "SELECT MAX(duration) as max FROM `programme`";
$duration = mysql_query($query_duration, $tams) or die(mysql_error());
$row_duration = mysql_fetch_assoc($duration);
$totalRows_duration = mysql_num_rows($duration);

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout($site_root.'/ict');  
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <!-- InstanceBegin template="/Templates/icttemplate.dwt.php" codeOutsideHTMLIsLocked="false" -->
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
        <td><!-- InstanceBeginEditable name="pagetitle" --><?php echo "Add student"?> <!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include 'include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->   
    <table width="690">
        <?php if($msg) {?>
        <tr>
          <td><?php echo $msg?></td>
        </tr>
        <?php }?>
        
        <tr>
            <td>
                <form action="<?php echo $editFormAction; ?>" method="post" name="form" id="form">
                  <table class="table table-striped">                                         
                    <tr valign="baseline">
                      <td nowrap="nowrap" align="right">Matric No.:</td>
                      <td><input type="text" name="stdid"/></td>
                    </tr>
                    <tr valign="baseline">
                      <td nowrap="nowrap" align="right">Session:</td>
                      <td>
                        <select name="sesid">
                            <?php for($idx = 0; $idx < $totalRows_rssess; $idx++, $row_rssess = mysql_fetch_assoc($rssess)) {?>
                            <option value="<?php echo $row_rssess['sesid']?>"><?php echo $row_rssess['sesname']?></option>
                            <?php }?>
                          </select>
                      </td>
                    </tr>
                      
                    <tr valign="baseline">
                      <td nowrap="nowrap" align="right">Level:</td>
                      <td>
                          <select name="level">
                              <?php for($idx = 0; $idx < $row_duration['max']; $idx++) {?>
                                <option value="<?php echo $idx + 1?>"><?php echo $idx + 1?></option>
                              <?php }?>
                          </select>
                      </td>
                    </tr>
                  </table>
                  <input type="submit" name="add" value="Add" />
                </form>
                
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
<!-- InstanceEnd -->
</html>

