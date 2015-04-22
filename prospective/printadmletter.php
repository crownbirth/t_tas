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


if($row_rschk['formpayment'] == 'No' ) {
	header('Location: termsandcon.php');
}

    mysql_select_db($database_tams, $tams);
    $query_rspros = sprintf("SELECT p.*, pr.progname as progofferd
                                                    FROM prospective p 
                                                    JOIN programme pr ON p.progofferd = pr.progid
                                                    WHERE p.jambregid=%s",
                                                    GetSQLValueString(getSessionValue('MM_Username'), "text"));
    $rspros = mysql_query($query_rspros, $tams) or die(mysql_error());
    $row_rspros = mysql_fetch_assoc($rspros);
    $totalRows_rspros = mysql_num_rows($rspros);
    
$resumption = " ";
$submission = " ";  
if ($row_rspros['admtype'] == "UTME" && $row_rspros['batch'] ==1) {
$resumption = " 15th September, 2014 ";
$submission = "22nd September, 2014";}
else if ($row_rspros['admtype'] == "DE" && $row_rspros['batch'] ==1){
$resumption = " 13th October, 2014 ";
$submission = "20th October, ";
}
else if ($row_rspros['batch'] ==2){
$resumption = "23rd November, 2014 ";
$submission = "30th November, 2014 ";
}
else if ($row_rspros['batch'] ==3){
$resumption = "9th January, 2015 ";
$submission = "16th January, 2015 ";
}
else {
$resumption = "12th January, 2015 ";
$submission = "23rd January, 2015 ";
}
$jambscore = ($row_rspros['jambscore1']+$row_rspros['jambscore2']+$row_rspros['jambscore3']+$row_rspros['jambscore4']);
if ($jambscore <170) {
$jambscore = "Regularisation";} 
else {$jambscore = $jambscore;}
$university = 'TAI SOLARIN UNIVERSITY OF EDUCATION, IJAGUN, IJEBU-ODE';

include("../mpdf/mpdf.php");
$mpdf=new mPDF('c','A4','','',15,15,60,15,10,10); 
$stylesheet = file_get_contents('../css/mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet, 1);


$header = '<table width="100%" style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 14pt; color: #000088;">
<tr>
<td width="100%" align="center"><img src="../images/logo.jpg" width="100px" /></td>
</tr>
<tr>
<td width="100%" align="center">
<div style="font-weight: bold;">
<h2 style="font-size: 50pt;">'.$university.'</h2><br />
<h5 style="font-size: 9pt;">'.$university_address.'</h5>
</div>
</td>
</tr>
</table>';

$mpdf->SetHTMLHeader($header);
 
  $html = '<table class="table">
    <tr>
        <td>
            <p>Name : '.strtoupper($row_rspros['lname']).' '.$row_rspros['fname'].' '.$row_rspros['mname'].'</p>
            <p>Post-UTME Form Number : '.$row_rspros['formnum'].'</p>
            <p>JAMB Registration No : '.$row_rspros['jambregid'].'</p>
            <p>JAMB Score : '.$jambscore.' </p>
            <p>Post-UTME Score : '.$row_rspros['score'].'</p>
            <p>Aggregate Score : '.
(round(((($row_rspros['jambscore1']+$row_rspros['jambscore2']+$row_rspros['jambscore3']+$row_rspros['jambscore4'])/8)+($row_rspros['score']*5/3))))
        .'</p>
        </td>
    </tr>
    
    
    <tr>
        <td align="center">
            <p ><strong> PROVISIONAL OFFER OF ADMISSION</strong> </p>
        </td>
</tr>
    <tr>
      <td align = "justify"><br />
        <p>I  am pleased to inform you that you have been offered provisional admission into  the University to pursue a Full-Time Bachelors Degree in <strong> '.$row_rspros['progofferd'].'</strong> for  2014/2015 Academic Session.</p><br />

        <p>This  provisional admission is based on the assumption that you have read through the  requirements for the chosen course and possess the requisite qualifications as  stated in the University advertisement. </p><br />

      <p>  The  confirmation of the provisional admission is however subject to your fulfilling  the following conditions:</p><br />
        <ul>
        <ul>
          <li>Payment of a  non-refundable Acceptance Fee of thirty thousand naira (N30,000) only through the web pay platform not later than '.$resumption.' to secure the placement.</li><br />
          <li>Presentation of the original copy of the official receipt at the  Admissions Office and collection of an Acceptance form.</li><br />
          <li>Satisfaction of the minimum entry  qualification requirements for the course offered.</li><br />
          <li>Presentation of  2013 Ogun State Electronic Tax Clearance Certificate (e-TCC)/Ogun State Education  Levy Receipt of parents/sponsors whose name(s) were stated on the Post-UTME  form.</li><br />
          <li>Submission of the  following documents in an A4 envelope to the Admissions Office not later than '.$submission.' .</li>
       <ul>
<ul>
          <li>Photocopy of  official Acceptance Fee receipt </li>
          <li>Duly completed  Acceptance form</li>
          <li>Bio-data print  out from the portal,</li>
          <li>Photocopies of  credentials</li>
        </ul>
<ul>
        </ul>
        </ul> <br /> <br />
        <p align = "center">The  A4 envelope should be addressed to the Admissions  Officer with your  full name, JAMB Registration Number, Post-UTME  Form Number and Course indicated at the back of the envelope.</p><br />
        <p align = "center"> After fulfilling the above conditions, you are to proceed to your  department for clearance formalities after which you will proceed to pay school  fees. </p> <br />
        <p align = "center"><strong>Candidates are to note that they are not  yet bonafide students of Tai Solarin University of Education until they fulfill all admissions requirements and are duly  cleared by the departments and Admissions office. Also, scores supplied by candidates would be verified by JAMB.</strong></p><br />
<p align = "center">The University has limited hostel facility for female students on first come first served basis. The hostel situated on the University campus has facilities like borehole water, standby generator for electricity, and adequate security. Interested students should register with the Dean, Students Affairs after clearance.</p><br />
        <p>&nbsp;</p>
        <p>Congratulations.!</p>
        <p>&nbsp;</p><br />
        <p><strong>Dr &lsquo;Femi Kayode, JP.</strong><br />
        <strong> REGISTRAR</strong></p></td>
    </tr>
    
    
    
    
</table>';
   
$mpdf->WriteHTML($html);
$mpdf->Output('Admission Letter.pdf', 'I');

exit;
?>