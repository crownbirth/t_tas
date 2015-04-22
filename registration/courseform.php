<?php require_once('../Connections/tams.php'); ?>
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

$filter = "";
$colname_sess = "-1";
if (isset($_GET['sid'])) {
  $colname_sess = $_GET['sid'];
  $filter = "WHERE sesid=".$colname_sess;
}

mysql_select_db($database_tams, $tams);
$query_sess = sprintf("SELECT * FROM `session` %s ORDER BY sesname DESC LIMIT 0,1", 
					GetSQLValueString($filter, "undefined", $filter));
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

$colname_stud = "-1";
if ( getSessionValue('stid') != NULL ) {
  $colname_stud = getSessionValue('stid');
}

if (isset($_GET['stid'])) {
  $colname_stud = $_GET['stid'];
}

mysql_select_db($database_tams, $tams);
$query_stud = sprintf("SELECT s.stdid, s.fname, s.lname, s.level, s.progid, p.progname, d.deptname FROM student s, programme p, department d WHERE s.progid = p.progid AND p.deptid = d.deptid AND stdid = %s", GetSQLValueString($colname_stud, "text"));
$stud = mysql_query($query_stud, $tams) or die(mysql_error());
$row_stud = mysql_fetch_assoc($stud);
$totalRows_stud = mysql_num_rows($stud);

$colname_regStatus = "-1";
if (isset($colname_stud)) {
  $colname_regStatus = $colname_stud;
}

$colname_regStatus1 = "-1";
if (isset($row_sess['sesid'])) {
  $colname_regStatus1 = $row_sess['sesid'];
}

mysql_select_db($database_tams, $tams);
$query_regStatus = sprintf("SELECT * FROM registration WHERE stdid = %s AND sesid = %s", 
						GetSQLValueString($colname_regStatus, "text"), 
						GetSQLValueString($colname_regStatus1, "int"));
$regStatus = mysql_query($query_regStatus, $tams) or die(mysql_error());
$row_regStatus = mysql_fetch_assoc($regStatus);
$totalRows_regStatus = mysql_num_rows($regStatus);

mysql_select_db($database_tams, $tams);
$query_course = sprintf("SELECT c.semester, r.csid, c.csname, c.status, c.unit "
                        . "FROM result r, course c "
                        . "WHERE r.stdid = %s "
                        . "AND r.cleared = 'TRUE' "
                        . "AND c.csid = r.csid "
                        . "AND r.sesid = %s "
                        . "ORDER BY c.semester ASC", 
                        GetSQLValueString($colname_regStatus, "text"), 
                        GetSQLValueString($colname_regStatus1, "int"));
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
	margin-top: 1px;
	margin-bottom: 1px;
}
.uniname {
	font-size: 30px;
}
.uniname {
	font-size: 33px;
}
.courseform {
	font-size: 16px;
	border-top-width: thin;
	border-right-width: thin;
	border-bottom-width: thin;
	border-left-width: thin;
	border-top-style: solid;
	border-right-style: solid;
	border-bottom-style: solid;
	border-left-style: solid;
}
</style>
</head>

<body topmargin="1">

<table width="850" border="0" align="center">
               <tr>
                <td height="140" colspan="3" valign="top"><table width="850" border="0">
                  <tr>
                    <td width="166" rowspan="2"><img src="../images/logo.jpg" alt="tasuedlogo" width="131" height="116"></td>
                    <td width="674" height="69"><h1 class="uniname">TAI SOLARIN UNIVERSITY OF EDUCATION</h1>
                      <h1  style="font-size:15px;text-align:center;line-height:0.1px">PMB 2118, Ijebu Ode, Ogun State, Nigeria</h1>
                    </td>
                    </tr>
                  <tr>
                    <td height="43"><p style="font-size:20px;text-align:center;line-height:0.1px">Course Registration Form (<?php echo $row_sess['sesname'];?>)</p></td>
                    </tr>
                 </table></td>
              </tr>
              <tr><td colspan="3" align="center"><table width="712" border="0" cellpadding="5" cellspacing="8">
              <tr class="courseform">
                <td width="300" align="left">Name: <?php echo  $row_stud['lname']." ". $row_stud['fname']?></td>
                <td width="272" align="left">Level: <?php echo $row_stud['level']?></td>
                <td width="78" align="right"><a href="javascript:print();">Print Form</a></td>
              </tr>
              <tr class="courseform">
                <td align="left">Matric. No.: <?php echo $row_stud['stdid'];?></td>
                <td colspan="2" align="left">Department: <?php echo $row_stud['deptname']?></td>
              </tr>
            </table>
  </td></tr>
              <tr>
                <td colspan="3" align="center"><table width="850" border="1" cellpadding="3" cellspacing="3">
                  <tr class="header">
                    <th width="96">COURSE CODE</th>
                    <th width="446">COURSE NAME</th>
                    <th width="91">STATUS</th>
                    <th width="47">UNIT</th>
                    <th width="72">SEMESTER</th>
                  </tr>
                  <?php 
                        $tunits = 0;
                      do { 
                  ?>
                    <tr class="<?php //echo $cname;?>">
                      <td><div align="center" class="courseform"><?php echo $row_course['csid']; ?></div></td>
                      <td><div align="left" class="courseform">&nbsp;&nbsp;<?php echo ucwords(strtolower($row_course['csname'])); ?></div></td>
                      <td><div align="center" class="courseform"><?php echo $row_course['status']; ?></div></td>
                      <td><div align="center" class="courseform"><?php echo $row_course['unit'];$tunits += $row_course['unit'];?></div></td>
                      <td><div align="center" class="courseform"><?php echo (strtolower($row_course['semester']) == "f")? "First": "Second" ;?></div></td>
                    </tr>
                    <?php } while ($row_course = mysql_fetch_assoc($course)); ?>
                    <tr>
                        <td colspan="3" align="right" class="courseform" >Total Units &nbsp;&nbsp;&nbsp; </td>
                        <td align="center" class="courseform"><?php echo $tunits;?></td>
                        <td></td>
                    </tr>
                </table></td>
              </tr>
              <tr>
                <td width="255"></td>
                <td width="315">
                  <p align="center">&nbsp;</p>
                  <p align="center">&nbsp;</p>
                  <p align="center">&nbsp;</p>
                  <p align="center">_______________________________</p>
                  <p align="center">Name/Date</p>
                  <p align="center">(File copy)</p>
                </td>
                <td width="270"></td>
                
              </tr>
              <tr>
                <td colspan="3">&nbsp;</td>
              </tr>
            </table>
</body></html>