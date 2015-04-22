<?php 
require_once('../../Connections/tams.php');
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

//mysql_select_db($database_tams, $tams);
//$query_cursess = sprintf("SELECT * FROM `session` WHERE sesid = %s",
// GetSQLValueString($session, "int"));
//$cursess = mysql_query($query_cursess, $tams) or die(mysql_error());
//$row_cursess = mysql_fetch_assoc($cursess);
//$totalRows_cursess = mysql_num_rows($cursess);

$session = (isset($_POST['session']))? $_POST['session']: '';

mysql_select_db($database_tams, $tams);
$query_rssess = sprintf("SELECT * FROM `session` WHERE sesid = %s",
 GetSQLValueString($session, "int"));
$rssess = mysql_query($query_rssess, $tams) or die(mysql_error());
$row_rssess = mysql_fetch_assoc($rssess);
$totalRows_rssess = mysql_num_rows($rssess);

$col = (isset($_POST['col']))? $_POST['col']: '';
$level = (isset($_POST['level']))? $_POST['level']: '';
$semester = (isset($_POST['semester']))? $_POST['semester']: '';
$prog = (isset($_POST['prog']))? $_POST['prog']: '';

$query_info = sprintf("SELECT colname, c.colid, deptname, d.deptid, progname FROM college c, department d, programme p WHERE c.colid = d.colid AND d.deptid = p.deptid AND p.progid=%s",
						 GetSQLValueString($prog, "int"));
$info = mysql_query($query_info, $tams) or die(mysql_error());
$row_info = mysql_fetch_assoc($info);
$totalRows_info = mysql_num_rows($info);

$query_hod = sprintf("SELECT lname, fname FROM lecturer l, programme p, department d WHERE d.deptid = p.deptid AND l.deptid = p.deptid AND access='3' AND p.progid=%s",
                    GetSQLValueString($prog, "int"));
$hod = mysql_query($query_hod, $tams) or die(mysql_error());
$row_hod = mysql_fetch_assoc($hod);
$totalRows_hod = mysql_num_rows($hod);

$query_dean = sprintf("SELECT lname, fname FROM lecturer l, department d WHERE d.deptid = l.deptid AND access='2' AND d.colid=%s",
                    GetSQLValueString($row_info['colid'], "int"));
$dean = mysql_query($query_dean, $tams) or die(mysql_error());
$row_dean = mysql_fetch_assoc($dean);
$totalRows_dean = mysql_num_rows($dean);

$query_vc = sprintf("SELECT lname, fname FROM lecturer l, appointment a WHERE l.lectid = a.lectid AND postid=1",
                    GetSQLValueString($row_info['colid'], "int"));
$vc = mysql_query($query_vc, $tams) or die(mysql_error());
$row_vc = mysql_fetch_assoc($vc);
$totalRows_vc = mysql_num_rows($vc);

$query_list = sprintf("SELECT * FROM student s, registration r WHERE s.stdid = r.stdid AND progid=%s AND r.sesid = %s AND r.level=%s",
						 GetSQLValueString($prog, "int"),
                                                 GetSQLValueString($session, "int"),
						 GetSQLValueString($level, "int"));
$list = mysql_query($query_list, $tams) or die(mysql_error());
$row_list = mysql_fetch_assoc($list);
$totalRows_list = mysql_num_rows($list);

$passlist = array();
$faillist = array();
$commendation = array();
$probation = array();
$counselling = array();
$examined = 0;
$suspension = array();

