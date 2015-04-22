<?php require_once('../Connections/tams.php'); 
 
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
$total = 0;
mysql_select_db($database_tams, $tams);

$query_sess = "SELECT * FROM `session` ORDER BY sesname DESC";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

$ses = $row_sess['sesid'];

if(isset($_GET['sid'])) {
    $ses = $_GET['sid'];
}

$query_RsCsReg = sprintf("SELECT d.deptid, d.deptname, count( r.stdid ) AS `count`
							FROM registration r
							RIGHT JOIN student s ON r.stdid = s.stdid
							JOIN programme p ON p.progid = s.progid
							JOIN department d ON d.deptid = p.deptid 							
							WHERE r.status = 'Registered' 
                                                        AND r.sesid = %s 
							GROUP BY p.deptid 
							ORDER BY d.deptname", 
                                                        GetSQLValueString($ses, 'int'));
$RsCsReg = mysql_query($query_RsCsReg) or die(mysql_error());
$row_RsCsReg = mysql_fetch_assoc($RsCsReg);
$totalRows_RsCsReg = mysql_num_rows($RsCsReg);

$reg = array();
for($idx = 0; $idx < $totalRows_RsCsReg; $idx++, $row_RsCsReg = mysql_fetch_assoc($RsCsReg)) {
	$reg[$row_RsCsReg['deptid']] = $row_RsCsReg['count'];

}
	
$query_RsCsRegAprv = sprintf("SELECT d.deptid, d.deptname, count( r.stdid ) AS `count`
							FROM registration r
							RIGHT JOIN student s ON r.stdid = s.stdid
							JOIN programme p ON p.progid = s.progid
							JOIN department d ON d.deptid = p.deptid 
							WHERE r.approved = 'TRUE'
                                                        AND r.sesid = %s 
							GROUP BY p.deptid 
							ORDER BY d.deptname", 
                                                        GetSQLValueString($ses, 'int'));
$RsCsRegAprv = mysql_query($query_RsCsRegAprv) or die(mysql_error());
$row_RsCsRegAprv = mysql_fetch_assoc($RsCsRegAprv);
$totalRows_RsCsRegAprv = mysql_num_rows($RsCsRegAprv);

$cleared = array();
$totalCleard = 0;
for($idx = 0; $idx < $totalRows_RsCsRegAprv; $idx++, $row_RsCsRegAprv = mysql_fetch_assoc($RsCsRegAprv)) {
    $cleared[$row_RsCsRegAprv['deptid']] = $row_RsCsRegAprv['count'];
    $totalCleard = ($totalCleard + $cleared[$row_RsCsRegAprv['deptid']]);
	
}
$query_RscolStaff = sprintf("SELECT c.colname, count(lectid) as `count` "
        . "FROM lecturer l, department d, college c "
        . "WHERE d.deptid = l.deptid "
        . "AND d.colid = c.colid "
        . "GROUP BY c.colid "
        . "ORDER BY c.colname ASC");
$RscolStaff = mysql_query($query_RscolStaff) or die(mysql_error());
$row_RscolStaff = mysql_fetch_assoc($RscolStaff);
$totalRows_RscolStaff = mysql_num_rows($RscolStaff);

$query_RsdeptStaff = sprintf("SELECT d.deptname, count(lectid) as `count` "
        . "FROM lecturer l, department d "
        . "WHERE d.deptid = l.deptid "
        . "GROUP BY l.deptid "
        . "ORDER BY d.deptname ASC");
$RsdeptStaff = mysql_query($query_RsdeptStaff) or die(mysql_error());
$row_RsdeptStaff = mysql_fetch_assoc($RsdeptStaff);
$totalRows_RsdeptStaff = mysql_num_rows($RsdeptStaff);
	
$query_RsdeptStd = sprintf("SELECT d.deptname, count(s.stdid) as `count` "
        . "FROM student s, department d, programme p, student_pop sp "
        . "WHERE s.progid = p.progid "
        . "AND d.deptid = p.deptid "
        . "AND sp.stdid = s.stdid "
        . "AND sp.sesid = %s "
        . "AND s.status = 'Undergrad' "
        . "GROUP BY p.deptid "
        . "ORDER BY d.deptname ASC",
        GetSQLValueString($ses, "int"));
