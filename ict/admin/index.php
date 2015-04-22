<?php require_once('../../Connections/tams.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}

define ('MAX_FILE_SIZE', 2048 * 1536);

$MM_authorizedUsers = "20";
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
?>
<?php

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

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  
}

require_once('../../param/param.php');
require_once('../../functions/function.php');

$root = $_SERVER['DOCUMENT_ROOT'];
$root = substr($root, 0, strlen($root)-1);
if(!isset($_SESSION['cur_folder']))
    $_SESSION['cur_folder'] = dirname($_SERVER['SCRIPT_FILENAME']);

$folder = NULL;
if(isset($_GET['f']))
    $folder = $_GET['f'];
  
if(isset($folder)) {
    if($folder == 'top') {
        $paths = explode('/', $_SESSION['cur_folder']);
        $pos = count($paths) - 1;
        $level = (isset($_GET['l']))? $_GET['l']: 1;
        for($i = 0; $i < $level; $i++) {
            unset($paths[$pos--]); 
        }
                
        $_SESSION['cur_folder'] = implode('/', $paths);
    }else {
        $_SESSION['cur_folder'] .= "/{$folder}";
    }
    header('Location: index.php');
}

$msg = '';

$root_path = explode('/', $root);
$root_count = count($root_path);

$cur_path = explode('/', $_SESSION['cur_folder']);
$cur_count = count($cur_path);

if($root_count < $cur_count) {
    $breadcrumb = '';
    for($i = $root_count; $i < $cur_count; $i++) {
        $step = strtolower($cur_path[$i]);
        if($i == $cur_count - 1) {
            $breadcrumb .= "/ {$step}";
        }else{
            $level = $cur_count - $i - 1;
            $breadcrumb .= "/ <a href='?f=top&l={$level}'>{$step}</a> ";
        }
    }
}else {
    $breadcrumb = '/'; 
    $_SESSION['cur_folder'] = $root;
}

if(isset($_POST['upload'])) {
    $msg = uploadFile($_SESSION['cur_folder'].'/', 'upload', MAX_FILE_SIZE);
}

$files = @scandir($_SESSION['cur_folder'], SORT_STRING); 
$cur_dir = $_SESSION['cur_folder'];
$dir_files = array();

$excl = array('.', '..');
        
if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout($site_root.'/ict');  
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/icttemplate.dwt.php"" codeOutsideHTMLIsLocked="false" -->
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
<?php include '../include/topmenu.php'; ?>
  </div>
  <!-- end .topmenu --> 
  
  <div class="loginuser">
  <?php include '../include/loginuser.php'; ?>
  
  <!-- end .loginuser --></div>
  <div class="pagetitle">
    <table width="600">
      <tr>
        <td><!-- InstanceBeginEditable name="pagetitle" -->Remote Update<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
        <table width="683" border="0" class="table table-striped">
            <?php if(isset($msg)){?>
            <tr>
                <td><?php echo $msg;?></td>
            </tr>
            <?php }?>
            <tr>
                <td><?php echo $breadcrumb;?></td>
            </tr>
            <tr>
                <td></td>
            </tr>
            <tr>
                <td>
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
                        <input type='file' name="filename" id="filename"/>
                        <input type='submit' name='upload' value='Upload'/>
                    </form>
                </td>
            </tr>
            <tr>
                <td></td>
            </tr>
            <?php 
                if($files){
                    foreach($files as $file){
                        if(in_array($file,$excl) || substr($file, 0, 1) == '.'){
                            continue;
                        }       
             
                        if(is_dir("{$cur_dir}/{$file}")){
            ?>
            <tr>
                <td><?php echo "<a href='?f={$file}'>{$file}</a>";?></td>
            </tr>
            <?php
                            
                        }else  
                            array_push($dir_files, $file);
            ?>
                
            <?php }
                foreach($dir_files as $file) {
            ?>
            <tr>
                <td><?php echo $file;?></td>
            </tr>
            <?php }}else{?>
            <tr>
                <td>Could not display content of the specified folder!</td>
            </tr>
            <?php }?>
        </table>
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
<?php
?>
