<?php  
if (!isset($_SESSION)) {
  session_start();
}

require_once('../Connections/tams.php');
require_once('../param/param.php');
require_once('../functions/function.php');

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root.'/prospective' );   
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/template.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php require('../param/site.php'); ?>
<title><?php echo $university ?> </title>
<!-- InstanceEndEditable -->
<link href="../css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
<link href="../css/menulink.css" rel="stylesheet" type="text/css" />
<link href="../css/footer.css" rel="stylesheet" type="text/css" />
<link href="../css/sidemenu.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div class="container">
  <div class="header">
    <!-- end .header -->
</div>
  <div class="topmenu">
<?php include '../include/topmenu.php'; ?>
  </div>
  <!-- end .topmenu --> 
  
  <div class="loginuser">
  <?php include '../include/loginuser.php'; ?>
  
  <!-- end .loginuser --></div>
  <div class="pagetitle">
    <table width="600">
      <tr>
        <td><!-- InstanceBeginEditable name="pagetitle" -->Account Creation Successful<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
      <tr>
        <td>
            <p>Congratulation....</p>
            <p>
                Your login Profile has been created successfully 
                and your login details has been sent to the E- mail 
                Address provided. Please note the following;
            </p>
            <ul>
                <li>Username = UTME Reg.No </li>
                <li>Password = Surname</li>
                <li>Login As = Prospective Student</li>
            </ul>
            <p>
                <a href="../login.php">Click Here</a> 
                to Login and proceed with your Application
            </p>
            
        </td>
        
        <?php 
            $fname=  $_SESSION['newacct']['fname'];
            $username= $_SESSION['newacct']['jambregid'];
            $lname=  $_SESSION['newacct']['lname'];
            $mail_to = $_SESSION['newacct']['email'];
            $subject = "TAMS: New Account Information";
            $sender="Tai Solarin University of Education";
            unset($_SESSION['newacct']);
            $message= "Congratulation....\n\n
            Dear {$fname} {$lname},\n   
            Your Login Account account has been created successfully below
            \n Username = {$username}\n Password = {$lname}\n ";			
            $body = $message;
            @mail($mail_to, $subject,$message,$sender);
        ?>
      </tr>
    </table>
  <!-- InstanceEndEditable --></div>
<div class="footer">
    <p><!-- end .footer -->   
    
    <?php require '../include/footer.php'; ?>
	
   </p>
  </div>
  <!-- end .container -->
</div>
</body>
<!-- InstanceEnd --></html>