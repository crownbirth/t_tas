<?php 
if (!isset($_SESSION)) {
  session_start();
}

define ('MAX_FILE_SIZE', 2048 * 1536);
define('UPLOAD_DIR', '../images/student/');

require_once('../Connections/tams.php');
require_once('../param/param.php');
require_once('../functions/function.php');

$MM_authorizedUsers = "11";
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
    $query = sprintf("SELECT * FROM session ORDER BY sesid DESC LIMIT 1 ");
    $session= mysql_query($query, $tams) or die(mysql_error());
    $row_session = mysql_fetch_assoc($session);
    $totalRows_session = mysql_num_rows($session);
    
mysql_select_db($database_tams, $tams);
$query_rschk = sprintf("SELECT * 
                                FROM prospective  
                                WHERE jambregid=%s",
                                GetSQLValueString(getSessionValue('MM_Username'), "text"));
$rschk = mysql_query($query_rschk, $tams) or die(mysql_error());
$row_rschk = mysql_fetch_assoc($rschk);
$totalRows_rschk = mysql_num_rows($rschk);

if($row_rschk['formpayment'] == 'No' ) {
	header('Location: prospective_payment/index.php');
}

if($row_rschk['formsubmit'] == 'Yes' ) {
	header('Location: viewform.php');
}

mysql_select_db($database_tams, $tams);
$query_rssit1 = sprintf("SELECT * 
						FROM olevel o 
						JOIN olevelresult l ON o.olevelid = l.olevelid 
						JOIN subject s ON l.subject = s.subjid 
						JOIN grade g ON l.grade = g.grdid 
						WHERE o.jambregid=%s
						AND sitting='first'",
						GetSQLValueString($row_rschk['jambregid'], "text"));
$rssit1 = mysql_query($query_rssit1, $tams) or die(mysql_error());
$row_rssit1 = mysql_fetch_assoc($rssit1);
$totalRows_rssit1 = mysql_num_rows($rssit1);

mysql_select_db($database_tams, $tams);
$query_rssit2 = sprintf("SELECT * 
                                FROM olevel o 
                                JOIN olevelresult l ON o.olevelid = l.olevelid 
                                JOIN subject s ON l.subject = s.subjid 
                                JOIN grade g ON l.grade = g.grdid 
                                WHERE o.jambregid=%s
                                AND sitting='second'",
                                GetSQLValueString($row_rschk['jambregid'], "text"));

$rssit2 = mysql_query($query_rssit2, $tams) or die(mysql_error());
$row_rssit2 = mysql_fetch_assoc($rssit2);
$totalRows_rssit2 = mysql_num_rows($rssit2);
$lga = '';
$msg = '';

