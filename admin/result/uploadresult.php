<?php require_once('../../Connections/tams.php'); ?>
<?php
require_once('../../param/param.php');
require_once('../../functions/function.php');

if (!isset($_SESSION)) {
  session_start();
}
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

$MM_restrictGoTo = "../../login.php";
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$insert_row = 0;
$insert_error = array();
$uploadstat = "";
if( isset($_POST['submit']) && $_POST['submit'] == "Upload Result"){ //database query to upload result	
		
	  $sesid = $_POST['sesid'];
	  
	  if(is_uploaded_file($_FILES['filename']['tmp_name'])){
		//Import uploaded file to Database	
		$handle = fopen($_FILES['filename']['tmp_name'], "r");
		
		
		$uploaded = true;
                mysql_query("BEGIN", $tams);
                
		while (($data = fgetcsv($handle, 1500, ",")) !== FALSE) 
		{
				//if( $count == 0) to skip first row of upload file
					//continue;
			
			$insert_query = sprintf("UPDATE result SET tscore=%s, escore=%s WHERE stdid=%s AND csid=%s AND sesid=%s",                                
                                GetSQLValueString($data[1], "int"),
                                GetSQLValueString($data[2], "int"),
                                GetSQLValueString($data[0], "text"),
                                GetSQLValueString($_POST['csid'], "text"),	
                                GetSQLValueString($sesid, "int"));
				
			$rsinsert = mysql_query($insert_query, $tams);
                        
			$update = ( mysql_affected_rows($tams) != -1 )? true: false;
			
			if($rsinsert){// && $update1){
				$insert_row++;
			}
			else{
				$insert_error[] = $data[0];
				$uploaded = false;
			}
			
		}
		
		if( $uploaded ){
			
			mysql_query("COMMIT", $tams);
			$insert_query = sprintf("UPDATE teaching SET upload=%s WHERE csid=%s AND sesid=%s AND deptid=%s",
                                                    GetSQLValueString("Yes", "text"),
                                                    GetSQLValueString($_POST['csid'], "text"),
                                                    GetSQLValueString($sesid, "int"),
                                                    GetSQLValueString($_POST['deptid'], "int"));
			mysql_query($insert_query, $tams);
			
			$uploadstat = "Upload Successful! ".$insert_row." results uploaded.";
			
		}else{		
		
			mysql_query("ROLLBACK", $tams);
			$uploadstat = "Upload Unsuccessful! The following results could not be uploaded:<br/>";
			foreach( $insert_error as $error){
				$uploadstat .= $error."<br/>";
			}
			
			$uploadstat .= "Please check result file and try again, or contact system admin.";
		}
		fclose($handle);
	}
	
}

mysql_select_db($database_tams, $tams);
$query_col = "SELECT colid, coltitle FROM college";
$col = mysql_query($query_col, $tams) or die(mysql_error());
$row_col = mysql_fetch_assoc($col);
$totalRows_col = mysql_num_rows($col);

mysql_select_db($database_tams, $tams);
$query_sess = "SELECT * FROM `session` ORDER BY sesname DESC";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

$colname_dept = "-1";
if (isset($row_col['colid'])) {
  $colname_dept = $row_col['colid'];
}

if (isset($_GET['cid'])) {
  $colname_dept = $_GET['cid'];
}

mysql_select_db($database_tams, $tams);
$query_dept = sprintf("SELECT deptid, deptname FROM department WHERE colid = %s", GetSQLValueString($colname_dept, "int"));
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);

$colname_crs = "-1";
if (isset($row_dept['deptid'])) {
  $colname_crs = $row_dept['deptid'];
}

if (isset($_GET['did'])) {
  $colname_crs = $_GET['did'];
}

$colname2_crs = "-1";
if ( isset($row_sess['sesid']) ) {
  $colname2_crs = $row_sess['sesid'];
}

if (isset($_GET['sid'])) {
  $colname2_crs = $_GET['sid'];
}

