<?php require_once('../Connections/tams.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}

require_once('../param/param.php');
require_once('../functions/function.php');

$MM_authorizedUsers = "10";
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

$acl = array(2,3,5);

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

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
    
    // Level column removed for inconsistency with online database
 $insertSQL = sprintf("INSERT INTO registration (stdid, sesid, status, course) VALUES (%s, %s, %s, %s)",
                       GetSQLValueString($_POST['stid'], "text"),
                       GetSQLValueString($_POST['sid'], "int"),
                       GetSQLValueString("Registered", "text"),
                       GetSQLValueString("Unregistered", "text"));

  
  $Result = mysql_query($insertSQL, $tams) or die(mysql_error());
 
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form2")) {
    
    if(isset($_POST['deleted_entries']) && !empty($_POST['deleted_entries'])) {
        $deletedCodes = array_unique($_POST['deleted_entries']);
        //array_key;
        $deletedEntries = implode('\',\'', $deletedCodes);
        $deleteSQL = sprintf("DELETE FROM result "
                                . "WHERE stdid = %s "
                                . "AND sesid = %s "
                                . "AND csid IN ('%s')",
                               GetSQLValueString($_POST['stid'], "text"),
                               GetSQLValueString($_POST['sid'], "int"),
                               GetSQLValueString($deletedEntries, "defined", $deletedEntries));

        $Result = mysql_query($deleteSQL, $tams) or die(mysql_error());
    }
    
    if(isset($_POST['courses']) && !empty($_POST['courses'])) {
        $uniqueCourses = array_unique($_POST['courses']);
        $registeredCourses = array();
        
        mysql_query('START TRANSACTION', $tams);
        
        foreach($uniqueCourses AS $course) {
            $course = htmlentities($course);
            $dbEntry = sprintf("(%s, %s, %s, %s)", 
                            GetSQLValueString($_POST['stid'], "text"),
                            GetSQLValueString($course, "text"), 
                            GetSQLValueString($_POST['sid'], "int"),
                            GetSQLValueString('TRUE', 'text'));
            
            array_push($registeredCourses, $dbEntry);
        }
        
        $finalCourses = implode(',', $registeredCourses);
        $insertSQL = sprintf("INSERT INTO result (stdid, csid, sesid, cleared) VALUES %s;",
                                GetSQLValueString($finalCourses, "defined", $finalCourses));

        $insertResult = mysql_query($insertSQL, $tams) or die(mysql_error());
        
        $uniqueCourses = null;
        $registeredCourses = null;
        $finalCourses = null;
    }

    $updateSQL = sprintf("UPDATE registration SET course = %s WHERE stdid = %s AND sesid = %s",
                                               GetSQLValueString("Registered", "text"), 
                                               GetSQLValueString($_POST['stid'], "text"), 
                                               GetSQLValueString($_POST['sid'], "int"));

    $updateResult = mysql_query($updateSQL, $tams) or die(mysql_error());
    
    if($insertResult && $updateResult) {
        mysql_query('COMMIT', $tams);
    }else {
        mysql_query('ROLLBACK', $tams);
    }
    
}

$query_sess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,1";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

if(!checkFees($row_sess['sesid'], getSessionValue('stid'))) {
    header('Location: ../student/fees_payment/index.php');
}
$paid = true;

$colname_stud = "-1";
if (isset($_SESSION['stid'])) {
  $colname_stud = $_SESSION['stid'];
}

if (isset($_GET['stid'])) {
  $colname_stud = $_GET['stid'];
}


$query_stud = sprintf("SELECT s.stdid, s.level, s.disciplinary, s.fname, s.payment, s.lname, s.level, "
        . "s.progid, p.progname, d.deptname "
        . "FROM student s, programme p, department d "
        . "WHERE s.progid = p.progid "
        . "AND p.deptid = d.deptid "
        . "AND stdid = %s", GetSQLValueString($colname_stud, "text"));
$stud = mysql_query($query_stud, $tams) or die(mysql_error());
$row_stud = mysql_fetch_assoc($stud);
$totalRows_stud = mysql_num_rows($stud);

$query_rsdisp = sprintf("SELECT * FROM disciplinary WHERE stdid = %s", GetSQLValueString($colname_stud, "text"));
$rsdisp = mysql_query($query_rsdisp, $tams) or die(mysql_error());
$row_rsdisp = mysql_fetch_assoc($rsdisp);
$totalRows_rsdisp = mysql_num_rows($rsdisp);

