<?php 
require_once('../../Connections/tams.php');
require_once('../../functions/function.php');
require_once('../../param/param.php');
?>
<?php
//initialize the session
if (!isset($_SESSION)) {
  session_start();
}

// ** Logout the current user. **
$logoutAction = $_SERVER['PHP_SELF']."?doLogout=true";
if ((isset($_SERVER['QUERY_STRING'])) && ($_SERVER['QUERY_STRING'] != "")){
  $logoutAction .="&". htmlentities($_SERVER['QUERY_STRING']);
}

$MM_authorizedUsers = "";
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

$order_no = "-1";
if(isset($_GET['no'])){
    $order_no = $_GET['no'];
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
$query_verify = sprintf("SELECT * "
                            . "FROM prospective "
                            . "WHERE jambregid = %s", 
                            GetSQLValueString(getSessionValue('MM_Username'), 'text'));
$verify = mysql_query($query_verify, $tams) or die(mysql_error());
$row_verify = mysql_fetch_assoc($verify);
$totalRows_verify = mysql_num_rows($verify);

$query_history = sprintf('SELECT matric_no, can_name, ordid, status, reference, amt, date_time '
        . 'FROM schfee_transactions '
        . 'WHERE matric_no = %s'
        . ' AND ordid = %s '
        . ' ORDER BY date_time DESC', GetSQLValueString($_SESSION['MM_Username'], "int"));
$history = mysql_query($query_history, $tams) or die(mysql_error());
$row_history = mysql_fetch_assoc($history);
$totalRows_history = mysql_num_rows($history);

if($row_verify['verified'] == 'FALSE') {
    header('Location: alreadyPaid.php');
}


$ch = curl_init();
$url="https://cipg.diamondbank.com/cipg/MerchantServices/UpayTransactionStatus.ashx?MERCHANT_ID=00456&ORDER_ID={$row_history['matric_no']}";
curl_setopt($ch, CURLOPT_URL, $url);

// Set so curl_exec returns the result instead of outputting it.
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Get the response and close the channel.
$response = curl_exec($ch);
$response=(string)$response;

list($id,$Mid, $canNoo, $stat, $statcode, $amt, $mydate, $tranref, $payref, $paygw,$responseCode,$responseDes,$currCode) = explode("  ", $response);

$university = 'Tai Solarin University of Education';

include("../../mpdf/mpdf.php");
$mpdf=new mPDF('c','A4','','',10,10,50,15,5,5); 
$stylesheet = file_get_contents('../../css/mpdfstyletables.css');
$mpdf->WriteHTML($stylesheet, 1);

$header = '<table width="100%" style="border-bottom: 1px solid #999999; vertical-align: top; font-family: serif; font-size: 9pt; color: #000088;">
<tr>
<td width="15%" align="left"><img src="../../images/logo.jpg" width="100px" /></td>
<td width="85%" align="center">
<div style="font-weight: bold;">
<h2 style="font-size: 25pt">'.$university.'</h2>
<h5 style="font-size: 9pt">'.$university_address.'</h5></div>
</td>
</tr>
</table>';

$mpdf->SetHTMLHeader($header);

$html .='<p style="text-align:center; font-size: 20pt; margin-bottom: 30px"><strong> POST UTME/DE APPLICATION RECEIPT</strong></p>
<div style="text-align:center; width:100%; font-size: 20pt">
    <table align="center" style="width: 60%;" class="table table-bordered table-striped">
        <tr>
            <td width="100">Name:</td>
            <td width="400">'.$row_history['can_name'].'</td>
            <td rowspan="4">
                <barcode code="'
                                                .$row_history['matric_no'].' '
                                                .$row_history['can_name'].' '
                                                .'" type="QR" class="barcode" size="1.3" error="M" />
            </td>
        </tr>
        
        <tr>
            <td>UTME Reg No .:</td>
            <td>'.$row_history['matric_no'].'</td>
        </tr>
        <tr>
            <td>Description:</td>
            <td>POST UTME/DE APPLICATION PAYMENT</td>
            <td></td>
        </tr>
        
        <tr>
            <td>Transaction Reference:</td>
            <td>'.$tranref.'</td>
            <td></td>
        </tr>
        
        <tr>
            <td>Date:</td>
            <td>'.$mydate.'</td>
            <td></td>
        </tr>

        <tr>
            <td>Amount:</td>
            <td>'.$mydate.'</td>
            <td></td>
        </tr>
    </table>

</div>';

$mpdf->WriteHTML($html);
$mpdf->Output('receipt.pdf', 'I');

exit;
?>