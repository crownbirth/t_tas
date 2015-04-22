<?php 
if (!isset($_SESSION)) {
  session_start();
}

require_once('../../Connections/tams.php');
require_once('../../param/param.php');
require_once('../../functions/function.php');

$MM_authorizedUsers = "20,24";
$MM_donotCheckaccess = "true";

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
    if (($strUsers == "") && true) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "index.php";
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


mysql_select_db($database_tams, $tams);
$query_rsprog = "SELECT progid, progname FROM programme";
$rsprog = mysql_query($query_rsprog, $tams) or die(mysql_error());
$row_rsprog = mysql_fetch_assoc($rsprog);
$totalRows_rsprog = mysql_num_rows($rsprog);


mysql_select_db($database_tams, $tams);
$query_rsses = "SELECT *
                    FROM `session`
                    ORDER BY `sesid` DESC
                    LIMIT 0 , 1";
$rsses = mysql_query($query_rsses, $tams) or die(mysql_error());
$row_rsses = mysql_fetch_assoc($rsses);
$totalRows_rsses = mysql_num_rows($rsses);

$ver_type = 1;
$ver = 'FALSE';
$insert_row = 0;
$insert_error = array();
$uploadstat = "";
if( isset($_POST['submit']) && $_POST['submit'] == "Upload"){ //database query to upload Admitted Student		

	  if(is_uploaded_file($_FILES['filename']['tmp_name'])){
		  
		//Import uploaded file to Database	
		$handle = fopen($_FILES['filename']['tmp_name'], "r");
		
		
		$uploaded = true;
                mysql_query("BEGIN", $tams);
                
		while (($data = fgetcsv($handle, 1500, ",")) !== FALSE) {
			// Update prospective table with the admited students	
			 $update_query = sprintf("UPDATE prospective SET score=%s, progofferd=%s, adminstatus=%s WHERE jambregid=%s",                       
                                                    GetSQLValueString($data[1], "int"),
                                                    GetSQLValueString($_POST['progid'], "int"),
                                                    GetSQLValueString($data[2], "text"),
                                                    GetSQLValueString($data[0], "text"));
				
			$rsinsert = mysql_query($update_query, $tams);
			
                        //insert verification code for each uploade prospective student 
                        $insert_query = sprintf("INSERT INTO verification (stdid, sesid, type, ver_code )"
                                                . " VALUES(%s, %s, %s, %s)",
                                                GetSQLValueString($data[0], "text"),
                                                GetSQLValueString($row_rsses['sesid'], "int"),
                                                GetSQLValueString($ver_type, "int"),
                                                GetSQLValueString(uniqid(), "text"));
                        
			$rsinsert_verCode = mysql_query($insert_query, $tams);
			
			if( $rsinsert ){// && $update1){
				$insert_row++;
			}
			else{		
				$insert_error[] = $data[0];
				$uploaded = false;
			}
		}
		
		if( $uploaded ){
                    mysql_query("COMMIT", $tams);
			
			
			$uploadstat = "<p style='color :green'>Upload Successful! ".$insert_row." Prospective students uploaded.<p>";
			
		}else{
                    
			mysql_query("ROLLBACK", $tams);
			$uploadstat = "<p style='color :red'>Upload Unsuccessful! The following Prospective student could not be uploaded:<br/>";
			foreach( $insert_error as $error){
				$uploadstat .= $error."<br/>";
			}
			
			$uploadstat .= "Please check your CSV file and try again, or contact system admin.</p>";
		}
		fclose($handle);
	}
	
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout($site_root.'/ict');  
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
<?php include '../include/topmenu.php'; ?>
  </div>
  <!-- end .topmenu --> 
  
  <div class="loginuser">
  <?php include '../include/loginuser.php'; ?>
  
  <!-- end .loginuser --></div>
  <div class="pagetitle">
    <table width="600">
      <tr>
        <td><!-- InstanceBeginEditable name="pagetitle" -->Admission Management <!-- InstanceEndEditable --></td>
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
                <form name="form1" method="post" action="<?php echo $editFormAction?>" enctype="multipart/form-data">
                    <table class="table table-bordered table-condensed table-striped">
                        <tr>
                            <td colspan="2"><?php echo $uploadstat?></td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <p style="font-weight: bold; color: blue">Bellow is an Example of your CSV file structure :</p>
                                <table  width="300" align="center"class="table table-bordered table-condensed">
                                    <tr style="font-weight: bold;color: #2a85a0">
                                        <td>Form No</td>
                                        <td>Post UTMe score</td>
                                        <td>Admission Status</td>
                                    </tr>
                                    <tr style="color: #b3b3b3">
                                        <td>10UTME0000</td>
                                        <td>80</td>
                                        <td>Yes</td>
                                    </tr>
                                    <tr style="color: #b3b3b3">
                                        <td>10UTME0002</td>
                                        <td>70</td>
                                        <td>Yes</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold">Programme</td>
                            <td>
                                <select name="progid" style="width: 200px">
                                    <option value="-1">Choose</option>
                                    <?php do{?>
                                    <option value="<?php echo $row_rsprog['progid']?>"><?php echo $row_rsprog['progname']?></option>
                                    <?php } while($row_rsprog = mysql_fetch_assoc($rsprog))?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold">CSV File</td>
                            <td><input type="file" name="filename"/></td>
                        </tr>
                        <tr>
                            <td colspan="2"> <input type="submit" name="submit" value="Upload"/></td>

                        </tr>
                    </table>
                </form>
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