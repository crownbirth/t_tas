<?php require_once('../../Connections/tams.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}

require_once('../../param/param.php');
require_once('../../functions/function.php');

$MM_authorizedUsers = "1";
$MM_donotCheckaccess = "false";

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
    if (($strUsers == "") && false) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$MM_restrictGoTo = "../login.php";
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

if ( isset($_POST['submit']) ) {
	
	$aids = "";
	if( isset($_POST['aid']) ){
		foreach( $_POST['aid'] as $aid){
			if( $aid != "" )		
				$aids .= ",".$aid;
		}
	}
	
	$updateSQL = sprintf("UPDATE lecturer SET access = %s WHERE lectid IN ( SELECT lectid FROM appointment WHERE appid IN (0%s) )",
                       GetSQLValueString("5", "int"),
					   GetSQLValueString($aids, "undefined",$aids));

	mysql_select_db($database_tams, $tams);
	$Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());
	
	$deleteSQL = sprintf("DELETE FROM appointment WHERE sdate = %s AND appid IN (0%s)",
                       GetSQLValueString($_POST['session'], "int"),
					   GetSQLValueString($aids, "undefined",$aids));
					   
	mysql_select_db($database_tams, $tams);
	$Result1 = mysql_query($deleteSQL, $tams) or die(mysql_error());
	
	if( isset($_POST['centre']) ){
		$insertSQL = sprintf("INSERT INTO appointment (lectid, postid, sdate) VALUES(%s,%s,%s)",
							 GetSQLValueString($_POST['centre'], "text"),
							 GetSQLValueString("4", "int"),
							 GetSQLValueString($_POST['session'], "int"));
	
		mysql_select_db($database_tams, $tams);
		$Result1 = mysql_query($insertSQL, $tams) or die(mysql_error());
		
		$updateSQL = sprintf("UPDATE lecturer SET access = %s WHERE lectid = %s",
                       		GetSQLValueString("4", "int"),
							GetSQLValueString($_POST['centre'], "text"));

	  	mysql_select_db($database_tams, $tams);
	  	$Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());
	}
	
	if( isset($_POST['dean']) )
		foreach($_POST['dean'] as $dean){
			$insertSQL = sprintf("INSERT INTO appointment (lectid, postid, sdate) VALUES(%s,%s,%s)",
								 GetSQLValueString($dean, "text"),
								 GetSQLValueString("2", "int"),
								 GetSQLValueString($_POST['session'], "int"));
		
			mysql_select_db($database_tams, $tams);
			$Result1 = mysql_query($insertSQL, $tams) or die(mysql_error());
			
			$updateSQL = sprintf("UPDATE lecturer SET access = %s WHERE lectid = %s",
								GetSQLValueString("2", "int"),
								GetSQLValueString($dean, "text"));
	
			mysql_select_db($database_tams, $tams);
			$Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());
		}
	
	if( isset($_POST['hod']) )
		foreach($_POST['hod'] as $hod){
			$insertSQL = sprintf("INSERT INTO appointment (lectid, postid, sdate) VALUES(%s,%s,%s)",
								 GetSQLValueString($hod, "text"),
								 GetSQLValueString("3", "int"),
								 GetSQLValueString($_POST['session'], "int"));
		
			mysql_select_db($database_tams, $tams);
			$Result1 = mysql_query($insertSQL, $tams) or die(mysql_error());
			
			$updateSQL = sprintf("UPDATE lecturer SET access = %s WHERE lectid = %s",
								GetSQLValueString("3", "int"),
								GetSQLValueString($hod, "text"));
	
			mysql_select_db($database_tams, $tams);
			$Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());
		}
	
	if( isset($_POST['admin']) ){
		$insertSQL = sprintf("INSERT INTO appointment (lectid, postid, sdate) VALUES(%s,%s,%s)",
							   GetSQLValueString($_POST['admin'], "text"),
							   GetSQLValueString("1", "int"),
							   GetSQLValueString($_POST['session'], "int"));
  
		mysql_select_db($database_tams, $tams);
		$Result1 = mysql_query($insertSQL, $tams) or die(mysql_error());
		
		$updateSQL = sprintf("UPDATE lecturer SET access = %s WHERE lectid = %s",
							  GetSQLValueString("1", "int"),
							  GetSQLValueString($_POST['admin'], "text"));
  
		mysql_select_db($database_tams, $tams);
		$Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());
		
		if( getSessionValue('lid') == $_POST['admin'] )
			$_SESSION['MM_UserGroup'] = 1;
		else {
                     $_SESSION['MM_UserGroup'] = 5;
                     //doLogout( $site_root );
                     header("Location: ../../index.php");                      
                }
	}
}
mysql_select_db($database_tams, $tams);
$query_rssess = "SELECT sesid, sesname FROM session ORDER BY sesname DESC";
$rssess = mysql_query($query_rssess, $tams) or die(mysql_error());
$row_rssess= mysql_fetch_assoc($rssess);
$totalRows_rsess = mysql_num_rows($rssess);

