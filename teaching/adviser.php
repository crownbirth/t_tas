<?php require_once('../Connections/tams.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "3";
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
?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
require_once('../param/param.php');
require_once('../functions/function.php');


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
$query_rssess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,6";
$rssess = mysql_query($query_rssess, $tams) or die(mysql_error());
$row_rssess = mysql_fetch_assoc($rssess);
$totalRows_rssess = mysql_num_rows($rssess);

$msg = '';
if(isset($_POST['assign'])) {
    
    foreach ($_POST as $key => $value) { 
        if($key == 'assign' || $value == '-1')
            continue;            
        
        $query_rsChk = sprintf("SELECT * "
                                . "FROM staff_adviser "
                                . "WHERE level=%s "
                                . "AND sesid=%s "
                                . "AND deptid=%s",
                                GetSQLValueString($key, "int"),
                                GetSQLValueString($row_rssess['sesid'], "int"),
                                GetSQLValueString(getSessionValue('did'), "int"));
        $rsChk = mysql_query($query_rsChk, $tams) or die(mysql_error());
        $row_rsChk = mysql_fetch_assoc($rsChk);
        $totalRows_rsChk = mysql_num_rows($rsChk);
        
        $Result1 = '';
        if($totalRows_rsChk > 0) {
            $updSQL = sprintf("UPDATE lecturer SET access=5 "
                                . "WHERE lectid=%s",
                                GetSQLValueString($row_rsChk['lectid'], "text"));
            mysql_query($updSQL, $tams) or die(mysql_error());

            $updateSQL = sprintf("UPDATE staff_adviser "
                                . "SET lectid=%s "
                                . "WHERE level=%s "
                                . "AND sesid=%s "
                                . "AND deptid=%s",
                           GetSQLValueString($value, "text"),
                           GetSQLValueString($key, "int"),
                           GetSQLValueString($row_rssess['sesid'], "int"),
                           GetSQLValueString(getSessionValue('did'), "int"));

            $Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());
            $update_info = mysql_info($tams);
        }

        if(!$Result1){
          $insertSQL = sprintf("INSERT INTO staff_adviser (lectid, sesid, level, deptid) VALUES (%s, %s, %s, %s)",
                               GetSQLValueString($value, "text"),
                               GetSQLValueString($row_rssess['sesid'], "int"),
                               GetSQLValueString($key, "text"),
                               GetSQLValueString(getSessionValue('did'), "text"));

          mysql_select_db($database_tams, $tams);
          $Result2 = mysql_query($insertSQL, $tams) or die(mysql_error()); 
        }
        
        $updateLct = sprintf("UPDATE lecturer SET access=6 "
                            . "WHERE lectid=%s",
                       GetSQLValueString($value, "text"));

        $Result1 = mysql_query($updateLct, $tams) or die(mysql_error());
        $msg = 'Staff advisers updated successfully';
    }
}

$query_lect = sprintf("SELECT lectid, fname, lname FROM lecturer WHERE (access = 5 OR access = 6) AND deptid = %s", GetSQLValueString(getSessionValue('did'), "int"));
$lect = mysql_query($query_lect, $tams) or die(mysql_error());
$row_lect = mysql_fetch_assoc($lect);
$totalRows_lect = mysql_num_rows($lect);


$sesid = $row_rssess['sesid'];
if(isset($_GET['sid'])) {
    $sesid = $_GET['sid'];
}
$query_adv = sprintf("SELECT * FROM staff_adviser WHERE deptid = %s AND sesid = %s", 
                        GetSQLValueString(getSessionValue('did'), "int"), 
                        GetSQLValueString($sesid, "int"));
$adv = mysql_query($query_adv, $tams) or die(mysql_error());
$row_adv = mysql_fetch_assoc($adv);
$totalRows_adv = mysql_num_rows($adv);
 
$advisers = array();
for($i = 0; $i < 6; $i++) {
    $advisers[$i] = NULL;
}