if(isset($_POST['save']) && $_POST['save'] != '') {
    $msg = 'Some required fields are missing!';
    $required = array('fname', 'lname', 'mname', 'sex', 'soforig',
            'jambregid', 'age', 'jambyear', 'lga', 'sponsorname', 
            'sponsorphn', 'sponsoradrs', 'health', 'email', 'religion', 
            'address', 'dob', 'phone', 'deschname', 'degradyear', 'degrade', 'progid1', 'progid2');
      
    $omitted = array();
    $count = count($required);
    for($idx = 0; $idx < $count; $idx++) {
        if(isset($_POST[$required[$idx]]) && $_POST[$required[$idx]] != '' && $_POST[$required[$idx]] != '-1')
            continue;
        
        $omitted[$required[$idx]] = $required[$idx];
    }
    
    if(!isset($_POST['exmyr'][0]) && $_POST['exmyr'][0] == '') {
        $omitted['exmyr'] = '';
    }

    if(!isset($_POST['exmtyp'][0]) && $_POST['exmtyp'][0] == '') {
        $omitted['exmtyp'] = '';
    }

    if(!isset($_POST['examnumber'][0]) && $_POST['examnumber'][0] == '') {
        $omitted['examnumber'] = '';
    }

    if($_FILES['filename']['error'] != 0) {
        $omitted['filename'] = '';
    }
    
    for($idx = 0; $idx < 5; $idx++) {
        if((isset($_POST['subj']['first'][$idx]) && ($_POST['subj']['first'][$idx] != '' || $_POST['subj']['first'][$idx] != '-1')) && 
    (isset($_POST['grade']['first'][$idx]) && ($_POST['grade']['first'][$idx] != '' || $_POST['grade']['first'][$idx] != '-1')))
            continue;

        $omitted['subj']["$idx"] = '';
    }
    
    if(empty($omitted)) {
        $msg = '';
        $admission_type = $row_rschk['admtype'];
        $parts = explode('/', $row_session['sesname']);
        $part2 = $row_rschk['pstdid'];
        if(strlen($part2) < 4)
                $part2 = str_pad($part2, 4, '0', STR_PAD_LEFT);

        $formnum = (substr($parts[0], 2) -1).$admission_type.$part2;
        $updateSQL = sprintf("UPDATE prospective SET formnum=%s WHERE jambregid=%s", 
                                                GetSQLValueString($formnum, "text"), 
                                                GetSQLValueString($row_rschk['jambregid'], "text"));
        $Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());

         mysql_query("BEGIN", $tams);

        $insertSQL = sprintf("UPDATE prospective "
                . "SET fname=%s, lname=%s, mname=%s, sex=%s, stid=%s, "
                . "jambregid=%s, age=%s, jambyear=%s, lga=%s,sponsorname=%s, sponsorphn=%s, sponsoradrs=%s, "
                . "access=%s,sesid=%s,phone=%s, DoB=%s, formsubmit=%s, address=%s, "
                . "Religion=%s, email=%s, healthStatus=%s, deschname=%s, degradyear=%s, degrade=%s, "
                . "progid1=%s, progid2=%s WHERE jambregid=%s",
                GetSQLValueString($_POST['fname'], "text"),
                GetSQLValueString($_POST['lname'], "text"),
                GetSQLValueString($_POST['mname'], "text"),
                GetSQLValueString($_POST['sex'], "text"),
                GetSQLValueString($_POST['soforig'], "int"),
                GetSQLValueString($_POST['jambregid'], "text"),
                GetSQLValueString($_POST['age'], "int"),
                GetSQLValueString($_POST['jambyear'], "text"),
                GetSQLValueString($_POST['lga'], "text"),
                GetSQLValueString($_POST['sponsorname'], "text"),
                GetSQLValueString($_POST['sponsorphn'], "text"),
                GetSQLValueString($_POST['sponsoradrs'], "text"),
                GetSQLValueString($_POST['access'], "int"),
                GetSQLValueString($row_session['sesid'], "text"),
                GetSQLValueString($_POST['phone'], "text"),
                GetSQLValueString($_POST['dob'], "text"),
                GetSQLValueString("Yes", "text"),
                GetSQLValueString($_POST['address'], "text"),
                GetSQLValueString($_POST['religion'], "text"),
                GetSQLValueString($_POST['email'], "text"),
                GetSQLValueString($_POST['health'], "text"),
                GetSQLValueString($_POST['deschname'], "text"),
                GetSQLValueString($_POST['degradeyear'], "text"),
                GetSQLValueString($_POST['degrade'], "int"),
                GetSQLValueString($_POST['progid1'], "int"), 
                GetSQLValueString($_POST['progid2'], "int"),
                GetSQLValueString(getSessionValue('MM_Username'), "text"));


        mysql_select_db($database_tams, $tams);
        $Result = mysql_query($insertSQL, $tams) or die(mysql_error());        

        $selectSQL = sprintf("SELECT olevelid FROM olevel WHERE jambregid=%s",
                                                   GetSQLValueString($row_rschk['jambregid'], "text"));

        mysql_select_db($database_tams, $tams);
        $rsid = mysql_query($selectSQL, $tams) or die(mysql_error());
        $row_rsid = mysql_fetch_assoc($rsid);
        $totalRows_rsid = mysql_num_rows($rsid);

        if($totalRows_rsid > 0) {
                for($i = 0; $i < $totalRows_rsid; $i++) {
                        $olevelid = $row_rsid['olevelid'];
                        mysql_query("DELETE FROM olevelresult WHERE olevelid = {$olevelid}", $tams) or die(mysql_error());
                        mysql_query("DELETE FROM olevel WHERE olevelid = {$olevelid}", $tams) or die(mysql_error());
                        $row_rsid = mysql_fetch_assoc($rsid);	
                }
        }

        $count = 1;
        if($_POST['exmyr'][1] != '')
                $count = 2;

        $pos = 'first';
        for($i = 0; $i < $count; $i++) {

                $insertSQL = sprintf("INSERT INTO olevel (jambregid, examtype, examyear, examnumber, sitting) VALUES (%s, %s, %s, %s, %s)",
                                                   GetSQLValueString(getSessionValue('MM_Username'), "text"),
                                                   GetSQLValueString($_POST['exmtyp'][$i], "text"),
                                                   GetSQLValueString($_POST['exmyr'][$i], "text"),
                                                   GetSQLValueString($_POST['examnumber'][$i], "text"),
                                                   GetSQLValueString($pos, "text"));

                mysql_select_db($database_tams, $tams);
                $Result1 = mysql_query($insertSQL, $tams) or die(mysql_error());
                $insertid = mysql_insert_id();

                for($y = 0; $y < count($_POST['subj'][$pos]); $y++) {
                        if($_POST['subj'][$pos][$y] == -1) {
                                continue;
                        }

                        $insertSQL = sprintf("INSERT INTO olevelresult (olevelid, subject, grade) VALUES (%s, %s, %s)",
                                                           GetSQLValueString($insertid, "int"),
                                                           GetSQLValueString($_POST['subj'][$pos][$y], "text"),
                                                           GetSQLValueString($_POST['grade'][$pos][$y], "text"));

                        mysql_select_db($database_tams, $tams);
                        $Result2 = mysql_query($insertSQL, $tams) or die(mysql_error());
                }

                $pos = 'second';
        }

        if($Result && $Result1 && $Result2) {

            mysql_query("COMMIT", $tams);
            
            $_SESSION['MM_Username'] = $_POST['jambregid'];
            
            $queryPstd = sprintf("SELECT formnum FROM prospective WHERE jambregid = %s",
                                            GetSQLValueString($row_rschk['jambregid'], "text"));
            $pstd = mysql_query($queryPstd, $tams) or die(mysql_error());
            $row_pstd = mysql_fetch_assoc($pstd);

            $upload = uploadFile( UPLOAD_DIR, "prospective", MAX_FILE_SIZE);	
            header('Location: viewform.php');
        }else{
            mysql_query("ROLLBACK", $tams);
        }
    }
    
}

mysql_select_db($database_tams, $tams);
$query_rschk = sprintf("SELECT * 
                                FROM prospective  
                                WHERE jambregid=%s",
                                GetSQLValueString(getSessionValue('MM_Username'), "text"));
$rschk = mysql_query($query_rschk, $tams) or die(mysql_error());
$row_rschk = mysql_fetch_assoc($rschk);
$totalRows_rschk = mysql_num_rows($rschk);


mysql_select_db($database_tams, $tams);
$query_rssit1 = sprintf("SELECT * 
                        FROM olevel o 
                        JOIN olevelresult l ON o.olevelid = l.olevelid 
                        WHERE o.jambregid=%s
                        AND sitting='first'",
                        GetSQLValueString($row_rschk['jambregid'], "text"));
$rssit1 = mysql_query($query_rssit1, $tams) or die(mysql_error());
$row_rssit1 = mysql_fetch_assoc($rssit1);
$totalRows_rssit1 = mysql_num_rows($rssit1);

