<?php require_once('../Connections/tams.php');
//require_once('../Connections/conn_burmas.php');

if (!isset($_SESSION)) {
  session_start();
}

require_once('../param/param.php'); 
require_once('../functions/function.php');

$MM_authorizedUsers = "20";
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

mysql_select_db($database_tams, $tams);

$query_pstd = '';

$msg = null;
$id = '';
$type = '';
$std_name = '';
$params = false;
$gen_matric = false;
$gen_type = 'new';

if(isset($_GET['id']) && $_GET['id'] != '' && isset($_GET['type']) && $_GET['type'] != '') {
    $params = true;
    $id = $_GET['id'];
    $type = $_GET['type'];
    $query_stdinfo = '';
    
    $query_trans = sprintf("SELECT * "
                    . "FROM schfee_transactions "
                    . "WHERE ordid = %s", GetSQLValueString($id, "text"));
    $trans = mysql_query($query_trans, $tams) or die(mysql_error());
    $row_trans = mysql_fetch_assoc($trans);
    $totalRows_trans = mysql_num_rows($trans);
    $transtatus = $row_trans['status'];
    
    // process initial page load
    switch($type) {
        case 'reg':
            $enttype = 'regular';
            $stdid = $row_trans['matric_no'];
            $query_stdinfo = sprintf("SELECT stdid, fname, lname "
                    . "FROM student "
                    . "WHERE stdid = %s", GetSQLValueString($stdid, "text"));
            break;

        case 'pros':
            $enttype = 'prospective';
            $stdid= $row_trans['can_no'];
            $query_stdinfo = sprintf("SELECT jambregid as stdid, fname, lname "
                    . "FROM prospective "
                    . "WHERE jambregid = %s", GetSQLValueString($stdid, "text"));

            break;

    }    
    
    $stdinfo = mysql_query($query_stdinfo, $tams) or die(mysql_error());
    $row_stdinfo = mysql_fetch_assoc($stdinfo);
    $totalRows_stdinfo = mysql_num_rows($stdinfo);
    
    $std_name = $row_stdinfo['lname'].' '.$row_stdinfo['fname'];   
    
    // Check payment for prospective students.
    $query_payment = sprintf("SELECT * "
                    . "FROM schfee_transactions "
                    . "WHERE can_no = %s AND status = 'APPROVED'", GetSQLValueString($row_stdinfo['stdid'], "text"));
    $payment = mysql_query($query_payment, $tams) or die(mysql_error());
    $row_payment = mysql_fetch_assoc($payment);
    $totalRows_payment = mysql_num_rows($payment);
    
    
    $query_pros = sprintf("SELECT * "
                    . "FROM student "
                    . "WHERE jambregid = %s", GetSQLValueString($row_trans['can_no'], "text"));
    $pros = mysql_query($query_pros, $tams) or die(mysql_error());
    $row_pros = mysql_fetch_assoc($pros);
    $totalRows_pros = mysql_num_rows($pros);        
        
    // Check if matric generation button should be enabled.
    if($type == 'pros' && $totalRows_payment > 0) {        
    
        // Check if entry exist in the student table.
        if($totalRows_pros != 0) {
            // If entry exists, check if matric number was generated.
            if($row_pros['stdid'] == '' || $row_pros['stdid'] == NULL) {
                // indicate generation of matric number and update of entry in the student table.
                $gen_matric = true;
                $gen_type = 'update';
            }
        }else {
            // indicate generation of matric number and new entry in the student table.
            $gen_matric = true;
        }
        
        if($gen_matric && isset($_GET['gen'])) {
            $query_ses = sprintf("SELECT * "
                                    . "FROM session "
                                    . "ORDER BY sesid DESC LIMIT 0, 1", 
                                    GetSQLValueString(getSessionValue('MM_Username'), "text"));
            $ses =  mysql_query($query_ses, $tams) or die(mysql_error());
            $row_ses = mysql_fetch_assoc($ses);
            
            mysql_query('START TRANSACTION;', $tams);
            $new_matric = migrate_details($row_ses, $id, $row_trans['can_no'], $tams, $gen_type);
            
            if($new_matric && $new_matric != '' ) {
                mysql_query('COMMIT;', $tams);
                $gen_matric = false;
                $msg = "A Matric number has been successfully generated for this student! "
                        . "The matric number is {$new_matric}.";
            }else {
                mysql_query('ROLLBACK;', $tams);
                $msg = "A Matric number could not be generated for this student! "
                        . "Please try again.";
            }
            
        }
    }
    
    // Process transaction update
    if(isset($_POST['update'])) {
        if(!empty($_POST)) {
            
            $update_array = array();
            
            foreach($_POST as $name => $value) {
                if($name == 'update') {
                    continue;
                }

                if(isset($value) && $value != '') {
                    if($name == 'amt') {
                        $value = str_replace(',', '', $value);
                        
                        if(stripos($value, 'ngn') !== false) {
                            $value = substr($value, 3);
                        }
                        
                        $value = 'NGN'.number_format($value, 2);
                    }

                    $update_text = sprintf('%s = %s', 
                            GetSQLValueString($name, 'defined', $name), 
                            GetSQLValueString($value, 'text'));

                    array_push($update_array, $update_text);

                    $row_trans[$name] = $value;
                    
                }

            }
            
            if($row_trans['status'] == 'APPROVED') {
                
                // Check if entry exist in the student table.
                if($totalRows_pros != 0) {
                    // If entry exists, check if matric number was generated.
                    if($row_pros['stdid'] == '' || $row_pros['stdid'] == NULL) {
                        // indicate generation of matric number and update of entry in the student table.
                        $gen_matric = true;
                        $gen_type = 'update';
                    }
                }else {
                    // indicate generation of matric number and new entry in the student table.
                    $gen_matric = true;
                }
                $transtatus = $row_trans['status'];
            }
            
                        
            if(!empty($update_array)) {
                $updateSQL = sprintf("UPDATE "
                        . " schfee_transactions "
                        . "SET %s "
                        . "WHERE ordid = %s",
                        GetSQLValueString('set', "defined", implode(', ', $update_array)),
                        GetSQLValueString($id, "text"));

                $Result = mysql_query($updateSQL, $tams) or die(mysql_error());

                $param['entid'] = $stdid;
                $param['enttype'] = $enttype;
                $param['action'] = 'edit';
                $cont = array(
                            'status' => array(
                                'old' => $row_trans['status'],
                                'new' => $_POST['status']
                            )
                        );
                $param['cont'] = json_encode($cont);
                if($Result) {
                    audit_log($param);
                }else {
                    $param['status'] = 'failed';
                    audit_log($param);
                }
            }
        }else {
            $msg = 'The transaction status type selected is incorrect!';
        }
    }
    
}else {
    $msg = 'The transaction information is incomplete!';
}

