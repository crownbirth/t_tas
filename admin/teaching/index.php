<?php require_once('../../Connections/tams.php'); ?>
<?php 
if (!isset($_SESSION)) {
  session_start();
}
require_once('../../param/param.php');
require_once('../../functions/function.php');


$sub = false;
$path = pathinfo( $_SERVER['SCRIPT_FILENAME']);

if( $path['dirname'] != $_SERVER['DOCUMENT_ROOT'].$site_root )
	$sub = true;
	

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );  
}

$MM_authorizedUsers = "1";
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

$MM_restrictGoTo = "../../index.php";
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

/*form action to submit form data to the teaching table*/

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ( isset($_POST['submit']) ) {
	
	$sid = $_POST['sesid'];
	$dpt = $_POST['deptid'];
	$deleteSQL = sprintf("DELETE FROM teaching WHERE deptid=%s AND sesid=%s AND csid NOT IN ( SELECT r.csid FROM result r, student s, programme p WHERE r.stdid = s.stdid AND p.progid = s.progid AND p.deptid=%s)",
                       GetSQLValueString($dpt, "int"),
                       GetSQLValueString($sid, "int"),
                       GetSQLValueString($dpt, "int"));

	mysql_select_db($database_tams, $tams);
	$Result1 = mysql_query($deleteSQL, $tams) or die(mysql_error());

	
	for( $i = 0; $i<count($_POST['course']); $i++){
		$lt1 = $_POST['clect'][$i];
		$lt2 = $_POST['alect'][$i];
		$crs = $_POST['course'][$i];
		$upld = ( isset($_POST['upld'][$i]) )? $_POST['upld'][$i]: "";
		$appr = ( isset($_POST['appr'][$i]) )? $_POST['appr'][$i]: "";
		
		
		$updateSQL = sprintf("UPDATE teaching SET lectid1=%s, lectid2=%s, upload=%s, approve=%s WHERE csid=%s AND deptid = %s AND sesid=%s",
                       GetSQLValueString($lt1, "text"),
                       GetSQLValueString($lt2, "text"),
                       GetSQLValueString($upld, "text"),
                       GetSQLValueString($appr, "text"),
                       GetSQLValueString($crs, "text"),
                       GetSQLValueString($dpt, "int"),
					   GetSQLValueString($sid, "int"));
										
		mysql_select_db($database_tams, $tams);
		$Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());
		$update_info = mysql_info($tams);
		list($f,$s,$t) = explode(":", $update_info);
					   
		if( strpos($s,"0") ){
		  $insertSQL = sprintf("INSERT INTO teaching (lectid1, lectid2, csid, deptid, sesid) VALUES (%s, %s, %s, %s, %s)",
							   GetSQLValueString($lt1, "text"),
							   GetSQLValueString($lt2, "text"),
							   GetSQLValueString($crs, "text"),
							   GetSQLValueString($dpt, "int"),
							   GetSQLValueString($sid, "int"));
							   					
			mysql_select_db($database_tams, $tams);
		  $Result1 = mysql_query($insertSQL, $tams) or die(mysql_error()); 
		}
	}
	
	
  $insertGoTo = "index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
 // header(sprintf("Location: %s", $insertGoTo));
}


/*query to build recordsets */

mysql_select_db($database_tams, $tams);
$query_sess = "SELECT * FROM `session` ORDER BY sesname DESC";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);
$cur = $row_sess['sesid'];


mysql_select_db($database_tams, $tams);
$query_col = "SELECT colid, coltitle FROM college";
$col = mysql_query($query_col, $tams) or die(mysql_error());
$row_col = mysql_fetch_assoc($col);
$totalRows_col = mysql_num_rows($col);
 
$colname_dept = "-1";
if( isset($row_col['colid'])){
	$colname_dept = $row_col['colid'];
}

if( isset($_GET['cid'])){
	$colname_dept = $_GET['cid'];
}

mysql_select_db($database_tams, $tams);
$query_dept = sprintf("SELECT deptid, deptname FROM department WHERE colid = %s ",GetSQLValueString($colname_dept, "int"));
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);

$colname_course = "-1";
if( isset($row_dept['deptid'])){
	$colname_course = $row_dept['deptid'];
}

if( isset($_GET['did'])){
	$colname_course = $_GET['did'];
}

