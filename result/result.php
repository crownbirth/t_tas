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

$insert_row = 0;
$insert_error = array();
$uploadstat = "";
if( isset($_POST) ){ //database query for approve result
	
	$sesid = $_GET['sid'];
	$csid = $_GET['csid'];
	$deptid = $_GET['did'];
	
	if( isset($_POST['approve']) ){ //database query for approve result
		
		mysql_query("BEGIN");	
                
                $update_query = sprintf("UPDATE result SET approved=%s WHERE csid=%s AND sesid=%s",
                                           GetSQLValueString('TRUE', "text"),
                                           GetSQLValueString($csid, "text"),
                                           GetSQLValueString($sesid, "int"));

                $rsupdate = mysql_query($update_query);
                $update = ( mysql_affected_rows($tams) != -1 )? true: false;	  
                
                $updateSQL = sprintf("UPDATE teaching SET approve=%s WHERE deptid = %s AND csid = %s AND sesid = %s",
                                           GetSQLValueString("Yes", "text"),
                                           GetSQLValueString($deptid, "int"),
                                           GetSQLValueString($csid, "text"),
                                           GetSQLValueString($sesid, "int"));

                mysql_select_db($database_tams, $tams);
                $result = mysql_query($updateSQL, $tams) or die(mysql_error());
                $uploadstat = "Approval Successful!";
                
		if($result){
                    mysql_query("COMMIT", $tams);
		}else{	
                    mysql_query("ROLLBACK", $tams);
                }               
                
	}elseif(isset($_POST['disapprove'])){ 

                //database query for disapprove result		
		$updateSQL = sprintf("UPDATE result SET tscore=%s, escore=%s WHERE csid=%s AND sesid=%s",
						   GetSQLValueString("", "text"),
						   GetSQLValueString("", "text"),
						   GetSQLValueString($csid, "text"),
						   GetSQLValueString($sesid, "int"));

		mysql_select_db($database_tams, $tams);
		$Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());
		
		$updateSQL = sprintf("UPDATE teaching SET upload=%s WHERE deptid = %s AND csid = %s AND sesid = %s",
						   GetSQLValueString("No", "text"),
						   GetSQLValueString($deptid, "int"),
						   GetSQLValueString($csid, "text"),
						   GetSQLValueString($sesid, "int"));

		mysql_select_db($database_tams, $tams);
		$Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());
		
	}elseif( isset($_POST['save']) ){ //database query for result modification
		
		for($i = 0; $i<count($_POST['matric']); $i++ ){
			
			$stdid = $_POST['matric'][$i];
			$tscore = $_POST['tedit'][$i];
			$escore = $_POST['eedit'][$i];
			
                        $query_edit = sprintf("SELECT * FROM result WHERE stdid=%s AND csid=%s AND sesid=%s",
						   GetSQLValueString($stdid, "text"),
						   GetSQLValueString($csid, "text"),
						   GetSQLValueString($sesid, "int"));

			$edit = mysql_query($query_edit, $tams) or die(mysql_error());
                        $row_edit = mysql_fetch_assoc($edit);
                        $totalRows_edit = mysql_num_rows($edit);                        
                        
                        $insertSQL = sprintf("INSERT INTO result_log (new_test, new_exam, old_test, old_exam, date, stdid, csid, sesid, lectid) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)",
						   GetSQLValueString($tscore, "int"),
						   GetSQLValueString($escore, "int"),
                                                   GetSQLValueString($row_edit['tscore'], "int"),
						   GetSQLValueString($row_edit['escore'], "int"),
						   GetSQLValueString(date('Y-m-d H:i:s'), "date"),
						   GetSQLValueString($stdid, "text"),
						   GetSQLValueString($csid, "text"),
						   GetSQLValueString($sesid, "int"),
						   GetSQLValueString(getSessionValue('lid'), "text"));

			mysql_select_db($database_tams, $tams);
			$Result1 = mysql_query($insertSQL, $tams) or die(mysql_error());
                        
			$updateSQL = sprintf("UPDATE result SET tscore=%s, escore=%s, edited='TRUE' WHERE stdid = %s AND csid = %s AND sesid = %s",
						   GetSQLValueString($tscore, "int"),
						   GetSQLValueString($escore, "int"),
						   GetSQLValueString($stdid, "text"),
						   GetSQLValueString($csid, "text"),
						   GetSQLValueString($sesid, "int"));
			$Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());
                        
		}
	}
	
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

