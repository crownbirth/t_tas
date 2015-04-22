<?php 
//initialize the session
if (!isset($_SESSION)) {
  session_start();
}

require_once('../../Connections/tams.php');
require_once('../../functions/function.php');
require_once('../../param/param.php');

$MM_authorizedUsers = "11";
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



   if(isset($_POST['from']) && ($_POST['from']!=NULL)){
       mysql_select_db($database_tams, $tams);
        $query = sprintf("SELECT olv.stdid, olv.level, olv.exam_type,olv.exam_no, p.progname, olv.exam_year, s.fname, s.lname "
                        . "FROM olevel_veri_data olv, student s,  programme p "
                        . "WHERE olv.stdid = s.stdid "
                        . "AND olv.progid = p.progid "
                        . "AND olv.treated = 'Yes' "
                        . "AND olv.approve = 'Yes' "
                        . "AND olv.date_treated >= %s AND olv.date_treated <= %s",
                        GetSQLValueString($_POST['from'], 'date'),
                        GetSQLValueString($_POST['to'], 'date'));
        $treated = mysql_query($query, $tams) or die(mysql_error());
        $row_treated = mysql_fetch_assoc($treated);
        $totalRows_treated = mysql_num_rows($treated);
        
   }

$university = 'Tai Solarin University of Education';

include("../../mpdf/mpdf.php");
$mpdf=new mPDF('c','A4','','',10,10,85,15,10,5); 
$stylesheet = file_get_contents('../../css/mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet, 1);

$header = '<table width="100%" style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 9pt; color: #000088;">
<tr>
<td width="90%" align="center"><img src="../../images/logo.jpg" width="120px" /></td>
</tr>
<tr>
<td width="90%" align="center">
<div style="font-weight: bold;">
<h2 style="font-size: 25pt">'.$university.'</h2>
<h5 style="font-size: 9pt">'.$university_address.'</h5><br /><br /></div>
</td>
</tr>
</table>
<p style="text-align:center; font-size: 15pt; margin-bottom: 10px"><strong>O`LEVEL VERIFICATION REPORT </strong></p>
<p style="text-align:center; font-size: 10pt; margin-bottom: 5px"> From :'.$_POST['from'].'     To: '.$_POST['to'].'</p>';

$mpdf->SetHTMLHeader($header);

//$html = '<p style="border-bottom: 1px solid #999999; font-size: 9pt;">There is no known order with this Order No. '.$order_no.'!</p>';

//if($totalRows_history > 0) {
    $html ='
        <div style="text-align:center; width:100%; font-size: 20pt">
        <table class="table table-bordered table-condensed table-striped table-hover">
                      <thead>
                          <tr>
                              <th width="70">Reg No</th>
                              <th  width="200">Name</th>
                              <th>Programme</th>
                              <th>Level</th>
                              <th width="70">Exam Type</th>
                              <th width="50">Exam Year</th>
                              <th width="50">Exam No</th>
                          </tr>
                      </thead>
                      <tbody >';
    do{ 
            $i = 1;
            $html .=    '<tr>  
                            <td>'.$row_treated['stdid'].'</td>
                            <td>'.$row_treated['fname'].' '.$row_treated['lname'].'</td>
                            <td>'.$row_treated['progname'].'</td>
                            <td>'.$row_treated['level'].'</td>    
                            <td>'.$row_treated['exam_type'].'</td> 
                            <td>'.$row_treated['exam_year'].'</td> 
                            <td>'.$row_treated['exam_no'].'</td>    
                        </tr>';
    }while($row_treated = mysql_fetch_assoc($treated));  
                     $html .='</tbody>
                    </table>

    </div>';


$mpdf->WriteHTML($html);
$mpdf->Output('olevel verification From '.$_POST['from'].' To '.$_POST['to'].'.pdf', 'I');

exit;
?>