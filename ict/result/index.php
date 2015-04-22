<?php require_once('../../Connections/tams.php'); ?>
<?php
// *** Validate request to login to this site.
if (!isset($_SESSION)) {
  session_start();
}

$reroot = 'index.php';
require_once('../../param/param.php');
require_once('../../functions/function.php');

$MM_authorizedUsers = "20, 22";
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
mysql_select_db($database_tams, $tams);
$query = sprintf("SELECT * FROM session ORDER BY sesid DESC");
$session = mysql_query($query, $tams) or die(mysql_error());
$row_session = mysql_fetch_assoc($session);

mysql_select_db($database_tams, $tams);
$query = sprintf("SELECT * FROM programme ORDER BY progname ASC");
$prog = mysql_query($query, $tams) or die(mysql_error());
$row_proramme = mysql_fetch_assoc($prog);

mysql_select_db($database_tams, $tams);
$query = "SELECT * FROM course "
                . "WHERE type = 'General' "
                . "AND csid LIKE 'EDU___' "
                . "OR csid LIKE 'GNS___' "
                . "OR csid LIKE 'ENT___' "
                . "OR csid LIKE 'EDU____' "
                . "OR csid LIKE 'GNS____' "
                . "OR csid LIKE 'ENT____' "
                . "ORDER BY csid ASC";
$course = mysql_query($query, $tams) or die(mysql_error());
$row_course = mysql_fetch_assoc($course);

    
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
<link href="../../css/template.css" rel="stylesheet" type="text/css" />
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
        <td><!-- InstanceBeginEditable name="pagetitle" --> General University Exam Result Page  <!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
      <form name="form1" method="POST" action="printresult.php" target="_blank">
            <table width="690" class="table table-bordered"> 
            <tr>
                <td>
                      <table class="table table-bordered table-condensed table-striped table-hover" >
                          <tr>
                              <td width="100">Programme </td>
                                <td>
                                  <select name="progid" style=" width: 300px" required>
                                      <option value="">-Choose-</option>
                                      <?php do{?>
                                      <option value="<?php echo $row_proramme['progid']?>"><?php echo ucfirst($row_proramme['progname'])?></option>
                                      <?php }while ($row_proramme = mysql_fetch_assoc($prog))?>
                                  </select>
                                </td>
                          </tr>
                          <!-- <tr>
                              <td width="100">Level  </td>
                                <td>
                                  <select name="level" style=" width: 100px" required>
                                      <option value="">-Choose-</option>
                                      <option value="1">100</option>
                                      <option value="2">200</option>
                                      <option value="3">300</option>
                                      <option value="4">400</option>
                                  </select>
                                </td>
                          </tr> -->
                          <tr>
                              <td>Session </td>
                              <td>
                                  <select name="sesid" required>
                                      <option value="">-Choose-</option>
                                      <?php do{?>
                                          <option value="<?php echo $row_session['sesid']?>"><?php echo $row_session['sesname']?></option>
                                      <?php }while ($row_session = mysql_fetch_assoc($session))?>
                                  </select>
                              </td>
                          </tr>
                          <tr>
                              <td width="100">Course </td>
                                <td>
                                  <select name="csid" style=" width: 300px" required>
                                      <option value="">-Choose-</option>
                                      <?php do{?>
                                      <option value="<?php echo $row_course['csid']?>"><?php echo $row_course['csid'].'  -  '.$row_course['csname']?></option>
                                      <?php }while ($row_course = mysql_fetch_assoc($course))?>
                                  </select>
                                </td>
                          </tr>
                          <tr>
                              <td>&nbsp;</td>
                              <td><input type="submit" name="submit" value="Generate"></td>
                              <input type="hidden" name="MM_Insert" value="form1"/>
                          </tr>
                      </table>
                    </td>
                </tr>    
            </table>
        </form>
    </div>
<div class="footer">
    <p><!-- end .footer -->   
    
    <?php require '../include/footer.php'; ?>
	
   </p>
  </div>
  <!-- end .container -->
</div>
</body>
</html>