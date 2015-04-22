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


mysql_select_db($database_tams, $tams);

$query_prog = sprintf("SELECT progid, progname "
                        . "FROM programme "
                        . "WHERE deptid = %s "
                        . "ORDER BY progname ASC", 
                        GetSQLValueString(getSessionValue('did'), 'int'));
$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);
$totalRows_prog = mysql_num_rows($prog);

$query_progstud = sprintf("SELECT p.progid, count(s.stdid) as `count` "
                            . "FROM student s "
                            . "JOIN programme p ON p.progid = s.progid "
                            . "WHERE p.deptid = %s "
                            . "GROUP BY p.progid "
                            . "ORDER BY p.progname ASC", 
                            GetSQLValueString(getSessionValue('did'), 'int'));
$progstud = mysql_query($query_progstud) or die(mysql_error());
$row_progstud = mysql_fetch_assoc($progstud);
$totalRows_progstud = mysql_num_rows($progstud);

$prog_stud = array();
for($idx = 0; $idx < $totalRows_progstud; $idx++, $row_progstud = mysql_fetch_assoc($progstud)) {
    $prog_stud[$row_progstud['progid']] = $row_progstud['count'];

}

$query_progreg = sprintf("SELECT p.progid, count( r.stdid ) AS `count`
                                FROM registration r
                                RIGHT JOIN student s ON r.stdid = s.stdid
                                JOIN programme p ON p.progid = s.progid 
                                WHERE p.deptid = %s 
                                AND r.status = 'Registered' 
                                GROUP BY p.progid 
                                ORDER BY p.progname", 
                            GetSQLValueString(getSessionValue('did'), 'int'));
$progreg = mysql_query($query_progreg) or die(mysql_error());
$row_progreg = mysql_fetch_assoc($progreg);
$totalRows_progreg = mysql_num_rows($progreg);

$prog_reg = array();
for($idx = 0; $idx < $totalRows_progreg; $idx++, $row_progreg = mysql_fetch_assoc($progreg)) {
	$prog_reg[$row_progreg['progid']] = $row_progreg['count'];

}

$query_progcleared = sprintf("SELECT p.progid, count(r.stdid) AS `count`
                                    FROM registration r
                                    RIGHT JOIN student s ON r.stdid = s.stdid
                                    JOIN programme p ON p.progid = s.progid 
                                    WHERE p.deptid = %s 
                                    AND r.approved = 'TRUE' 
                                    GROUP BY p.progid 
                                    ORDER BY p.progname", 
                            GetSQLValueString(getSessionValue('did'), 'int'));
$progcleared = mysql_query($query_progcleared) or die(mysql_error());
$row_progcleared = mysql_fetch_assoc($progcleared);
$totalRows_progcleared = mysql_num_rows($progcleared);

$prog_cleared = array();

for($idx = 0; $idx < $totalRows_progcleared; $idx++, $row_progcleared = mysql_fetch_assoc($progcleared)) {
	 $prog_cleared[$row_progcleared['progid']] = $row_progcleared['count'];	
	
}

$query_progpaid = sprintf("SELECT p.progid, p.progname, count(ph.stdid) AS `count`
                            FROM payhistory ph
                            RIGHT JOIN student s ON ph.stdid = s.stdid
                            JOIN programme p ON p.progid = s.progid
                            WHERE p.deptid = %s 
                            AND ph.status = 'paid' 
                            GROUP BY p.progid 
                            ORDER BY p.progname", 
                            GetSQLValueString(getSessionValue('did'), 'int'));
$progpaid = mysql_query($query_progpaid) or die(mysql_error());
$row_progpaid = mysql_fetch_assoc($progpaid);
$totalRows_progpaid = mysql_num_rows($progpaid);

$prog_paid = array();

for($idx = 0; $idx < $totalRows_progpaid; $idx++, $row_progpaid = mysql_fetch_assoc($progpaid)) {
	 $prog_paid[$row_progpaid['progid']] = $row_progpaid['count'];
		
}

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout($site_root);  
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
<link href="../css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<script src="../SpryAssets/SpryCollapsiblePanel.js" type="text/javascript"></script>
<link href="../SpryAssets/SpryCollapsiblePanel.css" rel="stylesheet" type="text/css" />
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Overview<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
      <tr>
        <td>
        <div id="CollapsiblePanel2" class="CollapsiblePanel">
          <div class="CollapsiblePanelTab" tabindex="0">Programme</div>
          <div class="CollapsiblePanelContent">
          <fieldset>
  <legend>Programme</legend>
          <p>&nbsp;</p>
          <table width="632" border="0" align="center" class="table table-condensed table-striped" >
          <thead>
            <tr>
              <th width="44" align="center" >S/n</th>
              <th width="220" align="center">Programme</th>
              <th width="50" align="center">Student </th>
              <th width="50" align="center">Registered</th>
              <th width="50" align="center">Cleared</th>
              <th width="50" align="center">Paid</th>
            </tr>
            </thead>
            <?php $i = 1;

                do { 

            ?>
          
              <tr align="center" >
                <td width="44"><?php echo $i;?></td>
                <td width="220"><?php echo $row_prog['progname']?></td>                
                <td width="50">
                    <a target="_blank" href="studentlist.php?pid=<?php echo $row_prog['progid']?>">
                        <?php echo isset($prog_stud[$row_prog['progid']])? $prog_stud[$row_prog['progid']]: 0?>
                    </a>
                </td>
                <td width="50">
                    <a target="_blank" href="studentlist.php?action=reg&pid=<?php echo $row_prog['progid']?>">
                        <?php echo isset($prog_reg[$row_prog['progid']])? $prog_reg[$row_prog['progid']]: 0; ?>
                    </a>
                </td>
                <td width="50">
                    <a target="_blank" href="studentlist.php?action=clear&pid=<?php echo $row_prog['progid']?>">
                        <?php echo isset($prog_cleared[$row_prog['progid']])? $prog_cleared[$row_prog['progid']]: 0?>
                    </a>
                </td>
                <td width="50">
                    <a target="_blank" href="studentlist.php?action=paid&pid=<?php echo $row_prog['progid']?>">
                        <?php echo isset($prog_paid[$row_prog['progid']])? $prog_paid[$row_prog['progid']]: 0?>
                    </a>
                </td>
              </tr>
              
              <?php 
			
                        $i++;
                        $row_progcleared = mysql_fetch_assoc($progcleared);
                        $row_progstud  = mysql_fetch_assoc($progstud);
                    } while ($row_prog = mysql_fetch_assoc($prog)); 
                ?>
            <tr>
              <th width="44" align="center"><strong>Total </strong></th>
              <th width="220" align="center"><?php echo $totalRows_prog?></th>
              <th width="50" align="center"><?php echo array_sum($prog_stud)?></th>
              <th width="50" align="center"><?php echo array_sum($prog_reg); ?></th>
              <th width="50" align="center"><?php echo array_sum($prog_cleared); ?></th>
              <th width="50" align="center"><?php echo array_sum($prog_paid); ?></th>
          </tr>
          </table>
          <p>&nbsp;</p>
      </fieldset>
          </div>
        </div></td>
      </tr>
    </table>
    <script type="text/javascript">
        var CollapsiblePanel2 = new Spry.Widget.CollapsiblePanel("CollapsiblePanel2",{contentIsOpen:false});
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

