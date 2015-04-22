<?php require_once('../Connections/tams.php');

if (!isset($_SESSION)) {
  session_start();
}
require_once('../param/param.php');
require_once('../functions/function.php');


$MM_authorizedUsers = "1,2,3,4,5,6,10,20,21,22,23";
$MM_donotCheckaccess = "false";

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
    if (($strUsers == "") && false) { 
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

$colname_student = "-1";
if (isset($_GET['stid'])) {
  $colname_student = $_GET['stid'];
}else {
    $colname_student = getSessionValue('stid');
}

mysql_select_db($database_tams, $tams);

$query_sess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,1";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

$query_student = sprintf("SELECT s.*, progname, p.deptid, deptname, d.colid, colname "
                        . "FROM student s, programme p, department d, college c "
                        . "WHERE s.progid = p.progid "
                        . "AND p.deptid = d.deptid "
                        . "AND d.colid = c.colid "
                        . "AND stdid = %s", GetSQLValueString($colname_student, "text"));
$student = mysql_query($query_student, $tams) or die(mysql_error());
$row_student = mysql_fetch_assoc($student);
$totalRows_student = mysql_num_rows($student);

$query_reg = sprintf("SELECT * FROM registration r WHERE stdid = %s", GetSQLValueString($colname_student, "text"));
$reg = mysql_query($query_reg, $tams) or die(mysql_error());
$row_reg = mysql_fetch_assoc($reg);
$totalRows_reg = mysql_num_rows($reg);

$query_rsdisp = sprintf("SELECT * FROM disciplinary WHERE stdid = %s", GetSQLValueString($colname_student, "text"));
$rsdisp = mysql_query($query_rsdisp, $tams) or die(mysql_error());
$row_rsdisp = mysql_fetch_assoc($rsdisp);
$totalRows_rsdisp = mysql_num_rows($rsdisp);

$pictureId = $colname_student;

if($row_student['jambregid'] != NULL) {
    $pictureId = strtoupper($row_student['jambregid']);
}

$image_url = '../images/student/profile.png';
$image = array("../images/student/{$colname_student}.jpg", "../images/student/{$pictureId}.jpg", 
                "../images/student/".strtolower($pictureId).".jpg", "../images/student/{$colname_student}.JPG",
                "../images/student/{$pictureId}.JPG", "../images/student/".strtolower($pictureId).".JPG", 
                "../images/student/{$colname_student}.png", "../images/student/{$pictureId}.png", 
                "../images/student/".strtolower($pictureId).".png", "../images/student/{$colname_student}.PNG", 
                "../images/student/{$pictureId}.PNG", "../images/student/".strtolower($pictureId).".PNG", 
                "../images/student/{$colname_student}.gif", "../images/student/{$pictureId}.gif", 
                "../images/student/".strtolower($pictureId).".gif", "../images/student/{$colname_student}.GIF", 
                "../images/student/{$pictureId}.GIF", "../images/student/".strtolower($pictureId).".GIF"
                );

for($idx = 0; $idx < count($image); $idx++) {
    if(realpath("{$image[$idx]}")) {
        $image_url = $image[$idx];
        break;
    }
}

$props = array('email', 'phone', 'sex', 'dob', 'addr', 'image');

foreach($props as $key => $prop) {
    $value = isset($row_student[$prop])? strtolower($row_student[$prop]): '';
    switch($prop) {
        case 'email':
            if(!isset($value) || $value == '' || in_array($value, array('change@youremail.com'))) {
                $props[$key] = 'Email Address';
            }else {
                unset($props[$key]);
            }
            break;
        
        case 'phone':
            if(!isset($value) || $value == '') {
                $props[$key] = 'Phone Number';
            }else {
                unset($props[$key]);
            }
            break;
        
        case 'sex':
            if(!isset($value) || !in_array($value, array('m', 'f'))) {
                $props[$key] = 'Sex';
            }else {
                unset($props[$key]);
            }
            break;
        
        case 'dob':
            if(!isset($value) || $value == '0000-00-00') {
                $props[$key] = 'Date of Birth';
            }else {
                unset($props[$key]);
            }
            break;
        
        case 'image':
            if($image_url == '../images/student/profile.png') {
                $props[$key] = 'Profile Image';
            }else {
                unset($props[$key]);
            }    
            break;
        
        default:
            if(!isset($value) || $value == 'Your Campus or Home Addres') {
                $props[$key] = 'Address';
            }else {
                unset($props[$key]);
            }            
    }
}

if((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")) {
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
        <td><!-- InstanceBeginEditable name="pagetitle" --><?php echo $row_student['lname']." ".$row_student['fname']."'s"?> Profile<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
      <?php if(!empty($props)) {?>  
      <tr>              
          <td colspan="3">
              <div style="color: red">
                  Please update your Profile Information! 
                  The following information is missing from your profile: <?php echo implode(', ', $props);?>.
              </div>
          </td>
      </tr>
        <tr>              
          <td colspan="3"></td>
      </tr>
      <?php }?>
       <tr>       
               <?php if( getSessionValue('stid') == $row_student['stdid'] || 
                       getAccess() == 1 || 
                       (getAccess() == 2 && $_SESSION['cid'] == $row_student['colid']) || 
                       (getAccess() == 3 && $_SESSION['did'] == $row_student['deptid'])){?>
           <td>
               <?php
                    if(checkFees($row_sess['sesid'], $colname_student) && $row_reg['course'] == 'Registered') {?>
                    <form action='../student/exampass.php' target="_blank" >
                        <input value='Print Exam Pass' type="submit"/>&nbsp;&nbsp;
                    </form>
                <?php }?> 
           </td>
           <td colspan="2" align="right">
           
        <?php
            if($row_reg['course'] == 'Registered') {?>
        
        <a href="../registration/editform.php" target="_new">Edit Form </a>|
        <?php }?>
        
        <a href="../registration/viewform.php?stid=<?php echo $row_student['stdid'];?>">Course Form</a> |
        <a href="../result/transcript.php?stid=<?php echo $row_student['stdid'];?>"> Transcript </a>  |
        <a href="editprofile.php?stid=<?php echo $row_student['stdid']; ?>" >Edit Profile</a>
        </td>      
        <?php }?>
      </tr>   
        
      <tr>              
        <td colspan="3" align="right"></td>
      </tr>  
      
      <tr>
        <td width="175" rowspan="7" align="center"><img src="<?php echo $image_url;?>" alt="" name="profile_image" width="150" height="150" id="profile_image" />
        <?php echo $row_student['stdid']; ?></td>
        <td width="96" height="24"><strong>Name:</strong></td>
        <td width="403"><?php echo $row_student['lname']; ?> <?php echo $row_student['fname']; ?></td>
      </tr>      
      <tr>
        <td height="24"><strong>College: </strong></td>
        <td height="24"><a href="../college/college.php?cid=<?php echo $row_student['colid']; ?>"><?php echo $row_student['colname']; ?></a></td>
      </tr>
      <tr>
        <td height="24"><strong>Department: </strong></td>
        <td height="24"><a href="../department/department.php?did=<?php echo $row_student['deptid']; ?>"><?php echo $row_student['deptname']; ?></a></td>
      </tr>
      <tr>
        <td height="24"><strong>Programme:</strong></td>
        <td height="24"><?php echo $degree." ".$row_student['progname']; ?></td>
      </tr>
      <tr>
        <td height="24"><strong>Phone: </strong></td>
        <td height="24"><?php echo $row_student['phone']; ?></td>
      </tr>
      <tr>
        <td height="11"><strong>Email:</strong></td>
        <td height="11"><?php echo $row_student['email']; ?></td>
      </tr>
      <tr>
        <td height="30"><strong>Entry Mode:</strong></td>
        <td height="30"><?php echo $row_student['admode']; ?> <em>(presently in Year <?php echo $row_student['level']; ?>)</em></td>
      </tr>
      <tr>
        <td colspan="3"><strong><?php if( $row_student['disciplinary']== 'True') echo "Disciplinary Action:" ; ?> </strong></td>
      </tr>
      <tr>
        <td colspan="3"><?php if( $row_student['disciplinary']== 'True'){?>
		You are on a disciplinary action<strong> <?php echo $row_rsdisp['status']; ?> </strong> as at <strong><?php echo $row_rsdisp['login']; ?> </strong> Kindlly  contact the Registrar's Office for advice and necessary action 
			<?php }?></td>
      </tr>
      <tr>
        <td height="38" colspan="3"><strong>Personal Profile Statement :</strong></td>
      </tr>
      <tr>
        <td colspan="3"><?php echo $row_student['profile']; ?></td>
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
<?php
mysql_free_result($student);

mysql_free_result($rsdisp);
?>
