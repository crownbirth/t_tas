<?php 
require_once('Connections/tams.php');
if (!isset($_SESSION)) {
  session_start();
}
require_once('param/param.php');
require_once('functions/function.php');


$sub = false;
$path = pathinfo( $_SERVER['SCRIPT_FILENAME']);

if( $path['dirname'] != $_SERVER['DOCUMENT_ROOT'].$site_root )
	$sub = true;
	

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );  
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

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/template.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $university ?> </title>
<!-- InstanceEndEditable -->
<link href="css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
<link href="css/menulink.css" rel="stylesheet" type="text/css" />
<link href="css/footer.css" rel="stylesheet" type="text/css" />
<link href="css/sidemenu.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div class="container">
  <div class="header">
    <!-- end .header -->
</div>
  <div class="topmenu">
<?php include 'include/topmenu.php'; ?>
  </div>
  <!-- end .topmenu --> 
  
  <div class="loginuser">
  <?php include 'include/loginuser.php'; ?>
  
  <!-- end .loginuser --></div>
  <div class="pagetitle">
    <table width="600">
      <tr>
        <td><!-- InstanceBeginEditable name="pagetitle" -->Project Development Team<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include 'include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
      <tr>
        <td valign="top" bgcolor="#FFFFFF"></br><img src="images/ademola-office.jpg" alt="Project Team" name="team" width="166" height="270" border="10" align="left" id="team" /></td>
        <td valign="top" bgcolor="#FFFFFF"><p>TAMS, Tertiary Academic Management System, is a product of qualitative Web Application Development research that is fully designed, developed and deployed by Mr Ademola Adenubi of the Department of Computer Science, Tai Solarin University of Education, in conjuction with 5 other Research Assistants: </p>
          <ul>
            <li>Akinsola Tunmise</li>
            <li>Sule-Odu Adedayo</li>
            <li>Bada Gabriel</li>
            <li>Olaniyan Olaoluwa and          </li>
            <li>Ayodele Miracle</li>
          </ul>
<p>TAMS seamlessly integrates all the key academic processes in the Institution: Admission, Payment, Profile/Record Management, Course Administration & Registration and Result Processing as a one-stop enterprise application with focus on security, scalability and accessibility. </p>
<p>We appreciate the unflinching support of the Vice Chancellor, Prof Oluyemisi Obilade, the Dean of COSIT, Prof Abayomi Arigbabu, the HOD Computer Science, Mrs Bolanle Abimbola and the Director of ICT, Dr Olumuyiwa Alaba. </p>

<p> For any enquiry, kindly email: adenubiao {at} tasued {dot} edu {dot} ng. Thanks

</td>
      </tr>
      <tr>
        <td colspan="2" valign="top" bgcolor="#FFFFFF"><p>&nbsp;</p></td>
      </tr>
    </table>
  <!-- InstanceEndEditable --></div>
<div class="footer">
    <p><!-- end .footer -->   
    
    <?php require 'include/footer.php'; ?>
	
   </p>
  </div>
  <!-- end .container -->
</div>
</body>
<!-- InstanceEnd --></html>