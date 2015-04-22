<?php 
if (!isset($_SESSION)) {
  session_start();
}

require_once('../../Connections/tams.php');
require_once('../../param/param.php');
require_once('../../functions/function.php');

$MM_authorizedUsers = "20,24";
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

mysql_select_db($database_tams, $tams);
$query_rsprog = "SELECT progid, progname FROM programme WHERE continued = 'Yes' ORDER BY progname ASC";
$rsprog = mysql_query($query_rsprog, $tams) or die(mysql_error());
$row_rsprog = mysql_fetch_assoc($rsprog);
$totalRows_rsprog = mysql_num_rows($rsprog);

$programmes = array();
for($idx = 0; $idx < $totalRows_rsprog ; $idx++, $row_rsprog = mysql_fetch_assoc($rsprog)) {
    $programmes[$row_rsprog['progid']] = 0;
}
mysql_data_seek($rsprog, 0);
$row_rsprog = mysql_fetch_assoc($rsprog);

$first_choice = $app_fee = $admitted = $acceptance = $school_fees = $programmes;

$query_admit = "SELECT progofferd, count(adminstatus) as count "
                    . "FROM prospective "
                    . "GROUP BY progofferd "
                    . "HAVING progofferd IS NOT NULL";
$admit = mysql_query($query_admit, $tams) or die(mysql_error());
$row_admit = mysql_fetch_assoc($admit);
$totalRows_admit = mysql_num_rows($admit);

for($idx = 0; $idx < $totalRows_admit; $idx++, $row_admit = mysql_fetch_assoc($admit)) {
    if(isset($admitted[$row_admit['progofferd']])) {
        $admitted[$row_admit['progofferd']] = $row_admit['count'];
    }
}

$query_first = "SELECT progid1, count(pstdid) as count "
                    . "FROM prospective "
                    . "GROUP BY progid1 "
                    . "HAVING progid1 IS NOT NULL";
$first = mysql_query($query_first, $tams) or die(mysql_error());
$row_first = mysql_fetch_assoc($first);
$totalRows_first = mysql_num_rows($first);

for($idx = 0; $idx < $totalRows_first; $idx++, $row_first = mysql_fetch_assoc($first)) {
    if(isset($first_choice[$row_first['progid1']])) {
        $first_choice[$row_first['progid1']] = $row_first['count'];
    }
}

$query_application = "SELECT progid1, status, count(pstdid) as count "
                    . "FROM prospective p "
                    . "JOIN schfee_transactions s ON p.jambregid = s.can_no "
                    . "WHERE status = 'APPROVED'"
                    . "GROUP BY progid1 ";
$application = mysql_query($query_application, $tams) or die(mysql_error());
$row_application = mysql_fetch_assoc($application);
$totalRows_application = mysql_num_rows($application);

for($idx = 0; $idx < $totalRows_application; $idx++, $row_application = mysql_fetch_assoc($application)) {
    if(isset($app_fee[$row_application['progid1']])) {
        $app_fee[$row_application['progid1']] = $row_application['count'];
    }
}

$query_accept = "SELECT progofferd, status, count(pstdid) as count "
                    . "FROM prospective p "
                    . "JOIN schfee_transactions s ON p.jambregid = s.can_no "
                    . "GROUP BY progofferd "
                    . "HAVING status = 'APPROVED'";
$accept = mysql_query($query_accept, $tams) or die(mysql_error());
$row_accept = mysql_fetch_assoc($accept);
$totalRows_accept = mysql_num_rows($accept);

//for($idx = 0; $idx < $totalRows_accept; $idx++, $row_accept = mysql_fetch_assoc($accept)) {
//    if(isset($acceptance[$row_accept['progofferd']])) {
//        $acceptance[$row_accept['progofferd']] = $row_accept['count'];
//    }
//}

$query_regcount = "SELECT admtype, regtype, count(pstdid) as count "
                    . "FROM prospective p "
                    . "GROUP BY regtype, admtype ";
