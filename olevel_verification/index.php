<?php  
if (!isset($_SESSION)) {
  session_start();
}
$msg = "";
$reroot = "index.php";
$reroot2 = "../prospective/status.php";
if(isset($_GET['state'])){
   if($_GET['state']== 'y'){
       $msg = "<p style='color: green'>The Verification Code you enterd is correct </p>";
   }elseif($_GET['state']== 'n'){
       $msg = "<p style='color: red'>The Verification Code you enterd is incorrect </p>";
   } 
}
        
require_once('../Connections/tams.php');
require_once('../param/param.php');
require_once('../functions/function.php');

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );   
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
if(getAccess() == '11'){
     mysql_select_db($database_tams, $tams);
    $query_student = sprintf("SELECT *  FROM prospective  WHERE  jambregid=%s",
                                    GetSQLValueString($_SESSION['MM_Username'], 'text'));
            $student = mysql_query($query_student, $tams) or die(mysql_error());
            $row_student = mysql_fetch_assoc($student);
            $veri_data_row_num = mysql_num_rows($student);
}

if($row_student['adminstatus']=='No'){
    header("Location: ". $reroot2 ); 
    exit;
}

mysql_select_db($database_tams, $tams);
$query1 = sprintf("SELECT *  FROM olevel_veri_data  WHERE  stdid=%s",
                                    GetSQLValueString($_SESSION['MM_Username'], 'text'));
            $veri_data = mysql_query($query1, $tams) or die(mysql_error());
            $veri_data_row = mysql_fetch_assoc($veri_data);
            $veri_data_row_num = mysql_num_rows($veri_data);

            
mysql_select_db($database_tams, $tams);
$query = sprintf("SELECT *  FROM verification  WHERE  stdid=%s",
                                    GetSQLValueString($_SESSION['MM_Username'], 'text'));
            $verify = mysql_query($query, $tams) or die(mysql_error());
            $verify_row = mysql_fetch_assoc($verify);
            $verify_row_num = mysql_num_rows($verify);
            

$status = ($verify_row['verified'] == "TRUE")? "<p style=' color: green; font-size: 20px; font-weight: bold'>VERIFIED</p>": "<p style=' color: red; font-size: 20px; font-weight: bold'>NOT YET VERIFIED</p>";

if(isset($_POST['ver_code']) && $_POST['ver_code'] != NULL ){
    
    if($_POST['ver_code'] == $verify_row['ver_code'] ){
        $state = 'y';
        
        $query2 = sprintf("UPDATE verification SET verified = 'TRUE' WHERE  stdid=%s",
                                    GetSQLValueString($_SESSION['MM_Username'], 'text'));
        $updateverify = mysql_query($query2, $tams) or die(mysql_error());
        
    }else{
        $state = 'n';
        
    }
    
 header("Location: ". $reroot ); 
 exit;
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Status Page  <!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690" class="table table-bordered table-condensed">
        <tr>
            <td align="center">
                <P> O'LEVEL VERIFICATION STATUS </P>
                <table class="table table-bordered table-condensed table-striped table-hover" width="400">
                    <thead>
                        <tr>
                            <th width="10">S/n</th>
                            <th width="40">Exam Type </th>
                            <th>Exam year</th>
                            <th>Exam No</th> 
                            <th >Date Submitted</th>
                            <th>Receipt </th>
                            <th>Status </th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if($veri_data_row_num > 0){
                            $i =1; 
                            do{?>
                            <tr>
                                <td><?php echo $i++;?></td>
                                <td><?php echo $veri_data_row['exam_type']?></td>
                                <td><?php echo $veri_data_row['exam_year']?></td>
                                <td><?php echo $veri_data_row['exam_no']?></td>
                                <td><?php echo $veri_data_row['date']?></td>
                                <td>
                                    <a target="_blank" href="olevel_veri_payment/receipt.php?no=<?php echo $veri_data_row['ordid'] ?>">
                                        <button type="button"> Print</button>
                                    </a>
                                </td>
                                <td><?php echo $veri_data_row['return_msg']?></td>
                            </tr>
                            <?php 
                            }while($veri_data_row = mysql_fetch_assoc($veri_data));      
                        }else{?>
                            <tr>
                                <td colspan="7" align="center">
                                    <p style="color: red">You have Not submit any O'level result for verification </p>
                                </td>
                            </tr>
                        <?php }?>
                    </tbody>    
                </table> 
            </td>
        </tr>
      <tr>
          <td>
              <table class="table table-bordered table-condensed table-striped" width="400">
                  <thead>
                    <tr align="center">
                        <th colspan="2"><?php echo $status;?></th>
                    </tr>
                  </thead>
                  <tbody>
                      
                        <?php if($verify_row['verified'] == "FALSE" || empty($verify_row)){?>
                            <form  name="form1" method="POST" action="<?php echo $_SERVER['PHP_SELF']?>"> 
                                  <tr>
                                      <td colspan="2" align="center"><?php echo $msg; ?> </td>
                                  </tr>
                                  <tr>
                                      <th width="150">Verification Code </th>
                                      <td><input type='text' name ="ver_code" style="font-size: 30px; float: center"/></td>
                                  </tr>
                                  <tr>
                                      <td></td>
                                      <td><input type='submit' name='submit' value="Verify"  /></td>
                                  </tr>
                            </form>

                        <?php }else {?>
                        <tr>
                            <?php if(getAccess() == '11') { ?>
                                <td colspan="2"><a href="../prospective/fees_payment/index.php"><input type='button' name='submit' value="Pay School Fee" style=" float: center" /> </a></td><?php } ?>
                           
                        </tr>
                        <?php }?>
                      
                        <tr>
                            <td colspan="2"> &nbsp;</td>
                        </tr>  
                        <tr> 
                            <td colspan="2">
                                <a href="form.php">
                                    <input type='button'  name='submit' value="Submit NECO/ WAEC Card Pin" style=" float: left"/>
                                </a>

                                <a href="olevel_veri_payment/index.php">
                                    <input type='button'  name='submit' value="Pay for O'Level Verification" style=" float: right"/>
                                </a>
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