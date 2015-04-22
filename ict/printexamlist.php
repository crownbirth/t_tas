<?php require_once('../Connections/tams.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "20";
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

$sesname = $row_rssess['sesname'];

$query_dept = "SELECT * FROM `department`";
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);

$query_duration = "SELECT MAX(duration) as max FROM `programme`";
$duration = mysql_query($query_duration, $tams) or die(mysql_error());
$row_duration = mysql_fetch_assoc($duration);
$totalRows_duration = mysql_num_rows($duration);

$deptid = -1;
if(isset($_POST['did'])) {
    $deptid = $_POST['did'];
}

$level = 1;
if(isset($_POST['lvl'])) {
    $level = $_POST['lvl'];
}
$query_stud = sprintf("SELECT *, sum(percentPaid) as percent FROM student s "
                    . "JOIN programme p ON p.progid = s.progid "
                    . "JOIN department d ON d.deptid = p.deptid "
                    . "JOIN schfee_transactions st ON s.stdid = st.matric_no "
                    . "WHERE d.deptid = %s AND s.level = %s "
                    . "GROUP BY stdid "
                    . "HAVING percent >= 100",
                    GetSQLValueString($deptid, "int"),
                    GetSQLValueString($level, "int"));
$stud = mysql_query($query_stud, $tams) or die(mysql_error());
$row_stud = mysql_fetch_assoc($stud);
$totalRows_stud = mysql_num_rows($stud);

$university = 'TAI SOLARIN UNIVERSITY OF EDUCATION';


include("../mpdf/mpdf.php");
$mpdf=new mPDF('c','A4','','',10,10,65,15,15,5); 
$stylesheet = file_get_contents('../css/mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet, 1);

$header = '<table width="100%" style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 9pt; color: #000088;">
<tr>
<td width="90%" align="center"><img src="../images/logo.jpg" width="100px" /></td></tr>
<tr>
<td width="90%" align="center">
<div style="font-weight: bold;">
<h2 style="font-size: 21pt">'.$university.'</h2>

<h5 style="font-size: 9pt">'.$university_address.'</h5></div>
</td>
</tr>
</table>';

$mpdf->SetHTMLHeader($header);

$html = '<p style="border-bottom: 1px solid #999999; font-size: 9pt;">No record available for this department!</p>';

if($totalRows_stud > 0) {
    $html ='<p style="text-align:center; font-size: 18pt; margin-bottom: 20px"><strong> Paid/Exam list '.$sesname.' ('.$level.'00 Level)'
            .' </strong></p>
    <div style="text-align:center; width:100%; font-size: 20pt">
        <table width="670" class="table table-striped table-condensed">
            <thead>
                <tr>
                  <th>S/N</th>
                  <th>Matric No</th>
                  <th>Name</th>                            
                  <th>Programme</th>
                  <th>Remark</th>
                </tr>
            </thead>
            <tbody>';
    
    for($idx = 1; $idx <= $totalRows_stud; $idx++, $row_stud = mysql_fetch_assoc($stud)) {
        
        $html .= '<tr>
                    <td>'.$idx.'</td>
                    <td>'.$row_stud['stdid'].'
                    </td>
                    <td>'.$row_stud['fname'].' '.$row_stud['lname'].'
                    </td>
                    <td width="200">'.$row_stud['progname'].'</td>
                    <td></td>                             
                </tr>';
    }
    
}

$html .= ' </tbody>
        </table>
    </div>';

$mpdf->WriteHTML($html);
$mpdf->Output('examlist.pdf', 'I');

exit;