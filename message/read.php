<?php require_once('../Connections/tams.php'); 
if (!isset($_SESSION)) {
  session_start();
}

require_once('../param/param.php'); 
require_once('../functions/function.php');
require('../param/site.php'); 

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

$colname_rsmsgviw = "-1";
if (isset($_GET['msgid'])) {
  $colname_rsmsgviw = $_GET['msgid'];
}
	mysql_select_db($database_tams,$tams);
	$updateSQL = sprintf("UPDATE message SET status=%s WHERE msgid=%s",
                       GetSQLValueString("Read", "text"),
                       GetSQLValueString($colname_rsmsgviw, "text"));
	$rsmsgupdt = mysql_query($updateSQL, $tams) or die(mysql_error());
	
	
mysql_select_db($database_tams, $tams);
$query_rsmsgviw = sprintf("SELECT * FROM message WHERE msgid = %s", GetSQLValueString($colname_rsmsgviw, "int"));
$rsmsgviw = mysql_query($query_rsmsgviw, $tams) or die(mysql_error());
$row_rsmsgviw = mysql_fetch_assoc($rsmsgviw);
$totalRows_rsmsgviw = mysql_num_rows($rsmsgviw);


	
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/template.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
    <title><?php echo $university ?></title>
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
        <td><!-- InstanceBeginEditable name="pagetitle" --><strong>Inbox Message</strong><!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
            
            
            <table>
            <tr>
            <td> <a href="/tams/message/index.php" >Back to inbox</a></td>
            </tr>
            <tr>
            <td><strong>Date :</strong><?php echo $row_rsmsgviw['date']; ?> </td>
            </tr>
            <tr>
            <td><strong>Sender :</strong> <?php echo $row_rsmsgviw['sndid']; ?> </td>
            </tr>
            <tr>
            <td><strong>Subject :</strong> <?php echo $row_rsmsgviw['subject']; ?></td>
            </tr>
            <tr>
            <td><strong>Body :</strong> <?php echo $row_rsmsgviw['body']; ?></td>
            </tr>
            <tr>
           <td> <a href="/tams/message/index.php#compose" class="btn btn-success">Reply</a></td>
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
<?php
mysql_free_result($rsmsgviw);
?>