$colname_ref = "-1";
if (isset($_GET['stid'])) {
  $colname_ref = $_GET['stid'];
}

$colname_regStatus = "-1";
if (isset($colname_stud)) {
  $colname_regStatus = $colname_stud;
}

$colname_regStatus1 = "-1";
if (isset($row_sess['sesid'])) {
  $colname_regStatus1 = $row_sess['sesid'];
}

if (isset($_GET['sid'])) {
  $colname_regStatus1 = $_GET['sid'];
}

$query_regStatus = sprintf("SELECT * FROM registration WHERE stdid = %s AND sesid = %s", 
                            GetSQLValueString($colname_regStatus, "text"), 
                            GetSQLValueString($colname_regStatus1, "int"));
$regStatus = mysql_query($query_regStatus, $tams) or die(mysql_error());
$row_regStatus = mysql_fetch_assoc($regStatus);
$totalRows_regStatus = mysql_num_rows($regStatus);

$colname_regsess = "-1";
if (isset($row_sess['sesid'])) {
  $colname_regsess = $row_sess['sesid'];
}
if (isset($_GET['sid'])) {
  $colname_regsess = $_GET['sid'];
}

$query_regsess = sprintf("SELECT s.* "
        . "FROM session s, registration r "
        . "WHERE r.sesid = s.sesid "
        . "AND r.status=%s AND r.stdid=%s "
        . "ORDER BY sesname DESC", 
                            GetSQLValueString("Registered", "text"), 
                            GetSQLValueString($colname_stud, "text"));
$regsess = mysql_query($query_regsess, $tams) or die(mysql_error());
$row_regsess = mysql_fetch_assoc($regsess);
$totalRows_regsess = mysql_num_rows($regsess);

$query_course = sprintf("SELECT r.csid, c.semester, c.csname, status, unit "
        . "FROM result r, course c "
        . "WHERE r.cleared = 'TRUE' "
        . "AND r.stdid = %s "
        . "AND c.csid = r.csid "
        . "AND r.sesid = %s "
        . "ORDER BY r.csid, c.semester ASC", 
                            GetSQLValueString($colname_regStatus, "text"), 
                            GetSQLValueString($colname_regsess, "int"));
$course = mysql_query($query_course, $tams) or die(mysql_error());
$row_course = mysql_fetch_assoc($course);
$totalRows_course = mysql_num_rows($course);

$query_crslist = sprintf("SELECT csid FROM result WHERE stdid = %s AND sesid = %s", 
                            GetSQLValueString($colname_regStatus, "text"), 
                            GetSQLValueString($colname_regsess, "int"));
$crslist = mysql_query($query_crslist, $tams) or die(mysql_error());
$row_crslist = mysql_fetch_assoc($crslist);
$totalRows_crslist = mysql_num_rows($crslist);

$query_studpop = sprintf("SELECT * FROM student_pop WHERE stdid = %s AND sesid = %s", 
                            GetSQLValueString($colname_regStatus, "text"), 
                            GetSQLValueString($colname_regsess, "int"));
$studpop = mysql_query($query_studpop, $tams) or die(mysql_error());
$row_studpop = mysql_fetch_assoc($studpop);
$totalRows_studpop = mysql_num_rows($studpop);

$regOpen = false;
if($row_sess['registration'] == 'FALSE') {
    $regOpen = true;
}

$sesReg = false;
$row_regStatus['status'];
if($row_regStatus['status'] == "Registered" )
	$sesReg = true;

$crsReg = false;

$studPop= false;
if($totalRows_studpop > 0)
    $studPop = true;

// Ensure student is properly registered
if($row_regStatus['course'] == "Registered" && $totalRows_crslist > 0) {
	$crsReg = true;
}else {
    $updateSQL = sprintf("UPDATE registration SET course = %s WHERE stdid=%s AND sesid=%s",
						   GetSQLValueString("Unregistered", "text"), 
                                                   GetSQLValueString($colname_regStatus, "text"), 
                                                   GetSQLValueString($colname_regsess, "int"));
    $Result = mysql_query($updateSQL, $tams) or die(mysql_error());
    $row_regStatus['course'] = 'Unregistered';
}