mysql_select_db($database_tams, $tams);
$query_sess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,1";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

$filter = "";
$colname_rslt = "-1";
if (isset($_GET['csid'])) {
  $colname_rslt = $_GET['csid'];
}

$colname1_rslt = "-1";
if (isset($row_sess['sesid'])) {
  $colname1_rslt = $row_sess['sesid'];
}

if (isset($_GET['sid'])) {
  $colname1_rslt = $_GET['sid'];
}

$colname2_rslt = "-1";
if (isset($_GET['did'])) {
	$colname2_rslt = $_GET['did'];
	$filter = "AND p.deptid =".$colname2_rslt;
}

mysql_select_db($database_tams, $tams);
$query_status = sprintf("SELECT colid, approve, upload, progname, type "
        . "FROM course c, teaching t, programme p, department d "
        . "WHERE d.deptid = p.deptid AND c.csid = t.csid AND t.deptid = p.deptid "
        . "AND t.deptid = %s AND sesid = %s AND t.csid = %s", 
							GetSQLValueString($colname2_rslt, "int"), 
							GetSQLValueString($colname1_rslt, "int"), 
							GetSQLValueString($colname_rslt, "text"));
$status = mysql_query($query_status, $tams) or die(mysql_error());
$row_status = mysql_fetch_assoc($status);
$totalRows_status = mysql_num_rows($status);

$approved = ( strtolower($row_status['approve']) == "yes" ) ? true: false;
$uploaded = ( strtolower($row_status['upload']) == "yes" ) ? true: false;
$name = $row_status['progname'];
$name .= ( isset($_GET['csid']) ) ? " (".$_GET['csid'].")": "";
	
$query_rslt = sprintf("SELECT r.edited, r.csid, r.stdid, tscore, escore, fname, lname FROM result r, student s, programme p, teaching t WHERE r.stdid = s.stdid AND r.csid = t.csid AND r.sesid = t.sesid AND t.upload = 'yes' AND r.csid = %s AND r.sesid = %s AND s.progid = p.progid %s ORDER BY r.stdid ASC", 
                                                GetSQLValueString($colname_rslt, "text"), 
                                                GetSQLValueString($colname1_rslt, "int"), 
                                                GetSQLValueString($filter, "undefined", $filter));
$rslt = mysql_query($query_rslt, $tams) or die(mysql_error());
$row_rslt = mysql_fetch_assoc($rslt);
$totalRows_rslt = mysql_num_rows($rslt);

$query_dept = sprintf("SELECT d1.deptid, d1.deptname FROM department d1 INNER JOIN department d2 ON d1.colid = d2.colid WHERE d2.deptid = %s", 
							GetSQLValueString($colname2_rslt, "int"));
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);

$query_grad = sprintf("SELECT * FROM grading g, session s WHERE g.sesid = %s AND g.colid = %s",
                GetSQLValueString($colname1_rslt, "int"),
                GetSQLValueString($row_status['colid'], "int"));
$grad = mysql_query($query_grad, $tams) or die(mysql_error());
$row_grad = mysql_fetch_assoc($grad);
$totalRows_grad = mysql_num_rows($grad);

