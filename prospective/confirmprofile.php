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
$query_rspros = sprintf("SELECT *  
                                    FROM prospective 
                                    WHERE jambregid=%s",
                                    GetSQLValueString(getSessionValue('MM_Username'), "text"));
$rspros = mysql_query($query_rspros, $tams) or die(mysql_error());
$row_rspros = mysql_fetch_assoc($rspros);
$totalRows_rspros = mysql_num_rows($rspros);


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
          <!-- end .loginuser -->
        </div>
        <div class="pagetitle">
            <table width="600">
                <tr>
                    <td><!-- InstanceBeginEditable name="pagetitle" -->Prospective Data Confirmation <!-- InstanceEndEditable --></td>
                </tr>
            </table>
        </div>
        <div class="sidebar1">
            <?php include '../include/sidemenu.php'; ?>
        </div> 
        <div class="content">
            <!-- InstanceBeginEditable name="maincontent" -->
                <table width="690" class="table table-condensed table-bordered table-striped ">
                    <tr>
                        <td>UTME Reg. No. :</td>
                        <td><?php echo $row_rspros['jambregid'] ?></td>
                    </tr>
                    <tr>
                        <td>First Name :</td>
                        <td><?php echo $row_rspros['fname'] ?></td>
                    </tr>
                    <tr>
                        <td>Middle Name :</td>
                        <td><?php echo $row_rspros['mname'] ?></td>
                    </tr>
                    <tr>
                        <td>Last Name :</td>
                        <td><?php echo $row_rspros['lname'] ?></td>
                    </tr>
                    
                    <tr>
                        <td colspan="2" align="center"><input type="button"  onclick="javascript:location='admform.php'" value="Proceed to registartion"/></td>
                    </tr>
                </table>
            <!-- InstanceEndEditable -->
        </div>
        <div class="footer">
            <p><!-- end .footer -->   
                <?php require '../include/footer.php'; ?>
            </p>
        </div>
        <!-- end .container -->
    </div>
</body>
<!-- InstanceEnd --></html>