mysql_select_db($database_tams, $tams);
$query_rscol = "SELECT colid, colname, coltitle FROM college";
$rscol = mysql_query($query_rscol, $tams) or die(mysql_error());
$row_rscol = mysql_fetch_assoc($rscol);
$totalRows_rscol = mysql_num_rows($rscol);

mysql_select_db($database_tams, $tams);
$query_rsdept = "SELECT deptid, deptname, d.colid, coltitle FROM department d, college c WHERE c.colid = d.colid ";
$rsdept = mysql_query($query_rsdept, $tams) or die(mysql_error());
$row_rsdept = mysql_fetch_assoc($rsdept);
$totalRows_rsdept = mysql_num_rows($rsdept);

mysql_select_db($database_tams, $tams);
$query_rslect = "SELECT lectid, title, fname, lname, l.deptid, colid FROM lecturer l, department d WHERE l.deptid = d.deptid";
$rslect = mysql_query($query_rslect, $tams) or die(mysql_error());
$row_rslect = mysql_fetch_assoc($rslect);
$totalRows_rslect = mysql_num_rows($rslect);

$colname_rsappt = $row_rssess['sesid'];
if( isset($_GET['sid'])){
	$colname_rsappt = $_GET['sid'];
}
mysql_select_db($database_tams, $tams);
$query_rsappt = sprintf("SELECT a.appid, a.lectid, a.postid, a.sdate, l.deptid, c.coltitle FROM appointment a, lecturer l, department d, college c WHERE a.lectid = l.lectid AND l.deptid = d.deptid AND d.colid = c.colid AND sdate =%s",
						GetSQLValueString($colname_rsappt, "int"));
$rsappt = mysql_query($query_rsappt, $tams) or die(mysql_error());
$row_rsappt = mysql_fetch_assoc($rsappt);
$totalRows_rsappt = mysql_num_rows($rsappt);

$ses = ( isset( $_GET['sid'] ) )? $_GET['sid']: $row_rssess['sesid'];
$sesmain = $row_rssess['sesid'];

$vc = array();
$centre = array();
$dean = array();
$deanval = array();
$hod = array();
$vala = "";
$valc = "";

