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
      <img src="../img/visa.jpg" width="70px" height="30px" />  Visa Instruction  InstanceEndEditable 
<!--    <table width="600">
      <tr>
          <td> InstanceBeginEditable name="pagetitle"  <img src="img/visa.jpg" width="70px" height="30px" />  Visa Instruction  InstanceEndEditable </td>
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
                                This site is protected with Verified by Visa (VbV),
                                    Visa Password-Protected Identity Checking Service,
                                    and requires that the card is enrolled to participate
                                    in the VbV program. If your Visa Card issued by Nigerian
                                    Banks is not enrolled, kindly follow the steps outlined
                                    below.
                            </p>
                            <ol>
                                <li>Locate the nearest VISA/VPAY enabled ATM</li>
                                <li>Insert your card and punch in your PIN</li>
                                <li>Select the PIN change option</li>
                                <li>Select Internet PIN (i-PIN) change option</li>
                                <li>Insert any four - six digits of your choice as your
                                        i-PIN</li>
                                <li>Re-enter the digits entered in step 5</li>
                                <li>If you have done the above correctly, a message is
                                        displayed that your PIN was changed successfully.
                                        This means your card is now enrolled in the VbV program
                                        and you have an Internet PIN (i-PIN) which can be
                                        used for any internet related transaction</li>
                                <li>Note the the word "<strong>i-PIN</strong>","<strong>Password</strong>"
                                        and "<strong>VbV Code</strong>" are the same</li>
                                <li>You
                                            can now visit your favourite VbV enabled site to shop
                                            securely</li>
                                <p>
                                    <strong>Important</strong><br />
                                    Please note that this is only for internet related
                                        transactions and it does not change your regular PIN
                                        on ATM and POS.
                                </p>
                            </ol>
                        </div>
                    </p>
                </td> 
            </tr>
            <tr>
                <td align="center"><input type="button" onclick="javascript:location='index.php'" value="Pay Now"/></td>
                <td align="center"><input type="button" onclick="javascript:location='../../status.php'" value="Cancle"/></td>
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