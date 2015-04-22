<?php require_once('../Connections/tams.php'); 
//require_once('../Connections/conn_burmas.php');

if (!isset($_SESSION)) {
  session_start();
}

$MM_authorizedUsers = "20";
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

$MM_restrictGoTo = "index.php";
if (!((isset($_SESSION['MM_Username'])) && 
        (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
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

mysql_select_db($database_tams, $tams);

$query_rssess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,1";
$rssess = mysql_query($query_rssess, $tams) or die(mysql_error());
$row_rssess = mysql_fetch_assoc($rssess);
$totalRows_rssess = mysql_num_rows($rssess);

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$msg = null;
$search_term = null;
$type = null;
$pType = "";

$payType = array(
    "schfee" => "School Fees",
    "accfee" => "Acceptance Fees",
    "appfee" => "Application Fees"
);

if (isset($_POST['search'])) { // && $_POST['search'] != NULL) {
    
    if($_POST['search'] != '') {
        
        $search_term = "%{$_POST['search']}%";
        $type = isset($_POST['type'])? $_POST['type']: '';
        $pType = isset($_POST['pType'])? $_POST['pType']: '';
        $db = $tams;
        
        if($type != '') {
            $table = 'schfee_transactions';
            switch($type) {
                case 'pros': 
                    if($pType == 'accfee') {
                        $table = 'accfee_transactions';
                    }elseif($pType == 'appfee') {
                        $table = 'appfee_transactions';
                    }else {
                        $pType = 'schfee';
                    }
                    
                    $query_rsstdnt = sprintf("SELECT ordid, amt, date_time, status, jambregid as stdid, lname, fname "
                            . "FROM prospective p "
                            . "JOIN %s s ON p.jambregid = s.can_no "
                            . "WHERE ordid IS NOT NULL "
                            . "AND (lname LIKE %s "
                            . "OR fname LIKE %s "
                            . "OR jambregid LIKE %s)", 
                            GetSQLValueString($table, "defined", $table), 
                            GetSQLValueString($search_term, "text"), 
                            GetSQLValueString($search_term, "text"), 
                            GetSQLValueString($search_term, "text"));
                    break;

                case 'reg': 
                    $query_rsstdnt = sprintf("SELECT ordid, amt, date_time, c.status, stdid, lname, fname "
                            . "FROM student s "
                            . "JOIN schfee_transactions c ON s.stdid = c.matric_no "
                            . "WHERE ordid IS NOT NULL "
                            . "AND (lname LIKE %s "
                            . "OR fname LIKE %s "
                            . "OR stdid LIKE %s)", 
                            GetSQLValueString($search_term, "text"), 
                            GetSQLValueString($search_term, "text"), 
                            GetSQLValueString($search_term, "text"));
                    break;

                default: 
                    break;
            }            
            
            $rsstdnt = mysql_query($query_rsstdnt, $db) or die(mysql_error());
            $row_rsstdnt = mysql_fetch_assoc($rsstdnt);
            $totalRows_rsstdnt = mysql_num_rows($rsstdnt);
            
        }else {
            $msg = 'You must select a valid type!';
        }
        
        $search_term = $_POST['search'];
    }else {    
        $msg = 'You did not enter a search term!';
    }
}

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout($site_root.'/ict');  
}

function getAccessByName($val){
	if ($val==20){
		echo "Admin Staff";
	}
	else if($val==21){
		echo "Regular Staff";
		}
	else{
		"";
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <!-- InstanceBegin template="/Templates/icttemplate.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php require('../param/site.php'); ?>
<title><?php echo $university ?> </title>
<!-- InstanceEndEditable -->
<link href="css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<script src="../SpryAssets/SpryCollapsiblePanel.js" type="text/javascript"></script>
<link href="../SpryAssets/SpryCollapsiblePanel.css" rel="stylesheet" type="text/css" />
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Search Payment<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include 'include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
        <?php if($msg) {?>
      <tr>
        <td><?php echo $msg?></td>
      </tr>
        <?php }?>
      <tr>
        <td height="37"><form id="form1" name="form1" method="post" action="">
          <table width="639" border="0" align="center">
            <tr>
              <td height="30" colspan="3">
                  Search Type: 
                  <select name="type">
                      <option value="reg" <?php if($type == 'reg') echo 'selected'?>>Regular</option>
                      <option value="pros" <?php if($type == 'pros') echo 'selected'?>>Prospective</option>
                  </select>
                  &nbsp;&nbsp;
                  Payment Type: 
                  
                    <select name="pType">
                        <?php foreach($payType as $value => $label) {?>
                        <option value="<?php echo $value?>" <?php if($pType == $value) echo 'selected'?>>
                            <?php echo $label?>
                        </option>
                        <?php }?>
                    </select>
              </td>
            </tr>
            <tr>
                <td height="30">Search By Name or Matric No </td>
                <td align="center">
                    <input name="search" type="text" id="search" size="55" value="<?php echo $search_term?>"/>
                </td>
                <td align="center">
                    <input type="submit" name="submit" id="submit" value="Search" />
                </td>
            </tr>
          </table>
        </form>
        <table width="626" align="center" class="table table-striped table-condensed" style="font-weight: normal">
        	<tr align="center">
            	<th width="20">S/n</th>
            	<th width="100">Matric</th>
                <th width="300">Full Name</th>                
            	<th width="120">Order No.</th>
            	<th width="100">Amount</th>
            	<th width="150">Status</th>
            	<th width="150">Date</th>
                <th width="110">Actions</th>
            </tr>
             <?php
	   if(!empty($row_rsstdnt)){
	   $i = 1; do {
	   ?>
            <tr align="center" >
            	<td><?php echo $i;?></td>	
            	<td><?php echo $row_rsstdnt['stdid']?></td>
                <td><?php echo $row_rsstdnt['fname']." ".$row_rsstdnt['lname']?></td>
                <td><?php echo $row_rsstdnt['ordid']?></td>
                <td><?php echo $row_rsstdnt['amt']?></td>
                <td><?php echo $row_rsstdnt['status']?></td>
                <td><?php echo $row_rsstdnt['date_time']?></td>
                <td>
                    <a target="_blank" href="editpayment.php?type=<?php echo $type?>&id=<?php echo $row_rsstdnt['ordid']?>">
                        Edit Payments
                    </a>
                </td>
            </tr>
             <?php $i++; } while ($row_rsstdnt = mysql_fetch_assoc($rsstdnt));
		}else {
			?>
            
            <tr>
            	<td style="color:#F00" colspan="8" align="center">
                    <Strong>SORRY !!</Strong> NO Record Available Search by Name, Jamb Reg. No. or Matric No.!
                </td>
            </tr>
            
            <?php }?>
        </table>
        </td>
      </tr>
    </table>
    <script type="text/javascript">
    </script>
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

