<?php  
if (!isset($_SESSION)) {
  session_start();
}

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

 mysql_select_db($database_tams, $tams);
    $query = sprintf("SELECT * FROM session ORDER BY sesid DESC LIMIT 1 ");
    $session= mysql_query($query, $tams) or die(mysql_error());
    $row_session = mysql_fetch_assoc($session);
    $totalRows_session = mysql_num_rows($session);
    
    //set the new Admission session Name
    $split = explode('/',  $row_session['sesname']);
    $adm_ses_name = ($split[0]-1).'/'.($split[1]-1);
    
 $msg = '';    
if(isset($_POST['jambregid']) && isset($_POST['surnname']) && $_POST['MM_insert']=='form1'){
    
    mysql_select_db($database_tams, $tams);
   $query = sprintf("SELECT * "
                    . "FROM prospective"
                    . " WHERE jambregid=%s "
                    . "AND lname=%s", 
                    GetSQLValueString($_POST['jambregid'], "text"), 
                    GetSQLValueString($_POST['surnname'], "text"));
    $pros= mysql_query($query, $tams) or die(mysql_error());
    $row_pros = mysql_fetch_assoc($pros);
    $foundUser = mysql_num_rows($pros);
    
    if($foundUser){
        //set neccessary session 
        $_SESSION['newacct']['jambreg'] = $row_pstd['jambregid'];
        $_SESSION['MM_Username'] = $row_pros['jambregid'];
        $_SESSION['MM_UserGroup'] = $row_pros['access'];
        $_SESSION['fname'] = $row_pros['fname'];
        $_SESSION['lname'] = $row_pros['lname'];        
        $_SESSION['mname'] = $row_pros['mname'];

            $insertGoTo = "confirmprofile.php";
        if (isset($_SERVER['QUERY_STRING'])) {
              $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
              $insertGoTo .= $_SERVER['QUERY_STRING'];
        }
       header(sprintf("Location: %s", $insertGoTo));
    }
    else{
        $msg = "Login Failed! Try again or Create New Account";
    }

    
}    

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

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
        <td><!-- InstanceBeginEditable name="pagetitle" --> <?php echo $adm_ses_name?> Post UTME / Direct Entry Application <!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690" class="table table-bordered">
      <tr>
        <td width="330">
            <table width="345" class="table table-bordered table-striped">
                <form name="form1" method="post" action="<?php echo $editFormAction?>">
                    <thead>
                        <td colspan="2" align="center"><h3>First Choice Applicant</h3>
                                <p align="justify"> 
                                    Candidates who chose TASUED as their first most preferred institution in the 2014 Unified Tertiary 
                                    Matriculation Examination (UTME), and scored a minimum of 180 in 2014 UTME should Login here.
                                </p>
                            </td>
                    </thead> 
                    <tbody>
                    
                    <tr>
                            <td colspan="2" style="color: red"><?php echo $msg;?></td>
                        </tr> 

                    
                        <tr>
                            <th width="100">UTME Reg. No. :</th>
                            <td><input type="text" name="jambregid"/></td>
                        </tr>
                        <tr>
                            <th>Surname</th>
                            <td><input type="text" name="surnname"/></td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td><input type="submit" name="submit" value="Submit"/></td>
                        </tr>
                    </tbody>
                    <input type="hidden" name="MM_insert" value="form1"/>
                </form>      
            </table>
        </td>
        <td width="330">
            <table  width="345" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <td colspan="2" align="center">
                                <h3>UTME Applicant Seeking Change of Institution</h3>
                                <p align="justify">
                                    Candidates seeking a change of institution to TASUED through JAMB, having scored a minimum 
                                    of 180 in 2014 UTME should Create New Account.
                                </p>
                            </td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="2" width="300" align="center">
                                <input type="button" onclick="javascript:location='crtacct.php'" name="create" value="Create New UTME Account"/>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" width="300" align="center">
                                <p style='color: red'>Direct Entry students should <a href='crtacct1.php'>click here</a> to APPLY</p>
                            </td>
                        </tr>
                    </tbody>  
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
<!-- InstanceEnd --></html>