$crsAppr = false;
if(isset($row_regStatus['approved']) && $row_regStatus['approved'] == "TRUE" )
    $crsAppr = true;

$levels = [];    
for($idx = 1; $idx <= $row_stud['level']; $idx++) {
    array_push($levels, $idx);
}

/**
    Used on the registration view
*/
$query_suggestion = sprintf("SELECT csid, status, csname, unit "
                        . "FROM course c "
                        . "WHERE (deptid = %s "
                        . "OR catid IN(3,4,5,8)) "
                        . "AND level IN (%s) "
                        . "AND csid NOT IN ( SELECT csid "
                        . "FROM result "
                        . "WHERE stdid = %s "
                        . "AND sesid = %s)",
                        GetSQLValueString(getSessionValue('did'), "int"),
                        GetSQLValueString(" ", "defined", implode(',', $levels)),
                        GetSQLValueString($colname_stud, "text"),
                        GetSQLValueString($row_sess['sesid'], "int"));
$suggestion = mysql_query($query_suggestion, $tams) or die(mysql_error());
$row_suggestion = mysql_fetch_assoc($suggestion);
$totalRows_suggestion = mysql_num_rows($suggestion);

$initialSug = array();
for($idx = 0 ; $totalRows_suggestion > $idx; $idx++, $row_suggestion = mysql_fetch_assoc($suggestion)) {
    $row_suggestion['registered'] = false;
    $row_suggestion['selected'] = false;
    $initialSug[] = $row_suggestion;
}

$query_registered = sprintf("SELECT r.csid, status, csname, unit "
                        . "FROM course c, result r "
                        . "WHERE c.csid = r.csid "
                        . "AND stdid = %s "
                        . "AND sesid = %s",
                        GetSQLValueString($colname_stud, "text"),
                        GetSQLValueString($row_sess['sesid'], "int"));
$registered = mysql_query($query_registered, $tams) or die(mysql_error());
$row_registered = mysql_fetch_assoc($registered);
$totalRows_registered = mysql_num_rows($registered);

$totalRegistered = 0;
$registeredCourses = array();
for($idx = 0 ;  $idx < $totalRows_registered; $idx++, $row_registered = mysql_fetch_assoc($registered)) {    
    $totalRegistered += $row_registered['unit'];
    $row_registered['registered'] = true;
    $row_registered['selected'] = true;
    $registeredCourses[] = $row_registered;
}

$query_depts = sprintf("SELECT deptid, deptname "
                            . "FROM department");
$depts = mysql_query($query_depts, $tams) or die(mysql_error());
$row_depts = mysql_fetch_assoc($depts);
$totalRows_depts = mysql_num_rows($depts);

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );  
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" ng-app="tams">
    <!-- InstanceBegin template="/Templates/template.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php require('../param/site.php'); ?>
