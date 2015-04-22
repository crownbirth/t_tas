<?php require_once('../Connections/tams.php'); ?>
<?php 
if (!isset($_SESSION)) {
  session_start();
}
require_once('../param/param.php');
require_once('../functions/function.php');


$sub = false;
$path = pathinfo( $_SERVER['SCRIPT_FILENAME']);

if( $path['dirname'] != $_SERVER['DOCUMENT_ROOT'].$site_root )
	$sub = true;
	

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
	doLogout( $site_root );  
}

$MM_authorizedUsers = "4";
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

/*query to build recordsets */

mysql_select_db($database_tams, $tams);
$query_sess = "SELECT * FROM `session` ORDER BY sesname DESC";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

/*form action to submit form data to the teaching table*/

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ( isset($_POST['submit']) ) {
	
	//var_dump($_POST);
	
	if( isset($_POST['state']) )
	for( $i = 0; $i < count($_POST['state']); $i++){
		
		list($dpts, $crss) = explode(":", $_POST['state'][$i]);
		
		$deleteSQL = sprintf("DELETE FROM teaching WHERE deptid=%s AND sesid=%s AND csid=%s AND csid NOT IN ( SELECT DISTINCT r.csid FROM result r, student s WHERE r.stdid = s.stdid AND r.sesid=%s )",
                       GetSQLValueString($dpts, "int"),
                       GetSQLValueString($row_sess['sesid'], "int"),
                       GetSQLValueString($crss, "text"),
                       GetSQLValueString($row_sess['sesid'], "int"));

		mysql_select_db($database_tams, $tams);
		$Result1 = mysql_query($deleteSQL, $tams) or die(mysql_error());
	}
	
	if( isset($_POST['course']) )
	for( $i = 0; $i<count($_POST['course']); $i++){
		
		$lt1 = $_POST['lect'][$i];
		$lt2 = "";;
		$crs = $_POST['course'][$i];	
		$upld = ( isset($_POST['upld'][$i]) )? $_POST['upld'][$i]: "";
		$appr = ( isset($_POST['appr'][$i]) )? $_POST['appr'][$i]: "";
		$dpt = $_POST['dept'][$i];
		
		$deleteSQL = sprintf("DELETE FROM teaching WHERE deptid=%s AND sesid=%s AND csid=%s AND csid NOT IN ( SELECT DISTINCT r.csid FROM result r, student s WHERE r.stdid = s.stdid AND r.sesid=%s )",
                       GetSQLValueString($dpt, "int"),
                       GetSQLValueString($row_sess['sesid'], "int"),
                       GetSQLValueString($crs, "text"),
                       GetSQLValueString($row_sess['sesid'], "int"));

		mysql_select_db($database_tams, $tams);
		$Result1 = mysql_query($deleteSQL, $tams) or die(mysql_error());

		$updateSQL = sprintf("UPDATE teaching SET lectid1=%s, lectid2=%s, upload=%s, approve=%s WHERE csid=%s AND deptid = %s AND sesid=%s",
                       GetSQLValueString($lt1, "text"),
                       GetSQLValueString($lt2, "text"),
                       GetSQLValueString($upld, "text"),
                       GetSQLValueString($appr, "text"),
                       GetSQLValueString($crs, "text"),
                       GetSQLValueString($dpt, "int"),
					   GetSQLValueString($row_sess['sesid'], "int"));
		
		mysql_select_db($database_tams, $tams);
		$Result1 = mysql_query($updateSQL, $tams) or die(mysql_error());
		$update_info = mysql_info($tams);
		list($f,$s,$t) = explode(":", $update_info);
					   
		if( strpos($s,"0") ){
		  $insertSQL = sprintf("INSERT INTO teaching (lectid1, lectid2, csid, deptid, sesid, upload, approve) VALUES (%s, %s, %s, %s, %s, %s, %s)",
							   GetSQLValueString($lt1, "text"),
							   GetSQLValueString($lt2, "text"),
							   GetSQLValueString($crs, "text"),
							   GetSQLValueString($dpt, "int"),
							   GetSQLValueString($row_sess['sesid'], "int"),
							   GetSQLValueString("no", "text"),
							   GetSQLValueString("no", "text"));
							   
			mysql_select_db($database_tams, $tams);
		  $Result1 = mysql_query($insertSQL, $tams) or die(mysql_error()); 
		}
	}
}