mysql_select_db($database_tams, $tams);
$query_crs = sprintf("SELECT csid FROM teaching WHERE upload='No' AND sesid=%s AND deptid=%s",
			GetSQLValueString($colname2_crs, "int"), 
			GetSQLValueString($colname_crs, "int"));
$crs = mysql_query($query_crs, $tams) or die(mysql_error());
$row_crs = mysql_fetch_assoc($crs);
$totalRows_crs = mysql_num_rows($crs);

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );  
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/template.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php require('../../param/site.php'); ?>
<title><?php echo $university ?> </title>
<script type="text/javascript" src="../../scripts/jquery.js"></script>
<script type="text/javascript" src="../../scripts/tams.js"></script>
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Upload Result<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
      <tr>
        <td><?php echo $uploadstat?></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td>
          <form name="form1" action="<?php echo $editFormAction;?>" method="post" enctype="multipart/form-data">
          <fieldset>
          	<legend>Upload Result for <?php echo $row_sess['sesname'];?></legend>
            <select name="colid" onchange="colfilt(this)">
              <?php
				do {  
				?>
			    <option value="<?php echo $row_col['colid']?>"<?php if (!(strcmp($row_col['colid'], $colname_dept))) {echo "selected=\"selected\"";} ?>><?php echo $row_col['coltitle']?></option>
							  <?php
				} while ($row_col = mysql_fetch_assoc($col));
				  $rows = mysql_num_rows($col);
				  if($rows > 0) {
					  mysql_data_seek($col, 0);
					  $row_col = mysql_fetch_assoc($col);
				  }
				?>            
            </select>
            
            <select name="deptid" onchange="deptfilt(this)">
              <?php
				do {  
				?>
			    <option value="<?php echo $row_dept['deptid']?>"<?php if (!(strcmp($row_dept['deptid'], $colname_crs))) {echo "selected=\"selected\"";} ?>><?php echo $row_dept['deptname']?></option>
							  <?php
				} while ($row_dept = mysql_fetch_assoc($dept));
				  $rows = mysql_num_rows($dept);
				  if($rows > 0) {
					  mysql_data_seek($dept, 0);
					  $row_dept = mysql_fetch_assoc($dept);
				  }
				?>
            
            </select>
            <select name="sesid" onchange="sesfilt(this)">
              <?php
			  $rows = mysql_num_rows($sess);
			  if($rows > 0) {
				  mysql_data_seek($sess, 0);
				  $row_sess = mysql_fetch_assoc($sess);
			  }	
			  do {  
			  ?>
							<option value="<?php echo $row_sess['sesid']?>"<?php if (!(strcmp($row_sess['sesid'], $colname2_crs))) {echo "selected=\"selected\"";} ?>><?php echo $row_sess['sesname']?></option>
							<?php
			  } while ($row_sess = mysql_fetch_assoc($sess));
				$rows = mysql_num_rows($sess);
				if($rows > 0) {
					mysql_data_seek($sess, 0);
					$row_sess = mysql_fetch_assoc($sess);
				}
			  ?>
             
            </select>
            <br /><br />
          	<input name="filename" type="file" />
            <?php if ($totalRows_crs > 0) { // Show if recordset not empty ?>
              <select name="csid">
                <?php
                            do {  
                            ?>
                <option value="<?php echo $row_crs['csid']?>"<?php if (!(strcmp($row_crs['csid'], $colname_dept))) {echo "selected=\"selected\"";} ?>><?php echo $row_crs['csid']?></option>
                <?php } while ($row_crs = mysql_fetch_assoc($crs));	?> 
              </select>
			<?php }else{ // Show if recordset is empty ?>
            	No course available. &nbsp;&nbsp;&nbsp;&nbsp;
            <?php }?>
            <input type="submit" name="submit" value="Upload Result" />
          </fieldset>
          </form>
        </td>
      </tr>
      <tr>
        <td>&nbsp;</td>
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
<?php
mysql_free_result($sess);

mysql_free_result($dept);

mysql_free_result($col);

mysql_free_result($crs);
?>