// Query status of specified order
//$ch = curl_init();
//$url="https://cipg.diamondbank.com/cipg/MerchantServices/"
//        . "UpayTransactionStatus.ashx?MERCHANT_ID=00456&ORDER_ID={$row_history['can_no']}";
//curl_setopt($ch, CURLOPT_URL, $url);
//
//// Set so curl_exec returns the result instead of outputting it.
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//
//// Get the response and close the channel.
//$response = curl_exec($ch);
//$response=(string)$response;


if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout($site_root.'/ict');  
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
        <td><!-- InstanceBeginEditable name="pagetitle" --><?php echo $std_name?> <!-- InstanceEndEditable --></td>
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
        <?php }
            if($params){
        ?>
        
        <tr>
            <td>
                <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1">
                  <table class="table table-striped">
                    <tr valign="baseline">
                      <td nowrap="nowrap" align="right">Candidiate Number:</td>
                      <td><?php echo $row_stdinfo['stdid']?></td>
                    </tr>
                    <tr valign="baseline">
                      <td nowrap="nowrap" align="right">Name:</td>
                      <td><?php echo $std_name?></td>
                    </tr>
                    <tr valign="baseline">
                      <td nowrap="nowrap" align="right">Reference:</td>
                      <td><?php echo $row_trans['reference']?></td>
                    </tr>
                    <tr valign="baseline">
                      <td nowrap="nowrap" align="right">Date:</td>
                      <td><?php echo $row_trans['date_time']?></td>
                    </tr>
                    <tr valign="baseline">
                      <td nowrap="nowrap" align="right">Year:</td>
                      <td><?php echo $row_trans['year']?></td>
                    </tr>
                    <tr valign="baseline">
                      <td nowrap="nowrap" align="right">Status:</td>
                      <td><select name="status">
                        <option value="CANCELLED" <?php if($transtatus == 'CANCELLED') echo 'selected'?>>Canceled</option>
                        <option value="DECLINED" <?php if($transtatus == 'DECLINED') echo 'selected'?>>Declined</option>
                        <option value="PENDING" <?php if($transtatus == 'PENDING') echo 'selected'?>>Pending</option>
                        <option value="APPROVED" <?php if($transtatus == 'APPROVED') echo 'selected'?>>Approved</option>
                      </select></td>
                    </tr>
                    <?php if(!isset($row_trans['amt']) || $row_trans['amt'] == '') {?>
                    <tr valign="baseline">
                      <td nowrap="nowrap" align="right">Amount:</td>
                      <td><input type="text" name="amt"/></td>
                    </tr>
                    <?php }?>
                      
                    <?php if(!isset($row_trans['pan']) || $row_trans['pan'] == '') {?>
                    <tr valign="baseline">
                      <td nowrap="nowrap" align="right">PAN:</td>
                      <td><input type="text" name="pan"/></td>
                    </tr>
                    <?php }?>
                      
                    <?php if(!isset($row_trans['resp_desc']) || $row_trans['resp_desc'] == '') {?>
                    <tr valign="baseline">
                      <td nowrap="nowrap" align="right">Response Description:</td>
                      <td><input type="text" name="resp_desc"/></td>
                    </tr>
                    <?php }?>
                      
                    <?php if(!isset($row_trans['resp_code']) || $row_trans['resp_code'] == '') {?>
                    <tr valign="baseline">
                      <td nowrap="nowrap" align="right">Response Code:</td>
                      <td><input type="text" name="resp_code"/></td>
                    </tr>
                    <?php }?>
                      
                    <?php if(!isset($row_trans['auth_code']) || $row_trans['auth_code'] == '') {?>
                    <tr valign="baseline">
                      <td nowrap="nowrap" align="right">Authentication Code:</td>
                      <td><input type="text" name="auth_code"/></td>
                    </tr>
                    <?php }?>
                  </table>
                  <input type="submit" name="update" value="Update Transaction" />
                </form>
                <?php 
                    if($gen_matric) {
                ?>
                <br/>
                <div>
                    This student does not have a matric number, but has successfully paid the school fees! 
                    Click the button below to generate a matric number for the student.
                </div>
                <button type="button" 
                        onclick="generate_matric()">
                    Generate Matric
                </button>
                <?php }?>
            </td>
        </tr>
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
<script type="text/javascript">
    function generate_matric() {
        if(location.search.indexOf('gen') === -1) {
            location.search = location.search+<?php echo "'&gen'"?>;
        }else {
            location.search = location.search;
        }
    }   

</script>
<!-- InstanceEnd -->
</html>