//var_dump($row_status);
if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );  
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/template.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php require('../param/site.php'); ?>
<title><?php echo $university ?> </title>
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../scripts/tams.js"></script>
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Consider Result for <?php echo $name?><!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
      <tr>
        <td colspan="2"><?php echo $uploadstat?></td>
      </tr>
      <tr>
        <td colspan="2" align="right">
			<?php if ($row_status['type'] != "Departmental") { // Show if recordset not empty ?>
              <select name="deptid" onchange="deptfilt(this)">
                <?php do{?>
                <option value="<?php echo $row_dept['deptid'] ?>" <?php if (!(strcmp($row_dept['deptid'], $colname2_rslt))) {echo "selected=\"selected\"";} ?>><?php echo $row_dept['deptname']?></option>
                <?php }while($row_dept = mysql_fetch_assoc($dept));?>
              </select>
            <?php } // Show if recordset not empty ?>
        </td>
      </tr>
      <tr>
        <td width="171">Total no. of Students:</td>
        <td width="508"><span id="total"><?php echo $totalRows_rslt?></span> (100%)</td>
      </tr>
      <tr>
        <td>No. Passed:</td>
        <td><span id="pass"></span></td>
      </tr>
      <tr>
        <td>No. Failed:</td>
        <td><span id="fail"></span></td>
      </tr>
      <tr>
        <td>Highest Score:</td>
        <td><span id="high"></span></td>
      </tr>
      <tr>
        <td>Lowest Score:</td>
        <td><span id="low"></span></td>
      </tr>
      <tr>
        <td colspan="2">&nbsp;</td>
      </tr>
      <form action="<?php echo $editFormAction;?>" method="post" name="form1">
      <tr>
        <td colspan="2">
            <table width="683" border="0" class="table table-striped" style="font-weight: normal">
            <tr>
                <td width="120"><strong>S/N</strong></td>
                  <td width="120"><strong>Matric</strong></td>
                  <td width="250"><strong>Name</strong></td>
                  <td width="69" align="center"><strong>CA</strong></td>
                  <td width="67" align="center"><strong>Exam</strong></td>
                  <td width="64" align="center"><strong>Total</strong></td>
                  <td width="66" align="center"><strong>Remark</strong></td>
                  <td width="17" align="center"></td>                  
                  <td width="17" align="center"><strong></strong></td>
              </tr>
              
            <?php if ($totalRows_rslt > 0) { $i = 0;// Show if recordset not empty  ?> 
            <?php do{?>                
                <tr>
                    <td class="matric" >
                        <?php echo $i+1;?>
                    </td>
                <td class="matric" >
                	<a href="../student/profile.php?stid=<?php echo $row_rslt['stdid']?>"><?php echo $row_rslt['stdid']?></a>
                    <input type="text" style=" display:none" name="matric[]" value="<?php echo $row_rslt['stdid']?>" disabled/>
                </td>
                <td><?php echo $row_rslt['lname'].", ".$row_rslt['fname']?></td>
                <td align="center" class="tscore">
                	<span><?php echo scoreValue($row_rslt['tscore']);?></span>
                    <input style=" display:none" type="text" name="tedit[]" value="<?php echo $row_rslt['tscore'];?>" size="1" maxlength="2" disabled/>
                </td>
                <td align="center" class="escore">
                	<span><?php echo scoreValue($row_rslt['escore']);?></span>
                    <input style=" display:none" type="text" name="eedit[]" value="<?php echo $row_rslt['escore'];?>" size="1" maxlength="2" disabled/>
                </td>
                <td align="center"><span class="totscore"><?php echo getScore( $row_rslt['tscore'], $row_rslt['escore'] );?></span></td>
                <td align="center" class="rem"><?php echo getRemark( getScore($row_rslt['tscore'], $row_rslt['escore']) );?></td>
                <td><?php echo ($row_rslt['edited'] == 'TRUE')? '<a target=\'_blank\' href=\'edithistory.php?stdid='.$row_rslt['stdid'].'&csid='.$colname_rslt.'&sid='.$colname1_rslt.'\'>Edited</a>':'';?></td>
                <td class="editdata"></td>
              </tr>
            <?php $i++;}while ($row_rslt = mysql_fetch_assoc($rslt));?>
			<?php }else{ ?>
              <tr>
              	<td colspan="7" align="center">Result not yet uploaded!</td>
              </tr>
            <?php } ?>
            </table>
		</td>
      </tr>
      <tr>
        <td colspan="2">
        </td>
      </tr>
      <tr>
        <td colspan="2" align="center">
        		<?php if( !$approved && $totalRows_rslt > 0 && $uploaded ){?>
                <?php if( getAccess() == 2 ){?>
                    <input name="approve" type="submit" value="Approve Result"/>
                <?php }?>
                    <input name="disapprove" type="submit" value="Disapprove Result"/>
                <?php }?>
                <?php if( $approved && getAccess() == 2 && $totalRows_rslt > 0 ){?>
                	<input id="editbutton" type="button" value="Edit" />
                	<input id="save" name="save" type="submit" value="Save Changes" disabled/>
                <?php }?>            
        </td>
      </tr>
      <input type="hidden" name="sesid" value="<?php echo $colname1_rslt?>" />
      </form>
    </table>
    <script type="text/javascript">
        var failValue = 39;
		$(function(){
			attach();	
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
<?php
mysql_free_result($rslt);

mysql_free_result($sess);

mysql_free_result($dept);
?>