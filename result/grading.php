<?php require_once('../Connections/tams.php'); ?>
<?php
 
if (!isset($_SESSION)) {
  session_start();
}

require_once('../param/param.php');
require_once('../functions/function.php');

$MM_authorizedUsers = "2,3";
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

$MM_restrictGoTo = "../login.php";
if (!((isset($_SESSION['MM_Username'])) && 
        (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
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

mysql_select_db($database_tams, $tams);
$query_rssess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,6";
$rssess = mysql_query($query_rssess, $tams) or die(mysql_error());
$row_rssess = mysql_fetch_assoc($rssess);
$totalRows_rssess = mysql_num_rows($rssess);

$type = 'College';
$unitid = getSessionValue('cid');

$query_deptcrs = sprintf("SELECT DISTINCT dc.csid, dc.unit, dc.status, c.csname "
                            . "FROM department_course dc "
                            . "JOIN course c ON dc.csid = c.csid "
                            . "JOIN department d ON d.deptid = dc.deptid "
                            . "WHERE d.colid=%s ", 
                            GetSQLValueString($unitid, "int"));
if(getAccess() == 3) {
    $type = 'Department';
    $unitid = getSessionValue('did');
    
    
    $query_deptcrs = sprintf("SELECT DISTINCT dc.csid, c.csname, dc.unit, dc.status  "
                                . "FROM department_course dc "
                                . "JOIN course c ON dc.csid = c.csid "
                                . "WHERE dc.deptid=%s ", 
                                GetSQLValueString($unitid,"int"));
}

if(isset($_POST['sesid'])) {
    if(!empty($_POST['grade'])) {
        $fields = implode(',', array_keys($_POST['grade']));
        $values = implode(',', array_values($_POST['grade']));

        if(isset($_POST['gradid'])) {  
            $updateValue = array();
            foreach($_POST['grade'] as $name => $value) {
                $updateValue[] = "{$name}=".GetSQLValueString($value, 'int');
            }
            
            $updateValue = implode(',', $updateValue);

            $updateSQL = sprintf("UPDATE grading SET %s WHERE gradid=%s",
                               GetSQLValueString($updateValue, "defined", $updateValue),
                               GetSQLValueString($_POST['gradid'], "int"));
            $Result = mysql_query($updateSQL, $tams) or die(mysql_error()); 
        }else {
            $insertSQL = sprintf("INSERT INTO grading (%s) VALUES (%s)",
                                 GetSQLValueString($fields, "defined", $fields),
                                 GetSQLValueString($values, "defined", $values));
            $Result = mysql_query($insertSQL, $tams) or die(mysql_error()); 
        }
    }
    
}elseif(isset($_POST['exceptions'])) {
    $error = array();
    foreach($_POST['crs'] as $idx => $crs) {
        $mark = $_POST['mark'][$idx];
        if(!($mark > -1 && $mark < 101)) {
            $error[] = $crs;
            continue;
        }
        
        $insertSQL = sprintf("INSERT INTO grade_exceptions (csid, sesid, unitid, passmark, type) VALUES (%s, %s, %s, %s, %s)",
                                 GetSQLValueString($crs, "text"),
                                 GetSQLValueString($row_rssess['sesid'], "int"),
                                 GetSQLValueString($unitid, "int"),
                                 GetSQLValueString($mark, "int"),
                                 GetSQLValueString($type, "text"));
        $Result = mysql_query($insertSQL, $tams) or die(mysql_error()); 
    }
    
}elseif(isset($_POST['save'])) {
    
    if(isset($_POST['expid'])) {
     
        foreach($_POST['expid'] as $idx => $exp) {
            if(isset($_POST['mark'][$idx])) {
                $mark = $_POST['mark'][$idx];                
            }else {
                continue;
            }
            
            if(!($mark > -1 && $mark < 101)) {
                continue;
            }

            $updateSQL = sprintf("UPDATE grade_exceptions SET passmark = %s WHERE expid = %s",
                                     GetSQLValueString($mark, "int"),
                                     GetSQLValueString($exp, "int"));
            $Result = mysql_query($updateSQL, $tams) or die(mysql_error()); 
        }
    }
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$colname_grad = $row_rssess['sesid'];
if(isset($_GET['sid'])) {
    $colname_grad = $_GET['sid'];
}

$query_grad = sprintf("SELECT * FROM grading WHERE colid = %s AND sesid = %s",
                GetSQLValueString(getSessionValue('cid'), "int"),
                GetSQLValueString($colname_grad, "int"));
$grad = mysql_query($query_grad, $tams) or die(mysql_error());
$row_grad = mysql_fetch_assoc($grad);
$totalRows_grad = mysql_num_rows($grad);

$query_exp = sprintf("SELECT c.csid, c.csname, g.expid, g.passmark, dc.status, dc.unit  "
                        . "FROM grade_exceptions g "
                        . "JOIN course c ON g.csid = c.csid "
                        . "JOIN department_course dc ON dc.csid = g.csid "
                        . "WHERE (g.type = %s AND g.unitid = %s) AND g.sesid = %s",
                        GetSQLValueString($type, "text"),
                        GetSQLValueString($unitid, "int"),
                        GetSQLValueString($colname_grad, "int"));
$exp = mysql_query($query_exp, $tams) or die(mysql_error());
$row_exp = mysql_fetch_assoc($exp);
$totalRows_exp = mysql_num_rows($exp);

$deptcrs = mysql_query($query_deptcrs, $tams) or die(mysql_error());
$row_deptcrs = mysql_fetch_assoc($deptcrs);
$totalRows_deptcrs = mysql_num_rows($deptcrs);

$courses = array();
if($totalRows_deptcrs > 1) {
    do{
        $courses[] = $row_deptcrs;
    }while($row_deptcrs = mysql_fetch_assoc($deptcrs));
}

$exceptions = array();
if($totalRows_exp > 0) { 
    do{
        $exceptions[] = $row_exp;
    }while($row_exp = mysql_fetch_assoc($exp));
}

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );  
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" ng-app="tams">
    <!-- InstanceBegin template="/Templates/template.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php require('../param/site.php'); ?>
<title><?php echo $university ?> </title>
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/tams.js"></script>
<script type="text/javascript" src="../scripts/angular/angular.min.js"></script>
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
        <td><!-- InstanceBeginEditable name="pagetitle" --><!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
        <tr>
            <td colspan="3">
                <select onchange="sesfilt(this)" name="sesid">
                    <?php do{?>
                    <option value="<?php echo $row_rssess['sesid']?>" 
                        <?php if (!(strcmp($row_rssess['sesid'], $colname_grad))) {echo "selected=\"selected\"";} ?>>
                            <?php echo $row_rssess['sesname']?>
                    </option>
                    <?php                             
                        }while($row_rssess = mysql_fetch_assoc($rssess));
                        $rows = mysql_num_rows($rssess);

                        if($rows > 0) {
                            mysql_data_seek($rssess, 0);
                            $row_rssess = mysql_fetch_assoc($rssess);
                        }

                    ?>
                </select>
            </td>
        </tr>
          
        <?php if(getAccess() == 2) {?>
        <form method="post" action="">          
            <tr>
                <td>Grade A</td>
                <td>
                    <input name="grade[gradeA]" 
                           value="<?php if(isset($row_grad['gradeA'])) echo $row_grad['gradeA']?>" disabled/>
                </td>
                <td><input type="checkbox" class="enable"/></td>
            </tr>
            
            <tr>
                <td>Grade B</td>
                <td>
                    <input name="grade[gradeB]" 
                           value="<?php if(isset($row_grad['gradeB'])) echo $row_grad['gradeB']?>" disabled/>
                </td>               
                <td><input type="checkbox" class="enable"/></td>
            </tr> 
            
            <tr>
                <td>Grade C</td>
                <td>
                    <input name="grade[gradeC]" 
                           value="<?php if(isset($row_grad['gradeC'])) echo $row_grad['gradeC']?>" disabled/>
                </td>
                <td><input type="checkbox" class="enable"/></td>            
            </tr> 
            
            <tr>
                <td>Grade D</td>
                <td>
                    <input name="grade[gradeD]" 
                           value="<?php if(isset($row_grad['gradeD'])) echo $row_grad['gradeD']?>" disabled/>
                </td>
                <td><input type="checkbox" class="enable"/></td>            
            </tr> 
              
            <tr>
                <td>Grade E</td>
                <td>
                    <input name="grade[gradeE]" 
                           value="<?php if(isset($row_grad['gradeE'])) echo $row_grad['gradeE']?>" disabled/>
                </td>
                <td><input type="checkbox" class="enable"/></td>            
            </tr> 
              
              <tr>
                <td>Grade F</td>
                <td>
                    <input name="grade[gradeF]" 
                           value="<?php if(isset($row_grad['gradeF'])) echo $row_grad['gradeF']?>" disabled/>
                </td>
                <td><input type="checkbox" class="enable"/></td>            
            </tr> 
              
            <tr>
                <td>Passmark</td>
                <td>
                    <input name="grade[passmark]" 
                           value="<?php if(isset($row_grad['passmark'])) echo $row_grad['passmark']?>" disabled/>
                </td>
                <td><input type="checkbox" class="enable"/></td>            
            </tr> 
            
            <tr>
                <td colspan="3" align="center">
                    <input type="hidden" name="colid" value="<?php echo getSessionValue('cid')?>"/>
                    
                    <?php
                        if($row_rssess['sesid'] == $colname_grad) {
                            if(isset($row_grad['gradid'])) {
                    ?> 
                    
                    <input type="hidden" name="gradid" value="<?php echo $row_grad['gradid']?>"/>
                    <input type="submit"  value="Edit Grades"/>
                    <?php }else { ?>
                    <input type="submit"  value="Create Grades"/>
                    <?php }}?>
                    
                </td>            
            </tr>
        </form>
        <?php }?>
        
        <tr>
            <td colspan="3"></td>
        </tr>
        
        <tr ng-controller="EditController">
            <td colspan="3">
                <h3>Grade Exceptions</h3>
                <form method="post" action="<?php echo $editFormAction?>">
                <table class="table table-striped">
                    <tr>
                        <th>Course Code</th>
                        <th>Course Name</th>
                        <th>Status</th>
                        <th>Pass Mark</th>
                        <th><span ng-show="data.edit">Edit</span></th>
                    </tr>
                
                    <tr ng-repeat="exp in data.exceptions">
                        <td ng-bind="exp.csid"></td>
                        <td ng-bind="exp.csname"></td>                        
                        <td ng-bind-template="{{exp.unit}}{{exp.status | first}}"></td>
                        <td><span ng-show="!exp.shown" ng-bind="exp.passmark"></span>
                            <input ng-show="exp.shown && data.edit"
                                   name="mark[]" 
                                   type="int" 
                                   ng-model="exp.editmark" 
                                   value=""
                                   ng-disabled="!exp.shown || !data.edit"/>
                        </td>
                        <td><button ng-show="data.edit" ng-click="toggleSingleEdit($index, $event)">Edit</button></td>
                        <input ng-disabled="!exp.shown || !data.edit" 
                               type="hidden" 
                               name="expid[]" 
                               value="{{exp.expid}}"/>
                    </tr>
                
                    <tr>
                        <td colspan="5" align="center" ng-show="data.exceptions.length > 0">
                            <button ng-click="toggleEdit($event)" ng-bind="data.label"> </button>
                            <input ng-show="data.edit" type="submit" name="save" value="Save Edit"/>
                        </td>
                    </tr>
                    
                    <tr ng-show="data.exceptions.length < 1">
                        <td colspan="5">You do not have any grade exceptions for this session!</td>
                    </tr>
                </table>
                </form>
            </td>
        </tr>
        <?php 
            if($row_rssess['sesid'] == $colname_grad) {
        ?>
        <tr ng-controller="GradeController">
            <td colspan="3">
                <button href="#" ng-click="newException()">Add New Exception</button>
                <form method="post" action="<?php echo $editFormAction?>" ng-show="data.exceptions.length > 0">
                    <div ng-repeat="exp in data.exceptions" ng-init="data.index = $index">
                        <span>                            
                            <select name="crs[]" style="width: 300px" >
                                <option ng-repeat="course in data.courses" 
                                         value="{{course.csid}}" 
                                         ng-bind-template="{{course.csname}} ({{course.unit}}{{course.status | first}})"
                                         ></option>
                            </select>
                        </span>
                        <span>
                            <input placeholder="Pass Mark" name="mark[]" type='number' min="0" max="100" width="30" />
                        </span>
                        <span>
                            <button ng-click="remException($index)">Remove</button>
                        </span>
                    </div>
                    <input type="submit" name="exceptions" value="Create Exceptions"/>
                </form>
            </td>
        </tr>
        <?php }?>
      </table>
    <script type="text/javascript">
    </script>
  <!-- InstanceEndEditable --></div>
<div class="footer">
    <p><!-- end .footer -->   
    
    <?php require '../include/footer.php'; ?>
	
   </p>
  </div>
  <!-- end .container -->
</div>
<script>
    $(function() {
            $('.enable').change(function() {
                if($(this).is(':checked')) {
                    $(this).parent().prev().children('input').attr('disabled', false);
                    return;
                }
                $(this).parent().prev().children('input').attr('disabled', true);
            });
        });
</script>
    
<script>
    var module = angular.module('tams', []);

    module.filter("exclude", function() {
      return function(input, exclude) {
        var result;
//        for (var i=0; i<input.length; i++) {
//          if (input[i] !== exclude) {
//            result.push(input[i]);
//          }
//        }
        
        result = input.filter(function(item, index, array) {
            var state = false;
            if(exclude.indexOf(item) !== -1) {
                state = true;
            }            
            return state;
        });
        
        return result;
      };
    });

    module.filter('first', function() {
        return function(input) {
            // input will be the string we pass in
            if (input)
                return input.substr(0, 1);
        }
    });

    module.controller('GradeController', function($scope) {

        $scope.data = {
            "index": null,
            "exceptions": [],
            "courses": <?php echo json_encode($courses)?>,
            "used": []
        };
        
        $scope.newException = function() {            
            $scope.data.exceptions.unshift({"id":""});
        };

        $scope.new = function(idx, index) { 
            // $scope.data.used[idx] = $scope.data.courses[idx];
            $scope.data.used[idx] = $scope.data.courses[index];
        };
        
        $scope.remException = function(idx) {            
            $scope.data.exceptions.splice(idx, 1);
        };
    });

    module.controller('EditController', function($scope) {

        $scope.data = {
            "edit": false,
            "label": "Edit Grade",
            "exceptions": <?php echo json_encode($exceptions)?>,
            "active": {}
        };
        
        //alert($scope.data.exceptions.length);
        $scope.toggleEdit = function(e) {  
            if($scope.data.label == "Edit Grade") {
                $scope.data.label = "Cancel Edit";
            }else {
                $scope.data.label = "Edit Grade";
                
                // Get all active edits and change display status to false
                var key = Object.keys($scope.data.active);
                key.forEach(function(value) {
                    $scope.data.exceptions[value]['shown'] = false;
                });
                
                // Remove all active edits
                $scope.data.active = {};
            }
            
            $scope.data.edit = !$scope.data.edit;
            e.preventDefault();
        };

        $scope.toggleSingleEdit = function(idx, e) {             
            
            if($scope.data.exceptions[idx]['shown'] == null || $scope.data.exceptions[idx]['shown'] == false) {                
                $scope.data.exceptions[idx]['editmark'] = $scope.data.exceptions[idx]['passmark'];
                $scope.data.exceptions[idx]['shown'] = true;
                $scope.data.active[idx] = true;
            }else {
                //$scope.data.exceptions[idx]['editmark'] = $scope.data.exceptions['passmark'];
                $scope.data.active[idx] = false;
                $scope.data.exceptions[idx]['shown'] = false
            }
            
            e.preventDefault();
        };
        
        $scope.remException = function(idx) {            
            $scope.data.exceptions.splice(idx, 1);
        };
    });
</script>
</body>
<!-- InstanceEnd --></html>
<?php
?>