for($i = 0; $i < $totalRows_adv; $i++, $row_adv = mysql_fetch_assoc($adv)) {
    $advisers[$row_adv['level']] = $row_adv['lectid'];
}

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
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/tams.js"></script>
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Assign Staff Adviser<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="679" border="0" class="mytext">
        <?php if(isset($msg)) {?>
        <tr>
            <td><?php echo $msg;?></td>
        </tr>
        <?php }?>
        <form method="post" action="">
            <tr>
                <td colspan="3">
                    <select onchange="sesfilt(this)">
                        <?php do{?>
                        <option value="<?php echo $row_rssess['sesid']?>" <?php if (!(strcmp($row_rssess['sesid'], $sesid))) {echo "selected=\"selected\"";} ?>><?php echo $row_rssess['sesname']?></option>
                        <?php }while($row_rssess = mysql_fetch_assoc($rssess));
                            $rows = mysql_num_rows($rssess);
                            if($rows > 0) {
                                mysql_data_seek($rssess, 0);
                                $row_rssess = mysql_fetch_assoc($rssess);
                            } 
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>100 Level</td>
                <td>
                    <select name="1" disabled>
                        <option value="-1" >Select a lecturer</option>
                        <?php for($i  =0; $i < $totalRows_lect; $i++, $row_lect = mysql_fetch_assoc($lect)) {?>
                        <option value="<?php echo $row_lect['lectid']?>" <?php if($row_lect['lectid'] == $advisers[1]) echo 'selected';?>>
                            <?php echo $row_lect['fname'].' '.$row_lect['lname']?>
                        </option>
                        <?php
                            }
                            $rows = mysql_num_rows($lect);
                            if($rows > 0) {
                                mysql_data_seek($lect, 0);
                                $row_lect = mysql_fetch_assoc($lect);
                            } 
                        ?>
                    </select>
                </td>
                <td><input type="checkbox" class="enable"/></td>
            </tr>
            
            <tr>
                <td>200 Level</td>
                <td>
                    <select name="2" disabled>
                        <option value="-1" >Select a lecturer</option>
                        <?php for($i  =0; $i < $totalRows_lect; $i++, $row_lect = mysql_fetch_assoc($lect)) {?>
                        <option value="<?php echo $row_lect['lectid']?>" <?php if($row_lect['lectid'] == $advisers[2]) echo 'selected';?>>
                            <?php echo $row_lect['fname'].' '.$row_lect['lname']?>
                        </option>
                        <?php
                            }
                            $rows = mysql_num_rows($lect);
                            if($rows > 0) {
                                mysql_data_seek($lect, 0);
                                $row_lect = mysql_fetch_assoc($lect);
                            } 
                        ?>
                    </select>
                </td>               
                <td><input type="checkbox" class="enable"/></td>
            </tr> 
            
            <tr>
                <td>300 Level</td>
                <td>
                    <select name="3" disabled>
                        <option value="-1" >Select a lecturer</option>
                        <?php for($i  =0; $i < $totalRows_lect; $i++, $row_lect = mysql_fetch_assoc($lect)) {?>
                        <option value="<?php echo $row_lect['lectid']?>" <?php if($row_lect['lectid'] == $advisers[3]) echo 'selected';?>>
                            <?php echo $row_lect['fname'].' '.$row_lect['lname']?>
                        </option>
                        <?php
                            }
                            $rows = mysql_num_rows($lect);
                            if($rows > 0) {
                                mysql_data_seek($lect, 0);
                                $row_lect = mysql_fetch_assoc($lect);
                            } 
                        ?>
                    </select>
                </td>
                <td><input type="checkbox" class="enable"/></td>            
            </tr> 
            
            <tr>
                <td>400 Level</td>
                <td>
                    <select name="4" disabled>
                        <option value="-1" >Select a lecturer</option>
                        <?php for($i  =0; $i < $totalRows_lect; $i++, $row_lect = mysql_fetch_assoc($lect)) {?>
                        <option value="<?php echo $row_lect['lectid']?>" <?php if($row_lect['lectid'] == $advisers[4]) echo 'selected';?>>
                            <?php echo $row_lect['fname'].' '.$row_lect['lname']?>
                        </option>
                        <?php
                            }
                            $rows = mysql_num_rows($lect);
                            if($rows > 0) {
                                mysql_data_seek($lect, 0);
                                $row_lect = mysql_fetch_assoc($lect);
                            } 
                        ?>
                    </select>
                </td>
                <td><input type="checkbox" class="enable"/></td>            
            </tr> 
            <?php if($sesid == $row_rssess['sesid']) {?>
            <tr>
                <td colspan="2" align="center">
                    <input type="submit" name="assign" value="Assign"/>
                </td>            
            </tr>
            <?php }?>
        </form>
    </table>
      
  <!-- InstanceEndEditable --></div>
<div class="footer">
    <p><!-- end .footer -->   
    
    <?php require '../include/footer.php'; ?>
	
   </p>
  </div>
  <!-- end .container -->
</div>
    <script type="text/javascript">
        $(function() {
            $('.enable').change(function() {
                if($(this).is(':checked')) {
                    $(this).parent().prev().children('select').attr('disabled', false);
                    return;
                }
                $(this).parent().prev().children('select').attr('disabled', true);
            });
        });
    </script>
</body>
<!-- InstanceEnd --></html>