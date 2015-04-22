<?php require_once('../../Connections/tams.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
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
  if (isset($QUERY_STRING) && strlen($QUERY_STRING) > 0) 
  $MM_referrer .= "?" . $QUERY_STRING;
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
}
?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
require_once('../../param/param.php');
require_once('../../functions/function.php');


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

$currentPage = $_SERVER["PHP_SELF"];

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO disciplinary (sesid, stdid, status, tearm, login) VALUES (%s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['seesion'], "int"),
                       GetSQLValueString($_POST['matric'], "text"),
                       GetSQLValueString($_POST['action'], "text"),
                       GetSQLValueString($_POST['comment'], "text"),
                       GetSQLValueString($_POST['login'], "text"));

  mysql_select_db($database_tams, $tams);
  $Result1 = mysql_query($insertSQL, $tams) or die(mysql_error());
  
  $updateSQL = sprintf("UPDATE student SET disciplinary = %s WHERE stdid =%s",GetSQLValueString("True", "text"),GetSQLValueString($_POST['matric'], "text"));
  
  mysql_select_db($database_tams, $tams);
  $Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());
}

mysql_select_db($database_tams, $tams);
$query_rsses = "SELECT * FROM `session` ORDER BY sesid DESC";
$rsses = mysql_query($query_rsses, $tams) or die(mysql_error());
$row_rsses = mysql_fetch_assoc($rsses);
$totalRows_rsses = mysql_num_rows($rsses);

$maxRows_rsdisp = 10;
$pageNum_rsdisp = 0;
if (isset($_GET['pageNum_rsdisp'])) {
  $pageNum_rsdisp = $_GET['pageNum_rsdisp'];
}
$startRow_rsdisp = $pageNum_rsdisp * $maxRows_rsdisp;

$stiddics = "-1";
if(isset($_POST['cleared'])){
    $stiddics = $_POST['stdiddisc'];
    $updateDics = sprintf("UPDATE student SET disciplinary = %s WHERE stdid =%s",GetSQLValueString("FALSE", "text"), GetSQLValueString($stiddics, "text"));
   mysql_select_db($database_tams, $tams);
  $Result1 = mysql_query($updateDics, $tams) or die(mysql_error()); 
    
 $deleteDisc =  sprintf("DELETE FROM disciplinary WHERE stdid =%s",GetSQLValueString($stiddics, "text")) ;
 mysql_select_db($database_tams, $tams);
 mysql_query($deleteDisc, $tams) or die(mysql_error());
}

mysql_select_db($database_tams, $tams);
$query_rsdisp = "SELECT * FROM disciplinary ORDER BY disid DESC";
$query_limit_rsdisp = sprintf("%s LIMIT %d, %d", $query_rsdisp, $startRow_rsdisp, $maxRows_rsdisp);
$rsdisp = mysql_query($query_limit_rsdisp, $tams) or die(mysql_error());
$row_rsdisp = mysql_fetch_assoc($rsdisp);

if (isset($_GET['totalRows_rsdisp'])) {
  $totalRows_rsdisp = $_GET['totalRows_rsdisp'];
} else {
  $all_rsdisp = mysql_query($query_rsdisp);
  $totalRows_rsdisp = mysql_num_rows($all_rsdisp);
}
$totalPages_rsdisp = ceil($totalRows_rsdisp/$maxRows_rsdisp)-1;

$colname_rsname = "-1";

if (isset($_GET['stdid'])) {
  $colname_rsname = $_GET['stdid'];
}
mysql_select_db($database_tams, $tams);
$query_rsname = sprintf("SELECT fname, lname, mname FROM student WHERE stdid = %s", GetSQLValueString($colname_rsname, "text"));
$rsname = mysql_query($query_rsname, $tams) or die(mysql_error());
$row_rsname = mysql_fetch_assoc($rsname);
$totalRows_rsname = mysql_num_rows($rsname);

$colname_sesnam = "-1";
if (isset($row_rsdisp['sesid'])) {
  $colname_sesnam =  $row_rsdisp['sesid']; 
}
mysql_select_db($database_tams, $tams);
$query_sesnam = sprintf("SELECT sesname FROM `session` WHERE sesid = %s", GetSQLValueString($colname_sesnam, "int"));
$sesnam = mysql_query($query_sesnam, $tams) or die(mysql_error());
$row_sesnam = mysql_fetch_assoc($sesnam);
$totalRows_sesnam = mysql_num_rows($sesnam);

