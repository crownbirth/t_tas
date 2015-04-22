<?php require_once('Connections/tams.php'); ?>
<?php

if (!isset($_SESSION)) {
  session_start();
}

//include required function files 
require_once('param/param.php');
require_once('functions/function.php');

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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$action = 'request';
$name = ' Password';
$msg = '';
$error = false;

if(isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != '') {
    $action = 'reset';
    $name = 'Reset Password';
    $id = $_SERVER['QUERY_STRING'];
    
    $query_rsChk = sprintf("SELECT * "
                    . "FROM password_reset "
                    . "WHERE param=%s",
                    GetSQLValueString($id, "text"));

    $rsChk = mysql_query($query_rsChk, $tams) or die(mysql_error());    
    $row_rsChk = mysql_fetch_assoc($rsChk);
    $totalRows_rsChk = mysql_num_rows($rsChk);
    
    if($totalRows_rsChk == 1) {
        $reqDate = new DateTime(date('Y-m-d', strtotime($row_rsChk['date'])));
        //$reqDate->modify("+24 hours");
        $todaysDate = new DateTime(date('Y-m-d 23:59:59'));
        $diff = $todaysDate->diff($reqDate)->format('%R%a');
        
        if($diff < 0) {
            $query_del = sprintf("DELETE "
                    . "FROM password_reset "
                    . "WHERE resetid=%s",
                    GetSQLValueString($row_rsChk['resetid'], "int"));

            $del = mysql_query($query_del, $tams) or die(mysql_error());
            $action = 'request';
            $msg = 'The specified password reset link has expired! Request for a new password reset.';
            $error = true;
        }else {
            $_SESSION['pswd'] = array();
            $_SESSION['pswd']['unique_id'] = $row_rsChk['resetid'];
            if(isset($row_rsChk['lectid'])) {
                $_SESSION['pswd']['res_type'] = 'lecturer';
                $_SESSION['pswd']['res_value'] = $row_rsChk['lectid'];
                $_SESSION['pswd']['res_field'] = 'lectid';
            }elseif(isset($row_rsChk['stdid'])){
                $_SESSION['pswd']['res_type'] = 'student';
                $_SESSION['pswd']['res_value'] = $row_rsChk['stdid'];
                $_SESSION['pswd']['res_field'] = 'stdid';
            }else {
                $_SESSION['pswd']['res_type'] = 'prospective';
                $_SESSION['pswd']['res_value'] = $row_rsChk['pstdid'];
                $_SESSION['pswd']['res_field'] = 'pstdid';
            }
        }
        
    }else {
        $action = 'invalid';
        $msg = 'The specified password reset link does not exist!';
        $error = true;
    }
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "request")) {
    
    if(isset($_POST['username']) && $_POST['username'] != '') {
        $username = trim($_POST['username']);
        $user = (strtolower(substr($username, 0, 3)) == 'pss')? 0: 1;
        
        if($user == 0) {            
            $query_rsChk = sprintf("SELECT l.lectid, l.title, l.fname, l.lname, l.email, d.deptname, c.colname "
                              . "FROM lecturer l, department d, college c "
                              . "WHERE d.deptid = l.deptid "
                              . "AND c.colid = d.colid "
                              . "AND l.lectid=%s",
                                GetSQLValueString($username, "text"));
        }else {
            $query_rsChk = sprintf("SELECT s.stdid, s.fname, s.lname, s.email,d.deptname, c.colname "
                              . "FROM student s, programme p, department d, college c "
                              . "WHERE p.progid = s.progid "
                              . "AND d.deptid = p.deptid "
                              . "AND c.colid = d.colid "
                              . "AND s.stdid=%s",
                                GetSQLValueString($username, "text"));
        }
        
          $rsChk = mysql_query($query_rsChk, $tams) or die(mysql_error());    
          $row_rsChk = mysql_fetch_assoc($rsChk);
          $totalRows_rsChk = mysql_num_rows($rsChk);

          if($totalRows_rsChk == 1) {
              
              $email = filter_var($row_rsChk['email'], FILTER_VALIDATE_EMAIL);
              
              //Check if the user has a valid email.
              if($email){
                  $lectid = $stdid = $pstdid = $uname = $dept = $college = '';                  
                  $uid = md5(uniqid(rand(),1));
                  
                  if($user == 0) {
                      $lectid = $row_rsChk['lectid'];
                      $uname = "Hello {$row_rsChk['title']} {$row_rsChk['lname']} {$row_rsChk['fname']}";
                      $dept = $row_rsChk['deptname'];
                      $college = $row_rsChk['colname'];
                  }else if($user == 1) {
                      $stdid = $row_rsChk['stdid'];
                      $uname = "Hello {$row_rsChk['lname']} {$row_rsChk['fname']} ";
                      $dept = $row_rsChk['deptname'];
                      $college = $row_rsChk['colname'];
                  }else {
                      $pstdid = $row_rsChk['pstdid'];
                  }
                  
                  $query_insert = sprintf("INSERT INTO password_reset (lectid, stdid, pstdid, param, date) VALUES(%s, %s, %s, %s, %s)",
                                  GetSQLValueString($lectid, "text"),
                                  GetSQLValueString($stdid, "text"),
                                  GetSQLValueString($pstdid, "int"),
                                  GetSQLValueString($uid, "text"),
                                  GetSQLValueString(date('Y-m-d H:i:s'), "text"));

                  $insert = mysql_query($query_insert, $tams) or die(mysql_error()); 

                  $reset_link = "<a href='http://portal.tasued.edu.ng/tams/reset_password.php?{$uid}'>http://portal.tasued.edu.ng/tams/reset_password</a>";
                  //Prepare message and send it
                  $bodyText = "<html><head></head><body>";
                  $bodyText .= sprintf("%s<br/>%s<br/>%s<br/>%s<br/><br/>You received this e-mail because you requested a password reset on the TAMS application.<br/>"
                                            . "Click on the link below within the next 24 hours to reset your password <br/><br/> %s <br/><br/> "
                                            . "If you did not request for a password reset, please ignore this email and continue using your existing password.<br/><br/> "
                                            . "TAMS support team<br/>"
                                            . "TASUED", 
                                            $uname,
                                            $dept,
                                            $college,
                                            $university,
                                      $reset_link);
                  $bodyText .= "</body></html>";

                  $headers = "MIME-Version: 1.0\r\n"
                          . "Content-Type:text/html;charset=UTF-8\r\n"
                          . "From:info@tasued.edu.ng\r\n";
                  $mail = @mail($email, "Password Reset", $bodyText, $headers);

                  //Display appropriate message on success or failure of mail delivery.
                  if($mail){
                      $error = true;
                      $msg = "An email with a reset link has been sent to your registered email – %s";
                      $msg = sprintf($msg, $email);
                  }else{
                      if($user == 0) {
                          $query_del = sprintf("DELETE "
                                  . "FROM password_reset "
                                  . "WHERE lectid=%s",
                                  GetSQLValueString($username, "text"));
                      }elseif($user == 1) {
                          $query_del = sprintf("DELETE "
                                  . "FROM password_reset "
                                  . "WHERE stdid=%s",
                                  GetSQLValueString($username, "text"));
                      }
                      
                      $del = mysql_query($query_del, $tams) or die(mysql_error());
                      $error = true;
                      $msg = "Could not send email to the following address: %s. Please try again or contact the system administrator!";
                      $msg = sprintf($msg, $email);
                   }
              }else {
                  $error = true;
                  $msg = 'The email address in your profile is incorrect. Please contact the system administrator!';
              }
        }else {
              $error = true;
              $msg = 'The username specified does not exist! ';
          }
        
    }else {
        $msg = 'Please specify a username!';
        $error = true;
    }
  
}//End of $_POST

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "reset")) {
  
    $name = 'Reset Password';
    $action = 'change';
    $msg = 'There was a problem resetting your password, try again or contact the system administrator!';
    
    if(isset($_POST['res_pw']) && $_POST['res_pw'] != '') {
        
        $query_upd = sprintf("UPDATE %s "
                        . "SET password=%s "
                        . "WHERE %s=%s",
                        GetSQLValueString($_SESSION['pswd']['res_type'], "defined", $_SESSION['pswd']['res_type']),
                        GetSQLValueString(md5($_POST['res_pw']), "text"),
                        GetSQLValueString($_SESSION['pswd']['res_field'], "defined", $_SESSION['pswd']['res_field']),
                        GetSQLValueString($_SESSION['pswd']['res_value'], "text"));

        $upd = mysql_query($query_upd, $tams) or die(mysql_error()); 

        if($upd) {
            $msg = 'Your password has been successfully reset!';
            $query_del = sprintf("DELETE "
                    . "FROM password_reset "
                    . "WHERE resetid=%s",
                    GetSQLValueString($_SESSION['pswd']['unique_id'], "int"));

            $del = mysql_query($query_del, $tams) or die(mysql_error()); 
            $_SESSION['pswd'] = NULL;
        }
    }else {
        $action = 'reset';
        $error = true;
        $msg = 'Please specify a password!';
    }
    
    
}//End of $_POST


