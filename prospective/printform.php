<?php 
if (!isset($_SESSION)) {
  session_start();
}

require_once('../Connections/tams.php');
require_once('../param/param.php');
require_once('../functions/function.php');

$MM_authorizedUsers = "11,20,21,22,23";
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
    $query = sprintf("SELECT * FROM session ORDER BY sesid DESC LIMIT 1 ");
    $session= mysql_query($query, $tams) or die(mysql_error());
    $row_session = mysql_fetch_assoc($session);
    $totalRows_session = mysql_num_rows($session);
    
    //set the new Admission session Name
    $split = explode('/',  $row_session['sesname']);
    $adm_ses_name = ($split[0]-1).'/'.($split[1]-1);

mysql_select_db($database_tams, $tams);
$query_rschk = sprintf("SELECT jambregid, admtype, formsubmit, formpayment 
						FROM prospective p 
						WHERE p.jambregid=%s",
						GetSQLValueString(getSessionValue('MM_Username'), "text"));
$rschk = mysql_query($query_rschk, $tams) or die(mysql_error());
$row_rschk = mysql_fetch_assoc($rschk);
$totalRows_rschk = mysql_num_rows($rschk);

if($row_rschk['formpayment'] == 'No' ) {
	header('Location: termsandcon.php');
}
if($row_rschk['admtype']=='DE'){
    mysql_select_db($database_tams, $tams);
    $query_rspros = sprintf("SELECT p.*, st.stname,  pr.progname AS prog1, pr2.progname AS prog2 
                                                    FROM prospective p 
                                                    JOIN programme pr ON p.progid1 = pr.progid
                                                    JOIN programme pr2 ON p.progid2 = pr2.progid
                                                    JOIN state st ON st.stid = p.stid
                                                    WHERE p.jambregid=%s",
                                                    GetSQLValueString($row_rschk['jambregid'], "text"));
    $rspros = mysql_query($query_rspros, $tams) or die(mysql_error());
    $row_rspros = mysql_fetch_assoc($rspros);
    $totalRows_rspros = mysql_num_rows($rspros);
}
else{
    mysql_select_db($database_tams, $tams);
    $query_rspros = sprintf("SELECT p.*, st.stname, sbj1.subjname as jamb1, sbj2.subjname as jamb2, sbj3.subjname as jamb3, sbj4.subjname as jamb4, pr.progname AS prog1, pr2.progname AS prog2 
                                                    FROM prospective p 
                                                    JOIN programme pr ON p.progid1 = pr.progid
                                                    JOIN programme pr2 ON p.progid2 = pr2.progid
                                                    JOIN subject sbj1 ON p.jambsubj1 = sbj1.subjid
                                                    JOIN subject sbj2 ON p.jambsubj2 = sbj2.subjid
                                                    JOIN subject sbj3 ON p.jambsubj3 = sbj3.subjid
                                                    JOIN subject sbj4 ON p.jambsubj4 = sbj4.subjid
                                                    JOIN state st ON st.stid = p.stid
                                                    WHERE p.jambregid=%s",
                                                    GetSQLValueString($row_rschk['jambregid'], "text"));
    $rspros = mysql_query($query_rspros, $tams) or die(mysql_error());
    $row_rspros = mysql_fetch_assoc($rspros);
    $totalRows_rspros = mysql_num_rows($rspros);
}



$jambtotal = ($row_rspros['jambscore1']+$row_rspros['jambscore2']+$row_rspros['jambscore3']+$row_rspros['jambscore4']);

mysql_select_db($database_tams, $tams);
$query_rssit1 = sprintf("SELECT * 
						FROM olevel o 
						JOIN olevelresult l ON o.olevelid = l.olevelid 
						JOIN subject s ON l.subject = s.subjid 
						JOIN grade g ON l.grade = g.grdid 
						WHERE o.jambregid=%s
						AND sitting='first'",
						GetSQLValueString(getSessionValue('MM_Username'), "text"));
$rssit1 = mysql_query($query_rssit1, $tams) or die(mysql_error());
$row_rssit1 = mysql_fetch_assoc($rssit1);
$totalRows_rssit1 = mysql_num_rows($rssit1);

mysql_select_db($database_tams, $tams);
$query_rssit2 = sprintf("SELECT * 
						FROM olevel o 
						JOIN olevelresult l ON o.olevelid = l.olevelid 
						JOIN subject s ON l.subject = s.subjid 
						JOIN grade g ON l.grade = g.grdid 
						WHERE o.jambregid=%s
						AND sitting='second'",
						GetSQLValueString(getSessionValue('MM_Username'), "text"));

$rssit2 = mysql_query($query_rssit2, $tams) or die(mysql_error());
$row_rssit2 = mysql_fetch_assoc($rssit2);
$totalRows_rssit2 = mysql_num_rows($rssit2);


$imgname = $row_rspros['jambregid'];

