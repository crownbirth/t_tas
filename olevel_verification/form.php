<?php  
if (!isset($_SESSION)) {
  session_start();
}
$reroot = 'index.php';
$msg = '';



require_once('../Connections/tams.php');
require_once('../param/param.php');
require_once('../functions/function.php');

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root.'/prospective' );   
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
$query = sprintf("SELECT * FROM session ORDER BY sesid DESC LIMIT 1 ");
$session= mysql_query($query, $tams) or die(mysql_error());
$row_session = mysql_fetch_assoc($session);
$totalRows_session = mysql_num_rows($session);

if(getAccess()== '10'){
    
    mysql_select_db($database_tams, $tams);
    $query_rspros = sprintf("SELECT *  
                                    FROM student  
                                    WHERE stdid=%s",
                                    GetSQLValueString(getSessionValue('MM_Username'), "text"));
    $rspros = mysql_query($query_rspros, $tams) or die(mysql_error());
    $row_rspros = mysql_fetch_assoc($rspros);
    $totalRows_rspros = mysql_num_rows($rspros);

}else{
    
    mysql_select_db($database_tams, $tams);
    $query_rspros = sprintf("SELECT p.*  
                                                   FROM prospective p 
                                                   WHERE p.jambregid=%s",
                                                   GetSQLValueString(getSessionValue('MM_Username'), "text"));
    $rspros = mysql_query($query_rspros, $tams) or die(mysql_error());
    $row_rspros = mysql_fetch_assoc($rspros);
    $totalRows_rspros = mysql_num_rows($rspros);
}

mysql_select_db($database_tams, $tams);
 $query = sprintf("SELECT *  FROM olevelverifee_transactions  WHERE  status='APPROVED' AND pay_used='No' AND card_submit='No' AND can_no=%s",
                                    GetSQLValueString($_SESSION['MM_Username'], 'text'));
            $payment = mysql_query($query, $tams) or die(mysql_error());
            $payment_row = mysql_fetch_assoc($payment);
           $payment_num_row = mysql_num_rows($payment);
     
$order = $payment_row['ordid'];           


if($payment_num_row < 1){
  //header("Location: ". $reroot ); 
  //  exit;  
}
$total = 0;
do{
   $total+= $payment_row['amt'];
   
}while($payment_row = mysql_fetch_assoc($payment)); 