$RsdeptStd = mysql_query($query_RsdeptStd) or die(mysql_error());
$row_RsdeptStd = mysql_fetch_assoc($RsdeptStd);
$totalRows_RsdeptStd = mysql_num_rows($RsdeptStd);


$query_RscolStd = sprintf("SELECT c.colname, count(s.stdid) as `count` "
        . "FROM student s, department d, programme p, college c, student_pop sp "
        . "WHERE s.progid = p.progid "
        . "AND d.deptid = p.deptid "
        . "AND sp.stdid = s.stdid "
        . "AND sp.sesid = %s "
        . "AND d.colid = c.colid "
        . "AND s.status = 'Undergrad' "
        . "GROUP BY c.colid "
        . "ORDER BY c.colname ASC",
        GetSQLValueString($ses, "int"));
$RscolStd = mysql_query($query_RscolStd) or die(mysql_error());
$row_RscolStd = mysql_fetch_assoc($RscolStd);
$totalRows_RscolStd = mysql_num_rows($RscolStd);
	

$query_Rsdept = "SELECT deptid, deptname FROM department ORDER BY deptname ASC";
$Rsdept = mysql_query($query_Rsdept, $tams) or die(mysql_error());
$row_Rsdept = mysql_fetch_assoc($Rsdept);
$totalRows_Rsdept = mysql_num_rows($Rsdept);


$query_Rscol = "SELECT colid, colname, coltitle FROM college ORDER BY colcode ASC";
$Rscol = mysql_query($query_Rscol, $tams) or die(mysql_error());
$row_Rscol = mysql_fetch_assoc($Rscol);
$totalRows_Rscol = mysql_num_rows($Rscol);



$query_RsColCsReg = sprintf("SELECT c.colid, c.colname,  count( r.stdid ) AS `count`
							FROM registration r
							RIGHT JOIN student s ON r.stdid = s.stdid
							JOIN programme p ON p.progid = s.progid
							JOIN department d ON d.deptid = p.deptid 
							JOIN college c ON c.colid = d.colid
							WHERE r.status = 'Registered' 
                                                        AND r.sesid = %s 
                                                        AND s.status = 'Undergrad' 
							GROUP BY c.colid 
							ORDER BY c.colname ASC", 
                                                        GetSQLValueString($ses, 'int'));
$RsColCsReg = mysql_query($query_RsColCsReg) or die(mysql_error());
$row_RsColCsReg = mysql_fetch_assoc($RsColCsReg);
$totalRows_RsColCsReg = mysql_num_rows($RsColCsReg);

$colreg = array();
for($idx = 0; $idx < $totalRows_RsColCsReg; $idx++, $row_RsColCsReg = mysql_fetch_assoc($RsColCsReg)) {
	$colreg[$row_RsColCsReg['colid']] = $row_RsColCsReg['count'];

}


$query_RsColCsRegAprv = sprintf("SELECT c.colid, d.deptid, d.deptname, count( r.stdid ) AS `count`
							FROM registration r
							RIGHT JOIN student s ON r.stdid = s.stdid
							JOIN programme p ON p.progid = s.progid
							JOIN department d ON d.deptid = p.deptid
							JOIN college c ON c.colid = d.colid
							WHERE r.approved = 'TRUE' 
                                                        AND r.sesid = %s 
                                                        AND s.status = 'Undergrad' 
							GROUP BY c.colid 
							ORDER BY c.colname ASC", 
                                                        GetSQLValueString($ses, 'int'));
$RsColCsRegAprv = mysql_query($query_RsColCsRegAprv) or die(mysql_error());
$row_RsColCsRegAprv = mysql_fetch_assoc($RsColCsRegAprv);
$totalRows_RsColCsRegAprv = mysql_num_rows($RsColCsRegAprv);

$colcleared = array();

for($idx = 0; $idx < $totalRows_RsColCsRegAprv; $idx++, $row_RsColCsRegAprv = mysql_fetch_assoc($RsColCsRegAprv)) {
	 $colcleared[$row_RsColCsRegAprv['colid']] = $row_RsColCsRegAprv['count'];	
	
}