{
	
do{
	$hod[$row_rsdept['deptid']] = -1;
}
while( $row_rsdept = mysql_fetch_assoc($rsdept) );

$rows = mysql_num_rows($rsdept);
if($rows > 0) {
	mysql_data_seek($rsdept, 0);
	$row_rsdept = mysql_fetch_assoc($rsdept);
}

do{
	$dean[$row_rscol['coltitle']] = array();
	$deanval[$row_rscol['coltitle']] = "";
	$dean[$row_rscol['coltitle']][0] = -1;
	$dean[$row_rscol['coltitle']][1] = 0;
	$dean[$row_rscol['coltitle']][2] = -1;
}
while( $row_rscol = mysql_fetch_assoc($rscol) );

$rows = mysql_num_rows($rscol);
if($rows > 0) {
	mysql_data_seek($rscol, 0);
	$row_rscol = mysql_fetch_assoc($rscol);
}

$vc['id'] = $vc['dept'] = $centre['id'] = $centre['dept'] = "-1";
$centre['aid'] = $vc['aid'] = "0";

do{
	
	
	switch( $row_rsappt['postid'] ){
		case 1: 
				$vc['id'] = ( isset($row_rsappt['lectid']) )? $row_rsappt['lectid']: "-1";
				$vc['dept'] = ( isset($row_rsappt['deptid']) )? $row_rsappt['deptid'] : "-1";
				$vc['aid'] = ( isset($row_rsappt['appid']) )? $row_rsappt['appid']:"0";
				$vala = $vc['dept'];
				break;
		case 2: 
				do{
					if( $row_rsappt['coltitle'] == $row_rscol['coltitle'] ){
						$dean[$row_rsappt['coltitle']] = array();
						$dean[$row_rsappt['coltitle']][0] = ( isset($row_rsappt['lectid']) )? $row_rsappt['lectid']: "-1";
						$dean[$row_rsappt['coltitle']][1] = $row_rsappt['appid'];
						$dean[$row_rsappt['coltitle']][2] = $row_rsappt['deptid'];
					}
				}while( $row_rscol = mysql_fetch_assoc($rscol) );	
				
				$rows = mysql_num_rows($rscol);
				if($rows > 0) {
					mysql_data_seek($rscol, 0);
					$row_rscol = mysql_fetch_assoc($rscol);
				}
				break;
		case 3: 
				do{
					if( $row_rsappt['deptid'] == $row_rsdept['deptid']){
						$hod[$row_rsappt['deptid']] = array();
						$hod[$row_rsappt['deptid']][0] = ( isset($row_rsappt['lectid']) )? $row_rsappt['lectid']: "-1";
						$hod[$row_rsappt['deptid']][1] = $row_rsappt['appid'];
					}
				}while( $row_rsdept = mysql_fetch_assoc($rsdept) );	
				
				$rows = mysql_num_rows($rsdept);
				if($rows > 0) {
					mysql_data_seek($rsdept, 0);
					$row_rsdept = mysql_fetch_assoc($rsdept);
				}
				break;
		case 4: 
				$centre['id']= ( isset($row_rsappt['lectid']) )? $row_rsappt['lectid']: "-1";
				$centre['dept'] = ( isset($row_rsappt['deptid']) )?  $row_rsappt['deptid']: "-1";
				$centre['aid'] = ( isset($row_rsappt['appid']) )? $row_rsappt['appid']:"0";
				$valc = $centre['dept'];
				break;	
		
	}
}while( $row_rsappt = mysql_fetch_assoc($rsappt) );	


}

$options = array();

do{
	$options[$row_rscol['coltitle']] = array();
	do{
		if( $row_rscol['colid'] == $row_rsdept['colid'] ){
			$options[$row_rscol['coltitle']][$row_rsdept['deptid']] = array();
			$count = 0;
			do{
				if( $row_rslect['deptid'] == $row_rsdept['deptid']){
					$options[$row_rscol['coltitle']][$row_rsdept['deptid']][$count]['name'] = $row_rslect['title']." ".$row_rslect['lname']." ".substr($row_rslect['fname'], 0 , 1);
					$options[$row_rscol['coltitle']][$row_rsdept['deptid']][$count]['id'] = $row_rslect['lectid'];
					$count++;
				}
			}while( $row_rslect = mysql_fetch_assoc($rslect) );
			$rows = mysql_num_rows($rslect);
			if($rows > 0) {
				mysql_data_seek($rslect, 0);
				$row_rslect = mysql_fetch_assoc($rslect);
			}
		}
	}while( $row_rsdept = mysql_fetch_assoc($rsdept) );
	$rows = mysql_num_rows($rsdept);
	if($rows > 0) {
		mysql_data_seek($rsdept, 0);
		$row_rsdept = mysql_fetch_assoc($rsdept);
	}
}while( $row_rscol = mysql_fetch_assoc($rscol) );
$rows = mysql_num_rows($rscol);
if($rows > 0) {
	mysql_data_seek($rscol, 0);
	$row_rscol = mysql_fetch_assoc($rscol);
}

