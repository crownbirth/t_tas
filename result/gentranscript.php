<?php require_once('../Connections/tams.php'); ?>
<?php
 
if (!isset($_SESSION)) {
  session_start();
}
require_once('../param/param.php');
require_once('../functions/function.php');

$MM_authorizedUsers = "1,2,3";
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

$MM_restrictGoTo = "../login.php";
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
$query_rssess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,1";
$rssess = mysql_query($query_rssess, $tams) or die(mysql_error());
$row_rssess = mysql_fetch_assoc($rssess);
$totalRows_rssess = mysql_num_rows($rssess);

$student = (isset($_POST['stid']))? $_POST['stid']: '';

$query_info = sprintf("SELECT colname, c.colid, deptname, d.deptid, progname, p.progid, level, fname, lname, sex, stdid, admode, stname "
        . "FROM college c, department d, programme p, student s, state st "
        . "WHERE c.colid = d.colid "
        . "AND d.deptid = p.deptid "
        . "AND p.progid = s.progid "
        . "AND s.stid = st.stid "
        . "AND s.stdid=%s",
        GetSQLValueString($student, "text"));
$info = mysql_query($query_info, $tams) or die(mysql_error());
$row_info = mysql_fetch_assoc($info);
$totalRows_info = mysql_num_rows($info);

$query_year1f = sprintf("SELECT r.csid, c.csname, c.semester, r.tscore+ r.escore as score, sesname, c.status, c.unit "
                            . "FROM `result` r, course c, session s, teaching t "
                            . "WHERE stdid = %s "
                            . "AND c.csid = r.csid "
                            . "AND r.csid = t.csid "
                            . "AND s.sesid = t.sesid "
                            . "AND t.approve = 'yes' "
                            . "AND s.sesid = r.sesid", 
                            GetSQLValueString($student, "text"));
$year1f = mysql_query($query_year1f, $tams) or die(mysql_error());
$row_year1f = mysql_fetch_assoc($year1f);
$totalRows_year1f = mysql_num_rows($year1f);

$colname_attn = $student;

$query_attn = sprintf("SELECT s.sesname "
        . "FROM session s, registration r "
        . "WHERE r.status = 'Registered' "
        . "AND s.sesid = r.sesid "
        . "AND stdid = %s "
        . "ORDER BY s.sesname ASC", GetSQLValueString($colname_attn, "text"));
$attn = mysql_query($query_attn, $tams) or die(mysql_error());
$row_attn = mysql_fetch_assoc($attn);
$totalRows_attn = mysql_num_rows($attn);

$colname_attn = $student;

mysql_select_db($database_tams, $tams);
$query_attn = sprintf("SELECT s.sesname "
        . "FROM session s, registration r "
        . "WHERE r.status = 'Registered' "
        . "AND s.sesid = r.sesid "
        . "AND stdid = %s "
        . "ORDER BY s.sesname ASC", GetSQLValueString($colname_attn, "text"));
$attn = mysql_query($query_attn, $tams) or die(mysql_error());
$row_attn = mysql_fetch_assoc($attn);
$totalRows_attn = mysql_num_rows($attn);

$sCount = 0;
do {
    $results[] = $row_attn['sesname'];
    $results[$row_attn['sesname']]['first'] = array();
    $results[$row_attn['sesname']]['second'] = array();
    $sCount++;	
}while( $row_attn = mysql_fetch_assoc($attn) );

do{
    for( $count = 0; $count < $totalRows_attn; $count++){
        if( $row_year1f['sesname'] == $results[$count])
            if($row_year1f['semester'] == "F"){
                $results[$row_year1f['sesname']]['first'][] = $row_year1f;				
            }else{
                $results[$row_year1f['sesname']]['second'][] = $row_year1f;				
            }			

    }
	
}while( $row_year1f = mysql_fetch_assoc($year1f) );

$query_grad = sprintf("SELECT * FROM grading g, session s WHERE g.sesid = s.sesid AND g.colid = %s",
                GetSQLValueString(getSessionValue('cid'), "int"));
$grad = mysql_query($query_grad, $tams) or die(mysql_error());
$row_grad = mysql_fetch_assoc($grad);
$totalRows_grad = mysql_num_rows($grad);

$sesGrad = array();
for($idx =0; $idx < $totalRows_grad; $idx++, $row_grad = mysql_fetch_assoc($grad)) {
    $sesGrad[$row_grad['sesname']] = array(
        'gradeA' => $row_grad['gradeA'],
        'gradeB' => $row_grad['gradeB'],
        'gradeC' => $row_grad['gradeC'],
        'gradeD' => $row_grad['gradeD'],
        'gradeE' => $row_grad['gradeE'],
        'gradeF' => $row_grad['gradeF'],
    );
}

