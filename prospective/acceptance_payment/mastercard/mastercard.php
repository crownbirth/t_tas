<?php 
require_once('../../../Connections/tams.php');
if (!isset($_SESSION)) {
  session_start();
}
require_once('../../../param/param.php');
require_once('../../../functions/function.php');


$sub = false;
$path = pathinfo( $_SERVER['SCRIPT_FILENAME']);

if( $path['dirname'] != $_SERVER['DOCUMENT_ROOT'].$site_root )
	$sub = true;
	

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );  
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
header('Location: ../../status.php');
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
      <img src="../img/mastercard.png" width="70px" height="30px" />  Mastercard Instruction 
<!--    <table width="600">
      <tr>
          <td> InstanceBeginEditable name="pagetitle"  <img src="img/mastercard.png" width="70px" height="30px" />  Mastercard Instruction  InstanceEndEditable </td>
      </tr>
    </table>-->
  </div>
<div class="sidebar1">
   
    <?php include '../../../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
        <table width="690" >
            <tr>
                <td colspan="2">
                    <p>
                        <div>
                            <p>
                                This site is a MasterCard SecureCode (MCSC) participating
                                Merchantâ€™s website. MCSC is designed to enable you (cardholder) make safer internet 
                                purchase transactions by authenticating your identity at the time of purchase in order to protect you from unauthorized usage of your MasterCard.
                            </p>
                            <p>
                                MasterCard SecureCode password is strictly for
                                online transactions and it is different from your
                                regular Personal Identification Number (PIN) used
                                for ATM and POS transactions.
                            </p>
                            <p>
                                Please follow the steps below to obtain and use your MasterCard SecureCode:
                            </p>
                            <ol>
                                <li>Click on the <strong>Pay Now</strong> button below to proceed to the next page</li>
                                <li>
                                    Enter your MasterCard card details such as Card Number,
                                    CVV2, Name on card, Expiry date and click OK</li>
                                <li>
                                    You will be redirected to your bankâ€™s website,
                                    kindly follow the process to completion as advised by your bank
                                </li>
                                <li>
                                    The next time you make purchase on the website
                                    of a participating Merchant, simply enter the MCSC
                                    Password and any Secret Questions (if any)  you created if
                                    required by your bank.
                                </li>

                                <p>
                                    <strong>Important</strong><br />
                                    The activation process is determined by your bank.
                                    Should you encounter any problem, please contact your
                                    bank
                                </p>
                            </ol>
                        </div>
                    </p>
                </td> 
            </tr>
            <tr>
                <td align="center"><input type="button" onclick="javascript:location='index.php'" value="Pay Now"/></td>
                <td align="center"><input type="button" onclick="javascript:location='../../status.php'" value="Cancel"/></td>
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