if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );  
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/template.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Tams </title>
<!-- InstanceEndEditable -->
<link href="css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
<link href="css/menulink.css" rel="stylesheet" type="text/css" />
<link href="css/footer.css" rel="stylesheet" type="text/css" />
<link href="css/sidemenu.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div class="container">
  <div class="header">
    <!-- end .header -->
</div>
  <div class="topmenu">
<?php include 'include/topmenu.php'; ?>
  </div>
  <!-- end .topmenu --> 
  
  <div class="loginuser">
  <?php include 'include/loginuser.php'; ?>
  
  <!-- end .loginuser --></div>
  <div class="pagetitle">
    <table width="600">
      <tr>
        <td><!-- InstanceBeginEditable name="pagetitle" --><?php echo $name?><!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include 'include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690" align="center">
        
      <?php if($action == 'request') {?>
      <tr>
        <td>&nbsp;
          <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1" class="form-inline form-signin">
          <div class="alert alert-info alert-dismissable">
              Please enter your username and a reset email will be sent to email address on your profile!
          </div>
            <table align="center">
              <tr valign="baseline">
                <td align="center" colspan="2"><?php if($error)echo $msg;?></td>
              </tr>
              <tr valign="baseline">
                <td>&nbsp;</td>
              </tr>
               
              <tr valign="baseline">
                <td nowrap="nowrap" align="right">
                    <input type="text" name="username" value="" size="32" placeholder="Enter your Username.." />
                </td>
                <td>
                    <input type="submit" value="recover"  class="btn btn-primary"/>
                </td>
              </tr>
            </table>
            <input type="hidden" name="MM_insert" value="request" />
          </form>
        <p>&nbsp;</p></td>
      </tr>
      <?php 
        }elseif($action == 'reset') {
            if($error) {
      ?>
        <tr>
            <td><?php echo $msg?></td>
        </tr>
    <?php }?>
     
        <tr>
            <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1" class="form-inline form-signin">
                <div class="alert alert-info alert-dismissable">
                    Enter a new password!
                </div>
              <table>
                <tr >
                  <td>NOTE: Password characters should not be more than 10!</td>
                </tr>
                <tr >
                  <td>&nbsp;</td>
                </tr>
                <tr valign="baseline">
                  <td nowrap="nowrap" align="right">
                      <input type="password" name="res_pw" maxlength="10" value="" size="32" placeholder="Enter new password" />
                  </td>
                  <td>
                      <input type="submit" value="reset" class="btn btn-primary"/>
                  </td>
                </tr>
              </table>
              <input type="hidden" name="MM_insert" value="reset" />
            </form>
        </tr>
    <?php }else {?>
        <tr>
            <td><?php echo $msg;?></td>
        </tr>
    <?php }?>
    </table>
  <!-- InstanceEndEditable --></div>
<div class="footer">
    <p><!-- end .footer -->   
    
    <?php require 'include/footer.php'; ?>
	
   </p>
  </div>
  <!-- end .container -->
</div>
</body>
<!-- InstanceEnd --></html>