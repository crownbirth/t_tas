<?php 
require_once('../../Connections/tams.php');
if (!isset($_SESSION)) {
  session_start();
}
require_once('../../param/param.php');
require_once('../../functions/function.php');

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

$session = (isset($_POST['session']))? $_POST['session']: '';
$level = (isset($_POST['level']))? $_POST['level']: '';
$semester = (isset($_POST['semester']))? $_POST['semester']: '';
$prog = (isset($_POST['prog']))? $_POST['prog']: '';

//if(!isset($session) ||)

mysql_select_db($database_tams, $tams);
$query_list = sprintf("SELECT * FROM student WHERE progid=%s AND level=%s",
						 GetSQLValueString($prog, "int"),
						 GetSQLValueString($level, "int"));
$list = mysql_query($query_list, $tams) or die(mysql_error());
$row_list = mysql_fetch_assoc($list);
$totalRows_list = mysql_num_rows($list);
$studlist = array();
for($i = 0; $i < $totalRows_list; $i++) {
	$studid = $row_list['stdid'];
	$stud['matric'] = $studid;
	$stud['name'] = $row_list['fname'].' '.$row_list['lname'];
	$cgpa = getCgpa($studid, $prog, $session, $tams);
	$stud['prev'] = $cgpa['prev'];
	$stud['cur'] = $cgpa['cur'];
	$stud['cum'] = $cgpa['cum'];
	$stud['ref'] = getRef($studid, $tams);
	$studlist[] = $stud;
	$row_list = mysql_fetch_assoc($list);
}

function gradepoint( $unit, $score  ){
	
	$gp;
	if( $score < 40)
		$gp = 0	;
	else if ( $score < 45)	
		$gp = 1;
	else if ( $score < 50)	
		$gp = 2;
	else if ( $score < 60)	
		$gp = 3;
	else if ( $score < 70)	
		$gp = 4;
	else if ( $score > 69)	
		$gp = 5;
		
	return $gp*$unit;
	}


function getCgpa($studid, $progid, $ses, $tams) {
	$prev = '';
	$cur = '';
	$cum = '';
	
	$query_cgpa = sprintf("SELECT r.csid, c.semester, r.tscore+ r.escore as score, r.sesid, sesname, status, unit FROM `result` r, course c, session s, department_course dc, teaching t WHERE stdid = %s AND c.csid = r.csid AND dc.csid = c.csid AND dc.csid = r.csid AND r.csid = t.csid AND s.sesid = t.sesid AND t.approve = 'yes' AND s.sesid = r.sesid AND dc.progid=%s AND r.sesid<=%s", 
					GetSQLValueString($studid, "text"), 
					GetSQLValueString($progid, "int"), 
					GetSQLValueString($ses, "int"));
					
	$cgpa = mysql_query($query_cgpa, $tams) or die(mysql_error());
	$row_cgpa = mysql_fetch_assoc($cgpa);
	$totalRows_cgpa = mysql_num_rows($cgpa);

	$curValue = 0;
	$prevValue = 0;
	$curUnit = 0;
	$prevUnit= 0;
	for($i = 0; $i < $totalRows_cgpa; $i++) {
		if($row_cgpa['sesid'] == $ses) {
			$curValue += gradepoint($row_cgpa['unit'], $row_cgpa['score']);
			$curUnit +=  $row_cgpa['unit'];
		}else {
			$prevValue += gradepoint($row_cgpa['unit'], $row_cgpa['score']);
			$prevUnit +=  $row_cgpa['unit'];
		}		
		$row_cgpa = mysql_fetch_assoc($cgpa);	
	}
	
	if($curUnit == 0)
		$curUnit == 1;
	if($prevUnit == 0)
		$prevUnit == 1;
	$prev = @number_format(round(($prevValue)/($prevUnit), 2), 2);
	$cur = @number_format(round(($curValue)/($curUnit), 2), 2);
	
	$cum = $prev + $cur;
	
	return array('prev' => $prev,
	 			 'cur'  => $cur,
				 'cum'  => $cum);
}

function getRef($studid, $tams) {
	
	$query_rsrefs = sprintf("SELECT DISTINCT r.csid FROM `result` r, department_course d, student s WHERE d.csid = r.csid AND r.stdid = s.stdid AND d.progid = s.progid AND tscore + escore < 40 AND r.stdid = %s AND r.csid NOT IN ( SELECT csid FROM result WHERE stdid = %s AND tscore + escore > 39)", 
											GetSQLValueString($studid, "text"),
											GetSQLValueString($studid, "text"));
	$rsrefs = mysql_query($query_rsrefs, $tams) or die(mysql_error());
	$row_rsrefs = mysql_fetch_assoc($rsrefs);
	$totalRows_rsrefs = mysql_num_rows($rsrefs);
	$refs = array();
	for($i = 0; $i < $totalRows_rsrefs; $i++) {
		$refs[] = $row_rsrefs['csid'];
		$row_rsrefs = mysql_fetch_assoc($rsrefs);	
	}
	
	return implode(', ', $refs);
}

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );  
}




?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/template.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php require('../../param/site.php'); ?>
<title><?php echo $university ?> </title>
<script type="text/javascript" src="../../scripts/jquery.js"></script>
<script type="text/javascript" src="../../scripts/tams.js"></script>
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
<?php include '../../include/topmenu.php'; ?>
  </div>
  <!-- end .topmenu --> 
  
  <div class="loginuser">
  <?php include '../../include/loginuser.php'; ?>
  
  <!-- end .loginuser --></div>
  <div class="pagetitle">
    <table width="600">
      <tr>
        <td><!-- InstanceBeginEditable name="pagetitle" -->Broadsheet <!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690" align="center" cellspacing="5">
       
      <tr>
        <th width="94">Matric</th>   
        <th width="160">Name</th>  
        <th width="62">Previous</th>  
        <th width="73">Current</th>   
        <th width="83">Cummulative</th> 
        <th width="169">Reference</th>  
      </tr>
      <?php 
	  	foreach($studlist as $std) {
	  ?>
      
      <tr>
        <td><a href="../../student/profile.php?stid=<?php echo $std['matric'];?>" target= "_blank "><?php echo $std['matric']?></a></td>   
        <td><?php echo $std['name']?></td>  
        <td><?php echo $std['prev']?></td>
        <td><?php echo $std['cur']?></td>   
        <td><?php echo $std['cum']?></td> 
        <td><?php echo $std['ref']?></td>  
      </tr>
      
      <?php 
	  	}	  
	  ?>
    </table>
    
    <p>&nbsp;</p>
    
   
  <!-- InstanceEndEditable --></div>
<div class="footer">
    <p><!-- end .footer -->   
    
    <?php require '../../include/footer.php'; ?>
	
   </p>
  </div>
  <!-- end .container -->
</div>
</body>
<!-- InstanceEnd --></html>
