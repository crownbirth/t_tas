<?php 
require_once('../../Connections/tams.php');
if (!isset($_SESSION)) {
  session_start();
}
require_once('../../param/param.php');
require_once('../../functions/function.php');


$sub = false;
$path = pathinfo( $_SERVER['SCRIPT_FILENAME']);

if( $path['dirname'] != $_SERVER['DOCUMENT_ROOT'].$site_root )
	$sub = true;
	

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root.'/prospective' );   
}

$std= getSessionValue('MM_Username');

$cyr=date('Y');
mysql_select_db($database_tams, $tams);
 $query_paid= sprintf("SELECT * FROM accfee_transactions  WHERE year='".$cyr."' AND status='APPROVED' AND can_no='".$std."'") ;
$paid= mysql_query($query_paid, $tams) or die(mysql_error());
$row_paid = mysql_fetch_assoc($paid);
$total_paid= mysql_num_rows($paid);

if ($total_paid>0 ) {
//echo $total_paid;
//means this candidate has paid
header('Location: ../status.php');
}



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/template.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php require('../../param/site.php'); ?>
<title><?php echo $university ?> </title>
<!-- InstanceEndEditable -->
<link href="../../css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
<link href="../../css/menulink.css" rel="stylesheet" type="text/css" />
<link href="../../css/footer.css" rel="stylesheet" type="text/css" />
<link href="../../css/sidemenu.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div class="container">
  <div class="header">
    <!-- end .header -->
</div>
  <div class="topmenu">
<?php include '../../include/topmenu.php'; ?>
  </div>
  <!-- end .topmenu --> 
  
  <div class="loginuser">
  <?php include '../../include/loginuser.php'; ?>
  
  <!-- end .loginuser --></div>
  <div class="pagetitle">
    <table width="600">
      <tr>
        <td><!-- InstanceBeginEditable name="pagetitle" -->Payment Instruction <!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
      <table width="690" class="table">
            <tr>
                <td colspan="2">
                    <p>
                        Your Application Form fee is to be paid by selecting a card type below and using our webpay platform.
                    </p>
                    
            <p>
                Payment will be made using Debit/Credit Cards (ATM Card)<br>
                Your Card can be from <u>any of the Nigerian Banks</u>
                <br>Ensure that your card has been enabled for internet transactions
                by your bank (kindly enquire from your bank if you must).
            </p> 
            <p>
                <b style="color :red">Fees paid to Tai Solarin University of Education are non-refundable</b>
                <h4>Are you using Internet explorer browser?</h4>
                Avoid browser issues, uncheck support for Use SSL2.0 by following the steps below:<br/>
                1. Click on Tool option on the menu bar<br/>
                2. Select Internet Options<br/>
                3. Click Advance tab<br/>
                4. Scroll down to Security option and uncheck Use SSL 2.0<br/>
            </p>
                </td> 
            </tr>
            <tr>
                <td >
                    <table width="400" align="center" class="table table-bordered table-striped table-condensed">
                        <tr>
                            <th colspan="2">Select a payment method to continue</th>
                        </tr>
                         <tr>
                             <td align="center" width="50%" style=" "><a href="mastercard/mastercard.php"><img src="img/mastercard.png"></a></td>
                             <td align="center" width="50%"><a href="visa/visa.php"><img src="img/visa.jpg"></a></td>
                        </tr>
                    </table>
                </td>  
            </tr>
        </table>
  <!-- InstanceEndEditable --></div>
<div class="footer">
    <p><!-- end .footer -->   
    
    <?php require '../../include/footer.php'; ?>
	
   </p>
  </div>
  <!-- end .container -->
</div>
</body>
<!-- InstanceEnd --></html>