$vala = ( isset( $_GET['dida'] ) && $_GET['dida'] != -1 )? $_GET['dida']: $vala;
$valc = ( isset( $_GET['didc'] ) && $_GET['didc'] != -1 )? $_GET['didc']: $valc;
$chkval[] = isset( $_GET['dida'] )? $_GET['dida']: "";
$chkval[] = isset( $_GET['didc'] )? $_GET['didc']: "";

if( isset( $_GET['dct'] ) && $_GET['dct'] != -1 ){
	$dean['COSIT'][2] = $deanval['COSIT'] = $_GET['dct'];
}

if( isset( $_GET['dcs'] ) && $_GET['dcs'] != -1 ){
	$dean['COSMAS'][2] = $deanval['COSMAS'] = $_GET['dcs'];
}

if( isset( $_GET['dcv'] ) && $_GET['dcv'] != -1 ){
	$dean['COAEVOT'][2] = $deanval['COAEVOT'] = $_GET['dcv'];
}

if( isset( $_GET['dch'] ) && $_GET['dch'] != -1 ){
	$dean['COHUM'][2] = $deanval['COHUM'] = $_GET['dch'];
}

if( isset( $_GET['dcl'] ) && $_GET['dcl'] != -1 ){
	$dean['CLINICAL'][2] = $deanval['CLINICAL'] = $_GET['dcl'];
}

