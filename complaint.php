<?php session_start(); ?>
<?php include ('functions/function.php')?>
<?php require_once('Connections/tams.php'); 

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

$colname_rsStd = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsStd = $_SESSION['MM_Username'];
}
mysql_select_db($database_tams, $tams);
$query_rsStd = sprintf("SELECT * FROM student WHERE stdid = %s", GetSQLValueString($colname_rsStd, "text"));
$rsStd = mysql_query($query_rsStd, $tams) or die(mysql_error());
$row_rsStd = mysql_fetch_assoc($rsStd);
$totalRows_rsStd = mysql_num_rows($rsStd);


	
$colname_rsLect = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsLect = $_SESSION['MM_Username'];
}
mysql_select_db($database_tams, $tams);
$query_rsLect = sprintf("SELECT * FROM lecturer WHERE lectid = %s", GetSQLValueString($colname_rsLect, "text"));
$rsLect = mysql_query($query_rsLect, $tams) or die(mysql_error());
$row_rsLect = mysql_fetch_assoc($rsLect);
$totalRows_rsLect = mysql_num_rows($rsLect);

$colname_rsdept = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsdept = $_SESSION['MM_Username'];
}
mysql_select_db($database_tams, $tams);
$query_rsdept = sprintf("SELECT department.deptname FROM department WHERE department.deptid = ( SELECT programme.deptid FROM programme WHERE programme.progid =  (select student.progid from student where %s = student.stdid))", GetSQLValueString($colname_rsdept, "text"));
$rsdept = mysql_query($query_rsdept, $tams) or die(mysql_error());
$row_rsdept = mysql_fetch_assoc($rsdept);
$totalRows_rsdept = mysql_num_rows($rsdept);

$colname_rsldept = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_rsldept = $_SESSION['MM_Username'];
}
mysql_select_db($database_tams, $tams);
$query_rsldept = sprintf("SELECT department.deptid, department.deptname FROM department WHERE department.deptid = ( SELECT lecturer.deptid FROM lecturer WHERE lecturer.lectid = %s)", GetSQLValueString($colname_rsldept, "text"));
$rsldept = mysql_query($query_rsldept, $tams) or die(mysql_error());
$row_rsldept = mysql_fetch_assoc($rsldept);
$totalRows_rsldept = mysql_num_rows($rsldept);