for($i = 0; $i < $totalRows_list; $i++) {
	$studid = $row_list['stdid'];
	$stud['matric'] = $studid;
	$stud['name'] = $row_list['fname'].' '.$row_list['lname'];
	$cgpa = getCgpa($studid, $prog, $session, $semester, $tams, $examined);
	$stud['prev'] = $cgpa['prev'];
	$stud['cur'] = $cgpa['cur'];
	$stud['cum'] = $cgpa['cum'];
        $ref = getRef($studid,$semester, $tams);
	$stud['ref'] = $ref['refs'];
        $disc = getDisc($studid, $session , $tams);
        if($disc != '') {
            $stud['disc'] = $disc;
            $suspension[] = $stud;
        }
        
        if($stud['cum'] == 0 || (isset($stud['ref']) && $stud['ref'] != '')) {
            $faillist[] = $stud;
            if($stud['cum'] <= 1.0 && $semester == 'S') {
                $probation[] = $stud;
            }else {
                $counselling[] = $stud;
            }
        }else {
            $passlist[] = $stud;
            if($stud['cum'] >= 4.5) {
                $commendation[] = $stud;
            }            
        }
	
	$row_list = mysql_fetch_assoc($list);
}

function getDisc($studid, $sesid, $tams) {
    $query_disc = sprintf("SELECT status FROM disciplinary d WHERE stdid=%s and sesid=%s",
                    GetSQLValueString($studid, "int"),
                    GetSQLValueString($sesid, "int"));
    $disc = mysql_query($query_disc, $tams) or die(mysql_error());
    $row_disc = mysql_fetch_assoc($disc);
    $totalRows_disc = mysql_num_rows($disc);
    
    if($totalRows_disc > 0) {
        return $row_disc['status'];
    }
    
    return '';
}

function gradepoint($unit, $score){
	
	$gp;
	if($score < 40)
		$gp = 0	;
	else if ($score < 45)	
		$gp = 1;
	else if ($score < 50)	
		$gp = 2;
	else if ($score < 60)	
		$gp = 3;
	else if ($score < 70)	
		$gp = 4;
	else if ($score > 69)	
		$gp = 5;
		
	return $gp*$unit;
	}


function getCgpa($studid, $progid, $ses, $sem, $tams, &$examined) {
	$prev = '';
	$cur = '';
	$cum = '';
	
	$query_cgpa = sprintf("SELECT r.csid, c.semester, r.tscore+ r.escore as score, r.sesid, sesname, dc.status, unit "
                . "FROM `result` r, course c, `session` s, department_course dc, teaching t "
                . "WHERE stdid = %s "
                . "AND c.csid = r.csid "
                . "AND c.csid = dc.csid "
                . "AND dc.csid = r.csid "
                . "AND r.csid = t.csid "
                . "AND s.sesid = t.sesid "
                . "AND t.approve = 'yes' "
                . "AND s.sesid = r.sesid "
                . "AND dc.progid=%s "
                . "AND r.sesid<=%s "
                . "AND c.semester=%s", 
					GetSQLValueString($studid, "text"), 
					GetSQLValueString($progid, "int"), 
					GetSQLValueString($ses, "int"), 
					GetSQLValueString(strtoupper($sem), "text"));
					
	$cgpa = mysql_query($query_cgpa, $tams) or die(mysql_error());
	$row_cgpa = mysql_fetch_assoc($cgpa);
	$totalRows_cgpa = mysql_num_rows($cgpa);

        if($totalRows_cgpa > 0) {
            $examined++;
        }
        
	$curValue = 0;
	$prevValue = 0;
	$curUnit = 0;
	$prevUnit= 0;
	for($i = 0; $i < $totalRows_cgpa; $i++) {
		if($row_cgpa['sesid'] == $ses) {
			$curValue += gradepoint($row_cgpa['unit'], $row_cgpa['score']);
			$curUnit +=  $row_cgpa['unit'];
		}else {
			$prevValue += gradepoint($row_cgpa['unit'], $row_cgpa['score']);
			$prevUnit +=  $row_cgpa['unit'];
		}		
		$row_cgpa = mysql_fetch_assoc($cgpa);	
	}
        
	$cumUnit = $prevUnit + $curUnit; 
        $cumValue = $prevValue + $curValue;
	if($curUnit == 0)
		$curUnit == 1;
	if($prevUnit == 0)
		$prevUnit == 1;
        if($cumUnit == 0)
		$cumUnit == 1;
        
	$prev = @number_format(round(($prevValue)/($prevUnit), 2), 2);
	$cur = @number_format(round(($curValue)/($curUnit), 2), 2);
	
	$cum = @number_format(round(($cumValue)/($cumUnit), 2), 2);
	
	return array('prev' => $prev,
                     'cur'  => $cur,
                     'cum'  => $cum);
}

