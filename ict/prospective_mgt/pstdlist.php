<?php require_once('../../Connections/tams.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}

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

$query_pstd = '';

$pid = '';
$view= '';


if(isset($_GET['pid']) && isset($_GET['view']) ) {
    $pid = $_GET['pid'];
    $view = $_GET['view'];
}

$query_proginfo = sprintf("SELECT * FROM programme WHERE progid = %s", GetSQLValueString($pid, "int"));
$proginfo = mysql_query($query_proginfo, $tams) or die(mysql_error());
$row_proginfo = mysql_fetch_assoc($proginfo);
$totalRows_proginfo = mysql_num_rows($proginfo);

$name = $row_proginfo['progname'].' ';

switch($view) {
    case 'first':
        $query_pstd = sprintf("SELECT formnum, jambregid, fname, lname, mname, admtype, "
                . "jambscore1, jambscore2, jambscore3, jambscore4, score "
                . "FROM prospective "
                . "WHERE progid1 = %s", GetSQLValueString($pid, "int"));
        $name .= '(First Choice Applicants)';
        break;
    
    case 'app_fee':
        $query_pstd = sprintf("SELECT formnum, jambregid, fname, lname, mname, admtype, "
                . "jambscore1, jambscore2, jambscore3, jambscore4, score "
                . "FROM prospective p "
                . "JOIN schfee_transactions s ON p.jambregid = s.can_no "
                . "WHERE s.status = 'APPROVED' AND progid1 = %s", 
                GetSQLValueString($pid, "int"));
        $name .= '(Paid Application Fee)';
        break;
    
    case 'admitted':
        $query_pstd = sprintf("SELECT formnum, jambregid, fname, lname, mname, admtype, "
                . "jambscore1, jambscore2, jambscore3, jambscore4, score "
                . "FROM prospective "
                . "WHERE progofferd = %s", GetSQLValueString($pid, "int"));
        $name .= '(Admitted Applicants)';
        break;
    
    case 'accept_fee':
        $query_pstd = sprintf("SELECT formnum, jambregid, fname, lname, mname, admtype, "
                . "jambscore1, jambscore2, jambscore3, jambscore4, score "
                . "FROM prospective p "
                . "JOIN accfee_transactions s ON p.jambregid = s.can_no "
                . "WHERE s.status = 'APPROVED' AND progofferd = %s", 
                GetSQLValueString($pid, "int"));
        $name .= '(Paid Acceptance Fees)';
        break;
    
    case 'school_fee':
     $query_pstd = sprintf("SELECT formnum, jambregid, fname, lname, mname, admtype, "
                . "jambscore1, jambscore2, jambscore3, jambscore4, score "
                . "FROM prospective p "
                . "JOIN schfee_transactions s ON p.jambregid = s.can_no "
                . "WHERE s.status = 'APPROVED' AND progofferd = %s", 
                GetSQLValueString($pid, "int"));
        $name .= '(Paid School Fees)';
        break;
    
    default :
        $query_pstd = sprintf("SELECT formnum, jambregid, fname, lname, mname, admtype, "
                . "jambscore1, jambscore2, jambscore3, jambscore4, score "
                . "FROM prospective "
                . "WHERE progid1 = %s", GetSQLValueString($pid, "int"));
    
}

$pstd = mysql_query($query_pstd, $tams) or die(mysql_error());
$row_pstd = mysql_fetch_assoc($pstd);
$totalRows_pstd = mysql_num_rows($pstd);

$query_regcount = sprintf("SELECT admtype, regtype, count(pstdid) as count "
                    . "FROM prospective p "
                    . "WHERE progid1 = %s "
                    . "GROUP BY regtype, admtype ", GetSQLValueString($pid, "int"));
$regcount = mysql_query($query_regcount, $tams) or die(mysql_error());
$row_regcount = mysql_fetch_assoc($regcount);
$totalRows_regcount = mysql_num_rows($regcount);

$stud_count = array('DE' => array('regular' => 0, 'coi' => 0), 'UTME' => array('regular' => 0, 'coi' => 0));
do{
    $stud_count[$row_regcount['admtype']][$row_regcount['regtype']] = $row_regcount['count'];
}while($row_regcount = mysql_fetch_assoc($regcount));

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
<?php include '../include/topmenu.php'; ?>
  </div>
  <!-- end .topmenu --> 
  
  <div class="loginuser">
  <?php include '../include/loginuser.php'; ?>
  
  <!-- end .loginuser --></div>
  <div class="pagetitle">
    <table width="600">
      <tr>
        <td><!-- InstanceBeginEditable name="pagetitle" --><?php echo $name?> <!-- InstanceEndEditable --></td>
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
    <table width="690">
        <tr>
            <td>
                <table class="table table-bordered table-condensed table-striped">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Jamb Reg. No.</th>
                            <th>Form Number</th>
                            <th>Name</th>
                            <th>Admission Type</th>
                            <th>JAMB Score</th>
                            <th>PUTME Score</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody style="font-weight: normal">
                        <?php 
                            if($totalRows_pstd > 0) {
                                for($idx = 0; $idx < $totalRows_pstd; $idx++, $row_pstd = mysql_fetch_assoc($pstd)) {
                        ?>
                        <tr>
                            <td><?php echo $idx + 1?></td>
                            <td align="center"><?php echo $row_pstd['jambregid']?></td>
                            <td align="center"><?php echo $row_pstd['formnum']?></td>
                            <td><?php echo "{$row_pstd['lname']} {$row_pstd['fname']}"?></td>
                            <td align="center"><?php echo $row_pstd['admtype']?></td>
                            <td align="center">
                                <?php 
                                    $score = 0;
                                    $score += (isset($row_pstd['jambscore1'])&& $row_pstd['jambscore1'] != '')? 
                                            $row_pstd['jambscore1']: 0;
                                    $score += (isset($row_pstd['jambscore2'])&& $row_pstd['jambscore2'] != '')? 
                                            $row_pstd['jambscore2']: 0;
                                    $score += (isset($row_pstd['jambscore3'])&& $row_pstd['jambscore3'] != '')? 
                                            $row_pstd['jambscore3']: 0;
                                    $score += (isset($row_pstd['jambscore4'])&& $row_pstd['jambscore4'] != '')? 
                                            $row_pstd['jambscore4']: 0;
                                    
                                    echo $score;
                                ?>
                            </td>
                            <td align="center"><?php echo (isset($row_pstd['score']))? $row_pstd['score']: '-' ?></td>
                            <td>
                                <a target="_blank" href="../../prospective/viewform.php?stid=<?php echo $row_pstd['jambregid']?>">View Profile</a>
                            </td>
                        </tr>
                        <?php }}else {?>
                        <tr>
                            <td colspan="8">There are no applicants to display!</td>
                        </tr>
                        <?php }?>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
   
  <!-- InstanceEndEditable --></div>
<div class="footer">
    <p><!-- end .footer -->   
    
    <?php require '../../include/footer.php'; ?>
	
   </p>
  </div>
  <!-- end .container -->
</div>
</body>
<!-- InstanceEnd -->
</html>

