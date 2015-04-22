<?php require_once('../Connections/tams.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "6";
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
if (!isset($_SESSION)) {
  session_start();
}
require_once('../param/param.php');
require_once('../functions/function.php');


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

if(isset($_POST['clear'])) {
    $updateSQL = sprintf("UPDATE result SET cleared=%s "
                            . "WHERE stdid=%s "
                            . "AND sesid=%s",
                       GetSQLValueString('FALSE', "text"),
                       GetSQLValueString($_POST['stid'], "text"),
                       GetSQLValueString($row_rssess['sesid'], "int"));

    mysql_select_db($database_tams, $tams);
    $Result = mysql_query($updateSQL, $tams) or die(mysql_error());
    
    foreach ($_POST['course'] as $value) {        
        
        $updateSQL = sprintf("UPDATE result SET cleared=%s "
                            . "WHERE csid=%s "
                            . "AND stdid=%s "
                            . "AND sesid=%s",
                       GetSQLValueString('TRUE', "text"),
                       GetSQLValueString($value, "text"),
                       GetSQLValueString($_POST['stid'], "text"),
                       GetSQLValueString($row_rssess['sesid'], "int"));

        mysql_select_db($database_tams, $tams);
        $Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());
        $update_info = mysql_info($tams);
        
        $updateSQL = sprintf("UPDATE registration SET approved=%s "
                            . "WHERE stdid=%s "
                            . "AND sesid=%s",
                       GetSQLValueString('TRUE', "text"),
                       GetSQLValueString($_POST['stid'], "text"),
                       GetSQLValueString($row_rssess['sesid'], "int"));

        mysql_select_db($database_tams, $tams);
        $Result2 = mysql_query($updateSQL, $tams) or die(mysql_error());
    }
}

$query_info = sprintf("SELECT * FROM `staff_adviser` WHERE lectid=%s AND sesid=%s", 
                        GetSQLValueString(getSessionValue('lid'), "text"), 
                        GetSQLValueString($row_rssess['sesid'], "int"));
$info = mysql_query($query_info, $tams) or die(mysql_error());
$row_info = mysql_fetch_assoc($info);
$totalRows_info = mysql_num_rows($info);

$query_studs = sprintf("SELECT s.stdid, fname, lname, s.progid "
                        . "FROM student s "
                        . "JOIN registration r ON s.stdid = r.stdid "
                        . "JOIN programme p ON p.progid = s.progid "
                        . "WHERE r.course = 'Registered' "
                        . "AND r.approved = 'FALSE' "
                        . "AND r.sesid = %s "
                        . "AND p.deptid = %s "
                        . "AND s.level = %s", 
                        GetSQLValueString($row_rssess['sesid'], "int"), 
                        GetSQLValueString(getSessionValue('did'), "int"), 
                        GetSQLValueString($row_info['level'], "int"));
$studs = mysql_query($query_studs, $tams) or die(mysql_error());
$row_studs = mysql_fetch_assoc($studs);
$totalRows_studs = mysql_num_rows($studs);

$query_pstuds = sprintf("SELECT s.stdid, fname, lname, s.progid "
                        . "FROM student s "
                        . "JOIN registration r ON s.stdid = r.stdid "
                        . "JOIN programme p ON p.progid = s.progid "
                        . "WHERE r.course = 'Registered' "
                        . "AND r.approved = 'TRUE' "
                        . "AND r.sesid = %s "
                        . "AND p.deptid = %s "
                        . "AND s.level = %s", 
                        GetSQLValueString($row_rssess['sesid'], "int"), 
                        GetSQLValueString(getSessionValue('did'), "int"),
                        GetSQLValueString($row_info['level'], "int"));
$pstuds = mysql_query($query_pstuds, $tams) or die(mysql_error());
$row_pstuds = mysql_fetch_assoc($pstuds);
$totalRows_pstuds = mysql_num_rows($pstuds);

if(isset($_GET['stid'])) {
    $query_chk = sprintf("SELECT * "
                        . "FROM student s "
                        . "JOIN registration r ON r.stdid = s.stdid "
                        . "WHERE s.stdid = %s "
                        . "AND r.sesid = %s "
                        . "AND r.approved = 'TRUE'", 
                        GetSQLValueString($_GET['stid'], "text"), 
                        GetSQLValueString($row_rssess['sesid'], "int"));
    $chk = mysql_query($query_chk, $tams) or die(mysql_error());
    $row_chk = mysql_fetch_assoc($chk);
    $totalRows_chk = mysql_num_rows($chk);
}

