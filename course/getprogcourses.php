<?php require_once('../Connections/tams.php');
require_once('../param/param.php');
require_once('../functions/function.php');

if (!isset($_SESSION)) {
  session_start();
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


$ret['status'] = 'error';
$ret['msg'] = 'There was a problem getting the courses for the department you selected!';

if(isset($_POST['new']) && isset($_POST['cur']) && isset($_POST['ref']) && 
        $_POST['new'] != '' && $_POST['cur'] != '' && $_POST['ref'] != '') {
    mysql_select_db($database_tams, $tams);
    
    if($_POST['ref'] == 0) {
        $query_crs = sprintf("SELECT csid, csname "
                . "FROM course "
                . "WHERE deptid = %s "
                . "AND csid "
                . "NOT IN (SELECT csid FROM department_course WHERE progid = %s) ",
                GetSQLValueString($_POST['new'], "int"),
                GetSQLValueString($_POST['cur'], "int"));
    }else {
        $query_crs = sprintf("SELECT c.csid, c.csname, d.status, d.unit "
                . "FROM course c, department_course d "
                . "WHERE c.deptid = d.deptid "
                . "AND d.deptid = %s ",
                GetSQLValueString($_POST['new'], "int"),
                GetSQLValueString($_POST['cur'], "int"));
    }
    $crs = mysql_query($query_crs, $tams) or die(mysql_error());
    $row_crs = mysql_fetch_assoc($crs);
    $totalRows_crs = mysql_num_rows($crs);
    
    $ret['status'] = 'success';    
    $ret['courses'] = $crslist = array();
    if($totalRows_crs > 0) {
        for($idx = 0; $idx < $totalRows_crs; $idx++, $row_crs = mysql_fetch_assoc($crs)) {
            $row_crs['csname'] = ucwords(strtolower($row_crs['csname']));
            $crslist[] = $row_crs;
        }
        
        $ret['msg'] = 'Courses retrieved successfully!';
        $ret['courses'] = $crslist;
    }else {        
        $ret['msg'] = 'The programme you selected does not have any course!';
    }
    
    mysql_free_result($crs);
}

echo json_encode($ret);