$imgnameUpper = $imgname;
$imgnameLower = strtolower($imgname);
$image_url = '../images/student/profile.png';
$image = array("../images/student/%s.jpg", 
                "../images/student/%s.JPG",
                 "../images/student/%s.jpeg",
                "../images/student/%s.JPEG", 
                "../images/student/%s.png",
                "../images/student/%s.PNG");

$count = count($image);
for($idx = 0; $idx < $count; $idx++) {
    if(file_exists(sprintf($image[$idx], $imgnameLower))) {
        $image_url = sprintf($image[$idx], $imgnameLower);
        break; 
    }elseif(file_exists(sprintf($image[$idx], $imgnameUpper))) {
        $image_url = sprintf($image[$idx], $imgnameUpper);
        break;
    }
}



$university = 'Tai Solarin University of Education';

include("../mpdf/mpdf.php");
$mpdf=new mPDF('c','A4','','',10,10,32,15,5,5); 
$stylesheet = file_get_contents('../css/mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet, 1);

$header = '<table width="100%" style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 9pt; color: #000088;">
<tr>
<td width="15%" align="left"><img src="../images/logo.jpg" width="100px" /></td>
<td width="85%" align="center">
<div style="font-weight: bold;">
<h2 style="font-size: 25pt">'.$university.'</h2>
<h5 style="font-size: 9pt">'.$university_address.'</h5></div>
</td>
</tr>
</table>';

$mpdf->SetHTMLHeader($header);
 
   $html .= '<table align="center" width="690">
       <tr>
        <td align="center">
           <span> <p style="alignment-adjust: central">2014/2015 UTME/DE APPLICATION FORM - '.$row_rspros['formnum'].'</p></span>
            <table width="670" class="table  table-bordered">
                <tr>
                    <td colspan="2">
                        <table width="670" class="table table-hover table-striped table-bordered">
                            <thead>
                            <tr>
                                <th colspan="4"> BIO-DATA</th>
                            </tr>
                            </thead>    
                            <tr>
                                <th width="120">Surname :</th>
                                <td colspan="2">'.$row_rspros['lname'].'</td>
                                <td rowspan="5" align="center"> <img  style="alignment-adjust: central"src="'.$image_url.'" alt="Image"  id="placeholder" name="placeholder" width="160" height="160" align="top"/></td> 
                            </tr>
                            <tr>
                                <th>First Name :</th>
                                <td colspan="2">'.$row_rspros['fname'].' </td>
                            </tr>
                            <tr>
                                <th>Middle Name :</th>
                                <td colspan="2">'.$row_rspros['mname'].'</td>
                            </tr>
                            <tr>
                                <th>Email :</th>
                                <td colspan="2">'.$row_rspros['email'].'</td>
                            </tr>
                            <tr>
                                <th>Phone :</th>
                                <td colspan="2">'.$row_rspros['phone'].'</td>
                            </tr>
                            <tr>
                                <th>State of Origin :</th>
                                <td colspan="2">'.$row_rspros['stname'].'</td>
                                <td><strong>Sex : </strong>'.getSex($row_rspros['Sex']).' </td>
                            </tr>
                            <tr>
                                <th>Address :</th>
                                <td colspan="2">'.$row_rspros['address'].'</td>
                                <td></td>    
                            </tr>
                        </table>
                    </td>
                </tr>';
   
   
                $html.= 
                        '<tr>
                            <td>';
                                if($row_rspros['admtype']== 'UTME'){
                                    $html .='
                                                    <table width="380" class="table table-hover table-striped table-bordered">
                                                        <tr>
                                                            <th colspan="2"> UTME RESULT</th>
                                                        </tr>
                                                        <tr>
                                                            <td>UTME Reg No. :</td>
                                                            <td align="left">'.$row_rspros['jambregid'].'</td>
                                                        </tr>
                                                        <tr>
                                                            <td>UTME Year. : </td>
                                                            <td align="left">'.$row_rspros['jambyear'].'</td>
                                                        </tr>
                                                        <tr>
                                                            <th colspan="2" align="center">Subjects / Scores </th>

                                                        </tr>
                                                        <tr>
                                                            <td>'.$row_rspros['jamb1'].'</td>
                                                            <td align="left">'.$row_rspros['jambscore1'].'</td>
                                                        </tr>
                                                        <tr>
                                                            <td>'.$row_rspros['jamb2'].'</td>
                                                            <td align="left">'.$row_rspros['jambscore2'].'</td>
                                                        </tr>
                                                        <tr>
                                                            <td>'.$row_rspros['jamb3'].'</td>
                                                            <td align="left">'.$row_rspros['jambscore3'].'</td>
                                                        </tr>
                                                        <tr>
                                                            <td>'.$row_rspros['jamb4'].'</td>
                                                            <td align="left">'.$row_rspros['jambscore4'].'</td>
                                                        </tr>
                                                         <tr>
                                                            <th>Aggregate </th>
                                                            <td style="color:green; font-weight: bold">'.$jambtotal.'</td>
                                                        </tr>
                                                    </table>
                                                ';}
                                elseif($row_rspros['admtype']=='DE'){
                                        $html .= '
                                                <table width="380" class="table table-hover table-striped table-bordered">
                                                    <tr>
                                                        <th colspan="2"> DIRECT ENTRY </th>
                                                    </tr>
                                                    <tr>
                                                        <td>UTME Reg No.</td>
                                                        <td align="left">'.$row_rspros['jambregid'].'</td>
                                                    </tr>
                                                    <tr>
                                                        <td>UTME Year.</td>
                                                        <td align="left">'.$row_rspros['jambyear'].'</td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2" style="font-weight: bold" align="center"> Previous Qualification </td>
                                                    </tr>
                                                    <tr>
                                                        <td>School Name :</td>
                                                        <td align="left">'.$row_rspros['deschname'].'</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Graduation year :</td>
                                                        <td align="left">'.$row_rspros['degradyear'].'</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Grade : </td>
                                                        <td align="left">
                                                            '.getDeGrade($row_rspros['degrade']).'
                                                        </td>
                                                    </tr>
                                                </table>';}
                           $html.= '</td>
                                    <td>
                                        <table width="470" class="table table-hover table-striped table-bordered">
                                            <tr>
                                                <th colspan="2">Programe Choices</th>
                                            </tr>
                                            <tr>
                                                <th width="150">1st choice programme: </th>
                                                <td>'.$row_rspros['prog1'].'</td>
                                            </tr>
                                            <tr>
                                                <th>2nd choice programme: </th>
                                                <td>'.$row_rspros['prog2'].'</td>
                                            </tr>
                                        </table>
                                        
                                        <table width="470" class="table table-hover table-striped table-bordered">
                                            <tr>
                                                <th colspan="2">Spnosor Details</th>
                                            </tr>
                                            <tr>
                                                <th width="120">Sponsor\'s Name: </th>
                                                <td>'.$row_rspros['sponsorname'].'</td>
                                            </tr>
                                            <tr>
                                                <th>Phone : </th>
                                                <td>'.$row_rspros['sponsorphn'].'</td>
                                            </tr>
                                            <tr>
                                                <th>Address  : </th>
                                                <td>'.$row_rspros['sponsoradrs'].'</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>';
                           
                            
                
   
                
                $html .= '<tr>
                    <td colspan="2"></td>
                </tr>
               
              <tr>
                  <th colspan="2" align="center" > O\'LEVEL</th>
              </tr>
              <tr>
                <td>
                	<table width="345" class="table table-hover table-striped table-bordered">
                    	<tr><th colspan="2">First Sitting</th></tr>
                         <tr>
                            <td>Exam. No. :'.$row_rssit1['examnumber'].' </td>
                            <td>Exam. Year. : '.$row_rssit1['examyear'].'</td>
                        </tr>
                        <tr>
                            <td colspan="2">Exam. Type. :'.$row_rssit1['examtype'].' </td>
                        </tr>';
                
                        
                if($totalRows_rssit1 > 0) {
                        for($i = 0; $i < $totalRows_rssit1; $i++){
						
                        $html .= '<tr>
                        	<td>'. $row_rssit1['subjname'].'</td>
                            <td>'. $row_rssit1['grdname'].'</td>
                        </tr>';
                    $row_rssit1 = mysql_fetch_assoc($rssit1);
                }}else{
                        $html .= '<tr><td colspan="2">No result</td></tr>';
                }
                    $html .= '</table>                    
                </td>
                <td>
                	<table width="345" class="table table-hover table-striped table-bordered">
                    	<tr><th colspan="2">Second Sitting</th></tr>
                        <tr>
                            <td>Exam. No. :'.$row_rssit2['examnumber'].' </td>
                            <td>Exam. Year. : '.$row_rssit2['examyear'].'</td>
                        </tr>
                        <tr>
                            <td colspan="2">Exam. Type. :'.$row_rssit2['examtype'].' </td>
                        </tr>';					
		
                    if($totalRows_rssit2 > 0) {
			for($i = 0; $i < $totalRows_rssit2; $i++){
                            
                        $html .= '<tr>
                        	<td>'. $row_rssit2['subjname'].'</td>
                            <td>'. $row_rssit2['grdname'].'</td>
                        </tr>';
                        $row_rssit2 = mysql_fetch_assoc($rssit2);
                        }
                    }else{
						
                        $html .= '<tr><td colspan="2">No result</td></tr>';
                    }
                    $html .= '</table>
                </td>
              </tr>
             
            </table>
        </td>
      </tr>
    </table>';
   
$mpdf->WriteHTML($html);
$mpdf->Output('Post_utme_form.pdf', 'I');

exit;