$regcount = mysql_query($query_regcount, $tams) or die(mysql_error());
$row_regcount = mysql_fetch_assoc($regcount);
$totalRows_regcount = mysql_num_rows($regcount);

    
do{
    $stud_count[$row_regcount['admtype']][$row_regcount['regtype']] = $row_regcount['count'];
}while($row_regcount = mysql_fetch_assoc($regcount));

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")) {
    doLogout($site_root.'/ict');  
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Admission Management <!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
</div> 
<div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table style="width:400px; font-weight: normal;" class="table table-striped table-condensed">
        <tr>
            <td></td>
            <td>UTME</td>
            <td>DE</td>
            <td>TOTAL</td>            
        </tr>
        <tr>
            <td>First</td>
            <td><?php echo $stud_count['UTME']['regular']?></td>
            <td><?php echo $stud_count['DE']['regular']?></td>
            <td><?php echo $stud_count['UTME']['regular'] + $stud_count['DE']['regular']?></td>            
        </tr>
        <tr>
            <td>Change Of Institution</td>
            <td><?php echo $stud_count['UTME']['coi']?></td>
            <td><?php echo $stud_count['DE']['coi']?></td>
            <td><?php echo $stud_count['UTME']['coi'] + $stud_count['DE']['coi']?></td>            
        </tr>
        <tr>
            <td>Total</td>
            <td><?php echo $stud_count['UTME']['regular'] + $stud_count['UTME']['coi']?></td>
            <td><?php echo $stud_count['DE']['regular'] + $stud_count['DE']['coi']?></td>
            <td><?php echo $stud_count['UTME']['coi'] + $stud_count['DE']['coi'] + $stud_count['UTME']['regular'] + $stud_count['DE']['regular']?></td>            
        </tr>
    </table>
    <table width="690" class="table table-striped">
        <thead>
            <tr>
                <th width="250">Programme</th>
                <th style="text-align: center">1st Choice</th>
                <th style="text-align: center">Application Fee</th>
                <th>Admitted</th>
                <th style="text-align: center">Acceptance Fee</th>
                <th style="text-align: center">School Fees</th>
            </tr>
        </thead>        
        <tfoot>       
            <tr>
                <th colspan="6">Total</th>
            </tr>
            <tr>
                <th><?php echo $totalRows_rsprog?></th>
                <th><?php echo array_sum($first_choice)?></th>
                <th><?php echo array_sum($app_fee)?></th>
                <th><?php echo array_sum($admitted)?></th>
                <th><?php echo array_sum($acceptance)?></th>
                <th><?php echo array_sum($school_fees)?></th>
            </tr>
        </tfoot>
        <tbody style="font-weight: normal">
        <?php for($idx = 0; $idx < $totalRows_rsprog; $idx++, $row_rsprog = mysql_fetch_assoc($rsprog)) { ?>
            <tr>
                <td><?php echo $row_rsprog['progname']?></td>
                <td align="center">
                     <a target="_blank" href="pstdlist.php?pid=<?php echo $row_rsprog['progid']?>&view=first">
                        <?php echo $first_choice[$row_rsprog['progid']]?>
                    </a>
                </td>
                <td align="center">
                     <a target="_blank" href="pstdlist.php?pid=<?php echo $row_rsprog['progid']?>&view=app_fee">
                        <?php echo $app_fee[$row_rsprog['progid']]?>
                    </a>
                </td>
                <td align="center">
                     <a target="_blank" href="pstdlist.php?pid=<?php echo $row_rsprog['progid']?>&view=admitted">
                        <?php echo $admitted[$row_rsprog['progid']]?>
                    </a>
                </td>
                <td align="center">
                     <a target="_blank" href="pstdlist.php?pid=<?php echo $row_rsprog['progid']?>&view=accept_fee">
                        <?php echo $acceptance[$row_rsprog['progid']]?>
                    </a>
                </td>
                <td align="center">
                     <a target="_blank" href="pstdlist.php?pid=<?php echo $row_rsprog['progid']?>&view=school_fee">
                        <?php echo $school_fees[$row_rsprog['progid']]?>
                    </a>
                </td>
            </tr>
        <?php }?>            
        </tbody>
    </table>
  <!-- InstanceEndEditable -->
</div>
<div class="footer">
    <p><!-- end .footer -->   
    
    <?php require '../include/footer.php'; ?>
	
   </p>
  </div>
  <!-- end .container -->
</div>
</body>
<!-- InstanceEnd --></html>