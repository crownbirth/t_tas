<?php require_once('../Connections/tams.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "1,2,3,4,5,6,10";
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
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("", $MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
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

$query = '';
if(getAccess() == 3) {
    $query = "AND p.deptid = ".  GetSQLValueString(getSessionValue('did'), 'int');
}

if(getAccess() == 2) {
    $query = "AND d.colid = ".  GetSQLValueString(getSessionValue('cid'), 'int');
}
// Recordset to populate programme dropdown
$query_prog = sprintf("SELECT p.progid, p.progname, p.duration, d.colid, p.deptid "
                        . "FROM programme p, department d "
                        . "WHERE d.deptid = p.deptid %s", 
                        GetSQLValueString($query, "defined", $query));
$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);
$totalRows_prog = mysql_num_rows($prog);

$duration = $row_prog['duration'];

$level = 1;
$prg = $row_prog['progid'];

if(isset($_GET['lvl'])) {
    $level = $_GET['lvl'];
}

if(isset($_GET['pid'])) {
    $prg = $_GET['pid'];
}

$colname_stud = "-1";
if (getAccess() < 7 && isset($_GET['stid'])) {
  $colname_stud = $_GET['stid'];
}

if (getAccess() < 7 && !isset($_GET['stid'])) {
    $colname_stud = '';
    
    $query_std = sprintf("SELECT s.stdid, s.progid, colid, p.deptid, fname, lname, level "
                            . "FROM student s, programme p, department d "
                            . "WHERE s.progid = p.progid AND d.deptid = p.deptid "
                            . "AND s.progid = %s AND s.level = %s", 
                            GetSQLValueString($prg, "text"), 
                            GetSQLValueString($level, "text"));
    $std = mysql_query($query_std, $tams) or die(mysql_error());
    $row_std = mysql_fetch_assoc($std);
    $totalRows_std = mysql_num_rows($std);
    
    if($totalRows_std > 0) {
        $colname_stud = $row_std['stdid'];
    }
}

if (getAccess() == 10) {
  $colname_stud = getSessionValue('stid');
}

$query_stud = sprintf("SELECT s.progid, colid, p.deptid, fname, lname, level FROM student s, programme p, department d WHERE s.progid = p.progid AND d.deptid = p.deptid AND stdid = %s", GetSQLValueString($colname_stud, "text"));
$stud = mysql_query($query_stud, $tams) or die(mysql_error());
$row_stud = mysql_fetch_assoc($stud);
$totalRows_stud = mysql_num_rows($stud);

if (getAccess() < 10) {
    $prg = $row_stud['progid'];
    $level = (isset($row_stud['level']))? $row_stud['level']: $level;
}

$query_studs = sprintf("SELECT stdid, fname, lname FROM student WHERE level = %s AND progid = %s"
                        , GetSQLValueString($level, "int")
                        , GetSQLValueString($prg, "int"));
$studs = mysql_query($query_studs, $tams) or die(mysql_error());
$row_studs = mysql_fetch_assoc($studs);
$totalRows_studs = mysql_num_rows($studs);

$colname1_year1f = "-1";
if (isset($row_stud['progid'])) {
  $colname1_year1f = $row_stud['progid'];
}

$query_year1f = sprintf("SELECT r.csid, r.sesid, c.csname, c.semester, r.tscore+ r.escore as score, sesname, c.status, unit "
                        . "FROM `result` r, course c, session s, teaching t "
                        . "WHERE stdid = %s "
                        . "AND c.csid = r.csid "
                        . "AND r.csid = t.csid "
                        . "AND s.sesid = t.sesid "
                        . "AND t.approve = 'yes' "
                        . "AND s.sesid = r.sesid AND r.csid NOT LIKE %s", 
                        GetSQLValueString($colname_stud, "text"), 
                        GetSQLValueString("VOS%", "text"));
$year1f = mysql_query($query_year1f, $tams) or die(mysql_error());
$row_year1f = mysql_fetch_assoc($year1f);
$totalRows_year1f = mysql_num_rows($year1f);

$colname_attn = $colname_stud;

$query_attn = sprintf("SELECT s.sesid, s.sesname "
        . "FROM session s, registration r "
        . "WHERE r.status = 'Registered' "
        . "AND s.sesid = r.sesid "
        . "AND stdid = %s "
        . "ORDER BY s.sesname ASC", GetSQLValueString($colname_attn, "text"));
$attn = mysql_query($query_attn, $tams) or die(mysql_error());
$row_attn = mysql_fetch_assoc($attn);
$totalRows_attn = mysql_num_rows($attn);

/*mysql_select_db($database_tams, $tams);
$query_rsrefs = sprintf("SELECT `result`.csid, course.csname FROM `result`, course WHERE course.csid = `result`.csid AND `result`.tscore+ `result`.escore < 40 AND `result`.stdid =%s AND `result`.csid NOT IN (SELECT csid FROM result WHERE stdid=%s AND `result`.tscore+ `result`.escore > 39)", GetSQLValueString($colname_attn, "text"), GetSQLValueString($colname_attn, "text"));
$rsrefs = mysql_query($query_rsrefs, $tams) or die(mysql_error());
$row_rsrefs = mysql_fetch_assoc($rsrefs);
$totalRows_rsrefs = mysql_num_rows($rsrefs);
*/
$name = "";
if ($_SESSION['MM_UserGroup'] < 6 && isset($_GET['Name'])) {
  $name = "for ".$_GET['Name'];
}

$sCount = 0;
$regSes = array();
$lastSes = 0;

if($totalRows_attn > 0) {
    $regSes[$sCount] = 0;
    do{
        $regSes[$sCount++] = $row_attn['sesid'];
        $lastSes = ($lastSes > $row_attn['sesid'])? $lastSes: $row_attn['sesid'];
        $results[$row_attn['sesname']]["first"] = array();
        $results[$row_attn['sesname']]["second"] = array();
    }while($row_attn = mysql_fetch_assoc($attn));
}

// Grading conditions
$query_grad = sprintf("SELECT * FROM grading g, session s WHERE g.sesid = s.sesid AND g.colid = %s",
                GetSQLValueString(getSessionValue('cid'), "int"));
$grad = mysql_query($query_grad, $tams) or die(mysql_error());
$row_grad = mysql_fetch_assoc($grad);
$totalRows_grad = mysql_num_rows($grad);

$sesGrad = array();
for($idx =0; $idx < $totalRows_grad; $idx++, $row_grad = mysql_fetch_assoc($grad)) {
    $sesGrad[$row_grad['sesid']] = array(
        'gradeA' => $row_grad['gradeA'],
        'gradeB' => $row_grad['gradeB'],
        'gradeC' => $row_grad['gradeC'],
        'gradeD' => $row_grad['gradeD'],
        'gradeE' => $row_grad['gradeE'],
        'gradeF' => $row_grad['gradeF'],
        'passmark' => $row_grad['passmark']
    );
}

$sesLimit = (isset($regSes[$sCount-1]))? $regSes[$sCount-1]: 0;
$query_exp = sprintf("SELECT csid, type, passmark, sesname, g.sesid "
                        . "FROM grade_exceptions g, session s "
                        . "WHERE g.sesid = s.sesid "
                        . "AND ((g.unitid = %s AND g.type = 'College') OR (g.unitid = %s AND g.type = 'Department')) "
                        . "AND g.sesid <= %s "
                        . "ORDER BY sesid DESC, csid, type",
                        GetSQLValueString(getSessionValue('cid'), "int"),
                        GetSQLValueString(getSessionValue('did'), "int"),
                        GetSQLValueString($sesLimit, "int"));
$exp = mysql_query($query_exp, $tams) or die(mysql_error());
$row_exp = mysql_fetch_assoc($exp);
$totalRows_exp = mysql_num_rows($exp);

$sesExp = array();
for($idx =0; $idx < $totalRows_exp; $idx++, $row_exp = mysql_fetch_assoc($exp)) {
    $sesExp[$row_exp['csid']][$row_exp['sesid']] = $row_exp['passmark'];
}

// Outstanding courses
$colname_out1 = "AND ((csid LIKE '___0%') AND csid NOT LIKE 'VOS%')";
if ($row_stud['level'] > 1) {
    $colname_out1 = 'AND ((';
    for($i = 1; $i <= $row_stud['level'] - 1; $i++) {
        $colname_out1 .= "csid LIKE '___{$i}%' OR ";
    } 
    $colname_out1 .= "csid LIKE '___{$row_stud['level']}%') AND csid NOT LIKE 'VOS%')";
}

$query_courses = sprintf("SELECT * "
        . "FROM department_course "
        . "WHERE progid = %s %s ",
//        . "AND csid "
//        . "NOT IN(SELECT DISTINCT csid FROM result WHERE stdid=%s)",									
        GetSQLValueString($row_stud['progid'], "int"),
        GetSQLValueString($colname_out1, "defined", $colname_out1));
//        GetSQLValueString($colname_attn, "text"));
$courses = mysql_query($query_courses, $tams) or die(mysql_error());
$row_courses = mysql_fetch_assoc($courses);
$totalRows_courses = mysql_num_rows($courses);
     
$outstanding = array();
for($i = 0; $i < $totalRows_courses; $i++, $row_courses = mysql_fetch_assoc($courses)) {
    $outstanding[] = $row_courses['csid'];
}

do{
    if(in_array($row_year1f['sesid'], $regSes)) {
        $mark = getPassmark($row_year1f['sesid'], $row_year1f['csid']) ;
        
        if($mark <= $row_year1f['score']) {            
            $key = array_search($row_year1f['csid'], $outstanding);
            unset($outstanding[$key]);
        }
        if($row_year1f['semester'] == "F") {
            $results[$row_year1f['sesname']]['first'][] = $row_year1f;				
        }else {
            $results[$row_year1f['sesname']]['second'][] = $row_year1f;				
        }
    }	
}while($row_year1f = mysql_fetch_assoc($year1f));

// Failed courses
//$query_rsrefs = sprintf("SELECT DISTINCT r.csid, d.status, unit "
//        . "FROM `result` r, department_course d, student s, department dt, grading g "
//        . "WHERE d.csid = r.csid "
//        . "AND r.stdid = s.stdid "
//        . "AND dt.deptid = d.deptid "
//        . "AND g.sesid = r.sesid "
//        . "AND g.colid = dt.colid "
//        . "AND d.progid = s.progid "
//        . "AND tscore + escore <= gradeF "
//        . "AND r.stdid = %s "
//        . "AND r.sesid <> %s "
//        . "AND r.csid "
//        . "NOT IN (SELECT csid FROM result WHERE stdid = %s AND tscore + escore > gradeF AND sesid <> %s)", 
//        GetSQLValueString($colname_attn, "text"), 
//        GetSQLValueString($row_rssess['sesid'], "int"),
//        GetSQLValueString($colname_attn, "text"), 
//        GetSQLValueString($row_rssess['sesid'], "int"));
//$rsrefs = mysql_query($query_rsrefs, $tams) or die(mysql_error());
//$row_rsrefs = mysql_fetch_assoc($rsrefs);
//$totalRows_rsrefs = mysql_num_rows($rsrefs);

// To reset the recordset to be used in the transcript table
$rows = mysql_num_rows($attn);
if($rows > 0) {
    mysql_data_seek($attn, 0);
    $row_attn = mysql_fetch_assoc($attn);
}

$pcgp = 0;
$put = 0;
$pup = 0;

if(isset($_GET['sesid']) && $_GET['sesid'] == $_SESSION['sesid']) {
	$pcgp = 0;
	$put = 0;
	$pup = 0;
}

$tgp1 = 0;
$tgp2 = 0;
$tut1 = 0;
$tut2 = 0;
$tup1 = 0;
$tup2 = 0;

function gradepoint($unit, $score, $sesid) {
    global $sesGrad;
    
    if(!isset($sesid)) {
        return '-';
    }
    
    $grades = $sesGrad[$sesid];
    $gp = 0;
    if($score < $grades['gradeF'])
            $gp = 0	;
    else if ($score < $grades['gradeE'])	
            $gp = 1;
    else if ($score < $grades['gradeD'])	
            $gp = 2;
    else if ($score < $grades['gradeC'])	
            $gp = 3;
    else if ($score < $grades['gradeB'])	
            $gp = 4;
    else if ($score <= $grades['gradeA'])	
            $gp = 5;

    return $gp*$unit;
}

function getRem($score, $sesid, $csid) {
    if(!isset($sesid)) {
        return '-';
    }
    
    $passmark = getPassmark($sesid, $csid);
    $remark = 'Failed';
    
    if($score >= $passmark)
        $remark = 'Passed';
    
    return $remark;
}

function unitPassed($unit, $score, $sesid, $csid) {
    
    if(!isset($sesid)) {
        return '-';
    }
    
    $passmark = getPassmark($sesid, $csid);
    
    return ($score < $passmark)? 0: $unit;
	
}

function getPassmark($sesid, $csid) {
    global $sesGrad;
    global $sesExp; 
    
    $default = (isset($sesGrad[$sesid]['passmark']))? $sesGrad[$sesid]['passmark']: $sesGrad[$sesid]['gradeF'] + 1;
    
    // Enforce normal grade in 100 level
    if(substr($csid, 3, 1) == '1') {
        $default = $sesGrad[$sesid]['gradeF'] + 1;
    }
        
    if(empty($sesExp[$csid])) {
        return $default;
    }
    
    $expMark = NULL;
    $keys = array_keys($sesExp[$csid]);
    
    foreach($keys as $value) {
        if($value > $sesid)             
            continue;
        
        if(isset($sesExp[$csid][$value]))
            $expMark = $sesExp[$csid][$value];
        
        break;        
    }
    
    return (isset($expMark))? $expMark: $default;
}

function calculateGpa($points, $units) {
    return number_format(round(($points)/($units), 2), 2);
}

//$option = array();
//$count = 0;
//
//if($totalRows_rsrefs > 0) {
//    do {  
//        $option[$count] = $row_rsrefs['csid'];         
//        $count++;
//    } while ($row_rsrefs = mysql_fetch_assoc($rsrefs));
//}

// Recordset to check if a college is medical or not
$query_special = sprintf("SELECT c.special "
                        . "FROM programme p, department d, college c "
                        . "WHERE d.deptid = p.deptid AND p.progid= %s", 
                        GetSQLValueString($prg, "int"));
$special = mysql_query($query_special, $tams) or die(mysql_error());
$row_special = mysql_fetch_assoc($special);
$totalRows_special = mysql_num_rows($special);

$special_college = $row_special['special'];

$did = "-1";
if(isset($row_stud['deptid'])) {	
    $did = $row_stud['deptid'];
}

$cid = "-1";
if(isset($row_stud['colid'])) {	
    $cid = $row_stud['colid'];
}

$allow = false;
$acl = array(4,5,6);
if(getAccess() == 1 || (getAccess() == 2 && getSessionValue('cid') == $cid) || (getAccess() == 3 && getSessionValue('did') == $did) || (in_array(getAccess(), $acl) && getSessionValue('did') == $did) || getSessionValue('stid') == $colname_stud) {
	 $allow = true;
}

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")) {
	doLogout($site_root);  
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Statement of Result for <?php echo $row_stud['lname'].", ".$row_stud['fname']." (".$colname_stud.")";?><!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="679" border="0" class="mytext">
    <?php if($allow) {?>
      <tr>
      <?php if(getSessionValue("MM_UserGroup") < 6) {?>
                              
        <td align="left">
            <form method="post" action="gentranscript.php" target="_blank">
                <input type="hidden" name="stid" value="<?php echo $colname_stud?>"/>
                <input type="submit" value="Print Transcript"/>
            </form>
        </td>
        <td align="right">
            <table>
                <tr>
                    <td>
                        <select onChange="progfilt(this)" name="stdid" style="width:200px">
                            <?php
                                do {  
                                    if($row_prog['duration'] > $duration) {
                                        $duration = $row_prog['duration'];
                                    }
                            ?>
                            <option <?php if($prg == $row_prog['progid'])echo "selected";?> value="<?php echo $row_prog['progid']?>"><?php echo $row_prog['progname']?></option>
                            <?php
                                } while ($row_prog = mysql_fetch_assoc($prog));
                            ?>
                          </select>
                    </td>
                    <td>
                        <select onChange="lvlfilt(this)">
                            <?php for($idx = 1; $idx <= $duration; $idx++){?>
                            <option value="<?php echo $idx?>" <?php if($level == $idx) echo 'selected';?>>
                                <?php echo $idx.'00'?>
                            </option>
                            <?php }

                                  if($duration > 0) {
                            ?>          
                            <option value="<?php echo $duration + 1?>" <?php if($level == $duration + 1) echo 'selected';?>>
                                Extra Year 1
                            </option>
                            <option value="<?php echo $duration + 2?>" <?php if($level == $duration + 2) echo 'selected';?>>
                                Extra Year 2
                            </option>
                            <?php }?>
                        </select>
                    </td>
                    <td>
                        <select onChange="studfilt(this)" name="stdid">
                            <?php
                                do {  
                            ?>
                            <option <?php if($colname_stud == $row_studs['stdid'])echo "selected";?> value="<?php echo $row_studs['stdid']?>"><?php echo ucwords(strtolower($row_studs['fname']." ".$row_studs['lname']))."(".$row_studs['stdid'].")"?></option>
                            <?php
                                } while ($row_studs = mysql_fetch_assoc($studs));
                            ?>
                          </select>
                    </td>
                </tr>
            </table>
        </td>
       </tr>
       <?php }?>
      
    </table>
    <?php if(!($special_college && $level > 2)) {?>
    <!--  First Table  -->
    <?php if($sCount > 0) { ?>
    <table width="679" border="0" class="mytext">
      <tr style=" border:solid 5px #000000; background:#000033; color:#FFF">
        <td colspan="2" align="right">
          Statement of Result for <?php echo $row_attn['sesname'] ?>
        </td>

      </tr>
      <tr>
        <td align="center">First semester</td>
        <td align="center">Second semester</td>
      </tr>
      <tr>
        <td width="362" height="58" align="left" valign="top"><table width="310" border="1" class="mytext">
          <tr>
            <td width="143" align="center">Code</td>
            <td width="144" align="center">Score</td>
            <td width="289" align="center">Unit</td>
            <td width="289" align="center">ST</td>
            <td width="289" align="center">GP</td>
            <td width="289" align="center">NUP</td>
          </tr>
          <?php for($i = 0; $i < count($results[$row_attn['sesname']]['first']); $i++) { 
                    $result = $results[$row_attn['sesname']]['first'][$i];
          ?>
            <tr>
              <td width="108" align="center"><?php echo strtoupper($result['csid']); ?></td>
              <td width="82" align="center"><?php echo $result['score']; ?></td>
              <td width="28" align="center"><?php $tut1 += $result['unit']; echo $result['unit']; ?></td>
              <td width="19" align="center"><?php echo getStatusAlpha($result['status']) ?></td>
              <td width="19" align="center">
              <?php 
                  $val = gradepoint($result['unit'], $result['score'], $row_attn['sesid']); 
                  $tgp1 += $val;
                  echo $val;
              
              ?>
              </td>
              <td width="25" align="center">
              <?php 
                  $val = unitPassed($result['unit'], $result['score'], $row_attn['sesid'], $result['csid']); 
                  $tup1 += $val;
                  echo $val;						
              ?>
              </td>
            </tr>
            <?php }?>
        </table></td>
        <td width="310" align="right" valign="top"><table width="310" border="1" class="mytext">
          <tr>
            <td width="143" align="center">Code</td>
            <td width="144" align="center">Score</td>
            <td width="289" align="center">Unit</td>
            <td width="289" align="center">ST</td>
            <td width="289" align="center">GP</td>
            <td width="289" align="center">NUP</td>
          </tr>
          <?php for($i = 0; $i < count($results[$row_attn['sesname']]['second']); $i++) { 
                    $result = $results[$row_attn['sesname']]['second'][$i];
          ?>
            <tr>
              <td width="108" align="center"><?php echo strtoupper($result['csid']); ?></td>
              <td width="82" align="center"><?php echo $result['score']; ?></td>
              <td width="28" align="center"><?php $tut2 += $result['unit']; echo $result['unit']; ?></td>
              <td width="19" align="center"><?php echo getStatusAlpha($result['status']) ?></td>
              <td width="19" align="center">
              <?php 
                  $val = gradepoint($result['unit'], $result['score'], $row_attn['sesid']); 
                  $tgp2 += $val;
                  echo $val;
              
              ?>
              </td>
              <td width="25" align="center">
              <?php 
                  $val = unitPassed($result['unit'], $result['score'], $row_attn['sesid'], $result['csid']); 
                  $tup2 += $val;
                  echo $val;						
              ?>
              </td>
            </tr>
            <?php }?>
        </table></td>
      </tr>
      <tr>
        <td height="29" valign="top" colspan="2"><table width="677" border="1">
          <tr>
            <td width="299" align="left"><table width="270" border="0" class="mytext">
              <tr>
                <td width="52">&nbsp;</td>
                <td width="52">TUT</td>
                <td width="51">TUP</td>
                <td width="52">TGP</td>
                <td width="51">GPA</td>
              </tr>
              <tr>
                <td>PREV</td>
                <td align="center"><?php echo $put; ?></td>
                <td align="center"><?php echo $pup; ?></td>
                <td align="center"><?php echo $pcgp; ?></td>
                <td align="center"><?php echo "0.00"; ?></td>
              </tr>
              <tr>
                <td>CUR</td>
                <td align="center"><?php echo $tut1; ?></td>
                <td align="center"><?php echo $tup1; ?> </td>
                <td align="center"><?php echo $tgp1; ?></td>
                <td align="center"><?php echo ($tut1 >0)? calculateGpa($tgp1, $tut1):"0.00"; ?></td>
              </tr>
              <tr>
                <td>CUM</td>
                <td align="center"><?php echo $put += $tut1; ?></td>
                <td align="center"><?php echo $pup += $tup1; ?></td>
                <td align="center"><?php echo $pcgp += $tgp1; ?></td>
                <td align="center"><?php echo ($put > 0)?calculateGpa(($pcgp), ($put)):"0.00"; ?></td>
              </tr>
            </table>
            </td>
            <td width="56">&nbsp;</td>
            <td width="300" align="right">
            <table width="270" border="0" class="mytext">
              <tr>
                <td width="52">&nbsp;</td>
                <td width="52">TUT</td>
                <td width="51">TUP</td>
                <td width="52">TGP</td>
                <td width="51">GPA</td>
              </tr>
              <tr>
                <td>PREV</td>
                <td><?php echo $put; ?></td>
                <td><?php echo $pup; ?></td>
                <td><?php echo $pcgp; ?></td>
                <td><?php echo ($put > 0)?calculateGpa(($pcgp), ($put)):"0.00"; ?></td>
              </tr>
              <tr>
                <td>CUR</td>
                <td><?php echo $tut2; ?></td>
                <td><?php echo $tup2; ?> </td>
                <td><?php echo $tgp2; ?></td>
                <td><?php echo ($tut2 >0)? calculateGpa($tgp2, $tut2):"0.00"; ?></td>
              </tr>
              <tr>
                <td>CUM</td>
                <td><?php echo $put += $tut2; ?></td>
                <td><?php echo $pup += $tup2; ?></td>
                <td><?php echo $pcgp += $tgp2; ?></td>
                <td><?php echo ($put > 0)?calculateGpa(($pcgp), ($put)):"0.00"; ?></td>
              </tr>
            </table>
            </td>
          </tr>
        </table>
        </td>
      </tr>
  </table>
    
    <?php  }?>
    
    <!--Second table-->
    <?php 
    $row_attn = mysql_fetch_assoc($attn);
    if($sCount> 1) { 
      $tgp1 = 0;
      $tgp2 = 0;
      $tut1 = 0;
      $tut2 = 0;
      $tup1 = 0;
      $tup2 = 0;
    ?>
      <table width="679" border="0" class="mytext" >
      <tr style=" border:solid 5px #000000; background:#000033; color:#FFF">
        <td colspan="2" align="right">
          Statement of Result for <?php echo $row_attn['sesname']; ?>
        </td>                  
      </tr>
      <tr>
        <td align="center">First semester</td>
        <td align="center">Second semester</td>
      </tr>
      
      <tr>
        <td width="299" height="58" align="left" valign="top"><table width="310" border="1" class="mytext">
          <tr>
            <td width="143" align="center">Code</td>
            <td width="144" align="center">Score</td>
            <td width="289" align="center">Unit</td>
            <td width="289" align="center">ST</td>
            <td width="289" align="center">GP</td>
            <td width="289" align="center">NUP</td>
          </tr>
          <?php for($i = 0; $i < count($results[$row_attn['sesname']]['first']); $i++) { 
                    $result = $results[$row_attn['sesname']]['first'][$i];
          ?>
            <tr>
              <td width="108" align="center"><?php echo strtoupper($result['csid']); ?></td>
              <td width="82" align="center"><?php echo $result['score']; ?></td>
               <td width="28" align="center"><?php $tut1 += $result['unit']; echo $result['unit']; ?></td>
              <td width="19" align="center"><?php echo getStatusAlpha($result['status']) ?></td>
              <td width="19" align="center">
              <?php 
                  $val = gradepoint($result['unit'], $result['score'], $row_attn['sesid']); 
                  $tgp1 += $val;
                  echo $val;
              
              ?>
              </td>
              <td width="25" align="center">
              <?php 
                  $val = unitPassed($result['unit'], $result['score'], $row_attn['sesid'], $result['csid']); 
                  $tup1 += $val;
                  echo $val;						
              ?>
              </td>

            </tr>

            <?php }?>
        </table></td>
        <td width="382" align="right" valign="top"><table width="310" border="1" class="mytext">
          <tr>
            <td width="37" align="center">Code</td>
            <td width="41" align="center">Score</td>
            <td width="41" align="center">Unit</td>
            <td width="37" align="center">ST</td>
            <td width="37" align="center">GP</td>
            <td width="79" align="center">NUP</td>
          </tr>
          <?php for($i = 0; $i < count($results[$row_attn['sesname']]['second']); $i++) { 
                    $result = $results[$row_attn['sesname']]['second'][$i];
          ?>
            <tr>
              <td width="37" align="center"><?php echo strtoupper($result['csid']); ?></td>
              <td width="41" align="center"><?php echo $result['score']; ?></td>
              <td width="41" align="center"><?php $tut2 += $result['unit']; echo $result['unit']; ?></td>
              <td width="37" align="center"><?php echo getStatusAlpha($result['status']) ?></td>
              <td width="37" align="center">
              <?php 
                  $val = gradepoint($result['unit'], $result['score'], $row_attn['sesid']); 
                  $tgp2 += $val;
                  echo $val;
              
              ?>
              </td>
              <td width="79" align="center">
              <?php 
                  $val = unitPassed($result['unit'], $result['score'], $row_attn['sesid'], $result['csid']); 
                  $tup2 += $val;
                  echo $val;						
              ?>
              </td>
            </tr>
            <?php }?>
        </table></td>
      </tr>
      <tr>
        <td height="29" valign="top" colspan="2"><table width="682" border="1">
          <tr>
            <td width="301" align="left"><table width="270" border="0" class="mytext">
              <tr>
                <td width="52">&nbsp;</td>
                <td width="52">TUT</td>
                <td width="51">TUP</td>
                <td width="52">TGP</td>
                <td width="51">GPA</td>
              </tr>
              <tr>
                <td>PREV</td>
                <td align="center"><?php echo $put; ?></td>
                <td align="center"><?php echo $pup; ?></td>
                <td align="center"><?php echo $pcgp; ?></td>
                <td align="center"><?php echo ($put > 0)?calculateGpa(($pcgp), ($put)):"0.00"; ?></td>
              </tr>
              <tr>
                <td>CUR</td>
                <td align="center"><?php echo $tut1; ?></td>
                <td align="center"><?php echo $tup1; ?> </td>
                <td align="center"><?php echo $tgp1; ?></td>
                <td align="center"><?php echo ($tut1 > 0)?calculateGpa($tgp1, $tut1):"0.00"; ?></td>
              </tr>
              <tr>
                <td>CUM</td>
                <td align="center"><?php echo $put += $tut1; ?></td>
                <td align="center"><?php echo $pup += $tup1; ?></td>
                <td align="center"><?php echo $pcgp += $tgp1; ?></td>
                <td align="center"><?php echo ($put > 0)?calculateGpa(($pcgp), ($put)):"0.00"; ?></td>
              </tr>
            </table>
            </td>
            <td width="62">&nbsp;</td>
            <td width="297" align="right">
            <table width="270" border="0" class="mytext">
              <tr>
                <td width="52">&nbsp;</td>
                <td width="52">TUT</td>
                <td width="51">TUP</td>
                <td width="52">TGP</td>
                <td width="51">GPA</td>
              </tr>
              <tr>
                <td>PREV</td>
                <td><?php echo $put; ?></td>
                <td><?php echo $pup; ?></td>
                <td><?php echo $pcgp; ?></td>
                <td><?php echo ($put > 0)?calculateGpa(($pcgp), ($put)):"0.00"; ?></td>
              </tr>
              <tr>
                <td>CUR</td>
                <td><?php echo $tut2; ?></td>
                <td><?php echo $tup2; ?> </td>
                <td><?php echo $tgp2; ?></td>
                <td><?php echo ($tut2 >0)? calculateGpa($tgp2, $tut2):"0.00"; ?></td>
              </tr>
              <tr>
                <td>CUM</td>
                <td><?php echo $put += $tut2; ?></td>
                <td><?php echo $pup += $tup2; ?></td>
                <td><?php echo $pcgp += $tgp2; ?></td>
                <td><?php echo ($put > 0)?calculateGpa(($pcgp), ($put)):"0.00"; ?></td>
              </tr>
            </table>
            </td>
          </tr>
        </table>
        </td>
      </tr>
    </table>
    
    <?php  }?>
    
   <!--Third table-->
    <?php 
    $row_attn = mysql_fetch_assoc($attn);
    if($sCount > 2) { 
      $tgp1 = 0;
      $tgp2 = 0;
      $tut1 = 0;
      $tut2 = 0;
      $tup1 = 0;
      $tup2 = 0;
    ?>
      <table width="687" border="0" class="mytext" >
      <tr style=" border:solid 5px #000000; background:#000033; color:#FFF">
        <td colspan="2" align="right">
          Statement of Result for <?php echo $row_attn['sesname']; ?>
        </td>                  
      </tr>
      <tr>
        <td align="center">First semester</td>
        <td align="center">Second semester</td>
      </tr>
      
      <tr>
        <td width="299" height="58" align="left" valign="top"><table width="310" border="1" class="mytext">
          <tr>
            <td width="143" align="center">Code</td>
            <td width="144" align="center">Score</td>
            <td width="289" align="center">Unit</td>
            <td width="289" align="center">ST</td>
            <td width="289" align="center">GP</td>
            <td width="289" align="center">NUP</td>
          </tr>
          <?php for($i = 0; $i < count($results[$row_attn['sesname']]['first']); $i++) { 
                    $result = $results[$row_attn['sesname']]['first'][$i];
          ?>
            <tr>
              <td width="108" align="center"><?php echo strtoupper($result['csid']); ?></td>
              <td width="82" align="center"><?php echo $result['score']; ?></td>
              <td width="28" align="center"><?php $tut1 += $result['unit']; echo $result['unit']; ?></td>
              <td width="19" align="center"><?php echo getStatusAlpha($result['status']) ?></td>
              <td width="19" align="center">
              <?php 
                  $val = gradepoint($result['unit'], $result['score'], $row_attn['sesid']); 
                  $tgp1 += $val;
                  echo $val;
              
              ?>
              </td>
              <td width="25" align="center">
              <?php 
                  $val = unitPassed($result['unit'], $result['score'], $row_attn['sesid'], $result['csid']); 
                  $tup1 += $val;
                  echo $val;						
              ?>
              </td>
            </tr>
            <?php }?>
        </table></td>
        <td width="378" align="right" valign="top"><table width="310" border="1" class="mytext">
          <tr>
            <td width="143" align="center">Code</td>
            <td width="144" align="center">Score</td>
            <td width="289" align="center">Unit</td>
            <td width="289" align="center">ST</td>
            <td width="289" align="center">GP</td>
            <td width="289" align="center">NUP</td>
          </tr>
          <?php for($i = 0; $i < count($results[$row_attn['sesname']]['second']); $i++) { 
                    $result = $results[$row_attn['sesname']]['second'][$i];
          ?>
            <tr>
              <td width="108" align="center"><?php echo strtoupper($result['csid']); ?></td>
              <td width="82" align="center"><?php echo $result['score']; ?></td>
              <td width="28" align="center"><?php $tut2 += $result['unit']; echo $result['unit']; ?></td>
              <td width="19" align="center"><?php echo getStatusAlpha($result['status']) ?></td>
              <td width="19" align="center">
              <?php 
                  $val = gradepoint($result['unit'], $result['score'], $row_attn['sesid']); 
                  $tgp2 += $val;
                  echo $val;
              
              ?>
              </td>
              <td width="25" align="center">
              <?php 
                  $val = unitPassed($result['unit'], $result['score'], $row_attn['sesid'], $result['csid']); 
                  $tup2 += $val;
                  echo $val;						
              ?>
              </td>
            </tr>
            <?php }?>
        </table></td>
      </tr> 
      <tr>
        <td height="29" valign="top" colspan="2"><table width="682" border="1">
          <tr>
            <td width="300" align="left"><table width="270" border="0" class="mytext">
              <tr>
                <td width="52">&nbsp;</td>
                <td width="52">TUT</td>
                <td width="51">TUP</td>
                <td width="52">TGP</td>
                <td width="51">GPA</td>
              </tr>
              <tr>
                <td>PREV</td>
                <td align="center"><?php echo $put; ?></td>
                <td align="center"><?php echo $pup; ?></td>
                <td align="center"><?php echo $pcgp; ?></td>
                <td align="center"><?php echo ($put > 0)?calculateGpa(($pcgp), ($put)):"0.00"; ?></td>
              </tr>
              <tr>
                <td>CUR</td>
                <td align="center"><?php echo $tut1; ?></td>
                <td align="center"><?php echo $tup1; ?> </td>
                <td align="center"><?php echo $tgp1; ?></td>
                <td align="center"><?php echo ($tut1 > 0)?calculateGpa($tgp1, $tut1):"0.00"; ?></td>
              </tr>
              <tr>
                <td>CUM</td>
                <td align="center"><?php echo $put += $tut1; ?></td>
                <td align="center"><?php echo $pup += $tup1; ?></td>
                <td align="center"><?php echo $pcgp += $tgp1; ?></td>
                <td align="center"><?php echo ($put > 0)?calculateGpa(($pcgp), ($put)):"0.00"; ?></td>
              </tr>
            </table>
            </td>
            <td width="62">&nbsp;</td>
            <td width="298" align="right">
            <table width="270" border="0" class="mytext">
              <tr>
                <td width="52">&nbsp;</td>
                <td width="52">TUT</td>
                <td width="51">TUP</td>
                <td width="52">TGP</td>
                <td width="51">GPA</td>
              </tr>
              <tr>
                <td>PREV</td>
                <td><?php echo $put; ?></td>
                <td><?php echo $pup; ?></td>
                <td><?php echo $pcgp; ?></td>
                <td><?php echo ($put > 0)?calculateGpa(($pcgp), ($put)):"0.00"; ?></td>
              </tr>
              <tr>
                <td>CUR</td>
                <td><?php echo $tut2; ?></td>
                <td><?php echo $tup2; ?> </td>
                <td><?php echo $tgp2; ?></td>
                <td><?php echo ($tut2 >0)? calculateGpa($tgp2, $tut2):"0.00"; ?></td>
              </tr>
              <tr>
                <td>CUM</td>
                <td><?php echo $put += $tut2; ?></td>
                <td><?php echo $pup += $tup2; ?></td>
                <td><?php echo $pcgp += $tgp2; ?></td>
                <td><?php echo ($put > 0)?calculateGpa(($pcgp), ($put)):"0.00"; ?></td>
              </tr>
            </table>
            </td>
          </tr>
        </table>
        </td>
      </tr>
    </table>
    
    <?php  }?>
    
    <!--Fourth table-->
    <?php 
    $row_attn = mysql_fetch_assoc($attn);
    if($sCount > 3) { 
      $tgp1 = 0;
      $tgp2 = 0;
      $tut1 = 0;
      $tut2 = 0;
      $tup1 = 0;
      $tup2 = 0;
    ?>
      <table width="686" border="0" class="mytext" >
      <tr style=" border:solid 5px #000000; background:#000033; color:#FFF">
        <td colspan="2" align="right">
          Statement of Result for <?php echo $row_attn['sesname']; ?>
        </td>                  
      </tr>
      <tr>
        <td align="center">First semester</td>
        <td align="center">Second semester</td>
      </tr>
      
      <tr>
        <td width="299" height="58" align="left" valign="top"><table width="310" border="1" class="mytext">
          <tr>
            <td width="143" align="center">Code</td>
            <td width="144" align="center">Score</td>
            <td width="289" align="center">Unit</td>
            <td width="289" align="center">ST</td>
            <td width="289" align="center">GP</td>
            <td width="289" align="center">NUP</td>
          </tr>
          <?php for($i = 0; $i < count($results[$row_attn['sesname']]['first']); $i++) { 
                    $result = $results[$row_attn['sesname']]['first'][$i];
          ?>
            <tr>
              <td width="108" align="center"><?php echo strtoupper($result['csid']); ?></td>
              <td width="82" align="center"><?php echo $result['score']; ?></td>
              <td width="28" align="center"><?php $tut1 += $result['unit']; echo $result['unit']; ?></td>
              <td width="19" align="center"><?php echo getStatusAlpha($result['status']) ?></td>
              <td width="19" align="center">
              <?php 
                  $val = gradepoint($result['unit'], $result['score'], $row_attn['sesid']); 
                  $tgp1 += $val;
                  echo $val;
              
              ?>
              </td>
              <td width="25" align="center">
              <?php 
                  $val = unitPassed($result['unit'], $result['score'], $row_attn['sesid'], $result['csid']); 
                  $tup1 += $val;
                  echo $val;						
              ?>
              </td>
            </tr>
            <?php }?>
        </table></td>
        <td width="377" align="right" valign="top"><table width="310" border="1" class="mytext">
          <tr>
            <td width="143" align="center">Code</td>
            <td width="144" align="center">Score</td>
            <td width="289" align="center">Unit</td>
            <td width="289" align="center">ST</td>
            <td width="289" align="center">GP</td>
            <td width="289" align="center">NUP</td>
          </tr>
          <?php for($i = 0; $i < count($results[$row_attn['sesname']]['second']); $i++) { 
                    $result = $results[$row_attn['sesname']]['second'][$i];
          ?>
            <tr>
              <td width="108" align="center"><?php echo strtoupper($result['csid']); ?></td>
              <td width="82" align="center"><?php echo $result['score']; ?></td>
              <td width="28" align="center"><?php $tut2 += $result['unit']; echo $result['unit']; ?></td>
              <td width="19" align="center"><?php echo getStatusAlpha($result['status']) ?></td>
              <td width="19" align="center">
              <?php 
                  $val = gradepoint($result['unit'], $result['score'], $row_attn['sesid']); 
                  $tgp2 += $val;
                  echo $val;
              
              ?>
              </td>
              <td width="25" align="center">
              <?php 
                  $val = unitPassed($result['unit'], $result['score'], $row_attn['sesid'], $result['csid']); 
                  $tup2 += $val;
                  echo $val;						
              ?>
              </td>
            </tr>
            <?php }?>
        </table></td>
      </tr>
      <tr>
        <td height="29" valign="top" colspan="2"><table width="682" border="1">
          <tr>
            <td width="301" align="left"><table width="270" border="0" class="mytext">
              <tr>
                <td width="52">&nbsp;</td>
                <td width="52">TUT</td>
                <td width="51">TUP</td>
                <td width="52">TGP</td>
                <td width="51">GPA</td>
              </tr>
              <tr>
                <td>PREV</td>
                <td align="center"><?php echo $put; ?></td>
                <td align="center"><?php echo $pup; ?></td>
                <td align="center"><?php echo $pcgp; ?></td>
                <td align="center"><?php echo ($put > 0)?calculateGpa(($pcgp), ($put)):"0.00"; ?></td>
              </tr>
              <tr>
                <td>CUR</td>
                <td align="center"><?php echo $tut1; ?></td>
                <td align="center"><?php echo $tup1; ?> </td>
                <td align="center"><?php echo $tgp1; ?></td>
                <td align="center"><?php echo ($tut1 > 0)?calculateGpa($tgp1, $tut1):"0.00"; ?></td>
              </tr>
              <tr>
                <td>CUM</td>
                <td align="center"><?php echo $put += $tut1; ?></td>
                <td align="center"><?php echo $pup += $tup1; ?></td>
                <td align="center"><?php echo $pcgp += $tgp1; ?></td>
                <td align="center"><?php echo ($put > 0)?calculateGpa(($pcgp), ($put)):"0.00"; ?></td>
              </tr>
            </table>
            </td>
            <td width="60">&nbsp;</td>
            <td width="299" align="right">
            <table width="270" border="0" class="mytext">
              <tr>
                <td width="52">&nbsp;</td>
                <td width="52">TUT</td>
                <td width="51">TUP</td>
                <td width="52">TGP</td>
                <td width="51">GPA</td>
              </tr>
              <tr>
                <td>PREV</td>
                <td><?php echo $put; ?></td>
                <td><?php echo $pup; ?></td>
                <td><?php echo $pcgp; ?></td>
                <td><?php echo ($put > 0)?calculateGpa(($pcgp), ($put)):"0.00"; ?></td>
              </tr>
              <tr>
                <td>CUR</td>
                <td><?php echo $tut2; ?></td>
                <td><?php echo $tup2; ?> </td>
                <td><?php echo $tgp2; ?></td>
                <td><?php echo ($tut2 >0)? calculateGpa($tgp2, $tut2):"0.00"; ?></td>
              </tr>
              <tr>
                <td>CUM</td>
                <td><?php echo $put += $tut2; ?></td>
                <td><?php echo $pup += $tup2; ?></td>
                <td><?php echo $pcgp += $tgp2; ?></td>
                <td><?php echo ($put > 0)?calculateGpa(($pcgp), ($put)):"0.00"; ?></td>
              </tr>
            </table>
            </td>
          </tr>
        </table>
        </td>
      </tr>
    </table>
    
    <?php  }?>
    
    <!--Fifth table-->
    <?php 
    $row_attn = mysql_fetch_assoc($attn);
    if($sCount > 4) { 
      $tgp1 = 0;
      $tgp2 = 0;
      $tut1 = 0;
      $tut2 = 0;
      $tup1 = 0;
      $tup2 = 0;
    ?>
      <table width="600" border="0" class="mytext" >
      <tr style=" border:solid 5px #000000; background:#000033; color:#FFF">
        <td colspan="2" align="right">
          Statement of Result for <?php echo $row_attn['sesname']; ?>
        </td>                  
      </tr>
      <tr>
        <td align="center">First semester</td>
        <td align="center">Second semester</td>
      </tr>
      
      <tr>
        <td width="295" height="58" align="left" valign="top"><table width="310" border="1" class="mytext">
          <tr>
            <td width="143" align="center">Code</td>
            <td width="144" align="center">Score</td>
            <td width="289" align="center">Unit</td>
            <td width="289" align="center">ST</td>
            <td width="289" align="center">GP</td>
            <td width="289" align="center">NUP</td>
          </tr>
          <?php for($i = 0; $i < count($results[$row_attn['sesname']]['first']); $i++) { 
                    $result = $results[$row_attn['sesname']]['first'][$i];
          ?>
            <tr>
              <td width="108" align="center"><?php echo strtoupper($result['csid']); ?></td>
              <td width="82" align="center"><?php echo $result['score']; ?></td>
              <td width="28" align="center"><?php $tut1 += $result['unit']; echo $result['unit']; ?></td>
              <td width="19" align="center"><?php echo getStatusAlpha($result['status']) ?></td>
              <td width="19" align="center">
              <?php 
                  $val = gradepoint($result['unit'], $result['score'], $row_attn['sesid']); 
                  $tgp1 += $val;
                  echo $val;
              
              ?>
              </td>
              <td width="25" align="center">
              <?php 
                  $val = unitPassed($result['unit'], $result['score'], $row_attn['sesid'], $result['csid']); 
                  $tup1 += $val;
                  echo $val;						
              ?>
              </td>
            </tr>
            <?php }?>
        </table></td>
        <td width="295" align="right" valign="top"><table width="310" border="1" class="mytext">
          <tr>
            <td width="143" align="center">Code</td>
            <td width="144" align="center">Score</td>
            <td width="289" align="center">Unit</td>
            <td width="289" align="center">ST</td>
            <td width="289" align="center">GP</td>
            <td width="289" align="center">NUP</td>
          </tr>
          <?php for($i = 0; $i < count($results[$row_attn['sesname']]['second']); $i++) { 
                    $result = $results[$row_attn['sesname']]['second'][$i];
          ?>
            <tr>
              <td width="108" align="center"><?php echo strtoupper($result['csid']); ?></td>
              <td width="82" align="center"><?php echo $result['score']; ?></td>
              <td width="28" align="center"><?php $tut2 += $result['unit']; echo $result['unit']; ?></td>
              <td width="19" align="center"><?php echo getStatusAlpha($result['status']) ?></td>
              <td width="19" align="center">
              <?php 
                  $val = gradepoint($result['unit'], $result['score'], $row_attn['sesid']); 
                  $tgp2 += $val;
                  echo $val;
              
              ?>
              </td>
              <td width="25" align="center">
              <?php 
                  $val = unitPassed($result['unit'], $result['score'], $row_attn['sesid'], $result['csid']); 
                  $tup2 += $val;
                  echo $val;						
              ?>
              </td>
            </tr>
            <?php }?>
        </table></td>
      </tr>
      <tr>
        <td height="29" valign="top" colspan="2"><table width="682" border="1">
          <tr>
            <td width="299" align="left"><table width="270" border="0" class="mytext">
              <tr>
                <td width="52">&nbsp;</td>
                <td width="52">TUT</td>
                <td width="51">TUP</td>
                <td width="52">TGP</td>
                <td width="51">GPA</td>
              </tr>
              <tr>
                <td>PREV</td>
                <td align="center"><?php echo $put; ?></td>
                <td align="center"><?php echo $pup; ?></td>
                <td align="center"><?php echo $pcgp; ?></td>
                <td align="center"><?php echo ($put > 0)?calculateGpa(($pcgp), ($put)):"0.00"; ?></td>
              </tr>
              <tr>
                <td>CUR</td>
                <td align="center"><?php echo $tut1; ?></td>
                <td align="center"><?php echo $tup1; ?> </td>
                <td align="center"><?php echo $tgp1; ?></td>
                <td align="center"><?php echo ($tut1 > 0)?calculateGpa($tgp1, $tut1):"0.00"; ?></td>
              </tr>
              <tr>
                <td>CUM</td>
                <td align="center"><?php echo $put += $tut1; ?></td>
                <td align="center"><?php echo $pup += $tup1; ?></td>
                <td align="center"><?php echo $pcgp += $tgp1; ?></td>
                <td align="center"><?php echo ($put > 0)?calculateGpa(($pcgp), ($put)):"0.00"; ?></td>
              </tr>
            </table>
            </td>
            <td width="62">&nbsp;</td>
            <td width="299" align="right">
            <table width="270" border="0" class="mytext">
              <tr>
                <td width="52">&nbsp;</td>
                <td width="52">TUT</td>
                <td width="51">TUP</td>
                <td width="52">TGP</td>
                <td width="51">GPA</td>
              </tr>
              <tr>
                <td>PREV</td>
                <td><?php echo $put; ?></td>
                <td><?php echo $pup; ?></td>
                <td><?php echo $pcgp; ?></td>
                <td><?php echo ($put > 0)?calculateGpa(($pcgp), ($put)):"0.00"; ?></td>
              </tr>
              <tr>
                <td>CUR</td>
                <td><?php echo $tut2; ?></td>
                <td><?php echo $tup2; ?> </td>
                <td><?php echo $tgp2; ?></td>
                <td><?php echo ($tut2 >0)? calculateGpa($tgp2, $tut2):"0.00"; ?></td>
              </tr>
              <tr>
                <td>CUM</td>
                <td><?php echo $put += $tut2; ?></td>
                <td><?php echo $pup += $tup2; ?></td>
                <td><?php echo $pcgp += $tgp2; ?></td>
                <td><?php echo ($put > 0)?calculateGpa(($pcgp), ($put)):"0.00"; ?></td>
              </tr>
        </table>
        </td>
      </tr>
    </table>
    
    <?php  } }else {?>
    <!--  First Table -->
    <?php if($sCount> 1) { ?>
    <table width="679" border="0" class="mytext" >
      <tr style=" border:solid 5px #000000; background:#000033; color:#FFF">
        <td colspan="2" align="right">
          Statement of Result for <?php echo $row_attn['sesname']; ?>
        </td>                  
      </tr>
      <tr>
        <td align="center">First semester</td>
        <td align="center">Second semester</td>
      </tr>
      
      <tr>
        <td width="299" height="58" align="left" valign="top"><table width="310" border="1" class="mytext">
          <tr>
            <td width="143" align="center">Code</td>
            <td width="144" align="center">Title</td>
            <td width="289" align="center">Remark</td>
          </tr>
          <?php for($i = 0; $i < count($results[$row_attn['sesname']]['first']); $i++) { 
                    $result = $results[$row_attn['sesname']]['first'][$i];
          ?>
            <tr>
              <td width="108" align="center"><?php echo strtoupper($result['csid']); ?></td>
              <td width="82" align="center"><?php echo $result['csname']; ?></td>
              <td width="19" align="center"><?php echo getRem($result['score'], $result['sesid'], $result['csid']);?></td>
            </tr>

            <?php }?>
        </table></td>
        <td width="382" align="right" valign="top"><table width="310" border="1" class="mytext">
          <tr>
            <td width="37" align="center">Code</td>
            <td width="41" align="center">Title</td>
            <td width="41" align="center">Remark</td>
          </tr>
          <?php for($i = 0; $i < count($results[$row_attn['sesname']]['second']); $i++) { 
                    $result = $results[$row_attn['sesname']]['second'][$i];
          ?>
            <tr>
              <td width="37" align="center"><?php echo strtoupper($result['csid']); ?></td>
              <td width="41" align="center"><?php echo $result['csname']; ?></td>
              <td width="19" align="center"><?php echo getRem($result['score'], $result['sesid'], $result['csid']);?></td>
            </tr>
            <?php }?>
        </table></td>
      </tr>
    </table>
    <?php }?>
    
    <!--Second table-->
    <?php 
    $row_attn = mysql_fetch_assoc($attn);
    if($sCount> 1) { 
      $tgp1 = 0;
      $tgp2 = 0;
      $tut1 = 0;
      $tut2 = 0;
      $tup1 = 0;
      $tup2 = 0;
    ?>
      <table width="679" border="0" class="mytext" >
      <tr style=" border:solid 5px #000000; background:#000033; color:#FFF">
        <td colspan="2" align="right">
          Statement of Result for <?php echo $row_attn['sesname']; ?>
        </td>                  
      </tr>
      <tr>
        <td align="center">First semester</td>
        <td align="center">Second semester</td>
      </tr>
      
      <tr>
        <td width="299" height="58" align="left" valign="top"><table width="310" border="1" class="mytext">
          <tr>
            <td width="143" align="center">Code</td>
            <td width="144" align="center">Title</td>
            <td width="289" align="center">Remark</td>
          </tr>
          <?php for($i = 0; $i < count($results[$row_attn['sesname']]['first']); $i++) { 
                    $result = $results[$row_attn['sesname']]['first'][$i];
          ?>
            <tr>
              <td width="108" align="center"><?php echo strtoupper($result['csid']); ?></td>
              <td width="82" align="center"><?php echo $result['csname']; ?></td>
              <td width="19" align="center"><?php echo getRem($result['score'], $result['sesid'], $result['csid']);?></td>
            </tr>

            <?php }?>
        </table></td>
        <td width="382" align="right" valign="top"><table width="310" border="1" class="mytext">
          <tr>
            <td width="37" align="center">Code</td>
            <td width="41" align="center">Title</td>
            <td width="41" align="center">Remark</td>
          </tr>
          <?php for($i = 0; $i < count($results[$row_attn['sesname']]['second']); $i++) { 
                    $result = $results[$row_attn['sesname']]['second'][$i];
          ?>
            <tr>
              <td width="37" align="center"><?php echo strtoupper($result['csid']); ?></td>
              <td width="41" align="center"><?php echo $result['csname']; ?></td>
              <td width="19" align="center"><?php echo getRem($result['score'], $result['sesid'], $result['csid']);?></td>
            </tr>
            <?php }?>
        </table></td>
      </tr>
    </table>
    
    <?php  }?>
    
   <!--Third table-->
    <?php 
    $row_attn = mysql_fetch_assoc($attn);
    if($sCount > 2) { 
      $tgp1 = 0;
      $tgp2 = 0;
      $tut1 = 0;
      $tut2 = 0;
      $tup1 = 0;
      $tup2 = 0;
    ?>
      <table width="687" border="0" class="mytext" >
      <tr style=" border:solid 5px #000000; background:#000033; color:#FFF">
        <td colspan="2" align="right">
          Statement of Result for <?php echo $row_attn['sesname']; ?>
        </td>                  
      </tr>
      <tr>
        <td align="center">First semester</td>
        <td align="center">Second semester</td>
      </tr>
      
      <tr>
        <td width="299" height="58" align="left" valign="top"><table width="310" border="1" class="mytext">
          <tr>
            <td width="143" align="center">Code</td>
            <td width="144" align="center">Title</td>
            <td width="289" align="center">Remark</td>
          </tr>
          <?php for($i = 0; $i < count($results[$row_attn['sesname']]['first']); $i++) { 
                    $result = $results[$row_attn['sesname']]['first'][$i];
          ?>
            <tr>
              <td width="108" align="center"><?php echo strtoupper($result['csid']); ?></td>
              <td width="82" align="center"><?php echo $result['csname']; ?></td>
              <td width="28" align="center"><?php echo getRem($result['score'], $result['sesid'], $result['csid']);?></td>
            </tr>
            <?php }?>
        </table></td>
        <td width="378" align="right" valign="top"><table width="310" border="1" class="mytext">
          <tr>
            <td width="143" align="center">Code</td>
            <td width="144" align="center">Title</td>
            <td width="289" align="center">Remark</td>
          </tr>
          <?php for($i = 0; $i < count($results[$row_attn['sesname']]['second']); $i++) { 
                    $result = $results[$row_attn['sesname']]['second'][$i];
          ?>
            <tr>
              <td width="108" align="center"><?php echo strtoupper($result['csid']); ?></td>
              <td width="82" align="center"><?php echo $result['csname']; ?></td>
              <td width="28" align="center"><?php echo getRem($result['score'], $result['sesid'], $result['csid']);?></td>
            </tr>
            <?php }?>
        </table></td>
      </tr>
    </table>
    
    <?php  }?>
    
    <!--Fourth table-->
    <?php 
    $row_attn = mysql_fetch_assoc($attn);
    if($sCount > 3) { 
      $tgp1 = 0;
      $tgp2 = 0;
      $tut1 = 0;
      $tut2 = 0;
      $tup1 = 0;
      $tup2 = 0;
    ?>
      <table width="686" border="0" class="mytext" >
      <tr style=" border:solid 5px #000000; background:#000033; color:#FFF">
        <td colspan="2" align="right">
          Statement of Result for <?php echo $row_attn['sesname']; ?>
        </td>                  
      </tr>
      <tr>
        <td align="center">First semester</td>
        <td align="center">Second semester</td>
      </tr>
      
      <tr>
        <td width="299" height="58" align="left" valign="top"><table width="310" border="1" class="mytext">
          <tr>
            <td width="143" align="center">Code</td>
            <td width="144" align="center">Title</td>
            <td width="289" align="center">Remark</td>
          </tr>
          <?php for($i = 0; $i < count($results[$row_attn['sesname']]['first']); $i++) { 
                    $result = $results[$row_attn['sesname']]['first'][$i];
          ?>
            <tr>
              <td width="108" align="center"><?php echo strtoupper($result['csid']); ?></td>
              <td width="82" align="center"><?php echo $result['csname']; ?></td>
              <td width="28" align="center"><?php echo getRem($result['score'], $result['sesid'], $result['csid']);?></td>
            </tr>
            <?php }?>
        </table></td>
        <td width="377" align="right" valign="top"><table width="310" border="1" class="mytext">
          <tr>
            <td width="143" align="center">Code</td>
            <td width="144" align="center">Title</td>
            <td width="289" align="center">Remark</td>
          </tr>
          <?php for($i = 0; $i < count($results[$row_attn['sesname']]['second']); $i++) { 
                    $result = $results[$row_attn['sesname']]['second'][$i];
          ?>
            <tr>
              <td width="108" align="center"><?php echo strtoupper($result['csid']); ?></td>
              <td width="82" align="center"><?php echo $result['csname']; ?></td>
              <td width="28" align="center"><?php echo getRem($result['score'], $result['sesid'], $result['csid']);?></td>
            </tr>
            <?php }?>
        </table></td>
      </tr>
    </table>
    
    <?php  }?>
    
    <!--Fifth table-->
    <?php 
    $row_attn = mysql_fetch_assoc($attn);
    if($sCount > 4) { 
      $tgp1 = 0;
      $tgp2 = 0;
      $tut1 = 0;
      $tut2 = 0;
      $tup1 = 0;
      $tup2 = 0;
    ?>
      <table width="686" border="0" class="mytext" >
      <tr style=" border:solid 5px #000000; background:#000033; color:#FFF">
        <td colspan="2" align="right">
          Statement of Result for <?php echo $row_attn['sesname']; ?>
        </td>                  
      </tr>
      <tr>
        <td align="center">First semester</td>
        <td align="center">Second semester</td>
      </tr>
      
      <tr>
        <td width="299" height="58" align="left" valign="top"><table width="310" border="1" class="mytext">
          <tr>
            <td width="143" align="center">Code</td>
            <td width="144" align="center">Title</td>
            <td width="289" align="center">Remark</td>
          </tr>
          <?php for($i = 0; $i < count($results[$row_attn['sesname']]['first']); $i++) { 
                    $result = $results[$row_attn['sesname']]['first'][$i];
          ?>
            <tr>
              <td width="108" align="center"><?php echo strtoupper($result['csid']); ?></td>
              <td width="82" align="center"><?php echo $result['csname']; ?></td>
              <td width="28" align="center"><?php echo getRem($result['score'], $result['sesid'], $result['csid']);?></td>
            </tr>
            <?php }?>
        </table></td>
        <td width="377" align="right" valign="top"><table width="310" border="1" class="mytext">
          <tr>
            <td width="143" align="center">Code</td>
            <td width="144" align="center">Title</td>
            <td width="289" align="center">Remark</td>
          </tr>
          <?php for($i = 0; $i < count($results[$row_attn['sesname']]['second']); $i++) { 
                    $result = $results[$row_attn['sesname']]['second'][$i];
          ?>
            <tr>
              <td width="108" align="center"><?php echo strtoupper($result['csid']); ?></td>
              <td width="82" align="center"><?php echo $result['csname']; ?></td>
              <td width="28" align="center"><?php echo getRem($result['score'], $result['sesid'], $result['csid']);?></td>
            </tr>
            <?php }?>
        </table></td>
      </tr>
    </table>
    
    <?php  }?>
    
    <?php }?>
    <table width="684" border="0" class="mytext" style="color:#fff; background:#000033">
              <tr align="center">
               <td>
                 References and Outstanding
               </td>
              </tr>
              <tr align="center">
               <td>                  
               <?php 
                   echo implode(', ', $outstanding);
                ?>
               
               </td>
              </tr>
              <?php }else{?>
              
              You cannot view this transcript!
              <?php }?>
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
<!-- InstanceEnd --></html>