$query_DeptPay = sprintf("SELECT d.deptid, d.deptname, count(distinct(st.matric_no)) AS `count`
							FROM schfee_transactions st
							RIGHT JOIN student s ON st.matric_no = s.stdid
							JOIN programme p ON p.progid = s.progid
							JOIN department d ON d.deptid = p.deptid
							JOIN college c ON c.colid = d.colid
							WHERE st.status = 'APPROVED' 
                                                        AND st.sesid = %s 
                                                        AND s.status = 'Undergrad' 
							GROUP BY d.deptid 
							ORDER BY d.deptname", 
                                                        GetSQLValueString($ses, 'int'));
$DeptPay = mysql_query($query_DeptPay) or die(mysql_error());
$row_DeptPay = mysql_fetch_assoc($DeptPay);
$totalRows_DeptPay = mysql_num_rows($DeptPay);

$deptpaid = array();

for($idx = 0; $idx < $totalRows_DeptPay; $idx++, $row_DeptPay = mysql_fetch_assoc($DeptPay)) {
	 $deptpaid[$row_DeptPay['deptid']] = $row_DeptPay['count'];
		
}

$query_ColPay = sprintf("SELECT c.colid, c.colname, count(distinct(st.matric_no)) AS `count`
                        FROM schfee_transactions st
                        RIGHT JOIN student s ON st.matric_no = s.stdid 
                        JOIN student_pop sp ON sp.stdid = s.stdid AND sp.stdid = st.matric_no 
                        JOIN programme p ON p.progid = s.progid
                        JOIN department d ON d.deptid = p.deptid
                        JOIN college c ON c.colid = d.colid
                        WHERE st.status = 'APPROVED' 
                        AND s.status = 'Undergrad' 
                        AND st.sesid = %s 
                        GROUP BY c.colid 
                        ORDER BY c.colname ASC", 
                        GetSQLValueString($ses, 'int'));
$ColPay = mysql_query($query_ColPay) or die(mysql_error());
$row_ColPay = mysql_fetch_assoc($ColPay);
$totalRows_ColPay = mysql_num_rows($ColPay);

$colpaid = array();


// Stats query
$query_statsColPaid = sprintf("SELECT c.colid, c.colname, count(distinct(st.matric_no)) AS `count`, st.level 
                                FROM schfee_transactions st 
                                JOIN student s ON st.matric_no = s.stdid 
                                JOIN student_pop sp ON sp.stdid = s.stdid AND sp.stdid = st.matric_no  
                                JOIN programme p ON p.progid = s.progid
                                JOIN department d ON d.deptid = p.deptid
                                JOIN college c ON c.colid = d.colid
                                WHERE st.status = 'APPROVED' 
                                AND st.sesid = %s 
                                AND s.status = 'Undergrad' 
                                GROUP BY c.colid, st.level
                                ORDER BY c.colname, st.level ASC", 
                                GetSQLValueString($ses, 'int'));
$statsColPaid = mysql_query($query_statsColPaid) or die(mysql_error());
$row_statsColPaid = mysql_fetch_assoc($statsColPaid);
$totalRows_statsColPaid = mysql_num_rows($statsColPaid);

$statCPaid = array();
$statCPaid['total'][1] = 0;
$statCPaid['total'][2] = 0;
$statCPaid['total'][3] = 0;
$statCPaid['total'][4] = 0;
for($idx = 0; $idx < $totalRows_statsColPaid; $idx++, $row_statsColPaid = mysql_fetch_assoc($statsColPaid)) {
    if($row_statsColPaid['level'] > 4){
        $statCPaid[$row_statsColPaid['colid']]['4'] += $row_statsColPaid['count'];
        $statCPaid['total']['4'] = isset($statCPaid['total']['4'])? 
                $statCPaid['total']['4'] + $row_statsColPaid['count']: $row_statsColPaid['count'];
    }else {
        $statCPaid[$row_statsColPaid['colid']][$row_statsColPaid['level']] = $row_statsColPaid['count'];
        $statCPaid['total'][$row_statsColPaid['level']] += $row_statsColPaid['count'];
    }
}

$query_statsColPop = sprintf("SELECT c.colid, c.coltitle, count(sp.stdid) AS `count`, sp.level 
                            FROM student_pop sp
                            RIGHT JOIN student s ON sp.stdid = s.stdid 
                            JOIN programme p ON p.progid = s.progid
                            JOIN department d ON d.deptid = p.deptid
                            JOIN college c ON c.colid = d.colid
                            WHERE sp.sesid = %s 
                            AND s.status = 'Undergrad' 
                            GROUP BY c.colid, sp.level
                            ORDER BY c.colname, sp.level ASC", 
                            GetSQLValueString($ses, 'int'));
