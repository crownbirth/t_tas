<?php require_once('../Connections/tams.php'); ?>
<?php
 
if (!isset($_SESSION)) {
  session_start();
}
require_once('../param/param.php');
require_once('../functions/function.php');

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

$colname_lect = "-1";
if (isset($_GET['lid'])) {
  $colname_lect = $_GET['lid'];
}
mysql_select_db($database_tams, $tams);
$query_lect = sprintf("SELECT title, fname, lname FROM lecturer WHERE lectid = %s", GetSQLValueString($colname_lect, "text"));
$lect = mysql_query($query_lect, $tams) or die(mysql_error());
$row_lect = mysql_fetch_assoc($lect);
$totalRows_lect = mysql_num_rows($lect);

$colname_hist = "-1";
if (isset($_GET['lid'])) {
  $colname_hist = $_GET['lid'];
}
mysql_select_db($database_tams, $tams);
$query_hist = sprintf("SELECT lectid1, lectid2, t.csid, c.csname, sesid FROM teaching t, course c WHERE c.csid = t.csid AND (lectid1 = %s OR lectid2 = %s)", 
					GetSQLValueString($colname_hist, "text"), 
					GetSQLValueString($colname_hist, "text"));
$hist = mysql_query($query_hist, $tams) or die(mysql_error());
$row_hist = mysql_fetch_assoc($hist);
$totalRows_hist = mysql_num_rows($hist);


mysql_select_db($database_tams, $tams);
$query_sess = sprintf("SELECT DISTINCT t.sesid, s.sesname FROM teaching t, session s WHERE s.sesid = t.sesid AND (lectid1 = %s OR lectid2 = %s) ORDER BY sesname DESC", 
					GetSQLValueString($colname_hist, "text"), 
					GetSQLValueString($colname_hist, "text"));
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

$name = ( isset($_GET['lid']) )? $row_lect['title']." ".$row_lect['lname'].", ".$row_lect['fname']:"";

$sub = false;
$path = pathinfo( $_SERVER['SCRIPT_FILENAME']);

if( $path['dirname'] != $_SERVER['DOCUMENT_ROOT'].$site_root )
	$sub = true;
	

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );  
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Teaching History for <?php echo $name?><!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
      <?php if ($totalRows_sess > 0) { // Show if recordset not empty ?>
        <tr>
          <td width="92">&nbsp;</td>
          <td align="center">&nbsp;</td>
        </tr>
        
	<?php do{?>
      <tr style="border-bottom:1px solid thick">
        <td width="92" valign="top"><?php echo $row_sess['sesname']?></td>
        <td>
        
        <?php 
				do{
					if( $row_sess['sesid'] == $row_hist['sesid']){
		?>
          <div style="border-bottom:2px solid thick #FC0; width:100%">            
            <span style="width:130px; float:right">
				<?php 
					if( $colname_hist == $row_hist['lectid1'])
						echo "Convener";
					else
						echo "Assistant";			
				?>
            
            </span>
            <span style="width:370px; float:right"><a href="../course/course.php?csid=<?php echo $row_hist['csid']?>"><?php echo ucwords(strtolower($row_hist['csname']))?></a></span>            
            <span style="width:70px; float:right"><?php echo $row_hist['csid']?></span>
            <div style="clear:both"></div>
          </div>
          <?php }}while( $row_hist = mysql_fetch_assoc($hist) );?>
        </td>
      </tr> 
      
       <tr style="border-bottom:1px solid thick">
        <td width="92" valign="top"></td>
        <td>  
        <hr />      
        </td>
      </tr> 
      <?php 
	  	$rows = mysql_num_rows($hist);
		if($rows > 0) {
			mysql_data_seek($hist, 0);
			$row_hist = mysql_fetch_assoc($hist);
		}
	  
	  	}while( $row_sess = mysql_fetch_assoc($sess) );
	  
	  ?>
      <?php }else{?>
      No history available
      <?php }?>
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
mysql_free_result($lect);

mysql_free_result($hist);
?>