function gradepoint($unit, $score, $sesName, $sesGrad){
	
    $gp = 0;
    $grades = $sesGrad[$sesName];
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

function degreeClass($cgpa) {    
    $class = array(
        '4.49' => 'First Class',
        '3.49' => 'Second Class (Upper Division)',
        '2.49' => 'Second Class (Lower Division)',
        '0.99' => 'Third Class'
    );
    
    foreach(array_keys($class) as $key) {
        if($cgpa > floatval($key)) {
            return $class[$key];
        }           
    }
    
    return 'Fail';
}

include("../mpdf/mpdf.php");
$mpdf=new mPDF('c','Legal','','',10,10,40,10,5,5); 
$stylesheet = file_get_contents('../css/mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet,1);

$header = '<table width="100%" style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 9pt; color: #000088;">
<tr>
<td width="15%" align="left"><img src="../images/logo.jpg" width="100px" /></td>
<td width="85%" align="center">
<div style="font-weight: bold;">
<h2 style="font-size: 23pt">'.$university.'</h2>
<h3 style="font-size: 15pt">'.$row_info['colname'].'<br></h3>
<h4 style="font-size: 11pt">'.$row_info['deptname'].'</h4>
<h5 style="font-size: 7pt">'.$university_address.'</h5></div>
</td>
</tr>
</table>';

$footer = '<div align="center">{PAGENO}</div>';

$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLFooter($footer);
  
$semSes = '';

$cgpaValue = 0;
$cgpaUnit = 0;
$loopCount = 0;

foreach($results as $count => $r) {
    if(!is_array($r)) {
        $semSes = $r;
        continue;
    }
    
    $mpdf->AddPage();
    
    $loopCount++;
    
    $html = '
        <div >
            Academic Record
        </div>
        <div >
            <div style="float:left;text-align:left; width:20%; font-size: 16pt;">
                <table class="table table-condensed" width="20%">
                    <tr>
                        <td>'.$row_info['lname'].'</td>
                    </tr>
                    <tr>
                        <td>'.$row_info['fname'].'</td>
                    </tr>
                </table>
            </div>
            <div style="float:left;text-align:center; width:18%; font-size: 16pt">
                <table class="table table-bordered table-condensed" width="16%">
                    <tr>
                        <td>STUDENT NO.</td>
                    </tr>
                    <tr>
                        <td>'.$row_info['stdid'].'</td>
                    </tr>
                </table>
            </div>
            <div style="float:right;text-align:left; width:58%; font-size: 16pt">
                <table class="table table-bordered table-condensed">
                    <tr>
                        <td>SEX</td>
                        <td>BIRTHDATE</td>
                        <td>DATE ADMITTED</td>
                        <td>MODE OF ADMISSION</td>
                    </tr>
                    <tr>
                        <td>'.(strtolower($row_info['sex'])=='m'? 'MALE': 'FEMALE').'</td>
                        <td>'.$row_info['dob'].'</td>
                        <td>'.$row_info['sesname'].'</td>
                        <td>'.$row_info['admode'].'</td>

                    </tr>
                </table>
            </div>
            <div style="clear:both"></div>
        </div>
    ';


     $html .= '
             <div >
             <div style="float:left;text-align:left; width:40%; font-size: 10pt;">
                FACULTY: '.$row_info['colname'].'
            </div>
            <div style="float:left;text-align:center; width:40%; font-size: 10pt">
                STATE: '.$row_info['stname'].'
            </div>
            <div style="float:right;text-align:left; width:20%; font-size: 10pt">

            </div>
            <div style="clear:both"></div>
                </div>

    ';       


     $html .= '
             <table class="table table-bordered table-condensed">
                <thead>
                    <tr>
                        <th>SESSION</th>
                        <th>COURSE CODE</th>
                        <th>TITLE</th>
                        <th>CREDIT UNIT</th>
                        <th>SCORE</th>
                        <th>GRAPE POINT</th>
                        <th>CUM. GPA</th>
                    </tr>
               </thead>
               <tbody>';
     
     $crsCount = 0;
     $fTotal = count($r['first']);
     $sTotal = count($r['second']);
     $total = $fTotal + $sTotal;
     
     $f = current($r['first']);   
     
    $html .= "<tr>
             <td rowspan='{$fTotal}'>{$semSes}<br>Harmattan Semester</td>         
             <td>{$f['csid']}</td>
             <td width='50%'>{$f['csname']}</td>
             <td>{$f['unit']}</td>
             <td>{$f['score']}</td>
             <td>".substr($f['status'], 0, 1)."</td>
             <td></td>
            </tr>";
    
    $cgpaValue += gradepoint($f['unit'], $f['score'], $semSes, $sesGrad);
    $cgpaUnit += $f['unit'];
    $crsCount++;
    
    $f = next($r['first']); 
    $f = each($r['first']);
            
    for(; $f; $f = each($r['first'])) {
        
        $cgpaValue += gradepoint($f['value']['unit'], $f['value']['score'], $semSes, $sesGrad);
        $cgpaUnit += $f['value']['unit'];
        $crsCount++;
        
        $html .= "<tr>
                    <td>{$f['value']['csid']}</td>
                    <td width='50%'>{$f['value']['csname']}</td>
                    <td>{$f['value']['unit']}</td>
                    <td>{$f['value']['score']}</td>
                    <td>".substr($f['value']['status'], 0, 1)."</td>
                    <td></td>
                </tr>";        
    }

    $s = current($r['second']);   
     
     $html .= "<tr>
             <td rowspan='{$sTotal}'>{$semSes}<br>Harmattan Semester</td>         
             <td>{$s['csid']}</td>
             <td width='50%'>{$s['csname']}</td>
             <td>{$s['unit']}</td>
             <td>{$s['score']}</td>
             <td>".substr($s['status'], 0, 1)."</td>
             <td></td>
            </tr>";
    
    $cgpaValue += gradepoint($s['unit'], $s['score'], $semSes, $sesGrad);
    $cgpaUnit += $s['unit'];
    $crsCount++;
    
    $s = next($r['second']); 
    $s = each($r['second']);
    $cgpa = '';
    
    for(; $s; $s = each($r['second'])) {
        
        $cgpaValue += gradepoint($s['value']['unit'], $s['value']['score'], $semSes, $sesGrad);
        $cgpaUnit += $s['value']['unit'];
        $crsCount++;
        
        
        if($crsCount == $total) {
            if($cgpaUnit == 0)
                $cgpaUnit = 1;
            
            $cgpa = @number_format(round(($cgpaValue)/($cgpaUnit), 2), 2);
        }
        
        $html .= "<tr>
                    <td>{$s['value']['csid']}</td>
                    <td width='50%'>{$s['value']['csname']}</td>
                    <td>{$s['value']['unit']}</td>
                    <td>{$s['value']['score']}</td>
                    <td>".substr($s['value']['status'], 0, 1)."</td>
                    <td>{$cgpa}</td>
                </tr>";        
    }
    
    $html .= '
                   </tbody>
                </table>';
    
    $degreeAwarded = '..............................';
    $degreeClass = '..............................';
    if($totalRows_attn == $loopCount) {
        $degreeAwarded = $degree;
        $degreeClass = degreeClass($cgpa);
    }
    
    $html .= ''
            . '<div>DEGREE AWARDED:'.$degreeAwarded.'</div>'
            . '<div>CLASS OF DEGREE:'.$degreeClass.'</div>'
            . '<div>DATE OF AWARD:..................................</div>';

    $html .= '<br/><br/><div style="text-align:center">INTERPRETATION OF GRADES</div>';

    $html .= '<div >
            <div style="float:left;text-align:left; width:50%; font-size: 10pt;">
            <div style="text-align:center">FROM 1982 - 1992</div>
                <table class="table table-condensed" width="95%">
                    <tr>
                        <th colspan="2">LETTER GRADE</th>
                        <th>RAW MARK</th>
                        <th>CREDIT POINT</th>
                    </tr>
                    <tr>
                        <td>A+</td>
                        <td>=</td>
                        <td>70% - 100%</td>
                        <td>7</td>
                    </tr>
                    <tr>
                        <td>A</td>
                        <td>=</td>
                        <td>65% - 69%</td>
                        <td>6</td>
                    </tr>
                    <tr>
                        <td>B+</td>
                        <td>=</td>
                        <td>60% - 64%</td>
                        <td>5</td>
                    </tr>
                    <tr>
                        <td>B</td>
                        <td>=</td>
                        <td>55% - 59%</td>
                        <td>4</td>
                    </tr>
                    <tr>
                        <td>B-</td>
                        <td>=</td>
                        <td>50% - 54%</td>
                        <td>3</td>
                    </tr>
                    <tr>
                        <td>C+</td>
                        <td>=</td>
                        <td>45% - 49%</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>C</td>
                        <td>=</td>
                        <td>40% -44%</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>D</td>
                        <td>=</td>
                        <td>30% - 39%</td>
                        <td>0</td>
                    </tr>
                    <tr>
                        <td>F</td>
                        <td>=</td>
                        <td>0% - 29%</td>
                        <td>0</td>
                    </tr>
                </table>
            </div>

            <div style="float:left;text-align:right; width:50%; font-size: 10pt">
                <div style="text-align:center">FROM 1992 - DATE</div>
                <table class="table table-condensed" width="100%">
                    <tr>
                        <th colspan="2">LETTER GRADE</th>
                        <th>RAW MARK</th>
                        <th>CREDIT POINT</th>
                    </tr>
                    <tr>
                        <td>A</td>
                        <td>=</td>
                        <td>70% - 100%</td>
                        <td>5</td>
                    </tr>
                    <tr>
                        <td>B</td>
                        <td>=</td>
                        <td>60% -69%</td>
                        <td>4</td>
                    </tr>
                    <tr>
                        <td>C</td>
                        <td>=</td>
                        <td>50% -59%</td>
                        <td>3</td>
                    </tr>
                    <tr>
                        <td>D</td>
                        <td>=</td>
                        <td>45% - 49%</td>
                        <td>2</td>
                    </tr>
                    <tr>
                        <td>E</td>
                        <td>=</td>
                        <td>40% - 44%</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td>F</td>
                        <td>=</td>
                        <td>0% - 39%</td>
                        <td>0</td>
                    </tr>
                </table>
            </div>
            <div style="clear:both"></div>
            </div>';
    
    $mpdf->WriteHTML($html);
}

$mpdf->Output('Transcript.pdf','I');
exit;