require('param/site.php'); 
require_once('functions/function.php');
require_once('param/param.php');
if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root ); 
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
        <td><!-- InstanceBeginEditable name="pagetitle" --> Register Your Complaint<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include 'include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
      <tr>
        <td> <?php
	define('CONFIG_PATH', '');
	require_once "form/simpleform.php";
	$sForm = new simpleForm();
	
	$sForm->handleMessage();
	?>
	<?php if(isset($_SESSION['MM_Username'])&& getAccess() == 10 ) {?>	
        
	<form action="form/simpleform.php" method="post" onsubmit="return checkform(this)">  
		  <?php $sForm->printData(1); ?>
		  <table>
          <tr>
		  <td width="179"><label  for="name"  >Name</label></td>
		  <td width="412"><input name="name" id="name" type="hidden"value="<?php echo $row_rsStd['lname']." ".$row_rsStd['fname']; ?>" />
		    <?php echo $row_rsStd['lname']. " " .$row_rsStd['fname']; ?> </td>
          </tr>
		
		 <tr> 
         <td><label for="email">Email </label></td>
		  <td><input name="email" id="email" type="hidden" value="<?php echo $row_rsStd['email']; ?>" />
		    <?php echo $row_rsStd['email']; ?></td>
          </tr>
		 <tr> 
         <td><label for="dept">Department</label></td>
		  <td><input name="dept" id="dept" type="hidden" value="<?php echo $row_rsdept['deptname']; ?>" />
		  <?php echo $row_rsdept['deptname']; ?></td>
          </tr>
          
		 <tr> 
         <td><label for="phone">Telephone </label></td>
		  <td><input name="phone" id="phone" type="hidden" value="<?php echo $row_rsStd['phone']; ?>" />
		    <?php echo $row_rsStd['phone']; ?></td>
          </tr>
          
          <tr>
		 <td> <label for="category">Complaint Category: </label></td>
		 <td> <select name="category" id="category">
		    <option value="-">Choose a category</option>
		    <option value="result">Result/Transcript</option>
		    <option value="course">Course Registration</option>
		    </select></td>
            </tr>
            
            
		<tr>  
        <td valign="top"><label for="complaint">Complaint</label></td>
		  <td><textarea name="comments" id="comments" rows="10" cols="50"></textarea></td>
          </tr>
		  
<!--		<tr> 
        <td>&nbsp;</td> 
		<td><fieldset class="radio">
		  <legend>Subscribe to our Newsletter? </legend>
            <label><input type="radio" name="newsletter" value="Yes" /> Yes</label>
            <label><input type="radio" name="newsletter" value="No" /> No</label>
      </fieldset></td>
      </tr>-->
		
        <tr>
        <td>&nbsp;</td>
        <td><button type="submit">Submit this!</button></td>
        </tr>
        </table>
    </form>
    
    <?php } elseif (isset($_SESSION['MM_Username'])&& getAccess() < 7) { ?>
   
    <form action="form/simpleform.php" method="post" onsubmit="return checkform(this)">  
		<p>
		  <?php $sForm->printData(1); ?>
		  <table>
          <tr>
		  <td width="179"><label  for="name"  >Name</label></td>
		  <td width="412"><input name="name" id="name" type="hidden"value="<?php echo $row_rsLect['lname']." ".$row_rsLect['fname']; ?>; ?>" /> <?php echo $row_rsLect['lname']." ".$row_rsLect['fname']; ?></td>
          </tr>
		
		 <tr> 
         <td><label for="email">Email</label></td>
		  <td><input name="email" id="email" type="hidden" value="<?php echo $row_rsLect['email']; ?>" /> <?php echo $row_rsLect['email']; ?></td>
          </tr>
		 <tr> 
         <td><label for="dept">Department</label></td>
		  <td><input name="dept" id="dept" type="hidden" value="<?php echo $row_rsldept['deptname']; ?>" />
		    <?php echo $row_rsldept['deptname']; ?></td>
          </tr>
          
		 <tr> 
         <td><label for="phone">Phone</label></td>
		  <td><input name="phone" id="phone" type="hidden" value="<?php echo $row_rsLect['phone']; ?>" />
		    <?php echo $row_rsLect['phone']; ?></td>
          </tr>
          
          <tr>
		 <td> <label for="category">Complaint Category:</label></td>
		 <td> <select name="category" id="category">
		    <option value="-">Choose a category</option>
		    <option value="result">Result/Transcript</option>
		    <option value="course">Course Registration</option>
		    </select></td>
            </tr>
            
            
		<tr>  
        <td valign="top"><label for="complaint">Complaint</label></td>
		  <td><textarea name="comments" id="comments" rows="10" cols="50"></textarea></td>
          </tr>
		  
<!--		<tr> 
        <td>&nbsp;</td> 
		<td><fieldset class="radio">
		  <legend>Subscribe to our Newsletter? </legend>
            <label><input type="radio" name="newsletter" value="Yes" /> Yes</label>
            <label><input type="radio" name="newsletter" value="No" /> No</label>
      </fieldset></td>
      </tr>-->
		
        <tr>
        <td>&nbsp;</td>
        <td><button type="submit">Submit this!</button></td>
        </tr>
        </table>
    </form>
    
    
    
    
    <?php }else { ?>
    
    <form action="form/simpleform.php" method="post" onsubmit="return checkform(this)">  
		<p>
		  <?php $sForm->printData(1); ?>
		  <table>
          <tr>
		  <td width="179"><label  for="name"  >Name</label></td>
		  <td width="412"><input name="name" type="text" id="name"value="" size="40" /> </td>
          </tr>
		
		 <tr> 
         <td><label for="email">Email <span class="required">(required)</span></label></td>
		  <td><input name="email" type="text" id="email" value="" size="40" /></td>
          </tr>
		 <tr> 
         <td><label for="dept">Department<span class="required">(required)</span></label></td>
		  <td><input name="dept" type="text" id="dept" value="" size="40" /> </td>
          </tr>
          
		 <tr> 
         <td><label for="phone">Telephone <span class="required">(required)</span></label></td>
		  <td><input name="phone" id="phone" type="text" value="" /></td>
          </tr>
          
          <tr>
		 <td> <label for="category">Complaint Category: <span class="required">(required)</span></label></td>
		 <td> <select name="category" id="category">
		    <option value="-">- Choose a category -</option>
		    <option value="result">Result/Transcript</option>
		    <option value="course">Course Registration</option>
            <option value="upload">Result Upload</option>
            <option value="allocation">Course Allocation to Lecturers</option>
            <option value="assign">Course Assignment to Department</option>
		    </select></td>
            </tr>
            
            
		<tr>  
        <td valign="top"><label for="complaint">Complaint</label></td>
		  <td><textarea name="comments" id="comments" rows="10" cols="50"></textarea></td>
          </tr>
		  
		<tr> 
        <td>&nbsp;</td> 
		<td><fieldset class="radio">
		  <legend>Subscribe to our Newsletter? <span class="required">(required)</span></legend>
            <label><input type="radio" name="newsletter" value="Yes" /> Yes</label>
            <label><input type="radio" name="newsletter" value="No" /> No</label>
      </fieldset></td>
      </tr>
		
        <tr>
        <td>&nbsp;</td>
        <td><button type="submit">Submit this!</button></td>
        </tr>
        </table>
    </form>
    <?php } ?>
    </td>
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
<?php
mysql_free_result($rsStd);

mysql_free_result($rsLect);

mysql_free_result($rsdept);

mysql_free_result($rsldept);
?>