if( isset( $_GET['dcm'] ) && $_GET['dcm'] != -1 ){
	$dean['MEDICAL'][2] = $deanval['MEDICAL'] = $_GET['dcm'];
}
$cola = "";
$colc = "";
do{
	
	if( $vala == $row_rsdept['deptid'] ){
		$cola = $row_rsdept['coltitle'];
	}
	if( $valc == $row_rsdept['deptid'] ){
		$colc = $row_rsdept['coltitle'];
	}
}while( $row_rsdept = mysql_fetch_assoc($rsdept) );
$rows = mysql_num_rows($rsdept);
if($rows > 0) {
	mysql_data_seek($rsdept, 0);
	$row_rsdept = mysql_fetch_assoc($rsdept);
}

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );  
}
/*var_dump($options);
exit();*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/template.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php require('../../param/site.php'); ?>
<title><?php echo $university ?> </title>
<script type="text/javascript" src="../../scripts/jquery.js"></script>
<script  type="text/javascript" src="../../scripts/tams.js"></script>
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Appointment<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="650">
    
    <form action="<?php echo $editFormAction?>" method="post">
      <tr>
      <td></td>
      <td colspan="3">
      	<select name="session" onchange="sesfilt(this)">
      	  <?php do{?>
      	  
          <option value="<?php echo $row_rssess['sesid']; ?>" <?php if (!(strcmp($ses, htmlentities($row_rssess['sesid'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>><?php echo $row_rssess['sesname'];?></option>
          
      	  <?php }while( $row_rssess = mysql_fetch_assoc($rssess) );?>
    	  </select>
      </td>
    </tr>
    <tr>
      	<td  colspan="4"></td>
    </tr>
      <tr>
      	<td  colspan="4">
        
        <div>
        	<p style="float:left;width:100px;">VC/Admin</p>
        	
            <p style="float:left;">
              <select style="width:200px" name="dept1" id="centre" onchange="deptnamefilt(this, '1')" <?php if($chkval[0] == "")echo "disabled"?>>
                  <option value="-1" >Department</option>
                <?php do{?>
                
                <option value="<?php echo $row_rsdept['deptid']; ?>" <?php if (!(strcmp($vala, htmlentities($row_rsdept['deptid'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>><?php echo $row_rsdept['deptname'];?></option>
                
                <?php }while( $row_rsdept = mysql_fetch_assoc($rsdept) );
                  $rows = mysql_num_rows($rsdept);
                  if($rows > 0) {
                      mysql_data_seek($rsdept, 0);
                      $row_rsdept = mysql_fetch_assoc($rsdept);
                  }		  
                ?>
                </select>
            </p>
            
            <p style="float:left;">
                <select style="width:200px" name="admin" <?php if($chkval[0] == "")echo "disabled"?>>
                <option value="-1">Not selected</option>
                  <?php 
                    if( $vala )
                      foreach( $options[$cola][$vala] as $opt ){
						  
                  ?>
                  <option value="<?php echo $opt['id']?>" <?php if (!(strcmp($vc['id'], htmlentities($opt['id'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>><?php echo $opt['name']?></option>
                  <?php }?>
                </select>
            </p>
            <p style="float:right;">
            	<input type="checkbox" name="admincheck" id="admincheck" <?php if($chkval[0] != "")echo "checked"?>/>
        	</p>
            <input type="hidden" name="aid[]" value="<?php echo $vc['aid']?>" <?php if($chkval[0] == "")echo "disabled"?>/>
        </div>
      	
        
        </td>
      </tr>
      <tr>
        <td colspan="4"></td>
      </tr>
      <tr>
      	<td colspan="4">
        
        <div>
        	<p style="float:left;width:100px;">Centre Director</p>
        	
            <p style="float:left;">
              <select style="width:200px" name="dept2" onchange="deptnamefilt(this, '2')" <?php if($chkval[1] == "")echo "disabled"?>>
        	
                  <option value="-1" >Department</option>
                <?php do{
                          
                ?>
                 
                <option value="<?php echo $row_rsdept['deptid']; ?>" <?php if (!(strcmp($valc, htmlentities($row_rsdept['deptid'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>><?php echo $row_rsdept['deptname'];?>
                </option>
                
                <?php }while( $row_rsdept = mysql_fetch_assoc($rsdept) );
                  $rows = mysql_num_rows($rsdept);
                  if($rows > 0) {
                      mysql_data_seek($rsdept, 0);
                      $row_rsdept = mysql_fetch_assoc($rsdept);
                  }		  
                ?>
                </select>
            </p>
            
            <p style="float:left;">
                <select style="width:200px" name="centre" <?php if($chkval[1] == "")echo "disabled"?>>
                <option value="-1">Not selected</option>
                  <?php
                    if( $valc )
                      foreach( $options[$colc][$valc] as $opt ){
                  ?>
                  <option value="<?php echo $opt['id']?>" <?php if (!(strcmp($centre['id'], htmlentities($opt['id'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>><?php echo $opt['name']?></option>
                  <?php }?>
                </select>
            </p>
            <p style="float:right;">
            	<input type="checkbox" name="centercheck" id="centrecheck" <?php if($chkval[1] != "")echo "checked"?>/>
        	</p>
            <input type="hidden" name="aid[]" value="<?php echo $centre['aid']?>" <?php if($chkval[1] == "")echo "disabled"?>/>
        </div>
        </td>
      </tr>
      <tr>
        <td colspan="4"></td>
      </tr>
      <tr>
        <td colspan="4">
        	<table width="650">
              <tr>
                <td colspan="2">Dean</td>
              </tr>
              <tr>
                <td  colspan="2"></td>
              </tr>
              <tr>
                <td  colspan="2"></td>
              </tr>
              <tr>
              	<td>
              <?php 
			  
			  	$snum = 3;
				do{
			  ?>
              
                <div>
                <p  style="float:left;width:100px"><?php echo $row_rscol['coltitle']?></p>
                <p style="float:left;">
                  <select style="width:200px" name="dept<?php echo $snum?>" onchange="deptnamefilt(this, '<?php echo $row_rscol['coltitle']?>')" <?php if($deanval[$row_rscol['coltitle']] == "")echo "disabled"?>>
                
                      <option value="-1" >Department</option>
                    <?php do{
                              if( $row_rsdept['colid'] == $row_rscol['colid']){
                    ?>
                     
                    <option value="<?php echo $row_rsdept['deptid']; ?>" <?php if (!(strcmp($dean[$row_rscol['coltitle']][2], htmlentities($row_rsdept['deptid'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>><?php echo $row_rsdept['deptname'];?>
                    </option>
                    
                    <?php }}while( $row_rsdept = mysql_fetch_assoc($rsdept) );
                      $rows = mysql_num_rows($rsdept);
                      if($rows > 0) {
                          mysql_data_seek($rsdept, 0);
                          $row_rsdept = mysql_fetch_assoc($rsdept);
                      }		  
                    ?>
                    </select>
                </p>
                <p  style="float:left;width:200px">
                  <select style="width:200px" name="dean[]" <?php if($deanval[$row_rscol['coltitle']] == "")echo "disabled"?>>
                  <option value="-1">Not selected</option>
                    <?php
						if( $dean[$row_rscol['coltitle']][2] != -1 )
						  foreach( $options[$row_rscol['coltitle']][$dean[$row_rscol['coltitle']][2]] as $opt ){
					?>
					<option value="<?php echo $opt['id']?>" <?php if (!(strcmp($dean[$row_rscol['coltitle']][0], htmlentities($opt['id'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>><?php echo $opt['name']?></option>
					<?php }?>
                  </select>
                </p>
                <p style="float:right;">
                    <input type="checkbox" name="centercheck" class="deancheck" <?php if($deanval[$row_rscol['coltitle']] != "")echo "checked"?>/>
                </p>
                <input type="hidden" name="aid[]" value="<?php echo $dean[$row_rscol['coltitle']][1]?>" <?php if($deanval[$row_rscol['coltitle']] == "")echo "disabled"?>/>
                </div>
                
              <?php }while( $row_rscol = mysql_fetch_assoc($rscol) );?>
              	</td>
              </tr>
            </table>
        </td>
      </tr>    
      <tr>
        <td colspan="4"></td>
      </tr>  
      <tr>
        <td colspan="4">
        	<table width="650">
              <tr>
                <td colspan="2">HOD</td>
              </tr>
              <tr>
                <td  colspan="2"></td>
              </tr>
              <tr>
                <td  colspan="2"></td>
              </tr>
              <tr>
              	<td colspan="2">
              <?php do{?>
              <div>
                  <p style="float:left;width:300px"><?php echo $row_rsdept['deptname']?></p>
                  <p style="float:left;width:200px">
                    <select style="width:200px" name="hod[]" disabled>
                    <option value="-1">Not selected</option>
                      <?php 
                          foreach( $options[$row_rsdept['coltitle']][$row_rsdept['deptid']] as $opt ){
                      ?>
                      <option value="<?php echo $opt['id']?>" <?php if (!(strcmp($hod[$row_rsdept['deptid']][0], htmlentities($opt['id'], ENT_COMPAT, 'utf-8')))) {echo "SELECTED";} ?>><?php echo $opt['name']?></option>
                      <?php }?>
                    </select>
                  </p>
                  <p style="float:right;">
                      <input type="checkbox" name="centercheck" class="hodcheck"/>
                  </p>
                  <input type="hidden" name="aid[]" value="<?php echo $hod[$row_rsdept['deptid']][1]?>" disabled/>
                </div>
              <?php }while( $row_rsdept = mysql_fetch_assoc($rsdept) );?>              
                </td>
              </tr>
            </table>
        </td>
      </tr>
      <?php if( $ses == $sesmain ){?>
      <tr>
        <td colspan="4" align="center"><input type="submit" name="submit" value="Submit Appointments" /></td>
      </tr>
      <?php }?>
      </form>
    </table>
    <script type="text/javascript">
		$(function(){
			appointment();	
		});
	
	</script>
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
<?php
$options = NULL;

mysql_free_result($rslect);

mysql_free_result($rscol);

mysql_free_result($rsappt);

mysql_free_result($rssess);
?>