function getRef($studid, $sem, $tams) {
	
	$query_rsrefs = sprintf("SELECT DISTINCT r.csid FROM `result` r, department_course d, student s WHERE d.csid = r.csid AND r.stdid = s.stdid AND d.progid = s.progid AND tscore + escore < 40 AND r.stdid = %s AND r.csid NOT IN ( SELECT csid FROM result WHERE stdid = %s AND tscore + escore > 39)", 
											//GetSQLValueString($sem, "text"),
                                                                                        GetSQLValueString($studid, "text"),
											GetSQLValueString($studid, "text"));
	$rsrefs = mysql_query($query_rsrefs, $tams) or die(mysql_error());
	$row_rsrefs = mysql_fetch_assoc($rsrefs);
	$totalRows_rsrefs = mysql_num_rows($rsrefs);
	$refs = array();
	for($i = 0; $i < $totalRows_rsrefs; $i++) {
		$refs[] = strtoupper($row_rsrefs['csid']);
		$row_rsrefs = mysql_fetch_assoc($rsrefs);	
	}
	
        $result['refs'] = implode(', ', $refs);
        $result['disc'] = $row_rsrefs['disciplinary'];
	return $result;
}

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );  
}

?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>TAMS - Summary Sheet</title>
<script type="text/javascript" src="../../scripts/jquery.js"></script>
<script type="text/javascript" src="../../scripts/tams.js"></script>
<style>

td {
	border:none;
	margin:0;
	padding:5px 0px;
}

.header th {
	background:#CCCCCC;
}

body {
	font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
	font-size:12px;
	margin-top: 1px;
	margin-bottom: 1px;
}

.uniname {
	font-size: 33px;
}


</style>
</head>

<body topmargin="1">

<table width="850" border="0" align="center">
   <tr>
    <td height="140" colspan="3" valign="top"><table width="850" border="0">
      <tr>
        <td width="166" rowspan="2"><img src="../../images/logo.jpg" alt="tasuedlogo" width="131" height="116"></td>
        <td width="674" height="114"><h1 class="uniname">TAI SOLARIN UNIVERSITY OF EDUCATION</h1>
            <h1  style="font-size:24px;text-align:center;line-height:0.1px"><?php echo $row_info['colname']?><br>
            </h1>
          <p  style="font-size:24px;text-align:center;line-height:0.1px">Department of <?php echo $row_info['deptname']?></p></td>
      </tr>
      <tr>
        <td height="27"><p style="font-size:20px;text-align:center;line-height:0.1px"><span style="font-size:15px;text-align:center;line-height:0.1px">PMB 2118, Ijebu Ode, Ogun State, Nigeria</span></p></td>
      </tr>
    </table>
     <hr></td>
  </tr>
  
  <tr>
    <td colspan="3" align="center">
          <p><span style="font-size:20px;text-align:center;line-height:0.1px">          Summary Sheet</span></p>
          <hr>
          <p><br>
            The following <?php echo count($passlist)?> <?php echo $level.'00Level'?> students of <?php echo $degree?> <?php echo $row_info['progname']?> have satisfied the examiners in the Examination held for the <?php if($semester == 'S') echo 'second semester'; else echo 'first semester'?> of  
            <?php  echo $row_rssess['sesname']?> 
      session</p></td>
  </tr>
  
  <tr>
      <td colspan="3">
          <?php if(!empty($passlist)) {?>
          <table width="850" border="0" cellpadding="5" cellspacing="8">
              <tr>
                <th rowspan="2">Matric</th>
                <th rowspan="2">Name</th>
                <th colspan="3"></th>     
            </tr>
              <tr>
                  <th>Prev</th>
                  <th>Cur</th>
                  <th>Cum</th>
              </tr>
              <?php 
                    foreach($passlist as $std) {
              ?>

            <tr>
              <td><a href="../../student/profile.php?stid=<?php echo $std['matric'];?>" target= "_blank "><?php echo $std['matric']?></a></td>   
              <td><?php echo $std['name']?></td>  
              <td><?php echo $std['prev']?></td>
              <td><?php echo $std['cur']?></td>   
              <td><?php echo $std['cum']?></td>
            </tr>

            <?php }?>
          </table>
          <?php }else {?>
          <p>NIL</p>
          <?php }?>
      </td>
  </tr>
  <tr>
    <td colspan="3">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="3"><p>B. Recommended for Commendation</p>
    <p>The following <?php echo count($commendation)?> <?php echo $level.'00Level'?> students of <?php echo $degree;?> <?php echo $row_info['progname']?> are recommended for Commendation for the <?php if($semester == 'S') echo 'second semester'; else echo 'first semester'?> of  
            <?php  echo $row_rssess['sesname']?>  of
      <?php  echo $row_rssess['sesname']?> 
