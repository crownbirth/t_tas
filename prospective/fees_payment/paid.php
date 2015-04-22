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

$query_ses = sprintf("SELECT * "
                        . "FROM session "
                        . "ORDER BY sesid DESC LIMIT 0, 1", 
                        GetSQLValueString(getSessionValue('MM_Username'), "text"));
$ses =  mysql_query($query_ses, $tams) or die(mysql_error());
$row_ses = mysql_fetch_assoc($ses);

$gen_matric = '';

 $paydesc = "SCHOOL FEES PAYMENT";

$query = sprintf("SELECT * "
                    . "FROM prospective "
                    . "WHERE jambregid=%s", 
                    GetSQLValueString(getSessionValue('MM_Username'), "text"));
$rsResult =  mysql_query($query, $tams) or die(mysql_error());
$row_result = mysql_fetch_assoc($rsResult);

// unified payment code

if(isset($_POST["xmlmsg"])) {
    $xml = simplexml_load_string($_POST["xmlmsg"]);			

    foreach($xml->children() as $child) {	
        if ($child->getName() == "ResponseDescription") {
            $_SESSION['response'] = $child;
            $res = $child;                         
        }elseif ($child->getName() == "PurchaseAmountScr") {
            $_SESSION['amt'] = "NGN".$child;
            $amt=$_SESSION['amt'];                        
        }
        if ($child->getName() == "ApprovalCode")
            $_SESSION['approvalcode'] = $child;
        
        if ($child->getName() == "OrderID")
            $ordid = $child;

        if ($child->getName() == "PAN")
            $pan = $child;
        
        if ($child->getName() == "TranDateTime")
            $date=$child;
        
        if ($child->getName() == "OrderStatus")
            $status=$child;
        
        if ($child->getName() == "Brand")
            $brand=$child;

        if ($child->getName() == "PurchaseAmount")
            $rawAmount=$child;
        
        if ($child->getName() == "Name")
            $name=$child;

        if ($child->getName() == "ResponseCode")
            $rc = $child;

        if ($child->getName() == "ApprovalCode")
            $ac = $child;

        if ($child->getName() == "TranDateTime")
            $dt = $child;

//			$portalName=$row_rs_personal["Last_Name"].', '.$row_rs_personal["First_Name"].' '.$row_rs_personal["Other_Names"];		
//			$canNo=$row_rs_personal['Candidate_no'];
    }//end for loop
    
    $year=date('Y');
    $xmlmsg = $_POST['xmlmsg'];
    
    mysql_query('START TRANSACTION', $tams);

    $query_paid= sprintf("UPDATE schfee_transactions 
        SET status = %s, amt = %s, resp_code = %s, resp_desc = %s, auth_code = %s, pan = %s, xml = %s, name = %s, sesid=%s 
        WHERE ordid=%s", 
        GetSQLValueString("APPROVED", "text"), 
        GetSQLValueString($amt, "text"),  
        GetSQLValueString($rc, "text"),  
        GetSQLValueString($res, "text"),  
        GetSQLValueString($ac, "text"),  
        GetSQLValueString($pan, "text"),  
        GetSQLValueString($xmlmsg, "text"),   
        GetSQLValueString($name, "text"),
        GetSQLValueString($row_ses['sesid'], "int"),  
        GetSQLValueString($ordid, "text"));
    $paid= mysql_query($query_paid, $tams);

    $query_paid= sprintf("UPDATE prospective "
                            . "SET schoolfee = %s "
                            . "WHERE jambregid=%s", 
                            GetSQLValueString("Yes", "text"), 
                            GetSQLValueString(getSessionValue('MM_Username'), "text"));
    $paid= mysql_query($query_paid, $tams);


    // check student payment status to avoid duplicate matric

    $query_paid1= sprintf("SELECT matric_no, SUM(percentPaid) as percent FROM schfee_transactions "
                         ."WHERE status=%s "
                         ."AND can_no=%s ",
                         GetSQLValueString("APPROVED", "text"), 
                         GetSQLValueString(getSessionValue('MM_Username'), "text"));
    $paid1 = mysql_query($query_paid1, $tams) or die(mysql_error());
    $row_paid1 = mysql_fetch_assoc($paid1); 

    // matric is generated only on first payment
    if ($row_paid1['matric_no'] == null) { 
        $gen_matric = migrate_details($row_ses, $ordid, getSessionValue('MM_Username'), $tams);
    }  
    
    mysql_query('COMMIT', $tams);
}
    
$row_result['stdid'] = $gen_matric;

$query_paid= sprintf("SELECT * FROM schfee_transactions WHERE ordid=%s",$ordid);
$paid= mysql_query($query_paid, $tams) or die(mysql_error());
$row_paid = mysql_fetch_assoc($paid);


if($rsResult['email']!=null){
    
    $to = $rsResult['email'];
    $subject = "TASUED TAMS Portal: School Fees Payment";          
    $from = "noreply@tasued.edu.ng";
    $headers = "From: TASUED ".$from; 
    $body ="Good day {$portalName},\nYou have successfully paid your school fee\n==Payment Details==\n"
                . "Card PAN: {$pan}\nCard Holder: {$name}\nUnique ID: {$ordid}"
                . "\nTransaction Date & Time: {$date}\nTransaction Reference: {$ref}\nAmount: {$_SESSION['amt']}"
                . "\nAuthorization Code: {$_SESSION['approvalcode']}"
                . "\nTuition Fee\nTai Solarin University of Education, "
                . "Ijebu Ode, Ogun State, Nigeria \nWebsite: www.tasued.edu.ng";
    mail($to,$subject,$body,$headers);

}

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")) {
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
                <td align="center" style="font-weight: bolder"><p> <?php echo $paydesc ?> RECIEPT </p></td>
            </tr>
            <tr >
                <td align="center">
                    <table class="table table-bordered table-condensed table-striped" style="width: 90%; alignment-adjust: central">
                        <tr>
                            <th width="170">Full Name :</th>
                            <td><?php echo $row_result['lname'].' '.$row_result['fname'].' '.$row_result['mname']?></td>
                        </tr>
                        <tr>
                            <th>Matric No. :</th>
                            <td><?php echo $row_result['stdid']?></td> 
                        </tr>
                        <tr>
                            <th>Payment Desc.:</th>
                            <td><?php echo $paydesc?></td>
                        </tr>
                        <tr>
                            <th>Response Desc. :</th>
                            <th><?php echo $res;?></th>
                        </tr>
                        <tr>
                            <th>Amount :</th>
                            <th><?php echo $amt?></th>
                        </tr>
                        <tr>
                            <th>Transaction Reference :</th>
                            <th><?php echo $row_paid['reference'];?></th>
                        </tr>
                        <tr>
                            <th>Date & Time :</th>
                            <th><?php echo $row_paid['date_time']?></th>
                        </tr>
                        <tr>
                            <td>
                                <a target="_blank" href="receipt.php?no=<?php echo $ordid?>">
                                    <button>Print Receipt</button>
                                </a>
                            </td>
                            <td>
                                <a target="_blank" href="../../index.php">
                                    <button>Proceed to registration.</button>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <p style="color: blue; font-weight: bold">
                                    Kindly note your Transaction Reference number as it will be used to track dispute.
                                    A copy of this receipt has been sent to the email address you provided.
                                </p>
                            </td>
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