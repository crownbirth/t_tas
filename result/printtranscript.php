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

$query_info = sprintf("SELECT sesname, colname, c.colid, deptname, "
        . "d.deptid, progname, p.progid, level, fname, lname, sex, stdid, admode, stname "
        . "FROM college c, department d, programme p, student s, session n, state st "
        . "WHERE c.colid = d.colid "
        . "AND d.deptid = p.deptid "
        . "AND p.progid = s.progid "
        . "AND s.stid = st.stid "
        . "AND n.sesid = s.sesid "
        . "AND s.stdid=%s",
        GetSQLValueString($student, "text"));
$info = mysql_query($query_info, $tams) or die(mysql_error());
$row_info = mysql_fetch_assoc($info);
$totalRows_info = mysql_num_rows($info);

$query_year1f = sprintf("SELECT r.csid, c.csname, c.semester, r.tscore+ r.escore as score, sesname, dc.status, unit "
        . "FROM `result` r, course c, session s, department_course dc, teaching t "
        . "WHERE stdid = %s "
        . "AND c.csid = r.csid "
        . "AND dc.csid = c.csid "
        . "AND dc.csid = r.csid "
        . "AND r.csid = t.csid "
        . "AND s.sesid = t.sesid "
        . "AND t.approve = 'yes' "
        . "AND s.sesid = r.sesid AND dc.progid=%s", 
					GetSQLValueString($student, "text"), 
					GetSQLValueString($row_info['progid'], "int"));
$year1f = mysql_query($query_year1f, $tams) or die(mysql_error());
$row_year1f = mysql_fetch_assoc($year1f);
$totalRows_year1f = mysql_num_rows($year1f);

$colname_attn = $student;

$query_attn = sprintf("SELECT s.sesname FROM session s, registration r WHERE r.status = 'Registered' AND s.sesid = r.sesid AND stdid = %s ORDER BY s.sesname ASC", GetSQLValueString($colname_attn, "text"));
$attn = mysql_query($query_attn, $tams) or die(mysql_error());
$row_attn = mysql_fetch_assoc($attn);
$totalRows_attn = mysql_num_rows($attn);

$colname_attn = $student;

mysql_select_db($database_tams, $tams);
$query_attn = sprintf("SELECT s.sesname FROM session s, registration r WHERE r.status = 'Registered' AND s.sesid = r.sesid AND stdid = %s ORDER BY s.sesname ASC", GetSQLValueString($colname_attn, "text"));
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

var_dump($results);
exit;
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

foreach($results as $count => $r) {
    if(!is_array($r))
        continue;
    
    $html = '
        <div >
            Academic Record
        </div>
        <div >
            <div style="float:left;text-align:left; width:20%; font-size: 16pt;">
                <table class="table table-condensed" width="20%">
                `   <tr>
                        <td>'.$row_info['lname'].'</td>
                    </tr>
                    <tr>
                        <td>'.$row_info['fname'].'</td>
                    </tr>
                </table>
            </div>
            <div style="float:left;text-align:center; width:18%; font-size: 16pt">
                <table class="table table-bordered table-condensed" width="16%">
                `   <tr>
                        <td>STUDENT NO.</td>
                    </tr>
                    <tr>
                        <td>'.$row_info['stdid'].'</td>
                    </tr>
                </table>
            </div>
            <div style="float:right;text-align:left; width:58%; font-size: 16pt">
                <table class="table table-bordered table-condensed">
                `   <tr>
                        <td>SEX</td>
                        <td>BIRTHDATE</td>
                        <td>DATE ADMITTED</td>
                        <td>MODE OF ADMISSION</td>
                    </tr>
                    <tr>
                        <td>'.(strtolower($row_info['stdid'])=='m'? 'MALE': 'FEMALE').'</td>
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

     $html .= '<tr>
             <td>'.$results[$count-1].'<br>Harmattan Semester</td>         
             <td colspan="6">
                <table class="">';

                foreach($r['first'] as $f) {
                    $html .= "<tr>
                        <td>{$f['csid']}</td>
                        <td width='50%'>{$f['csname']}</td>
                        <td>{$f['unit']}</td>
                        <td>{$f['score']}</td>
                        <td>".substr($f['status'], 0, 1)."</td>
                        <td></td>
                    </tr>";
                }
     $html .='           </table>
             </td>
    </tr>';

     $html .= '<tr>
             <td>'.$results[$count-1].'<br/>Rain Semester</td>        
             <td colspan="6">
                <table class="table table-condensed" width="100%">';

                foreach($r['second'] as $s) {
                    $html .= "<tr>
                        <td>{$s['csid']}</td>
                        <td width='50%'>{$s['csname']}</td>
                        <td>{$s['unit']}</td>
                        <td>{$s['score']}</td>
                        <td>".substr($s['status'], 0, 1)."</td>
                        <td></td>
                    </tr>";
                }
     $html .='           </table>
             </td>
    </tr>';
     
    $html .= '
                   </tbody>
                </table>';

    $html .= ''
            . '<div>DEGREE AWARDED:'.$degree.'</div>'
            . '<div>CLASS OF DEGREE:Second Class (Upper Division)</div>'
            . '<div>DATE OF AWARD:.................................</div>';

    $html .= '<br/><br/><div style="text-align:center">INTERPRETATION OF GRADES</div>';

    $html .= '<div >
            <div style="float:left;text-align:left; width:50%; font-size: 10pt;">
            <div style="text-align:center">FROM 1982 - 1992</div>
                <table class="table table-condensed" width="95%">
                `   <tr>
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
                        <td>65% - 68%</td>
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
                `   <tr>
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
    }
    
    //echo $html;
$mpdf->WriteHTML($html);

$mpdf->Output();
exit;