mysql_select_db($database_tams, $tams);
$query_course = sprintf("SELECT c.csid, c.csname FROM course c WHERE c.deptid = %s",
						GetSQLValueString($colname_course, "int"));
$course = mysql_query($query_course, $tams) or die(mysql_error());
$row_course = mysql_fetch_assoc($course);
$totalRows_course = mysql_num_rows($course);


mysql_select_db($database_tams, $tams);
$query_lect = sprintf("SELECT lectid, fname, lname FROM lecturer WHERE deptid = %s", GetSQLValueString($colname_course, "int"));
$lect = mysql_query($query_lect, $tams) or die(mysql_error());
$row_lect = mysql_fetch_assoc($lect);
$totalRows_lect = mysql_num_rows($lect);
 
$colname_crslist = "-1";
if( isset($row_sess['sesid'])){
	$colname_crslist = $row_sess['sesid'];
}

if( isset($_GET['sid'])){
	$colname_crslist = $_GET['sid'];
}
mysql_select_db($database_tams, $tams);
$query_crslist = sprintf("SELECT t.csid, t.lectid1, t.lectid2, upload, approve FROM teaching t WHERE t.deptid = %s AND t.sesid = %s",
						GetSQLValueString($colname_course, "int"), 
						GetSQLValueString($colname_crslist, "int"));

$crslist = mysql_query($query_crslist, $tams) or die(mysql_error());
$row_crslist = mysql_fetch_assoc($crslist);
$totalRows_crslist = mysql_num_rows($crslist);


$checked = array();
do{
	$checked[] = $row_crslist['csid'];
	$checked[$row_crslist['csid']]['lect1'] = $row_crslist['lectid1'];
	$checked[$row_crslist['csid']]['lect2'] = $row_crslist['lectid2'];
	$checked[$row_crslist['csid']]['upld'] = $row_crslist['upload'];
	$checked[$row_crslist['csid']]['appr'] = $row_crslist['approve'];
}while( $row_crslist = mysql_fetch_assoc($crslist) );
//var_dump($checked); 

$option1 = array(); 
$count = 0;
do {  
	$i=0;
	$option1[$count][$i++] = $row_col['colid'];
	$option1[$count][$i] = $row_col['coltitle'];
	/*if(($option1[$count][0] == $_GET['cid']) || $option1[$count][0] == $_GET['cid'])
	$name = $option1[$count][1];*/
	$count++;
} while ($row_col = mysql_fetch_assoc($col));

$option = array(); 
$count = 0;
do {  
	$i=0;
	$option[$count][$i++] = $row_dept['deptid'];
	$option[$count][$i] = $row_dept['deptname'];
	/*if(($option[$count][0] == $_GET['deptid']) || $option[$count][0] == $_GET['deptid'])
	$name = $option[$count][1];*/
	$count++;
} while ($row_dept = mysql_fetch_assoc($dept));

$option2 = array(); 
$count = 0;
do {  
	$i=0;
	$option2[$count][$i++] = $row_lect['lectid'];
	$option2[$count][$i] = $row_lect['lname'].", ".substr($row_lect['fname'],0,1);
	$count++;
} while ($row_lect = mysql_fetch_assoc($lect));

