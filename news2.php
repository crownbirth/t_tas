<?php require_once('Connections/tams.php'); ?>
<?php define('UPLOAD_DIR','images/news/');?>
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

$currentPage = $_SERVER["PHP_SELF"];



$maxRows_rsNews = 3;
$pageNum_rsNews = 0;
if (isset($_GET['pageNum_rsNews'])) {
  $pageNum_rsNews = $_GET['pageNum_rsNews'];
}

// CONVERT(Total, SIGNED INTEGER)
mysql_select_db($database_tams, $tams);


$max_matric = array();

$query_rsNews = "SELECT progid, stdid  
FROM student WHERE progid = 35 AND ((level = 1 AND admode = 'UTME') OR (level = 2 AND admode = 'DE'))  order by stdid desc";
$rsNews = mysql_query($query_rsNews, $tams) or die(mysql_error());
$row_rsNews = mysql_fetch_assoc($rsNews);
echo $total = mysql_num_rows($rsNews);

$query_rsNews = "SELECT * "
        . "FROM schfee_transactions "
        . "WHERE matric_no = '20140203005' OR can_no = '45643196HE'" ;
$rsNews = mysql_query($query_rsNews, $tams) or die(mysql_error());
$row_rsNews = mysql_fetch_assoc($rsNews);
var_dump($row_rsNews);

$query_details = sprintf("SELECT p.progofferd, c.colcode, pr.progcode "
            . "FROM prospective p, programme pr, department d, college c "
            . "WHERE p.progofferd = pr.progid "
            . "AND pr.deptid = d.deptid "
            . "AND d.colid = c.colid "
            . "AND jambregid = %s ", GetSQLValueString($row_rsNews['can_no'], 'text'));
    
    $details = mysql_query($query_details, $tams) or die(mysql_error());
    $row_details = mysql_fetch_assoc($details);
var_dump($row_details);

//for($idx = 0; $row_rsNews; $row_rsNews = mysql_fetch_assoc($rsNews)) {
//    var_dump($row_rsNews);
//    $max_matric[$row_rsNews['progid']] = intval(substr($row_rsNews['stdid'], -3));
//    echo '<br/><br/>';
//}
//
//$query_rsNews = "SELECT * "
//        . "FROM schfee_transactions "
//        . "WHERE level = '1' "
//        . "AND date_time LIKE '%29/01/2015%' "
//        . "AND status = 'APPROVED' "
//        . "AND matric_no is null "
//        . "ORDER BY date_time ASC";
//$rsNews = mysql_query($query_rsNews, $tams) or die(mysql_error());
//$row_rsNews = mysql_fetch_assoc($rsNews);
//
//for($idx = 0; $row_rsNews; $row_rsNews = mysql_fetch_assoc($rsNews)) {
//    
////    mysql_query('START TRANSACTION;', $tams);
//    $query_details = sprintf("SELECT p.*, c.colcode, pr.progcode "
//            . "FROM prospective p, programme pr, department d, college c "
//            . "WHERE p.progofferd = pr.progid "
//            . "AND pr.deptid = d.deptid "
//            . "AND d.colid = c.colid "
//            . "AND jambregid = %s ", GetSQLValueString($row_rsNews['can_no'], 'text'));
//    
//    $details = mysql_query($query_details, $tams) or die(mysql_error());
//    $row_details = mysql_fetch_assoc($details);
//    
//    
//    $max_matric[$row_details['progofferd']] += 1;
//    $gen_matric = '2014'.$row_details['colcode'].$row_details['progcode'].str_pad($max_matric[$row_details['progofferd']], 3, '0', STR_PAD_LEFT);
//    
//    echo $query = sprintf("INSERT INTO student (stdid, lname, fname, mname, progid, phone, email, addr, sex, dob, sesid, level, admode, password, stid, jambregid) VALUES(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
//                                GetSQLValueString($gen_matric, "text"),
//                                GetSQLValueString($row_details['lname'], "text"),
//                                GetSQLValueString($row_details['fname'], "text"),
//                                GetSQLValueString($row_details['mname'], "text"),
//                                GetSQLValueString($row_details['progofferd'], "int"),
//                                GetSQLValueString($row_details['phone'], "text"),
//                                GetSQLValueString($row_details['email'], "text"),
//                                GetSQLValueString($row_details['address'], "text"),
//                                GetSQLValueString(substr($row_details['Sex'], 0, 1), "text"),
//                                GetSQLValueString($row_details['DoB'], "text"),
//                                GetSQLValueString(10, "int"),
//                                GetSQLValueString($row_rsNews['level'], "int"),
//                                GetSQLValueString($row_details['admtype'], "text"),
//                                GetSQLValueString(md5($row_details['lname']), "text"),
//                                GetSQLValueString($row_details['stid'], "int"),
//                                GetSQLValueString($row_rsNews['can_no'], "text"));    
////    $update1 = mysql_query($query, $tams);
////    
////    
////    $query_update = sprintf("UPDATE schfee_transactions SET matric_no = %s WHERE ordid=%s",
////                             GetSQLValueString($gen_matric, "text"),
////                             GetSQLValueString($row_rsNews['ordid'], "text"));
////    $update2 = mysql_query($query_update, $tams);
////    
////    if($update1 && $update2) {
////        mysql_query('COMMIT;', $tams);
////    }else {
////        mysql_query('ROLLBACK', $tams);
////    }
//    
//    
//    echo '<br/><br/><br/>';
//}
