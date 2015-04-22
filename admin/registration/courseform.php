<?php require_once('../Connections/tams.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
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
$query_sess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,1";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

$colname_stud = "-1";
if (isset($_SESSION['stid'])) {
  $colname_stud = $_SESSION['stid'];
}

if (isset($_GET['stid'])) {
  $colname_stud = $_GET['stid'];
}

mysql_select_db($database_tams, $tams);
$query_stud = sprintf("SELECT s.stdid, s.fname, s.lname, s.level, s.progid, p.progname, d.deptname FROM student s, programme p, department d WHERE s.progid = p.progid AND p.deptid = d.deptid AND stdid = %s", GetSQLValueString($colname_stud, "text"));
$stud = mysql_query($query_stud, $tams) or die(mysql_error());
$row_stud = mysql_fetch_assoc($stud);
$totalRows_stud = mysql_num_rows($stud);

$colname_ref = "-1";
if (isset($_GET['stid'])) {
  $colname_ref = $_GET['stid'];
}

$colname_regStatus = "-1";
if (isset($colname_stud)) {
  $colname_regStatus = $colname_stud;
}
$colname_regStatus1 = "-1";
if (isset($row_sess['sesid'])) {
  $colname_regStatus1 = $row_sess['sesid'];
}

mysql_select_db($database_tams, $tams);
$query_regStatus = sprintf("SELECT * FROM registration WHERE stdid = %s AND sesid = %s", GetSQLValueString($colname_regStatus, "text"), GetSQLValueString($colname_regStatus1, "int"));
$regStatus = mysql_query($query_regStatus, $tams) or die(mysql_error());
$row_regStatus = mysql_fetch_assoc($regStatus);
$totalRows_regStatus = mysql_num_rows($regStatus);

mysql_select_db($database_tams, $tams);
$query_course = sprintf("SELECT r.csid, c.semester, c.csname, d.status, d.unit FROM result r, course c, department_course d WHERE r.stdid = %s AND c.csid = r.csid AND d.csid = r.csid AND r.sesid = %s ORDER BY c.semester ASC", GetSQLValueString($colname_regStatus, "text"),GetSQLValueString($colname_regStatus1, "int"));
$course = mysql_query($query_course, $tams) or die(mysql_error());
$row_course = mysql_fetch_assoc($course);
$totalRows_course = mysql_num_rows($course);

$sesReg = false;
$row_regStatus['status'];
if($row_regStatus['status'] == "Registered" )
	$sesReg = true;

$crsReg = false;
if($row_regStatus['course'] == "Registered" )
	$crsReg = true;
	
require_once('../param/param.php');
require_once('../functions/function.php');


$sub = false;
$path = pathinfo( $_SERVER['SCRIPT_FILENAME']);

if( $path['dirname'] != $_SERVER['DOCUMENT_ROOT'].$site_root )
	$sub = true;
	

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );  
}
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>TAMS - Course Form</title>
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/tams.js"></script>
<style>

td{
	border:none;
	margin:0;
	padding:5px 0px;
}

.header th{
	background:#CCCCCC;
	}
body {
	font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
	font-size:12px;
}
</style>
</head>

<body>

<table border="0" align="center">
              <tr>
                <td></td>
                <td align="right"><a href="javascript:print();">Print</a></td>
              </tr>
               <tr>
                <td height="151" colspan="2"><table width="701" border="0">
                  <tr>
                    <td width="168" rowspan="2"><img src="../images/logo.jpg" alt="tasuedlogo" width="131" height="116"></td>
                    <td width="523" height="69">
                        <p style="font-size:28px;text-align:center;">TAI SOLARIN UNIVERSITY OF EDUCATION</p>
                        <p style="font-size:12px;text-align:center;line-height:0.1px">PMB 2118, Ijebu Ode, Ogun State, Nigeria</p>
                    </td>
                    </tr>
                  <tr>
                    <td height="57"><p style="font-size:20px;text-align:center;line-height:0.1px">Course Registration Form (<?php echo $row_sess['sesname'];?>)</p></td>
                    </tr>
                 </table></td>
              </tr>
              <tr><td colspan="2"><table width="712" border="0">
              <tr>
                <td width="312">Name: <?php echo  $row_stud['lname']." ". $row_stud['fname']?></td>
                <td width="379">Level: <?php echo $row_stud['level']?></td>
              </tr>
              <tr>
                <td>Matric. No.: <?php echo $row_stud['stdid'];?></td>
                <td>Department: <?php echo $row_stud['deptname']?></td>
              </tr>
            </table>
            </td></tr>
              <tr>
                <td colspan="2"><table width="700" border="0">
                  <tr class="header">
                    <th width="170">COURSE CODE</th>
                    <th width="430">COURSE NAME</th>
                    <th width="20">STATUS</th>
                    <th width="20">UNIT</th>
                    <th width="50">SEMESTER</th>
                  </tr>
                  <?php 
                        $tunits = 0;
                      do { 
                  ?>
                    <tr class="<?php echo $cname;?>">
                      <td><div align="center"><?php echo $row_course['csid']; ?></div></td>
                      <td><?php echo $row_course['csname']; ?></td>
                      <td><div align="center"><?php echo $row_course['status']; ?></div></td>
                      <td><div align="center"><?php echo $row_course['unit'];$tunits += $row_course['unit'];?></div></td>
                      <td><div align="center"><?php echo (strtolower($row_course['semester']) == "f")? "First": "Second" ;?></div></td>
                    </tr>
                    <?php } while ($row_course = mysql_fetch_assoc($course)); ?>
                    <tr>
                        <td colspan="3" align="right" >Total Units</td>
                        <td align="center"><?php echo $tunits;?></td>
                        <td></td>
                    </tr>
                </table></td>
              </tr>
              <tr>
                <td width="339"><p align="center">______________________________</p>
                <p align="center">Course Adviser</p></td>
                <td width="375"><p align="center">_______________________________</p>
                <p align="center">HOD</p></td>
                
              </tr>
              <tr>
                <td colspan="2"><p align="center">______________________________</p>
                <p align="center">Dean</p></td>
              </tr>
              <tr>
                <td colspan="2">&nbsp;</td>
              </tr>
            </table>
            </body></html>