$default = 0;
$colname_stud = "-1";
if ( getAccess() < 7 && isset($_GET['stid']) ) {
    if($totalRows_chk > 0) {
        $colname_stud = $row_studs['stdid'];
        $colname_pstud = $_GET['stid'];
        $default = 1;
    }else {
        $colname_stud = $_GET['stid'];
        $colname_pstud = $row_pstuds['stdid'];
    }
}else {    
  $colname_stud = $row_studs['stdid'];
  $colname_pstud = $row_pstuds['stdid'];
}

$query_stud = sprintf("SELECT s.progid, colid, p.deptid, fname, lname, level "
                        . "FROM student s, programme p, department d "
                        . "WHERE s.progid = p.progid AND d.deptid = p.deptid AND stdid = %s", 
                        GetSQLValueString($colname_stud, "text"));
$stud = mysql_query($query_stud, $tams) or die(mysql_error());
$row_stud = mysql_fetch_assoc($stud);
$totalRows_stud = mysql_num_rows($stud);

$query_cour = sprintf("SELECT r.cleared, c.csid, c.csname, c.semester, c.unit, c.status "
                        . "FROM result r "
                        . "JOIN course c ON r.csid = c.csid "
                        . "WHERE r.sesid = %s " 
                        . "AND stdid = %s",                        
                        GetSQLValueString($row_rssess['sesid'], "int"),
                        GetSQLValueString($colname_stud, "text"));
$cour = mysql_query($query_cour, $tams) or die(mysql_error());
$row_cour = mysql_fetch_assoc($cour);
$totalRows_cour = mysql_num_rows($cour);

$query_pcour = sprintf("SELECT r.cleared, c.csid, c.csname, c.semester, c.unit, c.status "
                        . "FROM result r "
                        . "JOIN course c ON r.csid = c.csid "
                        . "WHERE r.sesid = %s "
                        . "AND stdid = %s",
                        GetSQLValueString($row_rssess['sesid'], "int"),
                        GetSQLValueString($colname_pstud, "text"));
$pcour = mysql_query($query_pcour, $tams) or die(mysql_error());
$row_pcour = mysql_fetch_assoc($pcour);
$totalRows_pcour = mysql_num_rows($pcour);