mysql_select_db($database_tams, $tams);
$query_cat = sprintf("SELECT catid, catname FROM category WHERE type=1");
$cat = mysql_query($query_cat, $tams) or die(mysql_error());
$row_cat = mysql_fetch_assoc($cat);
$totalRows_cat = mysql_num_rows($cat);

mysql_select_db($database_tams, $tams);
$query_deptlist = sprintf("SELECT deptid, deptname FROM department");
$deptlist = mysql_query($query_deptlist, $tams) or die(mysql_error());
$row_deptlist = mysql_fetch_assoc($deptlist);
$totalRows_deptlist = mysql_num_rows($deptlist);


$colname_course = "-1";
if( isset($row_cat['catid'])){
	$colname_course = $row_cat['catid'];
}

if( isset($_GET['catid'])){
	$colname_course = $_GET['catid'];
}


mysql_select_db($database_tams, $tams);
$query_course = sprintf("SELECT c.csid, c.csname, c.deptid FROM course c WHERE c.catid = %s AND c.type='General'",
						GetSQLValueString($colname_course, "int"));
$course = mysql_query($query_course, $tams) or die(mysql_error());
$row_course = mysql_fetch_assoc($course);
$totalRows_course = mysql_num_rows($course);


mysql_select_db($database_tams, $tams);
$query_lect = sprintf("SELECT lectid, fname, lname, deptid FROM lecturer WHERE deptid IN ( SELECT c.deptid FROM course c WHERE c.catid = %s AND c.type='General' )", 
					GetSQLValueString($colname_course, "int"));
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
$query_crslist = sprintf("SELECT t.csid, t.lectid1, t.lectid2, upload, approve FROM teaching t, course c WHERE c.csid = t.csid AND c.type='General' AND t.sesid = %s",
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


$option = array(); 
$count = 0;
do {  
	$i=0;
	$option[$count][$i++] = $row_cat['catid'];
	$option[$count][$i] = $row_cat['catname'];
	$count++;
} while ($row_cat = mysql_fetch_assoc($cat));

$option2 = array(); 
$count = 0;
do {  
	$i=0;
	$option2[$count][$i++] = $row_lect['lectid'];
	$option2[$count][$i++] = $row_lect['lname'].", ".substr($row_lect['fname'],0,1);
	$option2[$count][$i++] = $row_lect['deptid'];
	$count++;
} while ($row_lect = mysql_fetch_assoc($lect));

$cur = ( $colname_crslist == $row_sess['sesid'] )? true: false;
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Course Allocation to Lecturers for <?php echo $row_sess['sesname']?> Session<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
      <tr>
        <td colspan="2"></td>
      </tr>
      <tr>
        <td colspan="2">&nbsp;</td>
      </tr>
      <tr>
        <td width="253">        
        	<table>
              <tr>
                <td>
                 <select name="cat" onChange="catfilt(this)">
                 	<?php  	  
						
                       foreach( $option as $options){						
              		?>
                    <option value="<?php echo $options[0]?>" <?php if($options[0]==$colname_course) echo "selected";?>>
                      <?php echo $options[1]?>
                    </option>
              		<?php }?>
                </select>
                </td>
              </tr>
              </table>
        
        </td>
        <td width="425">
        Session
        <select name="sesid" id="sesid"  onchange="sesfilt(this)">
            <?php
			do {  
			?>
            <option value="<?php echo $row_sess['sesid']?>"<?php if (!(strcmp($row_sess['sesid'], $colname_crslist))) {echo "selected=\"selected\"";} ?>><?php echo $row_sess['sesname']?></option>
            <?php
			} while ($row_sess = mysql_fetch_assoc($sess));
			  $rows = mysql_num_rows($sess);
			  if($rows > 0) {
				  mysql_data_seek($sess, 0);
				  $row_sess = mysql_fetch_assoc($sess);
			  }
			?>
          </select>
        </td>
      </tr>
      <tr>
        <td colspan="2">
        	<form name="assignform" action="<?php echo $editFormAction?>" method="post">
                <fieldset>
                    <legend>Departmental Courses</legend>
                    <div style="font-size:inherit">
                        <p style="float:left;">
                            Code
                        </p>
                        
                        <p style="float:left; width:49%; text-align:left;  padding-left:30px">
                            Course Title
                        </p>
                        
                        <p style="float:right;">
                            Enable
                        </p>            
                        
                        <p style="float:right; text-align:left;">Coordinator</p>
                        
                        
                        <div style="clear:both;"></div>
                        
                    </div>
                    <?php if ($totalRows_course > 0) { // Show if recordset not empty  ?>
                    <?php do{
                        $conv = "";
                        $asst = "";
                        $check = "";
						$upld = "no";
						$appr = "no";
                        if( in_array($row_course['csid'],$checked)){
                            $conv = $checked[$row_course['csid']]['lect1'];
							$upld = $checked[$row_course['csid']]['upld'];
							$appr = $checked[$row_course['csid']]['appr'];
                            $check = true;
                        }
                    ?>
                    <div style="font-size:inherit">
                        <p style="float:left;"><?php echo strtoupper($row_course['csid'])?>
                        </p>
                        
                        <p style="float:left; width:48%;">
                            <?php echo ucwords(strtolower($row_course['csname']))?>
                        </p>
                        
                        <p style="float:right;">
                            <input type="checkbox" class="cbox" name="course[]" value="<?php echo $row_course['csid']?>" <?php if( $check ) echo "checked"; if(!$cur) echo "disabled"?> />
                        </p>            
                        
                        <p style="float:right;">
                            <select name="lect[]" style="width:150px">
                            <?php foreach( $option2 as $options){ 
									if( $row_course['deptid'] == $options[2]){
							?>
                              <option value="<?php echo $options[0]?>" <?php if (!(strcmp($options[0], $conv))) {echo "selected=\"selected\"";} ?>><?php echo ucwords(strtolower($options[1]));?></option>
                              <?php }}
							  	$rows = mysql_num_rows($lect);
								if($rows > 0) {
									mysql_data_seek($lect, 0);
									$row_lect = mysql_fetch_assoc($lect);
								}
							  ?>
                            </select>
                        </p>              
                        
                        <input type="text"  style="display:none" name="dept[]" class="dept" value="<?php echo $row_course['deptid']?>" <?php if(!$check)echo "disabled";?>/>
                        <input type="text"  style="display:none" name="state[]" value="<?php echo $row_course['deptid'].":".$row_course['csid'];?>" <?php if($check)echo "disabled";?>/>
                        <div style="clear:both;"></div>
                        <?php if( $check ){?>
                        	<input type="hidden" name="upld[]" value="<?php echo $upld;?>" />
                            <input type="hidden" name="appr[]" value="<?php echo $appr;?>" />
                        <?php }?>
                    </div>
                    
                    <?php }while( $row_course = mysql_fetch_assoc($course) );?>
                    <?php }?>
                </fieldset>
                <?php if( $cur ){?>
                <p style="padding:0 230px"><input type="submit" name="submit" value="Allocate Courses to Lecturers" /></p>
                <?php }?>
                <input type="hidden" name="MM_insert" value="form1" />
            </form>
        </td>
      </tr>
      <tr>
        <td colspan="2"></td>
      </tr>
    </table>
    <script type="text/javascript">
    	$(function(){
			courseaallocate();	
			
			$('input[name=course[]]').each(function() {			
				var cur = $(this);
				var state = <?php echo ($cur)? "false": "true"; ?>;
				if( state )
					cur.attr('disabled','disabled');				
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
<?php
mysql_free_result($sess);

mysql_free_result($cat);

mysql_free_result($course);

mysql_free_result($lect);

mysql_free_result($crslist);

mysql_free_result($deptlist);
?>