session</p>
    <?php 
        if(!empty($commendation)) {
            foreach($commendation as $comm) {
    ?>
    <p><a href="../../student/profile.php?stid=<?php echo $std['matric'];?>" target= "_blank "><?php echo $comm['matric']?></a> - <?php echo $comm['name']?></p>
    <?php }}else{?>
    <p>NIL</p>
    <?php }?>
    </td>
  </tr>
  <tr>
    <td colspan="3">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="3"><p>C. References </p>
    <p>The following <?php echo count($faillist)?> <?php echo $level.'00Level'?> students of <?php echo $degree?> <?php echo $row_info['progname']?> having failed to satisfy the examiners in the Examination held for the <?php if($semester == 'S') echo 'second semester'; else echo 'first semester'?> of
      <?php  echo $row_rssess['sesname']?> 
session in one or more courses.</p>
    <?php if(!empty($faillist)) {?>
    <table width="850" border="0" cellpadding="5" cellspacing="8">
      <tr>
        <th rowspan="2">Matric</th>
        <th rowspan="2">Name</th>
        <th colspan="3"></th>
        <th rowspan="2">Reference</th>
      </tr>
      <tr>
        <th>Prev</th>
        <th>Cur</th>
        <th>Cum</th>
      </tr>
      <?php 
                    foreach($faillist as $std) {
              ?>
      <tr>
        <td><a href="../../student/profile.php?stid=<?php echo $std['matric'];?>" target= "_blank "><?php echo $std['matric']?></a></td>
        <td><?php echo $std['name']?></td>
        <td><?php echo $std['prev']?></td>
        <td><?php echo $std['cur']?></td>
        <td><?php echo $std['cum']?></td>
        <td><?php echo $std['ref']?></td>
      </tr>
      <?php }?>
    </table>   
    <?php }else{?>
        
    <?php }?>
    <p>NIL</p>
    </td>
  </tr>
  <tr>
    <td colspan="3"><p>D. Recommended for Counseling:</p>
    <p>The following <?php echo count($counselling)?> <?php echo $level.'00Level'?> students of <?php echo $degree?> <?php echo $row_info['progname']?> are recommended for Counselling having got a current GPA of less than 1.00 in the Examination held for the <?php if($semester == 'S') echo 'second semester'; else echo 'first semester'?> of
      <?php  echo $row_rssess['sesname']?> 