<title><?php echo $university ?> </title>
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/tams.js"></script>
<script type="text/javascript" src="../scripts/typeahead.bundle.min.js"></script>
<script type="text/javascript" src="../scripts/angular/angular.min.js"></script>
<script type="text/javascript" src="../scripts/angular/angular-typeahead.js"></script>
<!-- InstanceEndEditable -->
<link href="../css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
<link href="../css/menulink.css" rel="stylesheet" type="text/css" />
<link href="../css/footer.css" rel="stylesheet" type="text/css" />
<link href="../css/sidemenu.css" rel="stylesheet" type="text/css" />
<link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->
            Course Registration
                <?php if( isset($_GET['stid']) )echo " for ".$row_stud['lname'].", ".$row_stud['fname'];?>
            <!-- InstanceEndEditable -->
        </td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
    <?php 
        if($regOpen) {
            if($row_stud['disciplinary']== 'False') {
    ?>
      <?php if(!$sesReg){?>
      	<form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1"> 
        
          <tr>
              <td>Please register for the session to proceed with course registration!</td>
          </tr>
          <tr>
            <td align="center"><input type="submit" name="submit" id="submit" value="Register" /></td>
          </tr>
          <input name="stid" type="hidden" value="<?php echo $colname_stud?>" />
          <input name="lvl" type="hidden" value="<?php echo $row_stud['level']?>" />
          <input name="sid" type="hidden" value="<?php echo $row_sess['sesid']?>" />
          <input type="hidden" name="MM_insert" value="form1" />
        </form>
      <?php }elseif($sesReg && !$crsReg && $studPop){?>
      <tr>
        <td>
            
            <form action="<?php echo $editFormAction; ?>" ng-submit="submitAction($event)" 
                  method="post" name="form" ng-controller="CourseController">
                <table width="644">
                  <tr>
                    <td colspan="3" valign="top" style="color:red">
                    	Please check your Department Handbook for courses to register. 
                        Courses with course code ending with alphabet 'n' are mainly for 100 - 200 level students. 
                    </td>
                  </tr>
                  <tr>
                    <td colspan="3" valign="top">
                    	
                    </td>
                  </tr>
                    <tr>
                        <td colspan="3" valign="top">
                            Max Unit Allowed: <span id="max" ng-bind="data.max"></span><br/>
                            Min Unit Allowed: <span id="min" ng-bind="data.min"></span><br/>
                            Registered Units: <span id="reg" ng-bind="data.reg"></span><br/>
                            Remaining Units: <span id="rem" ng-bind="data.rem"></span><br/>
                        </td>
                    </tr>
                    
                  <tr>
                      <td colspan="3" valign="top">&nbsp;</td>
                  </tr>
                  <tr>
                      <td colspan="3" valign="top">
                          <div>
                              Enter Number of Courses to take for the SESSION: 
                              <input type="text" size="5" ng-model="data.fields"/> 
                              <button type="button" ng-click="addFields()">Add</button>
                          </div>
                      </td>
                  </tr>
                  <tr>
                      <td colspan="3" valign="top">&nbsp;</td>
                  </tr>
                  <tr>
                    <td colspan="3" valign="top">
                        
                        <table id="courses" border="0"  class="table table-striped table-condensed" width="100%" ng-cloak>
                            <tr ng-show="data.courses.length > 0">
                                <td colspan="4">Type the Course Code or Title in the Textfield(s) 
                                    below and select from the SUGGESTIONS. 
                                    Use the <span style="color: red; font-weight: bolder">
                                        <i class="fa fa-trash-o"></i>
                                    </span> button 
                                    to delete courses as appropriate. 
                                    After REGISTRATION, see your Course Adviser to ADD/DELETE courses.
                                </td>
                            </tr>
                            <tr ng-show="data.courses.length > 0">
                                <td colspan="4">&nbsp;</td>
                            </tr>
                            <tr ng-repeat="course in data.courses track by $index">
                                <td valign="center" ng-bind="course.csid">
                                </td>
                                <td valign="center" >
                                    <span ng-bind="course.csname" ng-show="course.selected"></span>
                                    <div ng-if="!course.selected" style="margin-bottom: 0">
                                        <input class="typeahead" size="70" type="text" value="" sf-typeahead 
                                               options="coursesOptions" datasets="coursesDataset" 
                                               ng-focus="setIndex($index)"

                                               placeholder="Enter course code or name"/>
                                    </div>
                                    <input type="hidden" value="{{course.csid}}" ng-focus="course.focused"
                                           ng-disabled="course.registered || !course.selected" name="courses[]"/>
                                </td>
                                <td valign="center" 
                                    ng-bind-template="{{course.unit}}{{course.status | first}}">
                                </td>
                                <td valign="center">
                                    <a style="color: red; font-style: normal; font-weight: bolder" 
                                       ng-click="removeField($index)" href=""><i class="fa fa-trash-o"></i></a>
                                </td>
                            </tr>
                        </table>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="3" align="center">&nbsp;</td>
                  </tr>
                  <tr>
                    <td colspan="3" align="center">
                        <input type="submit" name="submit" value="Register Courses" ng-disabled="data.min > data.reg || data.reg > data.max"/>
                    </td>
                  </tr>
                </table>
                
          <input name="stid" type="hidden" value="<?php echo $colname_stud?>" />
          <input name="sid" type="hidden" value="<?php echo $row_sess['sesid']?>" />
          <input name="deleted_entries[]" ng-repeat="d in data.deletedEntries track by $index" type="hidden" value="{{d}}" />
          <input type="hidden" name="MM_insert" value="form2" />
             </form>
		</td>
      </tr>
      <?php }elseif(($sesReg && $crsReg) || in_array(getAccess(),$acl)){?>      
      <tr>
      	<td>
       	  <table border="0" align="center">
              <tr>
                <td colspan="2" align="right">
                    <a href="editform.php" target="_new">Edit Form</a>
                    <a href="courseformpdf.php?stid=<?php echo $colname_stud;?>" target="_new">Print Form</a>
                    <select name="sesid" onchange="sesfilt(this)">
                        <?php
                        do {  
                        ?>
                              <option value="<?php echo $row_regsess['sesid']?>" 
                                  <?php if (!(strcmp($row_regsess['sesid'], $colname_regsess))) {
                                          echo "selected=\"selected\"";                                      
                                      } 
                                  ?>>
                                  <?php echo $row_regsess['sesname']?>
                              </option>
                                                  <?php
                        } while ($row_regsess = mysql_fetch_assoc($regsess));
                          $rows = mysql_num_rows($regsess);
                          if($rows > 0) {
                              mysql_data_seek($regsess, 0);
                              $row_regsess = mysql_fetch_assoc($regsess);
                          }
                        ?>
                    </select>
                </td>
              </tr> 
              <tr>
                  <td colspan="2" width="100" align="right">
                  </td>
              </tr>             
              <tr>
                <td colspan="2">
                <table width="680" border="0" id="ctable">
                  <tr>
                    <th width="100" align="center">COURSE CODE</th>
                    <th width="410" align="center">COURSE NAME</th>
                    <th width="80" align="center">STATUS</th>
                    <th width="30">UNIT</th>
                    <th width="70" align="center">SEMESTER</th>
                  </tr>
                  <?php 
                        $tunits = 0;
                        if ($totalRows_course > 0) { // Show if recordset not empty 
                  ?>
                  <?php
                        do { 
                  ?>
                  <tr>
                        <td><div align="center"><?php echo $row_course['csid']; ?></div></td>
                        <td><?php echo $row_course['csname']; ?></td>
                        <td><div align="center"><?php echo $row_course['status']; ?></div></td>
                        <td>
                            <div align="center">
                            <?php echo $row_course['unit'];$tunits += $row_course['unit'];?>
                            </div>
                        </td>
                        <td>
                            <div align="center">
                                <?php echo (strtolower($row_course['semester']) == "f")? "First": "Second" ;?>
                            </div>
                        </td>
                  </tr>
                  <?php } while ($row_course = mysql_fetch_assoc($course)); ?>
                    
                  <?php } // Show if recordset not empty ?>
                <tr>
                    <td colspan="3" align="right" >Total Units</td>
                      <td align="center"><?php echo $tunits;?></td>
                        <td></td>
                    </tr>
                </table></td>
              </tr>
              <tr>
                <td colspan="2">&nbsp;</td>
              </tr>
            </table>
        </td>
      </tr>
      <?php }else{
      ?>
        <tr>
            <td>Your course form is awaiting approval by your course adviser. Check back later!</td>
        </tr>
      <?php    
      }?>
    <?php   
          }else {
              echo "You are on a disciplinary action<strong>  {$row_rsdisp['status']}</strong> as at <strong> "
              . "{$row_rsdisp['login']} "
              . "</strong> Kindlly  contact the Registrar's Office for advice and necessary action" ;
          }
      }else {
          echo "Registration for this session {$row_sess['sesname']} is closed!";
      }
    ?>
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
    
