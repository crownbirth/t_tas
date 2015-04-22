<?php require_once('../../Connections/tams.php'); ?>
<?php
// *** Validate request to login to this site.
if (!isset($_SESSION)) {
  session_start();
}

$reroot = 'reguler.php';
require_once('../../param/param.php');
require_once('../../functions/function.php');

$MM_authorizedUsers = "20, 22";
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

$MM_restrictGoTo = "../index.php";
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
//*****************************************
$currentPage = $_SERVER["PHP_SELF"];

$maxRows_Rsall = 25;
$pageNum_Rsall = 0;
if (isset($_GET['pageNum_Rsall'])) {
  $pageNum_Rsall = $_GET['pageNum_Rsall'];
}
$startRow_Rsall = $pageNum_Rsall * $maxRows_Rsall;
//***********************************************
mysql_select_db($database_tams, $tams);
$query = sprintf("SELECT * FROM `olevel_veri_data` WHERE treated = 'No' AND level <> 'UTME' ORDER BY date ASC ");
$query_limit_verify = sprintf("%s LIMIT %d, %d", $query, $startRow_Rsall, $maxRows_Rsall);

            $verify = mysql_query($query_limit_verify, $tams) or die(mysql_error());
            $verify_row = mysql_fetch_assoc($verify);
            $verify_row_num = mysql_num_rows($verify);
            
if (isset($_GET['totalRows_Rsall'])) {
  $totalRows_Rsall = $_GET['totalRows_Rsall'];
} else {
  $all_Rsall = mysql_query($query);
  $totalRows_Rsall = mysql_num_rows($all_Rsall);
}
$totalPages_Rsall = ceil($totalRows_Rsall/$maxRows_Rsall)-1;

$queryString_Rsall = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_Rsall") == false && 
        stristr($param, "totalRows_Rsall") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_Rsall = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_Rsall = sprintf("&totalRows_Rsall=%d%s", $totalRows_Rsall, $queryString_Rsall);


    if(isset($_POST['submit'])  ){
        
        $cur_detais = $_GET['id'];
        $cur_ordid = $_GET['ordid'];
        
        if($_POST['submit'] == 'Yes'){
            
            $msg = "<p style='color:green'>Your Olevel Result has been PRINTED by the ICT <br/> and it is being forwarded to Admission's Office for Verification .</p>";
            
            mysql_select_db($database_tams, $tams);
            
            mysql_query('BEGIN', $tams);
            
                $query = sprintf("UPDATE `olevel_veri_data` SET `treated` = 'Yes', approve = 'Yes', return_msg = %s, date_treated=%s, who=%s WHERE id = %s", 
                                            GetSQLValueString($msg, 'text'),
                                             GetSQLValueString(date('Y-m-d H:i:s'), 'date'),
                                            GetSQLValueString($_SESSION['MM_Username'], 'text'),
                                            GetSQLValueString($_GET['id'], 'text'));

                $verify = mysql_query($query, $tams) or die(mysql_error());

                $query = sprintf("UPDATE `olevelverifee_transactions` SET `pay_used` = 'Yes' WHERE status='APPROVED' AND can_no = %s AND ordid=%s",
                                            GetSQLValueString($_GET['stdid'], 'text'),
                                            GetSQLValueString($cur_ordid, 'text'));

                $verify = mysql_query($query, $tams) or die(mysql_error());
            
            mysql_query('COMMIT', $tams);
            
        }elseif($_POST['submit'] == 'No'){
            
            $msg = "<p style='color:red'>ICT could NOT PRINT your O'Level Result  <br/>Your Card details may be wronge. Please re-submit.</p>";
            
            mysql_select_db($database_tams, $tams);
            
            mysql_query('BEGIN', $tams);
            
                $query = sprintf("UPDATE `olevel_veri_data` SET `treated` = 'Yes', approve = 'No', return_msg = %s, date_treated=%s, who=%s WHERE id = %s", 
                                            GetSQLValueString($msg, 'text'),
                                             GetSQLValueString(date('Y-m-d H:i:s'), 'date'),
                                            GetSQLValueString($_SESSION['MM_Username'], 'text'),
                                            GetSQLValueString($cur_detais, 'text'));

                $verify = mysql_query($query, $tams) or die(mysql_error());

                $query = sprintf("UPDATE `olevelverifee_transactions` SET `pay_used`='Yes' WHERE status='APPROVED' AND can_no = %s AND ordid=%s",
                                            GetSQLValueString($_GET['stdid'], 'text'),
                                            GetSQLValueString($cur_ordid, 'text'));

                $verify = mysql_query($query, $tams) or die(mysql_error());
            
            mysql_query('COMMIT', $tams);
            
        }
        header("Location: ". $reroot); 
        exit;  
    }          
    