$statsColPop = mysql_query($query_statsColPop) or die(mysql_error());
$row_statsColPop = mysql_fetch_assoc($statsColPop);
$totalRows_statsColPop = mysql_num_rows($statsColPop);

$statCPop = array();
$statCPop['total'][1] = 0;
$statCPop['total'][2] = 0;
$statCPop['total'][3] = 0;
$statCPop['total'][4] = 0;
for($idx = 0; $idx < $totalRows_statsColPop; $idx++, $row_statsColPop = mysql_fetch_assoc($statsColPop)) {
    
    if($row_statsColPop['level'] > 4){
        $statCPop[$row_statsColPop['colid']]['4'] = isset($statCPop[$row_statsColPop['colid']]['4'])? 
                $statCPop[$row_statsColPop['colid']]['4'] + $row_statsColPop['count']: 0;        
        $statCPop['total']['4'] +=  $row_statsColPop['count'];
    }else {
        
        $statCPop[$row_statsColPop['colid']][$row_statsColPop['level']] = $row_statsColPop['count'];        
        $statCPop['total'][$row_statsColPop['level']] += $row_statsColPop['count'];
    }
}

if($ses == 10) {

    $query_statsColPop100 = sprintf("SELECT c.colid, c.coltitle, count(distinct(at.can_no)) AS `count`  
                                    FROM accfee_transactions at
                                    RIGHT JOIN prospective ps ON at.can_no = ps.jambregid
                                    JOIN programme p ON p.progid = ps.progofferd
                                    JOIN department d ON d.deptid = p.deptid
                                    JOIN college c ON c.colid = d.colid 
                                    WHERE ps.admtype = 'UTME' AND at.status = 'APPROVED'
                                    GROUP BY c.colid
                                    ORDER BY c.colname ASC", 
                                    GetSQLValueString($ses, 'int'));
    $statsColPop100 = mysql_query($query_statsColPop100) or die(mysql_error());
    $row_statsColPop100 = mysql_fetch_assoc($statsColPop100);
    $totalRows_statsColPop100 = mysql_num_rows($statsColPop100);
    
    $query_statsColPop200 = sprintf("SELECT c.colid, c.coltitle, count(distinct(at.can_no)) AS `count`  
                                    FROM accfee_transactions at
                                    RIGHT JOIN prospective ps ON at.can_no = ps.jambregid
                                    JOIN programme p ON p.progid = ps.progofferd
                                    JOIN department d ON d.deptid = p.deptid
                                    JOIN college c ON c.colid = d.colid 
                                    WHERE ps.admtype = 'DE' AND at.status = 'APPROVED'
                                    GROUP BY c.colid
                                    ORDER BY c.colname ASC", 
                                    GetSQLValueString($ses, 'int'));
    $statsColPop200 = mysql_query($query_statsColPop200) or die(mysql_error());
    $row_statsColPop200 = mysql_fetch_assoc($statsColPop200);
    $totalRows_statsColPop200 = mysql_num_rows($statsColPop200); 
//    
    $statCPop['total']['1'] = 0;
    
    for($idx = 0; $idx < $totalRows_statsColPop100; $idx++, $row_statsColPop100 = mysql_fetch_assoc($statsColPop100)) {
        $statCPop[$row_statsColPop100['colid']][1] = $row_statsColPop100['count'];        
        $statCPop['total'][1] += $row_statsColPop100['count'];        
    }
    
    for($idx = 0; $idx < $totalRows_statsColPop200; $idx++, $row_statsColPop200 = mysql_fetch_assoc($statsColPop200)) {
        $statCPop[$row_statsColPop200['colid']][2] += $row_statsColPop200['count'];        
        $statCPop['total'][2] += $row_statsColPop200['count'];        
    }
}

