<?php 
require_once('../../Connections/tams.php');

if (!isset($_SESSION)) {
  session_start();
}



require_once('../../param/param.php');
require_once('../../functions/function.php');

if (!function_exists("GetSQLValueString")) {
    function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = ""){
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
mysql_select_db($database_tams,$tams);
$query = sprintf("SELECT * FROM prospective WHERE jambregid=%s", GetSQLValueString(getSessionValue('MM_Username'), "text"));
$rsResult =  mysql_query($query, $tams) or die(mysql_error());
$row_result = mysql_fetch_assoc($rsResult);


$paydesc = "O'LEVEL VERIFICATION FEE";
	

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root.'/prospective' );   
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
<?php include '../../include/topmenu.php'; ?>
  </div>
  <!-- end .topmenu --> 
  
  <div class="loginuser">
  <?php include '../../include/loginuser.php'; ?>
  
  <!-- end .loginuser --></div>
  <div class="pagetitle">
      Payment Notification
<!--    <table width="600">
      <tr>
          <td> InstanceBeginEditable name="pagetitle"  <img src="img/visa.jpg" width="70px" height="30px" />  Visa Instruction  InstanceEndEditable </td>
      </tr>
    </table>-->
  </div>
<div class="sidebar1">
   
    <?php include '../../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
        <table width="690" >
            <tr>
                <td align="center" style="font-weight: bolder"><p> <?php echo $paydesc ?> NOTIFICATION </p></td>
            </tr>
            <tr >
                <td align="center">
                    <table class="table table-bordered table-condensed table-striped" style="width: 90%; alignment-adjust: central">
                        <tr>
                            <th width="170">Full Name :</th>
                            <td><?php echo $row_result['lname'].' '.$row_result['fname'].' '.$row_result['mname']?></td>
                        </tr>
                        <tr>
                            <th width="170">UTME Reg. No. :</th>
                            <td><?php echo $row_result['jambregid']?></td>
                        </tr>
                        <tr>
                            <th colspan="2">
                                <p style="color: red; alignment-adjust: central">Your payment transaction has been canceled</p>
                            </th>
                        </tr>
                    </table>
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