<?php 
require_once('../../../Connections/tams.php');
if (!isset($_SESSION)) {
  session_start();
}
require_once('../../../param/param.php');
require_once('../../../functions/function.php');


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
//Get current session 
mysql_select_db($database_tams, $tams);
$query = sprintf("SELECT * FROM session ORDER BY sesid DESC LIMIT 1 ");
$session= mysql_query($query, $tams) or die(mysql_error());
$row_session = mysql_fetch_assoc($session);
$totalRows_session = mysql_num_rows($session);

$row_rspros = '';
$totalRows_rspros = '';

if(getAccess()== '10'){
    
    mysql_select_db($database_tams, $tams);
    $query_rspros = sprintf("SELECT *  
                                    FROM student  
                                    WHERE stdid=%s",
                                    GetSQLValueString(getSessionValue('MM_Username'), "text"));
    $rspros = mysql_query($query_rspros, $tams) or die(mysql_error());
    $row_rspros = mysql_fetch_assoc($rspros);
    $totalRows_rspros = mysql_num_rows($rspros);

}else{
    
    mysql_select_db($database_tams, $tams);
    $query_rspros = sprintf("SELECT p.*  
                                                   FROM prospective p 
                                                   WHERE p.jambregid=%s",
                                                   GetSQLValueString(getSessionValue('MM_Username'), "text"));
    $rspros = mysql_query($query_rspros, $tams) or die(mysql_error());
    $row_rspros = mysql_fetch_assoc($rspros);
    $totalRows_rspros = mysql_num_rows($rspros);
}


$amount=300;
/*
if($row_rspros['regtype']=='regular'){
   $amount=5200; 
}
elseif($row_rspros['regtype']=='coi'){
    $amount=10200;
}
*/

$percent = 100;
$revhead = 'HKC026';

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root.'/prospective' );   
}

$std= getSessionValue('MM_Username');

$cyr=date('Y');
mysql_select_db($database_tams, $tams);
 $query_paid= sprintf("SELECT * FROM olevelverifee_transactions  WHERE year='".$cyr."' AND status='APPROVED' AND card_submit = 'No' AND can_no='".$std."'") ;
$paid= mysql_query($query_paid, $tams) or die(mysql_error());
$row_paid = mysql_fetch_assoc($paid);
$total_paid= mysql_num_rows($paid);

if ($total_paid>0 ) {
//echo $total_paid;
//means this candidate has paid
header('Location: ../../index.php');
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/template.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php require('../../../param/site.php'); ?>
<title><?php echo $university ?> </title>
<!-- InstanceEndEditable -->
<link href="../../../css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
<link href="../../../css/menulink.css" rel="stylesheet" type="text/css" />
<link href="../../../css/footer.css" rel="stylesheet" type="text/css" />
<link href="../../../css/sidemenu.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div class="container">
  <div class="header">
    <!-- end .header -->
</div>
  <div class="topmenu">
<?php include '../../../include/topmenu.php'; ?>
  </div>
  <!-- end .topmenu --> 
  
  <div class="loginuser">
  <?php include '../../../include/loginuser.php'; ?>
  
  
  <!-- end .loginuser --></div>
  <div class="pagetitle">
    <table width="600">
      <tr>
        <td><!-- InstanceBeginEditable name="pagetitle" -->Payment Confirmation <!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../../../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
        <table width="690" >
            <tr>
                <td colspan="2">
                    <p>Bellow is the details of the payment transaction you are
                        about to execute click the the <strong>Pay Now</strong> 
                        button to proceed your payment or <strong>Cancel</strong> button to terminate 
                    </p>
                    <table width="690" class="table table-striped table-bordered table-condensed">
                        <tr>
                            <th width="150">Reg.No : </th>
                            <td><?php echo (getAccess()== '10')?$row_rspros['stdid'] : $row_rspros['jambregid'] ?></td>
                        </tr>
                        <tr>
                            <th>Full Name : </th>
                            <td><?php echo strtoupper($row_rspros['lname'].' '.$row_rspros['fname']) ?></td>
                        </tr>
                        <tr>
                            <th>Level : </th>
                            <td><?php echo (getAccess()== '10')?$row_rspros['level'].'00' : $row_rspros['admtype']?> </td>
                        </tr>
                         <tr>
                            <th>Payment Type : </th>
                            <td>O'LEVEL VERIFICATION FEE</td>
                        </tr>
                        <tr>
                            <th>Amount to be Paid : </th>
                            <th style="color: #CC0000"><?php echo '=N= '. number_format($amount);?></th>
                        </tr>
                        <tr>
                            <td colspan="2">&nbsp; </td>  
                        </tr>
                        <tr>
                            <td colspan="2">
                                <table width="200" align="center" >
                                    <form name="form1" method="post" action="<?php echo "processpayment.php"?>">
                                        <tr>
                                            <td width="50%">
                                                <input type="submit" name="paynow" value="Pay Now"/>
                                            </td>
                                            <td width="50%">
                                                <input type="button" onclick="javacript:location='../../index.php'" value="Cancel"/>
                                            </td>
                                        </tr>
                                        <input type="hidden" name="jambregid" value="<?php echo (getAccess()== '10')?$row_rspros['stdid'] : $row_rspros['jambregid']?>"/>
                                        <input type="hidden" name="sesid" value="<?php echo $row_session['sesid'] ?>"/>
                                        <input type="hidden" name="prg" value="NULL"/>
                                        <input name="amount" type="hidden" value="<?php echo $amount ?>"/>
                                        <input name="canName" type="hidden" value="<?php echo $row_rspros['lname'].' '.$row_rspros['fname'].' '.$row_rspros['mname'] ?>"/>
                                        <input name="revhead" type="hidden" value="<?php echo $revhead ?>"/>
                                        <input name="percent" type="hidden" value="<?php echo $percent?>"/>
                                        <input name="form_trig" type="hidden" value="form1"/>
                                    </form>    
                                </table>
                            </td>
                        </tr>
                    </table>
                </td> 
            </tr>
            
        </table>
  <!-- InstanceEndEditable --></div>
<div class="footer">
    <p><!-- end .footer -->   
    
    <?php require '../../../include/footer.php'; ?>
	
   </p>
  </div>
  <!-- end .container -->
</div>
</body>
<!-- InstanceEnd --></html>