$queryString_rsdisp = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_rsdisp") == false && 
        stristr($param, "totalRows_rsdisp") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_rsdisp = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_rsdisp = sprintf("&totalRows_rsdisp=%d%s", $totalRows_rsdisp, $queryString_rsdisp);



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
<script src="../../scripts/jquery.js" type="text/javascript"></script>
<script src="../../SpryAssets/SpryCollapsiblePanel.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript">
function goto(){
    window.location ="/tams/admin/disciplinary/index.php?stdid="+ document.form1.matric.value;

}
</script>
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Disciplinary Actions <!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
      <tr>
        <td><div id="CollapsiblePanel1" class="CollapsiblePanel">
          <div class="CollapsiblePanelTab" tabindex="0">Add New Disciplinary Action </div>
          <div class="CollapsiblePanelContent">
            <form id="form1" name="form1" method="POST" action="<?php echo $editFormAction; ?>">
              <table width="471" border="0" align="center">
                <tr>
                  <td colspan="3"><?php echo $row_rsname['fname']; ?> <?php echo $row_rsname['lname']; ?> <?php echo $row_rsname['mname']; ?></td>
                  </tr>
                <tr>
                  <td width="90" align="left" valign="top">Matric:</td>
                  <td width="339"><label>
                    <input name="matric" type="text" id="matric"  value="<?php echo (isset($_GET['stdid']))?$_GET['stdid'] : ""?>" maxlength="11"/> 
                    <input type="button" name="check"  value="check" onclick="goto()" />
                  </label></td>
                  <td width="28">&nbsp;</td>
                </tr>
                <tr>
                  <td align="left" valign="top">Action:</td>
                  <td><label>
                    <select name="action" id="action">
                      <option value="-1">--Choose--</option>
                      <option value="Withdrawn">Withdrawn</option>
                      <option value="Suspended">Suspended</option>
                    </select>
                  </label></td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td align="left" valign="top">Comment:</td>
                  <td><textarea name="comment" id="comment" cols="45" rows="5"></textarea></td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td><input type="submit" name="Submit" id="Submit" value="Submit"<?php if($row_rsname['fname']==NULL){?> disabled="disabled"/><?php }?></td>
                  <td>&nbsp;</td>
                </tr>
              </table>
              <input type="hidden" name="login" value="<?php echo date('F d,Y');?>" />
              <input type="hidden" name="seesion" value="<?php echo $row_rsses['sesid']; ?>" />
              <input type="hidden" name="MM_insert" value="form1" />
            </form>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
          </div>
        </div></td>
      </tr>
    </table>
    <p>
      <script type="text/javascript">
<!--
var CollapsiblePanel1 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel1");
//-->
      </script>
    </p>
    <table width="542" border="0" align="center">
      <tr align="center">
        <td colspan="7"><strong>Students on Disciplinary Actions </strong></td>
      </tr>
      <tr>
        <th width="92" height="33">Matric </th>
        <th width="111">Session</th>
        <th width="101">Status</th>
        <th width="110"> Date Cleared</th>
        <td width="79" colspan="3"><strong>Clearance</strong></td>
      </tr>
      <?php 
        if($totalRows_rsdisp > 0) { 
            do { 
      ?>
        <tr>
          <td><a href="../../student/profile.php?stid=<?php echo $row_rsdisp['stdid']; ?>"><?php echo $row_rsdisp['stdid']; ?></a></td>
          <td><?php echo $row_sesnam['sesname']; ?></td>
          <td><?php echo $row_rsdisp['status']; ?></td>
          <td>&nbsp;</td>
          <form name="form2" method="post">
          <td colspan="3">
              <input type="submit" name="cleared" id="button" value="Cleared" disabled/>  
              <input type="hidden" name="stdiddisc" value="<?php echo $row_rsdisp['stdid'];?>" disabled/>
              <input type="checkbox" class="cleared" name="checkbox" id="checkbox" />
          </td>
           </form>   
        </tr>
        <?php } while ($row_rsdisp = mysql_fetch_assoc($rsdisp)); }?>
<tr>
  <td>&nbsp;</td>
          <td align="left"><a href="<?php printf("%s?pageNum_rsdisp=%d%s", $currentPage, max(0, $pageNum_rsdisp - 1), $queryString_rsdisp); ?>">&lt;&lt;Previous</a></td>
          <td align="right"><a href="<?php printf("%s?pageNum_rsdisp=%d%s", $currentPage, min($totalPages_rsdisp, $pageNum_rsdisp + 1), $queryString_rsdisp); ?>">Next&gt;&gt;</a></td>
          <td>&nbsp;</td>
          <td colspan="3">&nbsp;</td>
        </tr>
        
    </table>
    <p></p>
  <!-- InstanceEndEditable --></div>
<div class="footer">
    <p><!-- end .footer -->   
    
    <?php require '../../include/footer.php'; ?>
	
   </p>
  </div>
  <!-- end .container -->
</div>
</body>
    <script type="text/javascript">
        $(function() {
            $('.cleared').change(function() {
                if($(this).is(':checked')) {
                    $(this).siblings().attr('disabled', false);
                    return;
                }
                $(this).siblings().attr('disabled', true);
            });
        });
    </script>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($rsses);

mysql_free_result($rsdisp);

mysql_free_result($rsname);

mysql_free_result($sesnam);


?>
