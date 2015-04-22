<?php require_once('../../Connections/tams.php'); ?>
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

mysql_select_db($database_tams, $tams);


$query_pstd = '';

$pid = '';
$view= '';
$name = '';

if(isset($_GET['pid']) && isset($_GET['view']) ) {
    $pid = $_GET['pid'];
    $view = $_GET['view'];
}

switch($view) {
    case 'first':
        $query_pstd = sprintf("SELECT formnum, jambregid, fname, lname, mname, admtype, score "
                . "FROM prospective "
                . "WHERE progid1 = %s", GetSQLValueString($pid, "int"));
        $name = '(First Choice Applicants)';
        break;
    
    case 'second':
        $query_pstd = sprintf("SELECT formnum, jambregid, fname, lname, mname, admtype, score "
                . "FROM prospective "
                . "WHERE progid2 = %s", GetSQLValueString($pid, "int"));
        $name = '(Second Choice Applicants)';
        break;
    
    case 'admitted':
        $query_pstd = sprintf("SELECT formnum, jambregid, fname, lname, mname, admtype, score "
                . "FROM prospective "
                . "WHERE progofferd = %s", GetSQLValueString($pid, "int"));
        $name = '(Admitted Applicants)';
        break;
    
    case 'accept_fee':
    $query_pstd = sprintf("SELECT formnum, jambregid, fname, lname, mname, admtype, score "
                . "FROM prospective "
                . "WHERE acceptance = %s", GetSQLValueString("Yes", "text"));
        $name = '(Paid Acceptance Fees)';
        break;
    
    case 'school_fee':
        $name = '(Paid School Fees)';
        break;
    
    default :
        $query_pstd = sprintf("SELECT formnum, jambregid, fname, lname, mname, admtype, score "
                . "FROM prospective "
                . "WHERE progid1 = %s", GetSQLValueString($pid, "int"));
    
}

$pstd = mysql_query($query_pstd, $tams) or die(mysql_error());
$row_pstd = mysql_fetch_assoc($pstd);
$totalRows_pstd = mysql_num_rows($pstd);


if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );  
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
<?php include '../../include/topmenu.php'; ?>
  </div>
  <!-- end .topmenu --> 
  
  <div class="loginuser">
  <?php include '../../include/loginuser.php'; ?>
  
  <!-- end .loginuser --></div>
  <div class="pagetitle">
    <table width="600">
      <tr>
        <td><!-- InstanceBeginEditable name="pagetitle" -->Prospective Students <?php echo $name?> <!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
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
                            <th>UTME Score</th>
                            <th>Exam Score</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            if($totalRows_pstd > 0) {
                                for($idx = 0; $idx < $totalRows_pstd; $idx++, $row_pstd = mysql_fetch_assoc($pstd)) {
                        ?>
                        <tr>
                            <td><?php echo $idx + 1?></td>
                            <td align="center"><?php echo $row_pstd['jambregid']?></td>
                            <td align="center"><?php echo $row_pstd['formnum']?></td>
                            <td><?php echo "{$row_pstd['lname']} {$row_pstd['lname']}"?></td>
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
                                <a target="_blank" href="../viewform.php?stid=<?php echo $row_pstd['jambregid']?>">View Profile</a>
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

