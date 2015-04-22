<?php
require_once('../Connections/tams.php');
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

$final = false;

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

$query_list = sprintf("SELECT * "
                        . "FROM student s, student_pop r "
                        . "WHERE s.stdid = r.stdid "
                        . "AND progid=%s "
                        . "AND r.sesid = %s "
                        . "AND r.level=%s",
                        GetSQLValueString($prog, "int"),
                        GetSQLValueString($session, "int"),
                        GetSQLValueString($level, "int"));
$list = mysql_query($query_list, $tams) or die(mysql_error());
$row_list = mysql_fetch_assoc($list);
$totalRows_list = mysql_num_rows($list);

// All courses
if($semester == 'F') {
    $colname_out1 = "AND ((dc.csid LIKE '___1%' AND c.semester = 'F') AND dc.csid NOT LIKE 'VOS%')";
    if ($level > 1) {
        $colname_out1 = 'AND ((';

        for($i = 1; $i <= $level - 1; $i++) {
            $colname_out1 .= "dc.csid LIKE '___{$i}%' OR ";
        } 
        $colname_out1 .= "(dc.csid LIKE '___{$level}%' AND c.semester = 'F')) AND dc.csid NOT LIKE 'VOS%') ";
    }
}else {
    $colname_out1 = "AND ((dc.csid LIKE '___1%') AND dc.csid NOT LIKE 'VOS%')";
    if ($level > 1) {
        $colname_out1 = 'AND ((';

        for($i = 1; $i <= $level - 1; $i++) {
            $colname_out1 .= "dc.csid LIKE '___{$i}%' OR ";
        } 
        $colname_out1 .= "dc.csid LIKE '___{$level}%') AND dc.csid NOT LIKE 'VOS%') ";
    }
}
    
$query_courses = sprintf("SELECT * "
        . "FROM department_course dc, course c "
        . "WHERE dc.csid = c.csid "
        . "AND dc.progid = %s %s ",								
        GetSQLValueString($prog, "int"),
        GetSQLValueString($colname_out1, "defined", $colname_out1));
$courses = mysql_query($query_courses, $tams) or die(mysql_error());
$row_courses = mysql_fetch_assoc($courses);
$totalRows_courses = mysql_num_rows($courses);
     
$outstanding = array();
for($i = 0; $i < $totalRows_courses; $i++, $row_courses = mysql_fetch_assoc($courses)) {
    $outstanding[] = $row_courses['csid'];
}

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

$query_exp = sprintf("SELECT csid, type, passmark, sesname, g.sesid "
                        . "FROM grade_exceptions g, session s "
                        . "WHERE g.sesid = s.sesid "
                        . "AND ((g.unitid = %s AND g.type = 'College') OR (g.unitid = %s AND g.type = 'Department')) "
                        . "AND g.sesid <= %s "
                        . "ORDER BY sesid DESC, csid, type",
                        GetSQLValueString(getSessionValue('cid'), "int"),
                        GetSQLValueString(getSessionValue('did'), "int"),
                        GetSQLValueString($session, "int"));
$exp = mysql_query($query_exp, $tams) or die(mysql_error());
$row_exp = mysql_fetch_assoc($exp);
$totalRows_exp = mysql_num_rows($exp);

$sesExp = array();
for($idx =0; $idx < $totalRows_exp; $idx++, $row_exp = mysql_fetch_assoc($exp)) {
    $sesExp[$row_exp['csid']][$row_exp['sesid']] = $row_exp['passmark'];
}

$students = array();
$course = array();
$courseInfo = array();
$taken = array();
$courseCount = 0;
  
$filter = 'r.sesid <= '.GetSQLValueString($session, "int");
if($semester == 'F') {
    $filter = 'r.sesid < '.
          GetSQLValueString($session, "int").' OR (r.sesid = '.
          GetSQLValueString($session, "int").' AND c.semester='.  
          GetSQLValueString($semester, 'text').')';
}
  
for($i = 0; $i < $totalRows_list; $i++, $row_list = mysql_fetch_assoc($list)) {
    $stud = array();
    $studid = $row_list['stdid'];
    $stud['matric'] = $studid;
    $stud['sex'] = $row_list['sex'];
    $stud['name'] = $row_list['lname'].' '.$row_list['fname'];
    $cgpa = getCgpa($studid, $prog, $session, $semester, $tams, $examined, $outstanding, $sesGrad);
    $stud['prev'] = $cgpa['prev'];
    $stud['cur'] = $cgpa['cur'];
    $stud['cum'] = $cgpa['cum'];
    $stud['crs'] = $cgpa['crs'];    
    $stud['resit'] = $cgpa['resit'];
    //$ref = getRef($studid, $session, $semester, $filter, $tams, $row_info['colid']);
    $stud['ref'] = (empty($cgpa['ref']))? 'PASSED': $cgpa['ref'];
    
    $students[] = $stud;
}

