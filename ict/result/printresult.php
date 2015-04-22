<?php 
//initialize the session
if (!isset($_SESSION)) {
  session_start();
}

require_once('../../Connections/tams.php');
require_once('../../functions/function.php');
require_once('../../param/param.php');

$MM_authorizedUsers = "20,21";
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

function getRmk($val){
    $result = '';
    if($val >39){
        $result = 'PASS';
    }else{
        $result = 'FAIL';
    }
    return $result;
}
    mysql_select_db($database_tams, $tams);
    $query1 = sprintf("SELECT progname FROM programme WHERE progid=%s",  GetSQLValueString($_POST['progid'], "text"));
    $dept = mysql_query($query1, $tams) or die(mysql_error());
    $row_dept = mysql_fetch_assoc($dept);
    
    mysql_select_db($database_tams, $tams);
    $query1 = sprintf("SELECT sesname FROM session WHERE sesid=%s",  GetSQLValueString($_POST['sesid'], "text"));
    $session = mysql_query($query1, $tams) or die(mysql_error());
    $row_session = mysql_fetch_assoc($session);

   if(isset($_POST['MM_Insert']) && ($_POST['MM_Insert']== 'form1')){
       mysql_select_db($database_tams, $tams);
       mysql_query("SET SQL_BIG_SELECTS=1");
        $query = sprintf("SELECT DISTINCT rs.*, st.fname,st.lname,st.mname, p.progname "
                . "FROM result rs, student st, programme p, schfee_transactions sf "
                . "WHERE rs.stdid = st.stdid "
                . "AND st.progid = p.progid "
                . "AND rs.csid = %s "
                . "AND rs.sesid= %s "
                . "AND rs.stdid = sf.matric_no "
                . "AND sf.status = 'APPROVED' "
                . "AND st.progid = %s  ORDER BY rs.stdid ASC ",
            //  . "AND st.level = %s",
                GetSQLValueString($_POST['csid'], "text"),
                GetSQLValueString($_POST['sesid'], "text"),
                GetSQLValueString($_POST['progid'], "text"));
                // GetSQLValueString($_POST['level'], "text")) ;
        $result = mysql_query($query, $tams) or die(mysql_error());
        $row_result = mysql_fetch_assoc($result);
        $totalRows_result = mysql_num_rows($result);
        
   }

$university = 'Tai Solarin University of Education';

include("../../mpdf/mpdf.php");
$mpdf=new mPDF('c','A4','','',15,15,80,15,5,45); 
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
<p style="text-align:center; font-size: 15pt; margin-bottom: 10px"><strong>'.$row_session['sesname'].' &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; E- EXAM RESULT &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;'.$_POST['csid'].' </strong></p>
<p style="text-align:center; font-size: 15pt; margin-bottom: 5px">Department of  '.strtoupper($row_dept['progname']).' </p> <br />';

$mpdf->SetHTMLHeader($header);

//$html = '<p style="border-bottom: 1px solid #999999; font-size: 9pt;">There is no known order with this Order No. '.$order_no.'!</p>';

//if($totalRows_history > 0) {
    $html ='
        <div style="text-align:center; width:100%; font-size: 20pt">
        <table class="table table-bordered table-condensed table-striped table-hover">
                      <thead>
                          <tr>
                              <th width="50">S/N</th>
                              <th  width="120">Matric</th>
                              <th>Full Name</th>
                              <th width="60">C.A</th>
                              <th width="60">Exam</th>
                              <th width="60">Total</th>
                              <th width="70">Remark</th>
                          </tr>
                      </thead>
                      <tbody >';
                    if($totalRows_result > 0){
                        $i = 1;
                        do{
                            $html .=    '<tr>  
                                            <td>'.$i++.'</td>
                                            <td>'.$row_result['stdid'].'</td>
                                            <td>'.strtoupper($row_result['lname']).',  '.ucfirst(strtolower($row_result['fname'])).'</td>
                                            <td>'.$row_result['tscore'].'</td>    
                                            <td>'.$row_result['escore'].'</td> 
                                            <td>'.($row_result['tscore'] + $row_result['escore']).'</td> 
                                            <td>'.getRmk(($row_result['tscore'] + $row_result['escore'])).'</td>    
                                        </tr>';
                        }while($row_result = mysql_fetch_assoc($result));  
                    }else{
                        $html .='<tr><td colspan="7" align="center"><p style="color: red"> No Result Available for the Query you selected </p></td></tr>';
                    }
   
                    $html .='</tbody>
                    </table>

    </div>';


$mpdf->WriteHTML($html);
$mpdf->Output(''.$_POST['csid'].' E-exam result'.'pdf', 'I');

exit;
?>