<script>
    var module = angular.module('tams', ['siyfion.sfTypeahead']);
    
    module.filter('first', function() {
        return function(input) {
            // input will be the string we pass in
            if (input)
                return input.substr(0, 1);
        }
    });
    
    module.controller('CourseController', function($scope, $timeout, $interpolate) {
        $scope.$on('typeahead:selected', function(evt, elem, datum, dataset) {
            $scope.processSelection(elem, datum);
        });
        
        $scope.$on('typeahead:autocompleted', function(evt, elem, datum, dataset) {
            $scope.processSelection(elem, datum);
        });
         
        $scope.data = {
            "selectedIndex": null,
            "fields": 0,
            "disabled": true,
            "pending": 0,
            "deletedEntries": [],
            "max": <?php echo $row_sess['tnumax']?>,
            "min": <?php echo $row_sess['tnumin']?>,
            "rem": <?php echo $row_sess['tnumax'] - $totalRegistered?>,
            "reg": <?php echo $totalRegistered?>,
            "courses": <?php echo json_encode($registeredCourses);?>,
            "submit": false
        };
  
        // instantiate the bloodhound suggestion engine
        var courses = new Bloodhound({
            datumTokenizer: function(d) { 
                var keyName = Bloodhound.tokenizers.whitespace(d.csname);
                var keyCode = Bloodhound.tokenizers.whitespace(d.csid);
                return keyName.concat(keyCode); 
            },
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            remote: 'suggestions.php?l=<?php echo $row_stud['level'];?>&q=%QUERY',
            local: <?php echo json_encode($initialSug);?>,
            limit: 10,
            dupDetector: function(remote, local) {
                return remote.csid === local.csid;
            }
        });
        
        // initialize the bloodhound suggestion engine
        courses.initialize();

        var interpolateFn = $interpolate('<p class="selected-{{selected}}"><strong>{{csid}}</strong> – {{csname}}</p>');
        
        $scope.coursesDataset = {
            displayKey: 'csname',
            source: courses.ttAdapter(),
            templates: {
                empty: [
                  '<div>There is no Course that contains that Code or Title or you are not allowed to take that Course. \n\
                        Please try another Code or Title!',
                  '</div>'
                ].join('\n'),
                suggestion: interpolateFn
            }
        };

        $scope.clearValue = function() {
            $scope.selectedNumber = null;
        };
        
        $scope.addValue = function(datum) {
            courses.add(datum);
        };

        // Typeahead options object
        $scope.coursesOptions = {
            highlight: true
        };
  
        $scope.processSelection = function(elem, datum) {
            datum.selected = true;
            $scope.data.pending--;
            $scope.data.courses[$scope.data.selectedIndex] = datum;
            $scope.adjustCalc(parseInt(datum.unit), true);
            $timeout(function(){elem.remove();}, 10);
        };
        
        $scope.adjustCalc = function(unit, inc) {
            if(inc) {
                if(unit > $scope.data.rem) {
                    alert("You cannot register this course. Allowed units exceeded!");
                    return;
                }

                $scope.data.reg += unit;
                $scope.data.rem -= unit;
            }else {
                $scope.data.reg -= unit;
                $scope.data.rem += unit;
            }
            
        };
        
        $scope.addFields = function() {
            
            if($scope.data.fields > 0) {
                $scope.data.pending = $scope.data.fields;
                if($scope.data.fields > 1) {
                    for( ; $scope.data.fields !== 0; $scope.data.fields--) {
                        var emptyObj = {
                            "csid": "", 
                            "csname": "", 
                            "unit": null, 
                            "status": "", 
                            "registered": false, 
                            "selected": false, 
                            "removed": false,
                            "focused": false
                        };
                        
                        if($scope.data.fields === 1)
                            emptyObj.focused = true;
                        $scope.data.courses.unshift(emptyObj);
                    }
                }else {
                    var emptyObj = {
                        "csid": "", 
                        "csname": "", 
                        "unit": null, 
                        "status": "", 
                        "registered": false, 
                        "selected": false, 
                        "removed": false, 
                        "focused": true
                    };
                    $scope.data.courses.unshift(emptyObj);
                }
            }
            
            $scope.data.fields = 0; 
        };
        
        $scope.removeField = function(index) {
            if(confirm("Are you sure you want to remove this course?")) {
                var removed = $scope.data.courses.splice(index, 1);
                var removedEntry = removed[0];
                var unit = (removedEntry.unit === null) ? 0 : parseInt(removedEntry.unit);
                
                if(removedEntry.registered) {
                    removedEntry.registered = false;
                    $scope.data.deletedEntries.push(removedEntry.csid);
                    $scope.addValue(removedEntry);
                }
                
                if(!removedEntry.csid) {
                    $scope.data.pending--;
                }
                
                removedEntry.selected = false;
                $scope.adjustCalc(unit, false);
            }
        };
        
        $scope.setIndex = function(index) {
            $scope.data.selectedIndex = index;
        };
        
        $scope.submitAction = function(event) {
            if($scope.data.fields > 0){
                $scope.addFields();
                event.preventDefault();
            }
            
            if($scope.data.pending > 0) {
                if(!confirm('You have empty fields, do you want to submit your form?')) 
                    event.preventDefault();
            }
        };
    });

</script>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($stud);

mysql_free_result($sess);

mysql_free_result($course);