mysql_select_db($database_tams, $tams);
$query_rssit2 = sprintf("SELECT * 
                        FROM olevel o 
                        JOIN olevelresult l ON o.olevelid = l.olevelid 
                        WHERE o.jambregid=%s
                        AND sitting='second'",
                        GetSQLValueString($row_rschk['jambregid'], "text"));

$rssit2 = mysql_query($query_rssit2, $tams) or die(mysql_error());
$row_rssit2 = mysql_fetch_assoc($rssit2);
$totalRows_rssit2 = mysql_num_rows($rssit2);

mysql_select_db($database_tams, $tams);
$query_rsprg = sprintf("SELECT progid, progname FROM programme WHERE continued = %s ",  GetSQLValueString('Yes', 'text'));
$rsprg = mysql_query($query_rsprg, $tams) or die(mysql_error());
$row_rsprg = mysql_fetch_assoc($rsprg);
$totalRows_rsprg = mysql_num_rows($rsprg);

$query_state = "SELECT* FROM state";
$state = mysql_query($query_state, $tams) or die(mysql_error());
$row_state = mysql_fetch_assoc($state);
$totalRows_state = mysql_num_rows($state);

mysql_select_db($database_tams, $tams);
$query_rssubj = "SELECT * FROM subject";
$rssubj = mysql_query($query_rssubj, $tams) or die(mysql_error());
$row_rssubj = mysql_fetch_assoc($rssubj);
$totalRows_rssubj = mysql_num_rows($rssubj);

mysql_select_db($database_tams, $tams);
$query_rsgrd = "SELECT * FROM grade";
$rsgrd = mysql_query($query_rsgrd, $tams) or die(mysql_error());
$row_rsgrd = mysql_fetch_assoc($rsgrd);
$totalRows_rsgrd = mysql_num_rows($rsgrd);
 


$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
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
<script type="text/javascript" src="../scripts/jquery.js"></script>
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
<link href="../css/menulink.css" rel="stylesheet" type="text/css" />
<link href="../css/footer.css" rel="stylesheet" type="text/css" />
<link href="../css/sidemenu.css" rel="stylesheet" type="text/css" />
<script>
    $(function(){
        function toggleLga(action) {
            var lga = $('#lga');
            
            if(action === 'hide') {
                lga.children('.og').hide();
                lga.children('.others').show();
            }else {
                lga.children('.og').show();
                lga.children('.others').hide();
            }
        }
        
        if($('#soforig').val() == 27) {
            toggleLga('show');
        }else {
            toggleLga('hide');
        }
        
        
        $('#soforig').change(function(){
            var stid = $(this).val();
            if(stid == 27){
                toggleLga('show');                
            }else{
                toggleLga('hide');
                
            }
        });
        
    });
        
