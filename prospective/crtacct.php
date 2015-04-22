<?php 
if (!isset($_SESSION)) {
  session_start();
}
$error_msg = '';
require_once('../Connections/tams.php');
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

mysql_select_db($database_tams, $tams);
$query = sprintf("SELECT * FROM session ORDER BY sesid DESC LIMIT 1 ");
$session= mysql_query($query, $tams) or die(mysql_error());
$row_session = mysql_fetch_assoc($session);
$totalRows_session = mysql_num_rows($session);

$error_msg = '';
        
//set the new Admission session Name
$split = explode('/',  $row_session['sesname']);
$adm_ses_name = ($split[0]-1).'/'.($split[1]-1);

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1") && $_POST['admtype']!= -1 && $_POST['fname'] != null && $_POST['lname']!= null && $_POST['email'] !=null && $_POST['jambregid']!= NULL ) {
     
    mysql_select_db($database_tams, $tams);
    $query_chk = sprintf("SELECT * FROM prospective WHERE jambregid =%s",GetSQLValueString($_POST['jambregid'], "text"));
    $ResultChk = mysql_query($query_chk, $tams) or die(mysql_error());
    $num_row_chk = mysql_num_rows($ResultChk);
    
    if($num_row_chk < 1){  
        $insertSQL = sprintf("INSERT INTO prospective (jambregid, jambyear, admtype, fname, mname, Sex, lname, email, phone, regtype, sesid, access)"
                                                  . " VALUES (%s, %s, %s, %s, %s, %s, %s,%s, %s, %s, %s, %s)",
                                                  GetSQLValueString(strtoupper($_POST['jambregid']), "text"),
                                                  GetSQLValueString($_POST['jambyear'], "text"),
                                                  GetSQLValueString("UTME", "text"),
                                                  GetSQLValueString($_POST['fname'], "text"),
                                                  GetSQLValueString($_POST['mname'], "text"),
                                                  GetSQLValueString($_POST['sex'], "text"),
                                                  GetSQLValueString($_POST['lname'], "text"),
                                                  GetSQLValueString($_POST['email'], "text"),
                                                  GetSQLValueString($_POST['phone'], "text"),
                                                  GetSQLValueString("coi", "text"),
                                                  GetSQLValueString(($row_session['sesid']), "int"),
                                                  GetSQLValueString(11, "int"));
                  mysql_select_db($database_tams, $tams);
                  $Result1 = mysql_query($insertSQL, $tams) or die(mysql_error());
                  $insertid = mysql_insert_id();

              if(mysql_affected_rows() > 0) {

                      $queryPstd = sprintf("SELECT * FROM prospective WHERE jambregid = %s",
                                                      GetSQLValueString($_POST['jambregid'], "text"));
                      $pstd = mysql_query($queryPstd, $tams) or die(mysql_error());
                      $row_pstd = mysql_fetch_assoc($pstd);

                      $_SESSION['newacct']['fname'] = $row_pstd['fname'];
                      $_SESSION['newacct']['formnum'] = $row_pstd['formnum'];
                      $_SESSION['newacct']['lname'] = $row_pstd['lname'];
                      $_SESSION['newacct']['email'] = $row_pstd['email'];
                      $_SESSION['newacct']['jambregid']=$row_pstd['jambregid'];

                      $insertGoTo = "msg1.php";
                  if (isset($_SERVER['QUERY_STRING'])) {
                        $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
                        $insertGoTo .= $_SERVER['QUERY_STRING'];
                  }
                 header(sprintf("Location: %s", $insertGoTo));
              }
    }
    else{

        $error_msg = "The details you provided are either incorrect or your UTME No. already exits on our system!";
         //header(sprintf("Location: %s", $_SERVER['PHP_SELF']));
        }
}

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root.'/prospective' );   
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
<script src="../scripts/tams.js" type="text/javascript"></script>
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
        <td><!-- InstanceBeginEditable name="pagetitle" --><?php echo "{$adm_ses_name} UTME Application "?><!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
      <table width="690">
          <tr>
              <td></td>
          </tr>
          <tr>
              <td>
                  <form name="form1" method="post" action="<?php echo $editFormAction?>">
                      <table width="690" class="table  table-striped table-condensed">
                            <tr><td colspan="4" align="center" style="color: red"><p><?php echo (isset($error_msg))? $error_msg :''?></p></td></tr>
                            <tr>
                                <td align="left">UTME Reg. No. :</td>
                                <td align="left"><input type="text" name="jambregid" maxlength="12" required/></td>
                                <td align="left">Exam Year :</td>
                                <td align="left"><input type="text" name="jambyear" maxlength="4" required/></td>
                            </tr>
                            <tr>
                                <td align="left">Surname:</td>
                                <td align="left"><input type="text" name="lname" maxlength="15" required/></td>
                                <td align="left">First Name:</td>
                                <td align="left"><input type="text" name="fname" maxlength="15" required/></td>
                            </tr>
                            <tr>
                                <td align="left">Middle Name:</td>
                                <td align="left"><input type="text" name="mname" maxlength="15" required/></td>
                                <td align="left">Sex :</td>
                                <td align="left">
                                    <select name="sex" required>
                                        <option value="-1">Choose</option>
                                        <option value="M">Male</option>
                                        <option value="F">Female</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td align="left">Phone No:</td>
                                <td align="left"><input type="text" name="phone" maxlength="11" required/></td>
                                <td align="left">E-mail:</td>
                                <td align="left"><input type="email" name="email" maxlength="25" required/><span style="color: #FF1188"> *A valid email is required</span></td>
                            </tr>
                            <tr>
                                <td colspan="4">&nbsp;</td>  
                            </tr>
                            <tr>
                                <td colspan="4" align="center"><input type="submit" name="submit" value="Create New Account"/></td>  
                            </tr>
                        </table>
                        <input type="hidden" name="MM_insert" value="form1" />
                    </form>
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
<!-- InstanceEnd --></html>