if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
    doLogout($site_root.'/ict');  
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/icttemplate.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php require('../../param/site.php'); ?>
<title><?php echo $university ?> </title>
<!-- InstanceEndEditable -->
<link href="../../css/template.css" rel="stylesheet" type="text/css" />
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
        <td><!-- InstanceBeginEditable name="pagetitle" --> Returning Student O'Level Verification Page  <!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
 
      <table width="690" class="table table-bordered">
          <tr>
              <td>
                    <p>
                        <strong> Note !</strong><br/>
                        WAEC = WAEC (May/ June) &nbsp;&nbsp;&nbsp;&nbsp; GCE = WAEC (Nov/ Dec)<br/>
                        
                        NECO  = NECO (June/ July) &nbsp;&nbsp;&nbsp;NECO GCE = NECO (Nov/ Dec)<br/>
                       
                    </p><p>&nbsp;</p>
                    <p>
                        <a href="treated2.php">
                            <input type="button" value="View Treated"/>
                        </a>
                    </p>
              </td>
          </tr>    
      <tr>
          <td>
              <table class="table table-bordered table-condensed table-striped table-hover">
                <thead>
                    <tr>
                        <th  width="70">Matric No</th>
                        <th width="70">Exam Type</th>
                        <th width="20">Exam Year</th>
                        <th>Exam No</th>
                        <th>Card S/N</th>
                        <th>Card Pin</th>
                        <th>Printed</th>
                        
                    </tr>
                </thead>
                <tbody>
                    
                    <?php 
                    if($verify_row_num > 0){
                    do{?>
                        <tr>
                            <td>
                                <a href="<?php echo (strlen($verify_row['stdid']) == 11)? '../../ict/profile.php?stid='.$verify_row['stdid']:'../../ict/viewform.php?stid='.$verify_row['stdid']?>">
                                    <?php echo $verify_row['stdid'] ;?>
                                </a>    
                            </td>
                            <td><?php echo $verify_row['exam_type']; ?></td>
                            <td><?php echo $verify_row['exam_year']; ?></td>
                            <td><?php echo $verify_row['exam_no']; ?></td>
                            <td><?php echo $verify_row['card_no']; ?></td>
                            <td><?php echo $verify_row['card_pin']; ?></td>
                            
                            
                            
                            <?php if($verify_row['treated'] == 'Yes'){?>
                                <td><?php echo "Treated -".$verify_row['approve']."-"?></td>
                            <?php }else{?>
                                <form name='form1' method="POST" action="<?php echo urldecode(' reguler.php?id='.$verify_row['id'].'&stdid='.$verify_row['stdid'].'&ordid='.$verify_row['ordid']) ?>">
                                    <td><input type="submit" name='submit' value="Yes"/>&nbsp;&nbsp; | &nbsp;&nbsp;<input type="submit"  name='submit' value="No"/></td>
                                </form>
                            <?php }?>
                        </tr>
                    <?php } while($verify_row = mysql_fetch_assoc($verify));
                    }else{
                    ?>
                    <tr>
                        <td colspan="8" align="center"><p style="color: red">No O'Level Result Card Submitted </p></td>
                    </tr>
                    <?php }?>
                </tbody>
              </table>
          </td>
        </tr>
        <tr>
            <td align="center">
                <table  class="table table-bordered table-condensed table-striped">
                    <tr width="50" align="center">
                        <td><p><a href="<?php printf("%s?pageNum_Rsall=%d%s", $currentPage, max(0, $pageNum_Rsall - 1), $queryString_Rsall); ?>"><< Prev</a></p></td>
                        <td><?php echo 'Page '.($pageNum_Rsall + 1) ." of ". ($totalPages_Rsall + 1); ?></td>
                        <td><p><a href="<?php printf("%s?pageNum_Rsall=%d%s", $currentPage, min($totalPages_Rsall, $pageNum_Rsall + 1), $queryString_Rsall); ?>">Next >></a></p></td>
                    </tr>
                </table>
            </td>
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
</html>