</script>
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Direct Entry Application Form<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
      <table  align="center" class="table">
          <tr>
              <td style="color: red"><?php echo $msg?></td>
          </tr>
      <tr>
       <td height="680">
           <form action="<?php echo $editFormAction?>" method="post" enctype="multipart/form-data" name="form1" id="form1" class="form-signin" >
          <p>&nbsp; </p>
          <table width="666"  ><tr><td width="585">
          <fieldset >
            <legend>Bio Data</legend>
            <table  width="320" border="0"  class="table table-hover table-striped table-bordered">
              <tr>
                  <td width="169" align="left" valign="top">First Name :</td>
                <td width="442" align="left" valign="top">
                    <?php
                        $fname = '';
                        if(isset($_POST['fname']))
                            $fname = $_POST['fname'];
                        elseif(isset($row_rschk['fname'])) 
                           $fname = $row_rschk['fname'];
                        
                    ?>
                    <input type="text" name="fname" id="fname" value="<?php echo $fname?>"/><?php if(isset($omitted['fname']))echo '<span style="color : red">*</span>' ?>
                </td>
              </tr>
              <tr>
                <td valign="top" align="left">Middle Name : </td>
                <td align="left" valign="top" >
                    <?php
                        $mname = '';
                        if(isset($_POST['mname']))
                            $mname = $_POST['mname'];
                        elseif(isset($row_rschk['mname'])) 
                           $mname = $row_rschk['mname'];
                    ?>
                    <input type="text" name="mname" id="mname" value="<?php echo $mname?>"/><?php if(isset($omitted['mname']))echo '<span style="color : red">*</span>' ?>
                </td>
              </tr>
              <tr>
                <td valign="top" align="left">Last Name : </td>
                <td align="left" valign="top">
                    <?php
                        $lname = '';
                        if(isset($_POST['lname']))
                            $lname = $_POST['lname'];
                        elseif(isset($row_rschk['lname'])) 
                           $lname = $row_rschk['lname'];
                    ?>
                    <input type="text" name="lname" id="lname" value="<?php echo $lname?>"/><?php if(isset($omitted['lname']))echo '<span style="color : red">*</span>' ?>
                </td>
              </tr>
              <tr>
                <td valign="top" align="left">Sex :</td>
                <td align="left" valign="top">
                    <?php
                        $sex = '';
                        if(isset($_POST['sex']))
                            $sex =  $_POST['sex'];
                        elseif(isset($row_rschk['Sex']))
                            $sex = $row_rschk['Sex'];
                    ?>
                    <select name="sex" id="sex">
                        <option value="-1">--Choose--</option>
                        <option value="M" <?php if('M' == $sex)echo 'selected'?>>Male</option>
                        <option value="F" <?php if('F' == $sex)echo 'selected'?>>Female</option>
                    </select>
                    <?php if(isset($omitted['sex']))echo '<span style="color : red">*</span>' ?>
                </td>
              </tr>
              
              <tr>
                <td valign="top" align="left">Date of Birth :</td>
                <td align="left" valign="top">
                    <?php
                        $dob = '';
                        if(isset($_POST['dob']))
                            $dob =  $_POST['dob'];
                        elseif(isset($row_rschk['DoB']))
                            $dob = $row_rschk['DoB'];
                    ?>
                    <input type="text" name="dob" id="dob" value="<?php echo $dob;?>"/>
                    <span style="color: #999999"> yyyy-mm-dd</span>
                    <?php if(isset($omitted['dob']))echo '<span style="color : red">*</span>' ?>
                </td>
              </tr>
              <tr>
                <td valign="top" align="left">Age :</td>
                <td align="left" valign="top">
                    <?php
                        $age = '';
                        if(isset($_POST['age']))
                            $age =  $_POST['age'];
                        elseif(isset($row_rschk['age']))
                            $age = $row_rschk['age'];
                    ?><input type="text" name="age" id="age" value="<?php echo $age;?>"/>
                    <?php if(isset($omitted['age']))echo '<span style="color : red">*</span>' ?>
                </td>
              </tr
              <tr>
                <td align="left" valign="top">Health Status : </td>
                <td align="left" valign="top">
                    <?php
                        $health = '';
                        if(isset($_POST['health']))
                            $health =  $_POST['health'];
                        elseif(isset($row_rschk['healthStatus']))
                            $health = $row_rschk['healthStatus'];
                    ?>
                    <select name="health" id="health">
                        <option value="-1">--Choose--</option>
                        <option value="Fit" <?php if('Fit' == $health)echo 'selected'?>>Fit</option>
                        <option value="Disable" <?php if('Disable' == $health)echo 'selected'?>>Disable</option>
                    </select>
                    <?php if(isset($omitted['health']))echo '<span style="color : red">*</span>' ?>
                </td>
              </tr>
              <tr>
                <td align="left" valign="top">Passport  : </td>
                <td align="left" valign="top">
                    <input type="file" name="filename" id="image" />
                    <?php if(isset($omitted['filename']))echo '<span style="color : red">*</span>' ?>
                </td>
              </tr>
              <tr>
                <td align="left" valign="top">&nbsp;</td>
                <td align="left" valign="top">&nbsp;</td>
              </tr>
            </table>
            <p>&nbsp;</p>
        </fieldset>
                </td>
            </tr>
                <tr>
                    <td>
                        <fieldset>
                            <legend>Personal Data</legend>
                            <table class="table table-hover table-striped table-bordered">
                                <tr>
                                    <td valign="top" align="left">E-mail Address : </td>
                                    <td align="left" valign="top">
                                        <?php
                                            $email = '';
                                            if(isset($_POST['email']))
                                                $email =  $_POST['email'];
                                            elseif(isset($row_rschk['email']))
                                                $email = $row_rschk['email'];
                                        ?>
                                        <input type="text" name="email" id="email" value="<?php echo $email;?>" size="50"/>
                                        <?php if(isset($omitted['email']))echo '<span style="color : red">*</span>' ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="top" align="left">Phone No : </td>
                                    <td align="left" valign="top">
                                        <?php
                                            $phone = '';
                                            if(isset($_POST['phone']))
                                                $phone =  $_POST['phone'];
                                            elseif(isset($row_rschk['phone']))
                                                $phone = $row_rschk['phone'];
                                        ?>
                                        <input type="text" name="phone" id="phone" value="<?php echo $phone;?>"/>
                                        <?php if(isset($omitted['phone']))echo '<span style="color : red">*</span>' ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="left" valign="top">Address : </td>
                                    <td align="left" valign="top">
                                        <?php
                                            $address = '';
                                            if(isset($_POST['address']))
                                                $address =  $_POST['address'];
                                            elseif(isset($row_rschk['address']))
                                                $address = $row_rschk['address'];
                                        ?>
                                        <textarea name="address" id="address" cols="35" rows="5"><?php echo $address;?></textarea>
                                        <?php if(isset($omitted['address']))echo '<span style="color : red">*</span>' ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="left" valign="top">Religion : </td>
                                    <td align="left" valign="top">
                                        <?php
                                            $religion = '';
                                            if(isset($_POST['religion']))
                                                $religion =  $_POST['religion'];
                                            elseif(isset($row_rschk['Religion']))
                                                $religion = $row_rschk['Religion'];
                                        ?>
                                        <select name="religion" id="religion">
                                            <option value="-1">--Choose--</option>
                                            <option value="Christianity" <?php if('Christianity' == $religion)echo 'selected'?>>Christianity</option>
                                            <option value="Islam" <?php if('Islam' == $religion)echo 'selected'?>>Islam</option>
                                        </select>
                                        <?php if(isset($omitted['religion']))echo '<span style="color : red">*</span>' ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="left" valign="top">State Of Origin :</td>
                                    <td align="left" valign="top">
                                        <?php
                                            $stid = '';
                                            if(isset($_POST['soforig']))
                                                $stid =  $_POST['soforig'];
                                            elseif(isset($row_rschk['stid']))
                                                $stid = $row_rschk['stid'];
                                        ?>
                                        <select name="soforig" id="soforig" >
                                            <option value="-1">Choose</option>
                                            <?php do{ ?>
                                            <option value="<?php echo $row_state['stid']?>" <?php if($row_state['stid'] == $stid)echo 'selected'?>><?php echo $row_state['stname']?></option>
                                            <?php }while ($row_state = mysql_fetch_assoc($state));
                                                $rows = mysql_num_rows($state);
                                                if($rows > 0) {
                                                        mysql_data_seek($state, 0);
                                                        $row_state = mysql_fetch_assoc($state);
                                                    }
                                            ?>
                                        </select>
                                        <?php if(isset($omitted['soforig']))echo '<span style="color : red">*</span>' ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="left" valign="top">L.G.A :</td>
                                    <td align="left" valign="top">
                                        <?php
                                             $lga = '';
                                            
                                            if(isset($_POST['lga']))
                                                $lga =  $_POST['lga'];
                                            elseif(isset($row_rschk['lga']))
                                                $lga = $row_rschk['lga'];                                             
                                        ?>
                                        <select name="lga" id="lga" >
                                            <option  value="-1">Choose</option>
                                            <option class="og" value="Abeokuta North" <?php if('Abeokuta North' == $lga)echo 'selected'?>>Abeokuta North</option>
                                            <option class="og" value="Abeokuta South" <?php if('Abeokuta South' == $lga)echo 'selected'?>>Abeokuta South</option>
                                            <option class="og" value="Ado-Odo/Ota" <?php if('Ado-Odo/Ota' == $lga)echo 'selected'?>>Ado-Odo/Ota</option>
                                            <option class="og" value="Egbado North" <?php if('Egbado North' == $lga)echo 'selected'?>>Egbado North</option>
                                            <option class="og" value="Egbado South" <?php if('Egbado South' == $lga)echo 'selected'?>>Egbado South</option>
                                            <option class="og" value="Ewekoro" <?php if('Ewekoro' == $lga)echo 'selected'?>>Ewekoro</option>
                                            <option class="og" value="Ifo" <?php if('Ifo' == $lga)echo 'selected'?>>Ifo</option>
                                            <option class="og" value="Ijebu East" <?php if('Ijebu East' == $lga)echo 'selected'?>>Ijebu East</option>
                                            <option class="og" value="Ijebu North" <?php if('Ijebu North' == $lga)echo 'selected'?>>Ijebu North</option>
                                            <option class="og" value="Ijebu North East" <?php if('Ijebu North East' == $lga)echo 'selected'?>>Ijebu North East</option>
                                            <option class="og" value="Ijebu Ode" <?php if('Ijebu-Ode' == $lga)echo 'selected'?>>Ijebu Ode</option>
                                            <option class="og" value="Ikenne" <?php if('Ikenne' == $lga)echo 'selected'?>>Ikenne</option>
                                            <option class="og" value="Imeko-Afon" <?php if('Imeko-Afon' == $lga)echo 'selected'?>>Imeko-Afon</option>
                                            <option class="og" value="Ipokia" <?php if('Ipokia' == $lga)echo 'selected'?>>Ipokia</option>
                                            <option class="og" value="Obafemi-Owode" <?php if('Obafemi-Owode' == $lga)echo 'selected'?>>Obafemi-Owode</option>
                                            <option class="og" value="Ogun Waterside" <?php if('Ogun Waterside' == $lga)echo 'selected'?>>Ogun Waterside</option>
                                            <option class="og" value="Odeda" <?php if('Odeda' == $lga)echo 'selected'?>>Odeda</option>
                                            <option class="og" value="Odogbolu" <?php if('Odogbolu' == $lga)echo 'selected'?>>Odogbolu</option>
                                            <option class="og" value="Remo North" <?php if('Remo North' == $lga)echo 'selected'?>>Remo North</option>
                                            <option class="og" value="Shagamu" <?php if('Shagamu' == $lga)echo 'selected'?>>Shagamu</option>
                                            <option class="others" value="Others" <?php if('Others' == $lga)echo 'selected'?>>Others</option>
                                        </select>
                                        <?php if(isset($omitted['lga']))echo '<span style="color : red">*</span>' ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="top" align="left">Sponsor's Name : </td>
                                    <td align="left" valign="top">
                                        <?php
                                            $sponsorname = '';
                                            if(isset($_POST['sponsorname']))
                                                $sponsorname =  $_POST['sponsorname'];
                                            elseif(isset($row_rschk['sponsorname']))
                                                $sponsorname = $row_rschk['sponsorname'];
                                        ?>
                                        <input type="text" name="sponsorname" id="sponsorname" value="<?php echo $sponsorname?>"/>
                                        <?php if(isset($omitted['sponsorname']))echo '<span style="color : red">*</span>' ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="top" align="left">Sponsor's Phone : </td>
                                    <td align="left" valign="top">
                                        <?php
                                            $sponsorphn = '';
                                            if(isset($_POST['sponsorphn']))
                                                $sponsorphn =  $_POST['sponsorphn'];
                                            elseif(isset($row_rschk['sponsorphn']))
                                                $sponsorphn = $row_rschk['sponsorphn'];
                                        ?>
                                       <input type="text" name="sponsorphn" id="sponsorphn" value="<?php echo $sponsorphn;?>"/>
                                       <?php if(isset($omitted['sponsorphn']))echo '<span style="color : red">*</span>' ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="top" align="left">Sponsor's Address : </td>
                                    <td align="left" valign="top">                                        
                                        <?php
                                            $sponsoradrs = '';
                                            if(isset($_POST['sponsoradrs']))
                                                $sponsoradrs =  $_POST['sponsoradrs'];
                                            elseif(isset($row_rschk['sponsoradrs']))
                                                $sponsoradrs = $row_rschk['sponsoradrs'];
                                        ?>
                                        <textarea name='sponsoradrs' cols="35" rows="5" ><?php echo $sponsoradrs;?></textarea>
                                        <?php if(isset($omitted['sponsoradrs']))echo '<span style="color : red">*</span>' ?>
                                    </td>
                                </tr>
                            </table>
                        </fieldset> 
                    </td>
                </tr>
          </table>
          
          <table width="670"><tr><td width="651">
          <fieldset>
            <legend>Academic Data</legend>
            <table width="622" border="0" align="left">
                <tr>
                    <td colspan="2">
                        <p>&nbsp;</p>
                        <table width="320" class="table table-hover table-striped table-bordered">
                            <tr>
                                <th colspan="2"> DIRECT ENTRY </th>
                            </tr>
                            <tr>
                                <td>UTME Reg No.</td>
                                <td align="left">
                                    <?php
                                        $jambregid = '';
                                        if(isset($_POST['jambregid']))
                                            $jambregid =  $_POST['jambregid'];
                                        elseif(isset($row_rschk['jambregid']))
                                            $jambregid = $row_rschk['jambregid'];
                                    ?>
                                    <input type="text" name="jambregid" id="jambregid" value="<?php echo $jambregid;?>"/>
                                    <?php if(isset($omitted['jambregid']))echo '<span style="color : red">*</span>' ?>
                                </td>
                            </tr>
                            <tr>
                                <td>UTME Year.</td>
                                <td align="left">
                                    <?php
                                        $jambyear = '';
                                        if(isset($_POST['jambyear']))
                                            $jambyear =  $_POST['jambyear'];
                                        elseif(isset($row_rschk['jambyear']))
                                            $jambyear = $row_rschk['jambyear'];
                                    ?>
                                    <input type="text" name="jambyear" id="jambyr" value="<?php echo $jambyear;?>" maxlength="4"/>
                                    <?php if(isset($omitted['jambyear']))echo '<span style="color : red">*</span>' ?>
                                </td>
                                    
                            </tr>
                            <tr>
                                <td colspan="2"style="font-weight: bold" align="center"> Previous Qualification </td>
                            </tr>
                            <tr>
                                <td>School Name :</td>
                                <td align="left">
                                    <?php
                                        $deschname = '';
                                        if(isset($_POST['deschname']))
                                            $deschname =  $_POST['deschname'];
                                        elseif(isset($row_rschk['deschname']))
                                            $deschname = $row_rschk['deschname'];
                                    ?>
                                    <input type="text" name="deschname" value="<?php echo $deschname;?>" size="70"/>
                                    <?php if(isset($omitted['deschname']))echo '<span style="color : red">*</span>' ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Graduation year :</td>
                                <td align="left">
                                    <?php
                                        $degradyear = '';
                                        if(isset($_POST['degradyear']))
                                            $degradyear =  $_POST['degradyear'];
                                        elseif(isset($row_rschk['degradyear']))
                                            $degradyear = $row_rschk['degradyear'];
                                    ?>
                                    <input type="text" name="degradyear" value="<?php echo $degradyear?>" maxlength="4"/>
                                    <?php if(isset($omitted['degradyear']))echo '<span style="color : red">*</span>' ?>
                                    <span style="color: #999999"> *yyyy</span>
                                </td>
                            </tr>
                            <tr>
                                <td>Grade : </td>
                                <td align="left">
                                    <?php
                                        $degrade = '';
                                        if(isset($_POST['degrade']))
                                            $degrade =  $_POST['degrade'];
                                        elseif(isset($row_rschk['degrade']))
                                            $degrade = $row_rschk['degrade'];
                                    ?>
                                    <select name="degrade">
                                        <option value="-1" <?php if('-1' == $degrade)echo 'selected'?>>--Choose--</option>
                                        <option value="1"  <?php if('1' == $degrade)echo 'selected'?>>Distinction</option>
                                        <option value="2"  <?php if('2' == $degrade)echo 'selected'?>>Upper-Credit</option>
                                        <option value="3"  <?php if('3' == $degrade)echo 'selected'?>>Lower-Credit</option>
                                        <option value="4"  <?php if('4' == $degrade)echo 'selected'?>>Merit</option>
                                        <option value="5"  <?php if('5' == $degrade)echo 'selected'?>>Pass</option>
                                    </select>
                                    <?php if(isset($omitted['degrade']))echo '<span style="color : red">*</span>' ?>
                                </td>
                            </tr>
                        </table>
                        <p>&nbsp;</p>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <p>&nbsp;</p>
                        <table width="320" class="table table-hover table-striped table-bordered">
                            <tr>
                                <th colspan="2"> Programme Choices</th>
                            </tr>
                            <tr>
                                <td>1st Choice of Programme : </td>
                                <td align="left">
                                    <?php
                                        $progid1 = '';
                                        if(isset($_POST['progid']))
                                            $progid1 =  $_POST['progid'];
                                        elseif(isset($row_rschk['progid1']))
                                            $progid1 = $row_rschk['progid1'];
                                    ?>    
                                    <select name="progid1" id="progid1" style="width: 400px">
                                        <option value="-1">--Choose programme--</option>
                                        <?php
                                                        do {  
                                                        ?>
                                        <option value="<?php echo $row_rsprg['progid']?>" <?php if($row_rsprg['progid'] == $progid1)echo 'selected'?>><?php echo $row_rsprg['progname']?></option>
                                        <?php
                                          } while ($row_rsprg = mysql_fetch_assoc($rsprg));
                                            $rows = mysql_num_rows($rsprg);
                                            if($rows > 0) {
                                                mysql_data_seek($rsprg, 0);
                                                    $row_rsprg = mysql_fetch_assoc($rsprg);
                                            }
                                        ?>
                                    </select>
                                    <?php if(isset($omitted['progid1']))echo '<span style="color : red">*</span>' ?> 
                                </td>
                            </tr>
                            <tr>
                                <td> 2nd Choice of programme :</td>
                                <td align="left">
                                    <?php
                                        $progid2 = '';
                                        if(isset($_POST['progid']))
                                            $progid2 =  $_POST['progid'];
                                        elseif(isset($row_rschk['progid2']))
                                            $progid2 = $row_rschk['progid2'];
                                    ?>   
                                    <select name="progid2" id="progid2" style="width: 400px">
                                        <option value="-1">--Choose programme--</option>
                                        <?php
                                          do {  
                                          ?>
                                        <option value="<?php echo $row_rsprg['progid']?>" <?php if($row_rsprg['progid'] == $progid2)echo 'selected'?>><?php echo $row_rsprg['progname']?></option>
                                        <?php
                                            } while ($row_rsprg = mysql_fetch_assoc($rsprg));
                                              $rows = mysql_num_rows($rsprg);
                                              if($rows > 0) {
                                                    mysql_data_seek($rsprg, 0);
                                                    $row_rsprg = mysql_fetch_assoc($rsprg);
                                            }
                                        ?>
                                    </select>
                                    <?php if(isset($omitted['progid2']))echo '<span style="color : red">*</span>' ?>
                                </td>
                            </tr>
                        </table>
                        <p>&nbsp;</p>
                    </td>
                </tr>
                   
                <tr>
                  <td><strong>O'Level 1st Sitting </strong></td>
                  <td><strong> O'Level 2nd Sitting </strong></td>
                  </tr>
                <tr>
                  <td><table width="309" border="0" class="table table-hover table-striped table-bordered">
                  <tr>
                      <td>Exam No : </td>
                      <td>
                          <?php
                            $exmno = '';
                            if(isset($_POST['examnumber'][0]))
                                $exmno =  $_POST['examnumber'][0];
                            elseif(isset($row_rssit1['examnumber']))
                                $exmno = $row_rssit1['examnumber'];
                        ?>
                          <input name="examnumber[]" type="text" id="examnumber" size="10" value="<?php echo $exmno?>"/>
                          <?php if(isset($omitted['examnuber']))echo '<span style="color : red">*</span>' ?>
                      </td>
                  </tr>
                  <tr>
                    <td width="155">Exam Type : </td>
                    <td width="133">
                        <?php
                            $exmtyp = '';
                            if(isset($_POST['exmtyp'][0]))
                                $exmtyp =  $_POST['exmtyp'][0];
                            elseif(isset($row_rssit1['examtype']))
                                $exmtyp = $row_rssit1['examtype'];
                        ?>   
                    <select name="exmtyp[]" id="exmtyp" style="width: 85px">
                      <option value="-1">--Choose--</option>
                      <option value="WASCE(MAY/JUNE)" <?php if("WASCE(MAY/JUNE)" == $exmtyp)echo 'selected'?>>WASCE(MAY/JUNE)</option>
                      <option value="WASCE(Private)" <?php if("WASCE(Private)" == $exmtyp)echo 'selected'?>>WASCE(Private)</option>
                      <option value="NECO" <?php if("NECO" == $exmtyp)echo 'selected'?>>NECO</option>
                      <option value="NECO(Private)" <?php if("NECO(Private)" == $exmtyp)echo 'selected'?>>NECO(Private)</option>
                    </select>
                    <?php if(isset($omitted['exmtyp']))echo '<span style="color : red">*</span>' ?>
                    </td>
                  </tr>
                  <tr>
                    <td>Exam Year : </td>
                    <td>
                        <?php
                            $exmyr = '';
                            if(isset($_POST['exmyr'][0]))
                                $exmyr =  $_POST['exmyr'][0];
                            elseif(isset($row_rssit1['examyear']))
                                $exmyr = $row_rssit1['examyear'];
                        ?> 
                        <input name="exmyr[]" type="text" id="exmyr" size="8" value="<?php echo $exmyr?>" maxlength="4"/>
                        <?php if(isset($omitted['exmyr']))echo '<span style="color : red">*</span>' ?>
                    </td>
                  </tr>
                  <tr>
                    <td><strong>Subject </strong></td>
                    <td><strong>Grade</strong></td>
                  </tr>
                  <?php for($i=0;$i<$totalRows_rssit1;$i++){?>
                  <tr>
                    <td>
                        <?php
                            $sub = '';
                            if(isset($_POST['subj']['first'][$i]))
                                $sub =  $_POST['subj']['first'][$i];
                            elseif(isset($row_rssit1['subject']))
                                $sub = $row_rssit1['subject'];
                        ?> 
                        <select name="subj[first][]" id="subj['name'][]">
                      <option value="-1">--Choose--</option>
                      <?php
					  do {  
					  ?>
                      <option value="<?php echo $row_rssubj['subjid']?>" <?php if($row_rssubj['subjid'] == $sub)echo 'selected'?>><?php echo $row_rssubj['subjname']?></option>
                      <?php
					  } while ($row_rssubj = mysql_fetch_assoc($rssubj));
						$rows = mysql_num_rows($rssubj);
						if($rows > 0) {
							mysql_data_seek($rssubj, 0);
							$row_rssubj = mysql_fetch_assoc($rssubj);
						}
					  ?>
                    </select>
                    <?php if(isset($omitted['subj']["$i"]))echo '<span style="color : red">*</span>' ?>
                    </td>
                    <td>
                        <?php
                            $grade = '';
                            if(isset($_POST['grade']['first'][$i]))
                                $grade =  $_POST['grade']['first'][$i];
                            elseif(isset($row_rssit1['grade']))
                                $grade = $row_rssit1['grade'];
                        ?> 
                        <select name="grade[first][]" id="subj['grade'][]">
                      <option value="-1">grade</option>
                      <?php
do {  
?>
                      <option value="<?php echo $row_rsgrd['grdid']?>" <?php if($row_rsgrd['grdid'] == $grade)echo 'selected'?>><?php echo $row_rsgrd['grdname']?></option>
                      <?php
} while ($row_rsgrd = mysql_fetch_assoc($rsgrd));
  $rows = mysql_num_rows($rsgrd);
  if($rows > 0) {
      mysql_data_seek($rsgrd, 0);
	  $row_rsgrd = mysql_fetch_assoc($rsgrd);
  }
?>
                    </select></td>
                  </tr>
                  <?php $row_rssit1 = mysql_fetch_assoc($rssit1);}?>
                  
                  <?php for($i=0;$i<9-$totalRows_rssit1;$i++){?>
                  <tr>
                    <td>
                        <select name="subj[first][]" id="subj['name'][]">
                      <option value="-1">--Choose--</option>
                      <?php
					  do {  
					  ?>
                      <option value="<?php echo $row_rssubj['subjid']?>"><?php echo $row_rssubj['subjname']?></option>
                      <?php
					  } while ($row_rssubj = mysql_fetch_assoc($rssubj));
						$rows = mysql_num_rows($rssubj);
						if($rows > 0) {
							mysql_data_seek($rssubj, 0);
							$row_rssubj = mysql_fetch_assoc($rssubj);
						}
					  ?>
                    </select></td>
                    <td><select name="grade[first][]" id="subj['grade'][]">
                      <option value="-1">grade</option>
                      <?php
do {  
?>
                      <option value="<?php echo $row_rsgrd['grdid']?>"><?php echo $row_rsgrd['grdname']?></option>
                      <?php
} while ($row_rsgrd = mysql_fetch_assoc($rsgrd));
  $rows = mysql_num_rows($rsgrd);
  if($rows > 0) {
      mysql_data_seek($rsgrd, 0);
	  $row_rsgrd = mysql_fetch_assoc($rsgrd);
  }
?>
                    </select></td>
                  </tr>
                  <?php }?>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                  </tr>
                </table></td>
                <td><table width="309" border="0" class="table table-hover table-striped table-bordered">
                <tr>
                	<td>Exam No : </td>
                    <td><input name="examnumber[]" type="text" id="examnumber" size="10" value="<?php echo $row_rssit2['examnumber']?>"/></td>
                </tr>
                  <tr>
                    <td width="155">Exam Type : </td>
                    <td width="133">
                        <select name="exmtyp[]" id="exmtyp2" style="width: 85px">
                            <option value="-1">--Choose--</option>
                            <option value="WASCE(MAY/JUNE)" <?php if("WASCE(MAY/JUNE)" == $row_rssit2['examtype'])echo 'selected'?>>WASCE(MAY/JUNE)</option>
                            <option value="WASCE(Private)" <?php if("WASCE(Private)" == $row_rssit2['examtype'])echo 'selected'?>>WASCE(Private)</option>
                            <option value="NECO" <?php if("NECO" == $row_rssit2['examtype'])echo 'selected'?>>NECO</option>
                            <option value="NECO(Private)" <?php if("NECO(Private)" == $row_rssit2['examtype'])echo 'selected'?>>NECO(Private)</option>
                        </select>
                    </td>
                  </tr>
                  <tr>
                        <td>Exam Year : </td>
                        <td><input name="exmyr[]" type="text" id="exmyr2" size="8" value="<?php echo $row_rssit2['examyear']?>" maxlength="4"/></td>
                  </tr>
                  <tr>
                    <td><strong>Subject </strong></td>
                    <td><strong>Grade</strong></td>
                  </tr>
                  <?php for($i=0;$i<$totalRows_rssit2;$i++){?>
                  <tr>
                    <td><select name="subj[second][]" id="subj2['name'][]">
                      <option value="-1">--Choose-</option>
                      <?php
					  do {  
					  ?>
                      <option value="<?php echo $row_rssubj['subjid']?>" <?php if($row_rssubj['subjid'] == $row_rssit2['subject'])echo 'selected'?>><?php echo $row_rssubj['subjname']?></option>
                      <?php
					  } while ($row_rssubj = mysql_fetch_assoc($rssubj));
						$rows = mysql_num_rows($rssubj);
						if($rows > 0) {
							mysql_data_seek($rssubj, 0);
							$row_rssubj = mysql_fetch_assoc($rssubj);
						}
					  ?>
                    </select></td>
                    <td><select name="grade[second][]" id="subj2['grade'][]">
                      <option value="-1">grade</option>
                      <?php
do {  
?>
                      <option value="<?php echo $row_rsgrd['grdid']?>" <?php if($row_rsgrd['grdid'] == $row_rssit2['grade'])echo 'selected'?>><?php echo $row_rsgrd['grdname']?></option>
                      <?php
} while ($row_rsgrd = mysql_fetch_assoc($rsgrd));
  $rows = mysql_num_rows($rsgrd);
  if($rows > 0) {
      mysql_data_seek($rsgrd, 0);
	  $row_rsgrd = mysql_fetch_assoc($rsgrd);
  }
?>
                    </select></td>
                  </tr>
                  <?php $row_rssit2 = mysql_fetch_assoc($rssit2);}?>
                  
                  
                  <?php for($i=0;$i<9-$totalRows_rssit2;$i++){?>
                  <tr>
                    <td><select name="subj[second][]" id="subj2['name'][]">
                      <option value="-1">--Choose-</option>
                      <?php
					  do {  
					  ?>
                      <option value="<?php echo $row_rssubj['subjid']?>"><?php echo $row_rssubj['subjname']?></option>
                      <?php
					  } while ($row_rssubj = mysql_fetch_assoc($rssubj));
						$rows = mysql_num_rows($rssubj);
						if($rows > 0) {
							mysql_data_seek($rssubj, 0);
							$row_rssubj = mysql_fetch_assoc($rssubj);
						}
					  ?>
                    </select></td>
                    <td><select name="grade[second][]" id="subj2['grade'][]">
                      <option value="-1">grade</option>
                      <?php
do {  
?>
                      <option value="<?php echo $row_rsgrd['grdid']?>"><?php echo $row_rsgrd['grdname']?></option>
                      <?php
} while ($row_rsgrd = mysql_fetch_assoc($rsgrd));
  $rows = mysql_num_rows($rsgrd);
  if($rows > 0) {
      mysql_data_seek($rsgrd, 0);
	  $row_rsgrd = mysql_fetch_assoc($rsgrd);
  }
?>
                    </select></td>
                  </tr>
                  <?php }?>
                  <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                  </tr>
                </table></td>
              </tr>
            </table>
            <p>&nbsp;</p>
          </fieldset>           
           </td></tr></table>
          <table width="250" border="0" align="center">
              <input type="hidden" name="access" value="11"/>
           <p>&nbsp;</p>
            <tr align="center">
              <!--<td><input type="submit" name="submit" id="submit" value="Submit Form"  class="btn btn-primary"/></td>-->
              <td><input type="submit" name="save" id="save" value="Submit Application"  class="btn btn-primary"/></td>
            </tr>
          </table>
        </form></td>
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