function gradepoint($unit, $score, $ses, $sesGrad){
	
    $gp = 0;
    $grades = $sesGrad[$ses];
    if( $score <= $grades['gradeF'])
            $gp = 0;
    else if ( $score <= $grades['gradeE'])	
            $gp = 1;
    else if ( $score <= $grades['gradeD'])	
            $gp = 2;
    else if ( $score <= $grades['gradeC'])	
            $gp = 3;
    else if ( $score <= $grades['gradeB'])	
            $gp = 4;
    else if ( $score <= $grades['gradeA'])	
            $gp = 5;

    return $gp*$unit;
}

function grade($score){
    $value = '';
    global $sesGrad;
    global $session;

    $grades = $sesGrad[$session];

    if( $score <= $grades['gradeF'])
            $value = 'F0';
    else if ( $score <= $grades['gradeE'])	
            $value = 'E1';
    else if ( $score <= $grades['gradeD'])	
            $value = 'D2';
    else if ( $score <= $grades['gradeC'])	
            $value = 'C3';
    else if ( $score <= $grades['gradeB'])	
            $value = 'B4';
    else if ( $score <= $grades['gradeA'])	
            $value = 'A5';

    return $value;
}

function getPassmark($sesid, $csid, $status, $firstYear = FALSE) {
    global $sesGrad;
    global $sesExp; 
    
    $default = (isset($sesGrad[$sesid]['passmark']))? $sesGrad[$sesid]['passmark']: $sesGrad[$sesid]['gradeF'] + 1;
    
    // OOU specific requirements
    if($status == 'Required') {
        return 30;
    }elseif($status == 'Elective') {
        return 0;
    }
    
    // Enforce normal grade in 100 level
    if($firstYear) {
        $default = $sesGrad[$sesid]['gradeF'] + 1;
        return $default;
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

function getCgpa($studid, $progid, $ses, $sem, $tams, &$examined, $courses, $grad) {
    $prev = '';
    $cur = '';
    $cum = '';

    global $course;
    global $courseInfo;
    global $courseCount;
    global $taken;
    global $filter;
    
    $query_cgpa = sprintf("SELECT r.csid, c.semester, r.tscore+ r.escore as score, r.sesid, sesname, dc.status, c.unit "
            . "FROM `result` r, course c, session s, department_course dc, teaching t "
            . "WHERE stdid = %s "
            . "AND c.csid = r.csid "
            . "AND dc.csid = c.csid "
            . "AND dc.csid = r.csid "
            . "AND r.csid = t.csid "
            . "AND s.sesid = t.sesid "
            . "AND t.approve = 'yes' "
            . "AND s.sesid = r.sesid "
            . "AND dc.progid=%s "
            . "AND (%s) ORDER BY r.sesid ASC", 
            GetSQLValueString($studid, "text"), 
            GetSQLValueString($progid, "int"), 
            GetSQLValueString($filter, "defined", $filter));

    $cgpa = mysql_query($query_cgpa, $tams) or die(mysql_error());
    $row_cgpa = mysql_fetch_assoc($cgpa);
    $totalRows_cgpa = mysql_num_rows($cgpa);

    
    $query_firstYear = sprintf("SELECT sesid "
            . "FROM student_pop r "
            . "WHERE level = 1 "
            . "AND stdid = %s ", GetSQLValueString($studid, "text"));
    $firstYear = mysql_query($query_firstYear, $tams) or die(mysql_error());
    $row_firstYear = mysql_fetch_assoc($firstYear);
    
    if($totalRows_cgpa > 0) {
        $examined++;
    }

    $curValue = 0;
    $prevValue = 0;
    $curUnit = 0;
    $prevUnit = 0;
    $curPassed = 0;
    $prevPassed = 0;
    $crs = array();
    $resit = array();
    
    for($i = 0; $i < $totalRows_cgpa; $i++, $row_cgpa = mysql_fetch_assoc($cgpa)) {
        $unit = $row_cgpa['unit'];
        $sesn = $row_cgpa['sesid'];
        $first = ($row_firstYear['sesid'] == $sesn)? TRUE: FALSE;
        
        // Remove courses whose score is above passmark from outstanding list
        if(getPassmark($sesn, $row_cgpa['csid'], $row_cgpa['status'], $first) <= $row_cgpa['score']) {            
            $key = array_search($row_cgpa['csid'], $courses);
            unset($courses[$key]);
        }
        
        if($sesn == $ses) {
            
            if($row_cgpa['semester'] == $sem) {
                $gp = gradepoint($unit, $row_cgpa['score'], $sesn, $grad);
                $curValue += $gp;
                $curUnit +=  $unit;
            
                if($gp > 0)
                    $curPassed += $unit;
                
                if(!in_array($row_cgpa['csid'], $course) && !in_array($row_cgpa['csid'], $taken)){
                    $course[$courseCount++] = $row_cgpa['csid'];
                    $courseInfo[$row_cgpa['csid']] = array(
                                                        'unit' => $unit, 
                                                        'status' => substr($row_cgpa['status'], 0, 1)
                                                    );
                }
                
                if(in_array($row_cgpa['csid'], $taken)) {
                    $resit[] = $row_cgpa['csid'].'-'
                            .$unit.substr($row_cgpa['status'], 0, 1)
                            .'('.$row_cgpa['score'].')';
                }
                
                $crs[$row_cgpa['csid']] = $row_cgpa['score'];
            }else{
                $gp = gradepoint($unit, $row_cgpa['score'], $sesn, $grad);
                $prevValue += $gp;
                $prevUnit +=  $unit;

                if($gp > 0)
                    $prevPassed += $unit;
            }
            
            
        }else {
            $taken[] = $row_cgpa['csid'];
            $gp = gradepoint($unit, $row_cgpa['score'], $sesn, $grad);
            $prevValue += $gp;
            $prevUnit +=  $unit;
            
            if($gp > 0)
                $prevPassed += $unit;
        }	
    }
    
    $cumUnit = $prevUnit + $curUnit; 
    $cumValue = $prevValue + $curValue;
    $cumPassed = $prevPassed + $curPassed;
    
    $prev = ($prevUnit == 0)? '0.00': @number_format(round(($prevValue)/($prevUnit), 2), 2);
    $cur = ($curUnit == 0)? '0.00': @number_format(round(($curValue)/($curUnit), 2), 2);

    $cum = ($cumUnit == 0)? '0.00': @number_format(round(($cumValue)/($cumUnit), 2), 2);

    return array('prev' => array('gpa' => $prev, 'tgp' => $prevValue, 'tut' => $prevUnit, 'tup' => $prevPassed),
                 'cur' => array('gpa' => $cur, 'tgp' => $curValue, 'tut' => $curUnit, 'tup' => $curPassed),
                 'cum' => array('gpa' => $cum, 'tgp' => $cumValue, 'tut' => $cumUnit, 'tup' => $cumPassed),
                 'crs' => $crs,
                 'resit' => implode(', ', $resit),
                 'ref' => implode(', ', $courses)
        );
}

$semester = $_POST['semester'];
$sem = 'first semester';
if($semester == 'S') 
    $sem = 'second semester';
      

include("../mpdf/mpdf.php");
$mpdf=new mPDF('c','Legal-L','','',10,10,50,15,5,5); 
$stylesheet = file_get_contents('../css/mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet,1);

$header = '<table width="100%" style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 9pt; color: #000088;">
<tr>
<td width="15%" align="left"><img src="../images/logo.jpg" width="100px" /></td>
<td width="85%" align="center">
<div style="font-weight: bold;">
<h2 style="font-size: 25pt">'.$university.'</h2>
<h3 style="font-size: 17pt">'.$row_info['colname'].'<br></h3>
<h4 style="font-size: 13pt">'.$row_info['deptname'].'</h4>
<h5 style="font-size: 9pt">'.$university_address.'</h5></div>
</td>
</tr>
</table>
<div style="text-align:center; width:100%; font-size: 16pt">
    <div style="float:left;text-align:left; width:19%; font-size: 16pt">'.$row_rssess['sesname'].' ('.strtoupper($semester).')'.'</div>
    <div style="float:left;text-align:center; width:60%; font-size: 16pt">Broadsheet</div>
    <div style="float:right;text-align:left; width:20%; font-size: 16pt">'.$level.'00 Level</div>
    <div style="clear:both"></div>
</div>';

$footer = '<div align="center">{PAGENO}</div>';


$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLFooter($footer);

$html = '';
        
if(!empty($students)) {
    $html .= "<table width='850' class='table table-striped table-bordered table-condensed'>
            <thead style='font-size:8pt'>
            <tr>
              <th rowspan='2' align='center' valign='top'>S/N</th>
              <th rowspan='2' align='center' valign='top'>Matric</th>
              <th rowspan='2' align='center' valign='top'>Name</th>
              <th rowspan='2' align='center' valign='top'>Sex</th>";
    
    foreach($course as $c) {
        $html .= "<th rowspan='2' valign='top' align='center'>".substr($c, 0, 3).
                    "<br/>".substr($c, 3).
                    "<br/>
                    {$courseInfo[$c]['unit']}<br/>{$courseInfo[$c]['status']}</th>";
    }
    
    $html .="<th rowspan='2' valign='top' align='center' width='24%'>Courses retaken or repeated</th>
              <th colspan='4' valign='top' align='center' width='10%'>Previous</th>
              <th colspan='4' valign='top' align='center' width='10%'>Current</th>
              <th colspan='4' valign='top' align='center' width='10%'>Cummulative</th>
              <th rowspan='2' valign='top' align='center' width='30%'>Remark</th>
          </tr>
            <tr>
                <th style='font-size:6pt'>TCP</th> 
                <th style='font-size:6pt'>TNU</th>                  
                <th style='font-size:6pt'>GPA</th>
                <th style='font-size:6pt'>TNUP</th> 
                <th style='font-size:6pt'>TCP</th>
                <th style='font-size:6pt'>TNU</th>                    
                <th style='font-size:6pt'>GPA</th>
                <th style='font-size:6pt'>TNUP</th>
                <th style='font-size:6pt'>TCP</th>
                <th style='font-size:6pt'>TNU</th>                   
                <th style='font-size:6pt'>CGPA</th>
                <th style='font-size:6pt'>TNUP</th> 
            </tr>
            </thead>";

    foreach($students as $count => $std) {
        
        $html .= "<tr>
          <td>".($count+1)."</td>
          <td><a href='../student/profile.php?stid={$std['matric']}' target= '_blank '>{$std['matric']}</a></td>
          <td>{$std['name']}</td>
          <td>{$std['sex']}</td>";
        
        foreach($course as $c) {
            
            if(isset($std['crs'][$c]) && $std['crs'][$c] != '') {
                $html .= "<td align='center'>{$std['crs'][$c]}<br/>".grade($std['crs'][$c])."</td>";
                continue;
            }
            
//            if($std['ref'] != 'PASSED') {
//                $std['ref'] .= ', '.$c;
//            }else {
//                $std['ref'] = $c;
//            }
            $html .= "<td align='center'> - </td>";
        }
        
        $html .= "
          <td>{$std['resit']}</td>  
          <td>{$std['prev']['tgp']}</td> 
          <td>{$std['prev']['tut']}</td>
          <td>{$std['prev']['gpa']}</td>
          <td>{$std['prev']['tup']}</td>  
          <td>{$std['cur']['tgp']}</td>  
          <td>{$std['cur']['tut']}</td>
          <td>{$std['cur']['gpa']}</td>
          <td>{$std['cur']['tup']}</td> 
          <td>{$std['cum']['tgp']}</td>  
          <td>{$std['cum']['tut']}</td> 
          <td>{$std['cum']['gpa']}</td>
          <td>{$std['cum']['tup']}</td> 
          <td>{$std['ref']}</td>
        </tr>";

    }

    $html .= '</table><br/><br/><br/><br/><br/><br/><br/><br/>';
    
    $width = ($final)? '33%': '50%';
    
    $html .= "<table width='100%' border='0'>
      <tr>
        <td width='{$width}' align='center'>{$row_hod['fname']} {$row_hod['lname']}</td>
        <td width='{$width}' align='center'>{$row_dean['fname']} {$row_dean['lname']}</td>";
        
        if($final) {
            $html .= "<td width='{$width}' align='center'>{$row_vc['fname']} {$row_vc['lname']}</td>";
        }
        
        
    $html .= "</tr>
      <tr>
        <td width='{$width}' align='center'>HOD</td>
        <td width='{$width}' align='center'>DEAN</td>";
        
        if($final) {
            $html .= "<td width='{$width}' align='center'>External Examiner</td>";
        }
    $html .= "
      </tr>
    </table>"; 
}

     
$mpdf->WriteHTML($html);

$mpdf->Output('broadsheet.pdf', 'I');
exit;