if(isset($_POST['MM_Submit']) && ($_POST['MM_Submit']== 'form1') &&  ($_POST['entry'] != NULL)){
    
    if(isset($_POST['entry'][1]['exam_year']) && $_POST['entry'][1]['exam_year'] != NULL){
        
        mysql_select_db($database_tams, $tams);
        
        mysql_query("BEGIN",$tams );
        
            $query = sprintf("INSERT INTO olevel_veri_data (stdid, exam_type, exam_year, exam_no, card_no, card_pin,date, ordid)  VALUES(%s, %s, %s, %s, %s,%s, %s,%s)",
                                    GetSQLValueString($_SESSION['MM_Username'], 'text'),
                                    GetSQLValueString($_POST['entry'][0]['exam_type'], 'text'),
                                    GetSQLValueString($_POST['entry'][0]['exam_year'], 'text'),
                                    GetSQLValueString($_POST['entry'][0]['exam_no'], 'text'),
                                    GetSQLValueString($_POST['entry'][0]['card_no'], 'text'),
                                    GetSQLValueString($_POST['entry'][0]['card_pin'], 'text'),
                                    GetSQLValueString(date('Y-m-d H:i:s'), 'date'),
                                    SQLValueString($_POST['entry'][0]['ordid'], 'text'));

            $olevel = mysql_query($query, $tams) or die(mysql_error());


            $query =sprintf("INSERT INTO olevel_veri_data (stdid, exam_type, exam_year, exam_no, card_no, card_pin, date, ordid)  VALUES(%s, %s, %s, %s, %s,%s,%s,%s)",
                                    GetSQLValueString($_SESSION['MM_Username'], 'text'),
                                    GetSQLValueString($_POST['entry'][1]['exam_type'], 'text'),
                                    GetSQLValueString($_POST['entry'][1]['exam_year'], 'text'),
                                    GetSQLValueString($_POST['entry'][1]['exam_no'], 'text'),
                                    GetSQLValueString($_POST['entry'][1]['card_no'], 'text'),
                                    GetSQLValueString($_POST['entry'][1]['card_pin'], 'text'),
                                    GetSQLValueString(date('Y-m-d H:i:s'), 'date'),
                                    SQLValueString($_POST['entry'][0]['ordid'], 'text'));

            $olevel = mysql_query($query, $tams) or die(mysql_error());
        mysql_query("COMMIT",$tams );
        
        
    }else{
        $stat = "<p style='color:blue'>Submission Successful for further processing <br/>Please check back later </p>";
        mysql_query("BEGIN",$tams );
        
            mysql_select_db($database_tams, $tams);
            $query = sprintf("INSERT INTO olevel_veri_data (stdid, exam_type, exam_year, exam_no, card_no, card_pin, date, ordid, level, sesid, progid, return_msg )  VALUES(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                                    GetSQLValueString($_SESSION['MM_Username'], 'text'),
                                    GetSQLValueString($_POST['entry'][0]['exam_type'], 'text'),
                                    GetSQLValueString($_POST['entry'][0]['exam_year'], 'text'),
                                    GetSQLValueString($_POST['entry'][0]['exam_no'], 'text'),
                                    GetSQLValueString($_POST['entry'][0]['card_no'], 'text'),
                                    GetSQLValueString($_POST['entry'][0]['card_pin'], 'text'),
                                    GetSQLValueString(date('Y-m-d'), 'text'),
                                    GetSQLValueString($_POST['entry'][0]['ordid'], 'text'),
                                    GetSQLValueString($_POST['entry'][0]['level'], 'text'),
                                    GetSQLValueString($_POST['entry'][0]['sesid'], 'int'),
                                    GetSQLValueString($_POST['entry'][0]['progid'], 'int'),
                                    GetSQLValueString($stat, 'text'));
            $olevel = mysql_query($query, $tams) or die(mysql_error());
            

            $query = sprintf("UPDATE `olevelverifee_transactions` SET `card_submit`='Yes' WHERE status='APPROVED' AND can_no = %s AND ordid=%s",
                                                GetSQLValueString($_SESSION['MM_Username'], 'text'),
                                                GetSQLValueString($_POST['entry'][0]['ordid'], 'text'));
            $verify = mysql_query($query, $tams) or die(mysql_error());
                
        mysql_query("COMMIT",$tams );
    }
    
    $msg = ($olevel)?"<p style='color : green'>Card Submited Succesfully </p>" : "<p style='color : red'>Card NOT Submited  </p>";
    header("Location: ". $reroot.'?status=1' ); 
    exit;
}
?>
<!DOCTYPE html>
<html > 
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->O'level Result Verification  <!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
        <?php if($payment_num_row > 0){?>
      <tr>
        <td>
            <form name='form1' method="POST" action="<?php echo urldecode('form.php?id='.$payment_row['id'].'&ordid='.$payment_row['ordid']) ?>">
                <table class="table table-bordered">
                    <tr>
                        <td>
                            <?php echo $msg; ?>
                            <p>
                                <strong> Note !</strong><br/>
                                WAEC = WAEC (May/ June) &nbsp;&nbsp;&nbsp;&nbsp; GCE = WAEC (Nov/ Dec)<br/>

                                NECO  = NECO (June/ July) &nbsp;&nbsp;&nbsp;&nbsp;NECO GCE = NECO (Nov/ Dec)<br/>

                            </p>
                        </td>
                    </tr> 
                    <tr>
                        <td align="center">
                            <table class="table table-condensed table-bordered table-striped" width="300">
                                <thead>
                                    <tr>
                                        <th colspan="2"> 1st Sitting </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Exam Type</td>
                                        <td>
                                            <select name='entry[0][exam_type]' class="input-medium" required="required">
                                                <option value="-1">--Choose--</option>
                                                <option value="WAEC">WAEC</option>
                                                <option value="GCE">GCE</option>
                                                <option value="NECO">NECO</option>
                                                <option value="NECO GCE">NECO GCE </option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Exam Year</td>
                                        <td><input type='text' name='entry[0][exam_year]' required="required"/></td>
                                    </tr>
                                    <tr>
                                        <td>Exam No</td>
                                        <td><input type='text' name=entry[0][exam_no]' required="required"/></td>
                                    </tr>
                                    <tr>
                                        <td>Card Pin</td>
                                        <td><input type='text' name='entry[0][card_pin]' required="required"/></td>
                                    </tr>
                                    <tr>
                                        <td>Card Serial No.</td>
                                        <td><input type='text' name='entry[0][card_no]' required="required"/></td>
                                    </tr>
                                    <input type="hidden" name='entry[0][sesid]' value="<?php echo $row_session['sesid']?>"/>
                                    <input type="hidden" name='entry[0][ordid]' value="<?php echo $order?>"/>
                                    <input type="hidden" name='entry[0][level]' value="<?php echo (getAccess() == '10')? $row_rspros['level']: "UTME" ?>"/>
                                    <input type="hidden" name='entry[0][progid]' value="<?php echo (getAccess() == '10')? $row_rspros['progid']: $row_rspros['progofferd'] ?>"/>
                                </tbody>    
                            </table>
                        </td>
                    </tr>
                     <?php if( 3 == 600){?>
                    <tr>
                        <td>
                            <table class="table table-condensed table-bordered table-striped" width="300">
                                <thead>
                                    <tr>
                                        <th colspan="2"> 2nd Sitting </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Exam Type</td>
                                        <td>
                                            <select name='entry[1][exam_type]' class="input-medium">
                                                <option value="-1">--Choose--</option>
                                                <option value="WAEC">WAEC</option>
                                                <option value="GCE">GCE</option>
                                                <option value="NECO">NECO</option>
                                                <option value="NECO GCE">NECO GCE </option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Exam Year</td>
                                        <td><input type='text' name='entry[1][exam_year]' /></td>
                                    </tr>
                                    <tr>
                                        <td>Exam No</td>
                                        <td><input type='text' name=entry[1][exam_no]'/></td>
                                    </tr>
                                    <tr>
                                        <td>Card Pin</td>
                                        <td><input type='text' name=entry[1][card_pin]'/></td>
                                    </tr>
                                    <tr>
                                        <td>Card Sn No</td>
                                        <td><input type='text' name='entry[1][card_no]'/></td>
                                    </tr>
                                    <input type="hidden" name='entry[1][ordid]' value="<?php echo $order?>"/>
                                </tbody>    
                            </table>
                        </td>
                    </tr>
                     <?php }?>
                    <tr>
                        <td>
                            <input type="submit" name='submit' value="Submit" class="btn"/>
                        </td>
                    </tr>
                    <input type="hidden" name="MM_Submit" value="form1"/>
                </table>
                
            </form>    
        </td>
      </tr>
        <?php } else{?>
        <tr>
            <td>
                <table class="table table-bordered table-condensed ">
                    <tr>
                        <td>
                            <p style="color: red" ><strong>Sorry !!! </strong>You have either USED your earlier submitted WAEC/NECO card or you have NOT PAID for your O'Level verification fee <a href="olevel_veri_payment/index.php">click here </a> to make payment</p>
                        </td>
                    </tr>
                </table>
                
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
<!-- InstanceEnd --></html>