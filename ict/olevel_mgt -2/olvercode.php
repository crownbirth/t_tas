<?php require_once('../../Connections/tams.php'); ?>
<?php
// *** Validate request to login to this site.
if (!isset($_SESSION)) {
  session_start();
}

$reroot = 'index.php';
require_once('../../param/param.php');
require_once('../../functions/function.php');

$MM_authorizedUsers = "20, 24";
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
mysql_select_db($database_tams, $tams);
$query = sprintf("SELECT vr.stdid, vr.ver_code, pr.fname,pr.lname,pr.mname, prg.progname "
                            . "FROM verification vr,prospective pr,programme prg "
                            . "WHERE vr.stdid = pr.jambregid "
                            . "AND pr.progofferd = prg.progid");
$code = mysql_query($query, $tams) or die(mysql_error());
$row_code = mysql_fetch_assoc($code);

$arr = array();
    empty($arr);
    do{
        array_push($arr, $row_code);
        
    }while($row_code = mysql_fetch_assoc($code));
    
    $json = json_encode($arr);

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
    doLogout($site_root.'/ict');  
}
?>
<!DOCTYPE html>
<html ng-App=""> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require('../../param/site.php'); ?>
    <title><?php echo $university ?> </title>
    <!-- InstanceEndEditable -->
    <link href="../../css/template.css" rel="stylesheet" type="text/css" />
    <!-- InstanceBeginEditable name="head" -->
    <!-- InstanceEndEditable -->
    <link href="../css/menulink.css" rel="stylesheet" type="text/css" />
    <link href="../css/footer.css" rel="stylesheet" type="text/css" />
    <link href="../css/sidemenu.css" rel="stylesheet" type="text/css" />
    <script src="js/angular.js"></script>
    <script src="js/app.js"></script>
</head>

    <body >
        <script type="text/javascript">
            
           
            var data = <?php echo $json?>;
            
            var codeCtrl = function($scope){
                $scope.code = data;
                
            }
        </script>
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
        <td><!-- InstanceBeginEditable name="pagetitle" --> O'Level Verification Code Page  <!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
</div> 
<div class="content"><!-- InstanceBeginEditable name="maincontent" -->

    <table width="690" class="table table-bordered" ng-controller="codeCtrl">
        <tr>
            <td>
                <table class="table table-bordered table-condensed table-striped table-hover">
                    <tr>
                        <th>Search by any parameter : </th>
                        <td><input type="text" name="search" ng-model="search" size="50"/></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                <table class="table table-bordered table-condensed table-striped table-hover">
                    <thead>
                        <tr>
                            <th>UTME Reg</th>
                            <th>Full Name</th>
                            <th>Programme</th>
                            <th>Code</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr ng-repeat="d in code | filter:search">
                            <td>{{d.stdid}}</td>
                            <td>{{d.fname}} {{d.lname}} {{d.mname}}</td>
                            <td>{{d.progname}}</td>
                            <td>{{d.ver_code}}</td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
</div>
<div class="footer">
    <p><!-- end .footer -->   
    
    <?php require '../include/footer.php'; ?>
	
   </p>
  </div>
  <!-- end .container -->
</div>
</body>
</html>