?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/template.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<?php require('../../param/site.php'); ?>
<title><?php echo $university ?> </title>
<!-- InstanceEndEditable -->
<link href="../../css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<script src="../../SpryAssets/SpryCollapsiblePanel.js" type="text/javascript"></script>
<link href="../../SpryAssets/SpryCollapsiblePanel.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../../scripts/jquery.js"></script>
<script type="text/javascript" src="../../scripts/tams.js"></script>
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Course Allocation to Lecturers for <?php echo $row_sess['sesname']?> Session<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
      <tr>
        <td></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td>        
        	<table>
                <form>
                <tr>
                <td>
               <select name="colid" onChange="colfilt(this)">
                             <?php  	  
					 foreach( $option1 as $options){						
			?>
              <option value="<?php echo $options[0]?>" <?php if($options[0]==$colname_dept) echo "selected";?>>
			  	<?php echo $options[1]?>
              </option>
              <?php } ?>
                            </select>
              </td>
              <td>
               <select name="deptid" onChange="deptfilt(this)">
                              <?php  	  
					 foreach( $option as $options){						
			?>
              <option value="<?php echo $options[0]?>" <?php if($options[0]==$colname_course) echo "selected";?>>
			  	<?php echo $options[1]?>
              </option>
              <?php } ?>
              </select>
              
              <select name="sesid" onChange="sesfilt(this)">
             	<?php  	  
					 do{						
				?>
              <option value="<?php echo $row_sess['sesid']?>" <?php if($row_sess['sesid']==$colname_crslist) echo "selected";?>>
			  	<?php echo $row_sess['sesname'] ?>
              </option>
              <?php }while( $row_sess = mysql_fetch_assoc($sess) ); ?>
              </select>
              </td></tr>
              </form>
              </table>
        
        </td>
      </tr>
      <tr>
        <td>
        	<form name="assignform" action="<?php echo $editFormAction?>" method="post">
                <fieldset>
                    <legend>Departmental Courses</legend>
                    <div style="font-size:inherit">
                        <p style="float:left;">
                            Code
                        </p>
                        
                        <p style="float:right;">
                            Enable
                        </p>            
                        
                        <p style="float:right;">Assistant</p>
                        
                        <p style="float:right;">Convener</p>
                        
                        <p style="float:right; width:40%; text-align:left">
                            Course Title
                        </p>
                        <div style="clear:both;"></div>
                        
                    </div>
                    <?php if ($totalRows_course > 0) { // Show if recordset not empty  ?>
                    <?php do{
                        $conv = "";
                        $asst = "";
                        $check = "";
                        if( in_array($row_course['csid'],$checked)){
                            $conv = $checked[$row_course['csid']]['lect1'];
                            $asst = $checked[$row_course['csid']]['lect2'];
							$upld = $checked[$row_course['csid']]['upld'];
							$appr = $checked[$row_course['csid']]['appr'];
                            $check = true;
                        }
                    ?>
                    <div style="font-size:inherit">
                        <p style="float:left;"><?php echo $row_course['csid']?>
                        </p>
                        
                        <p style="float:right;">
                            <input type="checkbox" class="cbox" name="course[]" value="<?php echo $row_course['csid']?>" <?php if( $check ) echo "checked"?> <?php if( $colname_crslist != $cur )echo "disabled";?>/>
                        </p>            
                        
                        <p style="float:right;">
                            <select name="alect[]" style="width:70px" <?php if( $colname_crslist != $cur )echo "disabled";?>>
                            	<option value="">---</option>
                            <?php foreach( $option2 as $options){ ?>
                              <option value="<?php echo $options[0]?>" <?php if (!(strcmp($options[0], $asst))) {echo "selected=\"selected\"";} ?>><?php echo $options[1]?></option>
                              <?php }?>
                            </select>
                        </p>
                        
                        <p style="float:right;">
                            <select name="clect[]" style="width:70px" <?php if( $colname_crslist != $cur )echo "disabled";?>>                              
                              <?php foreach( $option2 as $options){ ?>
                              <option value="<?php echo $options[0]?>" <?php if (!(strcmp($options[0], $conv))) {echo "selected=\"selected\"";} ?>><?php echo $options[1]?></option>
                              <?php }?>
                            </select>
                        </p>
                        
                        <p style="float:right; width:45%;">
                            <?php echo ucwords(strtolower($row_course['csname']))?>
                        </p>
                        <div style="clear:both;"></div>
                        <?php if( $check ){?>
                        	<input type="hidden" name="upld[]" value="<?php echo $upld;?>" />
                            <input type="hidden" name="appr[]" value="<?php echo $appr;?>" />
                        <?php }?>
                    </div>
                    
                    <?php }while( $row_course = mysql_fetch_assoc($course) );?>
                    <?php }?>
                </fieldset>
                <?php if( $colname_crslist == $cur ){?>
                <p style="padding:0 230px">
                	<input type="submit" name="submit" value="Allocate Courses to Lecturers" />
                </p>
				<?php }?>
                <input type="hidden" name="sesid" value="<?php echo $colname_crslist?>" />
                <input type="hidden" name="deptid" value="<?php echo $colname_course?>" />
                <input type="hidden" name="MM_insert" value="form1" />
            </form>
        </td>
      </tr>
      <tr>
        <td></td>
      </tr>
    </table>
    <script type="text/javascript">
    	$(function(){
			courseaallocate();			
		});
    </script>
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
<?php
mysql_free_result($sess);

mysql_free_result($dept);

mysql_free_result($course);

mysql_free_result($lect);

mysql_free_result($col);

mysql_free_result($crslist);
?>