session.</p>
     <?php 
        if(!empty($counselling)) {
            foreach($counselling as $coun) {
    ?>
    <p><a href="../../student/profile.php?stid=<?php echo $coun['matric'];?>" target= "_blank "><?php echo $coun['matric']?></a> - <?php echo $coun['name']?></p>
    <?php }}else{?>
    <p>NIL</p>
    <?php }?>
  </tr>
  <tr>
    <td colspan="3">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="3"><p>E. Recommended for University Probation:</p>
    <p>The following <?php echo count($probation)?> <?php echo $level.'00Level'?> students of <?php echo $degree?> <?php echo $row_info['progname']?> are recommended for Probation having got a Cummulative GPA of less than 1.00 in the Examination held for the 
      <?php  echo $row_rssess['sesname']?> 
session.</p>
    <?php 
        if(!empty($probation)) {
            foreach($probation as $prob) {
    ?>
    <p><a href="../../student/profile.php?stid=<?php echo $prob['matric'];?>" target= "_blank "><?php echo $prob['matric']?></a> - <?php echo $prob['name']?></p>
    <?php }}else{?>
    <p>NIL</p>
    <?php }?>
    </td>
  </tr>
  <tr>
    <td colspan="3">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="3"><p>F. Recommended for Withdrawal:</p>
    <p>The following <?php echo 0//echo count($counselling)?> <?php echo $level.'00Level'?> students of <?php echo $degree?> <?php echo $row_info['progname']?> are recommended for Probation having got a Cummulative GPA of less than 1.00 in the Examination held for the
      <?php  echo $row_rssess['sesname']?> 
session.</p>
<!--    <p>List Student Matric and Names or NIL </p>-->
    <p>&nbsp; </p></td>
  </tr>
  <tr>
    <td colspan="3">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="3"><p>G. Suspension of Studentship:</p>
    <p>The following <?php echo count($suspension)?> <?php echo $level.'00Level'?> students of <?php echo $degree?> <?php echo $row_info['progname']?> have been placed on Disciplinary Action (Suspension/Withdrawal) for the
      <?php  echo $row_rssess['sesname']?> 
session.</p>
    <?php 
        if(!empty($suspension)) {
            foreach($suspension as $sus) {
    ?>
    <p><a href="../../student/profile.php?stid=<?php echo $sus['matric'];?>" target= "_blank "><?php echo $sus['matric']?></a> - <?php echo $sus['name']?> - <?php echo $sus['disc']?></p>
    <?php }}else{?>
    <p>NIL</p>
    <?php }?>
    </td>
  </tr>
  <tr>
    <td colspan="3">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="3"><p>H. Summary of Result:</p>
    <p>Number of Students in the Class:<?php echo $totalRows_list?></p>
    <p>Number of Student Examined:<?php echo $examined?></p>
    <p>Number of Students that Passed:<?php echo count($passlist)?></p>
    <p>Number of Students to be Commended:<?php echo count($commendation)?></p>
    <p>Number of Students     that Failed:<?php echo count($faillist)?></p>
    <p>Number of Student Recommended for Counseling: <?php echo count($counselling)?></p>
    <p>Number of Students on Probation:  <?php echo count($probation)?></p>
    <p>Number of Students recommended for Withdrawal:<?php echo count($suspension)?></p>
    <p>&nbsp; </p></td>
  </tr>
  <tr>
    <td colspan="3">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="3"><table width="100%" border="0">
      <tr>
        <td width="33%"><div align="center"><?php echo $row_hod['fname'].' '.$row_hod['lname']?></div></td>
        <td width="34%"><div align="center"><?php echo $row_dean['fname'].' '.$row_dean['lname']?> </div></td>
        <td width="33%"><div align="center"><?php echo $row_vc['fname'].' '.$row_vc['lname']?></div></td>
      </tr>
      <tr>
        <td><div align="center">HOD</div></td>
        <td width="34%"><div align="center">DEAN</div></td>
        <td width="33%"><div align="center">VC</div></td>
      </tr>
    </table></td>
  </tr>
</table>
</body></html>