$utUnits = 0;
$puUnits = 0;

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );  
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <!-- InstanceBegin template="/Templates/template.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php require('../param/site.php'); ?>
<title><?php echo $university ?> </title>
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/tams.js"></script>
<script type="text/javascript" src="../SpryAssets/SpryTabbedPanels.js"></script>
<!-- InstanceEndEditable -->
<link href="../css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
<link href="../css/menulink.css" rel="stylesheet" type="text/css" />
<link href="../css/footer.css" rel="stylesheet" type="text/css" />
<link href="../css/sidemenu.css" rel="stylesheet" type="text/css" />
<link href="../SpryAssets/SpryTabbedPanels.css" rel="stylesheet" type="text/css" />
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
            Course Form Clearance<?php echo ' ('.$row_info['level'].'00 Level)';?><!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="679" border="0" class="mytext">
      <tr>
        <td>
            <form method="post" action="">
                <div id="TabbedPanels1" class="TabbedPanels">
                    <ul class="TabbedPanelsTabGroup">
                      <li class="TabbedPanelsTab" tabindex="0">Unprocessed</li>
                      <li class="TabbedPanelsTab" tabindex="1">Processed</li>
                    </ul>
                    <div class="TabbedPanelsContentGroup">
                        <div class="TabbedPanelsContent">
                            <?php if($totalRows_studs){?>
                                <table width="660">
                                    <tr>                            
                                        <td>
                                            <a href="editform.php?stid=<?php echo $colname_stud?>">Add/Delete Courses</a>
                                        </td>
                                        <td colspan="4" align="right">
                                          <select onChange="studfilt(this)" name="stdid">
                                            <?php
                                                do {  
                                            ?>
                                            <option value="<?php echo $row_studs['stdid']?>" 
                                                <?php if($colname_stud == $row_studs['stdid']) echo 'selected'?>>
                                                <?php echo ucwords(strtolower($row_studs['lname']." "
                                                        .$row_studs['fname']))." (".$row_studs['stdid'].")"?>
                                            </option>
                                            <?php
                                                } while ($row_studs = mysql_fetch_assoc($studs));
                                            ?>
                                          </select>
                                        </td>
                                       </tr>

                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Unit</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                    <?php for($i = 0; $i < $totalRows_cour; $i++){?>
                                    <tr>
                                        <td><?php echo $row_cour['csid']?></td>
                                        <td><?php echo $row_cour['csname']?></td>
                                        <td><?php echo $row_cour['unit']; $utUnits += $row_cour['unit'];?></td>
                                        <td><?php echo $row_cour['status']?></td>
                                        <td><span class="hide"><?php echo $row_cour['unit'];?></span>
                                            <input class="processed" type="checkbox" name="course[]" 
                                                   value="<?php echo $row_cour['csid']?>" 
                                                       <?php if($row_cour['cleared'] == 'TRUE')echo 'checked'?>/></td>
                                    </tr>
                                    <?php $row_cour = mysql_fetch_assoc($cour);}?>

                                    <tr>
                                          <td></td>
                                          <td align="right">Total</td>
                                          <td><span id="total"><?php echo $utUnits?></span></td>
                                          <td></td>
                                          <td></td>
                                      </tr>

                                    <tr>
                                        <td><input type="hidden" name="stid" value="<?php echo $colname_stud?>"</td>
                                        <td colspan="4" align="center"><input type="submit" name="clear" value="Clear"/></td>
                                    </tr>
                                </table>
                            <?php }else {?>
                            No unprocessed course form!
                            <?php }?>
                        </div>

                        <div class="TabbedPanelsContent">
                                <?php if($totalRows_pstuds){?>
                                  <table width="660">
                                      <tr>                            
                                          <td>
                                              <a href="editform.php?stid=<?php echo $colname_pstud?>">Add/Delete Courses</a>
                                          </td>
                                          <td colspan="4" align="right">
                                            <select onChange="studfilt(this)" name="stdid">
                                              <?php
                                                  do {  
                                              ?>
                                              <option value="<?php echo $row_pstuds['stdid']?>" 
                                                  <?php if($colname_pstud == $row_pstuds['stdid']) echo 'selected'?>>
                                                  <?php echo ucwords(strtolower($row_pstuds['fname']." "
                                                          .$row_pstuds['lname']))."(".$row_pstuds['stdid'].")"?>
                                              </option>
                                              <?php
                                                  } while ($row_pstuds = mysql_fetch_assoc($pstuds));
                                              ?>
                                            </select>
                                          </td>
                                         </tr>

                                      <tr>
                                          <th>Code</th>
                                          <th>Name</th>
                                          <th>Unit</th>
                                          <th>Status</th>
                                          <th></th>
                                      </tr>
                                      <?php for($i = 0; $i < $totalRows_pcour; $i++){?>
                                      <tr>
                                          <td><?php echo $row_pcour['csid']?></td>
                                          <td><?php echo $row_pcour['csname']?></td>
                                          <td><?php echo $row_pcour['unit']; $puUnits += $row_pcour['unit'];?></td>
                                          <td><?php echo $row_pcour['status']?></td>
                                          <td><span class="hide"><?php echo $row_pcour['unit'];?></span>
                                              <input type="checkbox" class="unprocessed" value="<?php echo $row_pcour['csid']?>" 
                                                  <?php if($row_pcour['cleared'] == 'TRUE')echo 'checked'?>/></td>
                                      </tr>
                                      <?php $row_pcour = mysql_fetch_assoc($pcour);}?>

                                      <tr>
                                          <td></td>
                                          <td align="right">Total</td>
                                          <td><span id="totalUnpro"><?php echo $puUnits?></span></td>
                                          <td></td>
                                          <td></td>
                                      </tr>
                                  </table>
                              <?php }else {?>
                              No processed course form!
                              <?php }?>
                          </div>
                        </div>
                    </div>
            </form>
        </td>
       </tr> 
    </table>
        
      <script>
          var TabbedPanel1 = new Spry.Widget.TabbedPanels("TabbedPanels1", {defaultTab: <?php echo $default?>});
          
          $(function() {
              $('.processed').change(function() {
                  var cur = $(this); 
                  var unit = parseInt(cur.prev().text());
                  var total = $('#total');
                  var totalUnit = parseInt(total.text());
                  if(cur.is(':checked')) {
                      total.text(totalUnit + unit);                      
                  }else {
                      total.text(totalUnit - unit);  
                  }
              });
              
              $('.unprocessed').change(function() {
                  var cur = $(this); 
                  var unit = parseInt(cur.prev().text());
                  var total = $('#totalUnpro');
                  var totalUnit = parseInt(total.text());
                  if(cur.is(':checked')) {
                      total.text(totalUnit + unit);                      
                  }else {
                      total.text(totalUnit - unit);  
                  }
              });
          });
      </script>                   
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