for($idx = 0; $idx < $totalRows_ColPay; $idx++, $row_ColPay = mysql_fetch_assoc($ColPay)) {
    $colpaid[$row_ColPay['colid']] = $row_ColPay['count'];
		
}

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")) {
    doLogout( $site_root );  
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <!-- InstanceBegin template="/Templates/icttemplate.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php require('../param/site.php'); ?>
<title><?php echo $university ?> </title>
<!-- InstanceEndEditable -->
<link href="css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<script src="../scripts/jquery.js" type="text/javascript"></script>
<script src="../scripts/tams.js" type="text/javascript"></script>
<script src="../SpryAssets/SpryCollapsiblePanel.js" type="text/javascript"></script>
<link href="../SpryAssets/SpryCollapsiblePanel.css" rel="stylesheet" type="text/css" />
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->General University Statistics<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include 'include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
      <tr>
          <select name='ses' onchange="sesfilt(this)">
              <?php for(; $row_sess != false ; $row_sess = mysql_fetch_assoc($sess)){?>
              <option value="<?php echo $row_sess['sesid']?>" <?php if($ses == $row_sess['sesid']) echo 'selected'?>>
                  <?php echo $row_sess['sesname'];?>
              </option>
              <?php }?>
          </select>
      </tr>
      <tr>
        <td>
        <div id="CollapsiblePanel1" class="CollapsiblePanel" >
          <div class="CollapsiblePanelTab" tabindex="0">Statistics By Department</div>
          <div class="CollapsiblePanelContent">
          		<fieldset>
  <legend>Statistics By Department</legend>
          <p>&nbsp;</p>
          <table width="632" border="0" align="center" class="table table-condensed table-striped" >
          <thead>
            <tr>
              <th width="44" align="center" >S/n</th>
              <th width="220" align="center">Departments</th>
              <th width="50" align="center">Staff</th>
              <th width="50" align="center">Student </th>
              <th width="50" align="center">Registered</th>
              <th width="50" align="center">Cleared</th>
              <th width="50" align="center">Paid</th>
            </tr>
            </thead>
            <?php $i = 1;
			$totalStaff= 0;
			$totalStudent = 0;
			$totalReg = 0;
			$totalCleard = 0;
			do { 
			 
                             $totalStaff =($totalStaff + $row_RsdeptStaff['count']); 
                             $totalStudent = ($totalStudent + $row_RsdeptStd['count']);
                             $totalReg = ($totalReg + $row_RsCsReg['count']);

			?>
          
              <tr align="center" >
                <td width="44"><?php echo $i;?></td>
                <td width="220"><?php echo $row_Rsdept['deptname']; ?></td>
                <td width="50">
                    <a target="_blank" href="stafflist.php?did=<?php echo $row_Rsdept['deptid']?>">
                        <?php echo $row_RsdeptStaff['count']?>
                    </a>
                </td>
                <td width="50">
                    <a target="_blank" href="studentlist.php?did=<?php echo $row_Rsdept['deptid']?>&sid=<?php echo $ses?>">
                        <?php echo $row_RsdeptStd['count'] ?>
                    </a>
                </td>
                <td width="50">
                    <a target="_blank" 
                       href="studentlist.php?action=reg&did=<?php echo $row_Rsdept['deptid']?>&sid=<?php echo $ses?>">
                        <?php echo isset($reg[$row_Rsdept['deptid']])? $reg[$row_Rsdept['deptid']]: 0; ?>
                    </a>
                </td>
                <td  width="50">
                    <a target="_blank" 
                       href="studentlist.php?action=clear&did=<?php echo $row_Rsdept['deptid']?>&sid=<?php echo $ses?>">
                        <?php echo isset($cleared[$row_Rsdept['deptid']])? $cleared[$row_Rsdept['deptid']]: 0?>
                    </a>
                </td>
                <td  width="50">
                    <a target="_blank" 
                       href="studentlist.php?action=paid&did=<?php echo $row_Rsdept['deptid']?>&sid=<?php echo $ses?>">
                        <?php echo isset( $deptpaid[$row_Rsdept['deptid']])?  $deptpaid[$row_Rsdept['deptid']]: 0?>
                    </a>
                </td>
              </tr>
              
              <?php 
			
                        $i++;

                        $row_RsdeptStd = mysql_fetch_assoc($RsdeptStd);
                        $row_RsCsRegAprv = mysql_fetch_assoc($RsCsRegAprv);
                        $row_RsdeptStaff = mysql_fetch_assoc($RsdeptStaff); 
                } while ($row_Rsdept = mysql_fetch_assoc($Rsdept)); 
                ?>
            <tr>
              <th width="44" align="center"><strong>Total </strong></th>
              <th width="220" align="center"><?php echo $totalRows_Rsdept?></th>
              <th width="50" align="center"><?php echo $totalStaff?></th>
              <th width="50" align="center"><?php echo $totalStudent?></th>
              <th width="50" align="center"><?php echo array_sum($reg); ?></th>
              <th width="50" align="center"><?php echo array_sum($cleared) ?></th>
              <th width="50" align="center"><?php echo array_sum($deptpaid)?></th>
          </tr>
          </table>
          <p>&nbsp;</p>
      </fieldset>
       
          </div>
        </div>
        <p>&nbsp;</p><p>&nbsp;</p>
        <div id="CollapsiblePanel2" class="CollapsiblePanel">
          <div class="CollapsiblePanelTab" tabindex="0">Statistics By College</div>
          <div class="CollapsiblePanelContent">
          <fieldset>
  <legend>Statistics By College</legend>
          <p>&nbsp;</p>
          <table border="0" align="center" class="table table-condensed table-striped" >
          <thead>
            <tr>
              <th align="center">College</th>
              <th align="center">100L</th>
              <th align="center">200L</th>
              <th align="center">300L</th>
              <th align="center">400L</th>
            </tr>
            </thead>
            <?php 
		do { 	
            ?>
          
              <tr align="center" >
                <td><?php echo $row_Rscol['coltitle']?></td>
                <td>
                    <a href="studentlist.php?action=paid&cid=<?php echo $row_Rscol['colid']?>&sid=<?php echo $ses?>&lvl=1">
                        <?php echo isset($statCPaid[$row_Rscol['colid']][1])? $statCPaid[$row_Rscol['colid']][1]: 0; ?>
                    </a> | 
                    <a href="studentlist.php?cid=<?php echo $row_Rscol['colid']?>&sid=<?php echo $ses?>&lvl=1">
                        <?php echo $statCPop[$row_Rscol['colid']][1]?>
                    </a>
                </td>
                
                <td>
                    <a href="studentlist.php?action=paid&cid=<?php echo $row_Rscol['colid']?>&sid=<?php echo $ses?>&lvl=2">
                        <?php echo $statCPaid[$row_Rscol['colid']][2]?>
                    </a> | 
                    <a href="studentlist.php?cid=<?php echo $row_Rscol['colid']?>&sid=<?php echo $ses?>&lvl=2">
                        <?php echo isset($statCPop[$row_Rscol['colid']][2])? $statCPop[$row_Rscol['colid']][2]: 0;?>
                    </a>
                </td>
                
                <td>
                    <a href="studentlist.php?action=paid&cid=<?php echo $row_Rscol['colid']?>&sid=<?php echo $ses?>&lvl=3">
                        <?php echo $statCPaid[$row_Rscol['colid']][3]?>
                    </a> | 
                    <a href="studentlist.php?cid=<?php echo $row_Rscol['colid']?>&sid=<?php echo $ses?>&lvl=3">
                        <?php echo isset($statCPop[$row_Rscol['colid']][3])? $statCPop[$row_Rscol['colid']][3]: 0?>
                    </a>
                </td>
                
                <td>
                    <a href="studentlist.php?action=paid&cid=<?php echo $row_Rscol['colid']?>&sid=<?php echo $ses?>&lvl=4">
                        <?php echo $statCPaid[$row_Rscol['colid']][4]?>
                    </a> | 
                    <a href="studentlist.php?cid=<?php echo $row_Rscol['colid']?>&sid=<?php echo $ses?>&lvl=4">
                        <?php echo isset($statCPop[$row_Rscol['colid']][4])? $statCPop[$row_Rscol['colid']][4]: 0?>
                    </a>
                </td>
              </tr>
              
            <?php 
              
                } while ($row_Rscol = mysql_fetch_assoc($Rscol)); 
                $rows = mysql_num_rows($Rscol);
                if($rows > 0) {
                    mysql_data_seek($Rscol, 0);
                    $row_Rscol = mysql_fetch_assoc($Rscol);
                }
            ?>
            <tr>
              <th align="center"><strong>Total </strong></th>
              <th align="center"><?php echo "{$statCPaid['total'][1]} | {$statCPop['total'][1]}"?></th>
              <th align="center"><?php echo "{$statCPaid['total'][2]} | {$statCPop['total'][2]}"?></th>
              <th align="center"><?php echo "{$statCPaid['total'][3]} | {$statCPop['total'][3]}"?></th>
              <th align="center"><?php echo "{$statCPaid['total'][4]} | {$statCPop['total'][4]}"?></th>
          </tr>
          </table>
          <p>&nbsp;</p>
          <table width="632" border="0" align="center" class="table table-condensed table-striped" >
          <thead>
            <tr>
              <th width="44" align="center" >S/n</th>
              <th width="220" align="center">College</th>
              <th width="50" align="center">Staff</th>
              <th width="50" align="center">Student </th>
              <th width="50" align="center">Registered</th>
              <th width="50" align="center">Cleared</th>
              <th width="50" align="center">Paid</th>
            </tr>
            </thead>
             <?php $i = 1;
			$totalColStaff= 0;
			$totalColStudent = 0;
			
			do { 
			 
                             $totalColStaff =($totalColStaff + $row_RscolStaff['count']); 
                             $totalColStudent = ($totalColStudent + $row_RscolStd['count']);
				
			?>
          
              <tr align="center" >
                <td width="44"><?php echo $i;?></td>
                <td width="220"><?php echo $row_Rscol['colname']?></td>
                <td width="50">
                    <a target="_blank" href="stafflist.php?cid=<?php echo $row_Rscol['colid']?>">
                        <?php echo $row_RscolStaff['count']?>
                    </a>
                </td>
                <td width="50">
                    <a target="_blank" href="studentlist.php?cid=<?php echo $row_Rscol['colid']?>&sid=<?php echo $ses?>">
                        <?php echo $row_RscolStd['count']?>
                    </a>
                </td>
                <td width="50">
                    <a target="_blank" 
                       href="studentlist.php?action=reg&cid=<?php echo $row_Rscol['colid']?>&sid=<?php echo $ses?>">
                        <?php echo isset($colreg[$row_Rscol['colid']])? $colreg[$row_Rscol['colid']]: 0; ?>
                    </a>
                </td>
                <td width="50">
                    <a target="_blank" 
                       href="studentlist.php?action=clear&cid=<?php echo $row_Rscol['colid']?>&sid=<?php echo $ses?>">
                        <?php echo isset($colcleared[$row_Rscol['colid']])? $colcleared[$row_Rscol['colid']]: 0?>
                    </a>
                </td>
                <td width="50">
                    <a target="_blank" 
                       href="studentlist.php?action=paid&cid=<?php echo $row_Rscol['colid']?>&sid=<?php echo $ses?>">
                        <?php echo isset($colpaid[$row_Rscol['colid']])? $colpaid[$row_Rscol['colid']]: 0?>
                    </a>
                </td>
              </tr>
              
              <?php 
			
                        $i++;
                        $row_RsColCsRegAprv = mysql_fetch_assoc($RsColCsRegAprv);
                        $row_RscolStd  = mysql_fetch_assoc($RscolStd);
                        $row_RscolStaff  = mysql_fetch_assoc($RscolStaff); 
                    } while ($row_Rscol = mysql_fetch_assoc($Rscol)); 
                ?>
            <tr>
              <th width="44" align="center"><strong>Total </strong></th>
              <th width="220" align="center"><?php echo $totalRows_Rscol?></th>
              <th width="50" align="center"><?php echo $totalColStaff?></th>
              <th width="50" align="center"><?php echo  $totalColStudent?></th>
              <th width="50" align="center"><?php echo array_sum($colreg); ?></th>
              <th width="50" align="center"><?php echo array_sum($colcleared); ?></th>
              <th width="50" align="center"><?php echo array_sum($colpaid); ?></th>
          </tr>
          </table>
          <p>&nbsp;</p>
      </fieldset>
          </div>
        </div></td>
      </tr>
    </table>
    <script type="text/javascript">
        var CollapsiblePanel1 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel1", {contentIsOpen:false});
        var CollapsiblePanel2 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel2",{contentIsOpen:false});
    </script>
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
<?php
mysql_free_result($Rsdept);
?>
