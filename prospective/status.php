<?php 
if (!isset($_SESSION)) {
  session_start();
}

require_once('../Connections/tams.php');
require_once('../param/param.php');
require_once('../functions/function.php');

$MM_authorizedUsers = "11,20,21,22,23";
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

mysql_select_db($database_tams, $tams);
$query_rspros = sprintf("SELECT p.*, pr.progname  
						FROM prospective p 
						JOIN programme pr ON p.progid1 = pr.progid
						WHERE p.jambregid=%s",
						GetSQLValueString(getSessionValue('MM_Username'), "text"));
$rspros = mysql_query($query_rspros, $tams) or die(mysql_error());
$row_rspros = mysql_fetch_assoc($rspros);
$totalRows_rspros = mysql_num_rows($rspros);

$admitted = ($row_rspros['adminstatus'] == 'Yes')? true: false;

$accepted = ($row_rspros['acceptance'] == 'Yes')? true: false;
        
//$schoolfee = ($row_rspros['schoolfee'] == 'Yes')? true: false;

function getProg($id){
    
    if($id != NULL){
        $query = "SELECT progname FROM programme WHERE progid = {$id}";                                                 
        $rsprog = mysql_query($query) or die(mysql_error());
        $row_rspros = mysql_fetch_assoc($rsprog);

        return $row_rspros['progname'];
    }
    return NULL;
}

$imgname = $row_rspros['jambregid'];
$image_url = '../images/student/profile.png';
$image = array("../images/student/{$imgname}.jpg", 
                "../images/student/{$imgname}.JPG", 
                "../images/student/{$imgname}.png",
                "../images/student/{$imgname}.PNG");
for($idx = 0; $idx < count($image); $idx++) {
    if(realpath("{$image[$idx]}")) {
        $image_url = $image[$idx];
        break;
    }
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Admission Status<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
      <table width="690" class="table table-condensed ">
       <?php if($row_rspros['formsubmit']=='Yes'){?>  
        <tr>
            <td width="400" height="21"><strong>Form No.: </strong><?php echo $row_rspros['formnum'];?></td>
            <td colspan="2" rowspan="4" valign="top"><img src="<?php echo  $image_url;?>" alt="Image" id="placeholder" name="placeholder" width="160" height="160" align="top"/></td>
        </tr>
        <tr>
            <td height="43"> <strong>Name:</strong> <?php echo $row_rspros['fname'].' '.$row_rspros['lname'];?></td>
        </tr>
        <tr>
            <td height="44"><strong>Post UTME score:</strong> <?php echo ($row_rspros['score'] == NULL)? 'No score available': $row_rspros['score'];?></td>
        </tr>
        <tr>
            <td height="44" colspan="2"><strong>UTME No:</strong> <?php echo $row_rspros['jambregid'];?></td>
        </tr>
<?php if($admitted){?> 
          
        <tr>
            <td height="44" colspan="2"><strong>Admission Status:</strong> <?php echo ($row_rspros['adminstatus']== 'Yes')? '<span style="color : green"><strong> ADMITTED </strong></span>':'<span style="color : red">Not Admitted Yet</span>';?></td>
        </tr>
        <tr>
            <td height="44" colspan="2"><strong>Programme Offer:</strong> <?php echo ($row_rspros['adminstatus']!= NULL)? getProg($row_rspros['progofferd']): ""?></td>
        </tr>
       
        <tr>
            <td colspan="1"><a target="_blank" href="printadmletter.php"><button>Print Admission Letter</button></a></td>
            
         
<?php if($admitted && !$accepted ){?>        
       
            <td colspan="1"> <a href="acceptance_payment/"><button>Pay Acceptance Fees</button></a></td>
      </tr> 
<?php }?>
<?php if($admitted && $accepted) { //&& !$schoolfee) {?>
         <tr>
            <td height="44" colspan="2"> <br/>Our record shows that you have paid for ACCEPTANCE FEES. Visit the Admissions Office for other necessary actions. If you have fulfilled all admission requirements, <a href="fees_payment/">Click Here</a> to pay your School Fees.</td>
        </tr>
        
        
<?php }?>          
<?php }?>
          
<?php } else{?>
         <tr>
            <td height="44" colspan="2"> <br/>You have not submitted your Application. <a href="viewform.php">Click here</a> to proceed.</td>
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