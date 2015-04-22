<?php 
require_once('../Connections/tams.php'); 

require_once('../param/param.php');
require_once('../functions/function.php');

require_once ('../phpexcel/PHPExcel/IOFactory.php');
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "2,3,4,5,6";
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

mysql_select_db($database_tams, $tams);

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$insert_row = 0;
$insert_error = array();
$uploadstat = "";
if( isset($_POST['submit']) && $_POST['submit'] == "Upload Result") { //database query to upload result	
		
    $sesid = $_POST['sesid'];
    $csid = $_POST['csid'];
    $dpt = (isset($_POST['deptid']))? $_POST['deptid']: getSessionValue('did');

    $type = ['text/comma-separated-values', 
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 
                'application/vnd.ms-excel'
        ];
    
    if(is_uploaded_file($_FILES['filename']['tmp_name']) && in_array($_FILES['filename']['type'], $type)) {
        
        /*
        // Update values we got from somewhere
        $update_values = Array(
          '1034786' => Array('column1' => 0, 'column2' => NULL, 'column3'=> 'Text One'),
          '1037099' => Array('column1' => 0, 'column2' => 1034789 , 'column3'=> 'Text Two'),
          '1034789' => Array('column1' => 3, 'column2' => 1034789 , 'column3'=> 'Text Three')
        );

        // Start of the query
        $update_query = "UPDATE `table` SET ";


        // Add a default case, here we are going to use whatever value was already in the field
        foreach($columns as $column_name => $query_part){
          $columns[$column_name] .= " ELSE `$column_name` END ";
        }

        // Build the WHERE part. Since we keyed our update_values off the database keys, this is pretty easy
        $where = " WHERE `id`='" . implode("' OR `id`='", array_keys($update_values)) . "'";

        // Join the statements with commas, then run the query
        $update_query .= implode(', ',$columns) . $where;
        mysql_query($update_query) or die(mysql_error());

         */
        
        //Query for select boxes in the result view	
        $query_exist = sprintf("SELECT r.resultid, r.stdid "
                                . "FROM result r "
                                . "WHERE r.sesid=%s "
                                . "AND r.csid=%s ",  
                                GetSQLValueString($sesid, "int"),  
                                GetSQLValueString($csid, "text"));
        $exist = mysql_query($query_exist, $tams) or die(mysql_error());
        $row_exist = mysql_fetch_assoc($exist);
        $totalRows_exist = mysql_num_rows($exist);
        
        $existing_courses = array();
        for($idx = 0; $idx < $totalRows_exist; $idx++, $row_exist = mysql_fetch_assoc($exist)) {
            $existing_courses[$row_exist['stdid']] = $row_exist;
        }
        
        $ids = array();
        $missing_entries = array();
        $update_columns = array('tscore' => '`tscore` = CASE ', 'escore' => '`escore` = CASE ');
        
        mysql_query("BEGIN", $tams);

        //Import uploaded file to Database	
        $objPHPExcel = PHPExcel_IOFactory::load($_FILES['filename']['tmp_name']);
        $objWorkSheet = $objPHPExcel->getActiveSheet();
        $objIterator = $objWorkSheet->getRowIterator();
        
        unset($objPHPExcel);
        unset($objWorkSheet);
        
        foreach($objIterator as $row) {
            
            $stdid = (string)$row->getColumnValue(0)->getValue();            
            $tscore = $row->getColumnValue(1)->getValue();
            $escore = $row->getColumnValue(2)->getValue();
            
            if(array_key_exists($stdid, $existing_courses)) {
                // Update entry for tscore
                $update_columns['tscore'] .= sprintf("WHEN `resultid` = %s THEN %s ",
                                                    GetSQLValueString($existing_courses[$stdid]['resultid'], "int"),
                                                    GetSQLValueString($tscore, "int"));
                
                // Update entry for escore
                $update_columns['escore'] .= sprintf("WHEN `resultid` = %s THEN %s ",
                                                    GetSQLValueString($existing_courses[$stdid]['resultid'], "int"),
                                                    GetSQLValueString($escore, "int"));
                $ids[] = $existing_courses[$stdid]['resultid'];
                $insert_row++;
            }else {
                $missing_entries[] = [$stdid, $tscore, $escore];
            }
        }
        
        unset($objIterator);
        
        if($insert_row > 0) {
            $update_columns['tscore'] .= 'END';
            $update_columns['escore'] .= 'END';
            $where = sprintf(" WHERE `resultid` IN (%s)",
                            GetSQLValueString("ids", "defined", implode(',', $ids)));
        
            $update_query = sprintf("UPDATE `result` SET %s %s",                       
                                        GetSQLValueString("update_columns", "defined", implode(',', $update_columns)),	
                                        GetSQLValueString($where, "defined", $where));

            $rsupdate = mysql_query($update_query, $tams);
        }
        
        if(!empty($missing_entries)) {
            $entry = array();
            foreach ($missing_entries as $data) {
                $data[3] = $csid;
                $data[4] = $sesid;
                $data[5] = getSessionValue('lid');
                $entry[] = "('".implode("','", $data)."')";
            }
            
            $entry = implode(',', $entry);
            $repl_query = sprintf("REPLACE INTO result_error (stdid, tscore, escore, csid, sesid, lectid) "
                                        . "VALUES %s;",	
                                        GetSQLValueString($entry, "defined", $entry));
            $rsrepl = mysql_query($repl_query, $tams);
            
            $uploadstat = "Unfortunately, the result file contained unregistered students! "
                    .count($missing_entries)
                    ." entries could not be uploaded <a href='result_error.php?sid={$sesid}&csid={$csid}'>"
                    . "Click here to view</a>.";
        }else {
            $uploadstat = "Upload Successful! ".$insert_row." results uploaded.";
        }
        
        $insert_query = sprintf("UPDATE teaching SET upload=%s WHERE csid=%s AND sesid=%s AND deptid=%s",
                                    GetSQLValueString("Yes", "text"),
                                    GetSQLValueString($csid, "text"),
                                    GetSQLValueString($sesid, "int"),
                                    GetSQLValueString($dpt, "int"));
        mysql_query($insert_query, $tams);
        
        mysql_query("COMMIT", $tams);
    }else {
        $uploadstat = "The file you specified is not of the accepted type! Please upload an Excel or CSV file.";
    }
}

$query_sess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,2";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

$colname_dept = "-1";
if( getSessionValue('cid') != NULL ) {
  $colname_dept = getSessionValue('cid');
}

$query_dept = sprintf("SELECT deptid, deptname FROM department WHERE colid = %s", GetSQLValueString($colname_dept, "int"));
$dept = mysql_query($query_dept, $tams) or die(mysql_error());
$row_dept = mysql_fetch_assoc($dept);
$totalRows_dept = mysql_num_rows($dept);

$colname_crs = "-1";
if (isset($row_dept['deptid'])) {
  $colname_crs = $row_dept['deptid'];
}

if (isset($_GET['did'])) {
  $colname_crs = $_GET['did'];
}

if ( getAccess() == 3 ) {
  $colname_crs = getSessionValue('did');
}

if ( getAccess()==4 || getAccess()==5 ) {
  $colname_crs = getSessionValue('did');
}

$filter = "";
$colname1_crs = "-1";
if ( getSessionValue('lid') != NULL ) {
  $colname1_crs = getSessionValue('lid');
  $filter = "AND lectid1=".GetSQLValueString($colname1_crs, 'text');
}

if ( getAccess() == 2 || getAccess() == 3 ) {
  $filter = "";
}

$colname2_crs = "-1";
if ( isset($row_sess['sesid']) ) {
  $colname2_crs = $row_sess['sesid'];
}

if ( isset($_GET['sid']) ) {
   $colname2_crs = $_GET['sid'];
}

$query_crs = sprintf("SELECT csid "
                        . "FROM teaching "
                        . "WHERE upload='No' "
                        . "AND sesid=%s "
                        . "AND deptid=%s %s "
                        . "ORDER BY csid ASC",
			GetSQLValueString($colname2_crs, "int"), 
			GetSQLValueString($colname_crs, "int"), 
			$filter);
$crs = mysql_query($query_crs, $tams) or die(mysql_error());
$row_crs = mysql_fetch_assoc($crs);
$totalRows_crs = mysql_num_rows($crs);


//Query for select boxes in the result view	
$query_ses = sprintf("SELECT DISTINCT s.sesname, t.sesid "
                        . "FROM session s, teaching t "
                        . "WHERE s.sesid = t.sesid "
                        . "AND t.lectid1=%s "
                        . "ORDER BY s.sesname DESC",  
                        GetSQLValueString(getSessionValue('lid'), "text"));
$ses = mysql_query($query_ses, $tams) or die(mysql_error());
$row_ses = mysql_fetch_assoc($ses);
$totalRows_ses = mysql_num_rows($ses);

$colname_sescrs = "-1";
if ( isset($row_ses['sesid']) ) {
	$colname_sescrs = $row_ses['sesid'];
}

if ( isset($_GET['ssid']) ) {
	$colname_sescrs = $_GET['ssid'];
}

$colname1_sescrs = "-1";
if ( isset($_GET['crs']) ) {
	$colname1_sescrs = $_GET['crs'];
}

$query_sescrs = sprintf("SELECT t.csid "
                            . "FROM teaching t "
                            . "WHERE t.sesid=%s "
                            . "AND t.lectid1=%s "
                            . "ORDER BY t.csid DESC", 
                            GetSQLValueString($colname_sescrs, "int"), 
                            GetSQLValueString(getSessionValue('lid'), "text"));
$sescrs = mysql_query($query_sescrs, $tams) or die(mysql_error());
$row_sescrs = mysql_fetch_assoc($sescrs);
$totalRows_sescrs = mysql_num_rows($sescrs);

$colname2_rslt = "-1";
if ( getSessionValue('lid') != NULL ) {
	$colname2_rslt = getSessionValue('lid');
}

$query_rslt = sprintf("SELECT r.csid, r.edited, r.stdid, r.sesid, tscore, escore, s.fname, s.lname "
                            . "FROM result r, student s, programme p, teaching t "
                            . "WHERE r.stdid = s.stdid "
                            . "AND r.csid = t.csid "
                            . "AND t.upload = 'yes' "
                            . "AND t.lectid1=%s "
                            . "AND r.csid = %s "
                            . "AND r.sesid = t.sesid "
                            . "AND r.sesid = %s "
                            . "AND s.progid = p.progid", 
                            GetSQLValueString($colname2_rslt, "text"),							 
                            GetSQLValueString($colname1_sescrs, "text"), 
                            GetSQLValueString($colname_sescrs, "int"));
$rslt = mysql_query($query_rslt, $tams) or die(mysql_error());
$row_rslt = mysql_fetch_assoc($rslt);
$totalRows_rslt = mysql_num_rows($rslt);

$query_error = sprintf("SELECT count(*) as count "
                        . "FROM result_error r "
                        . "WHERE r.csid = %s "
                        . "AND r.sesid = %s" , 
                        GetSQLValueString($colname1_sescrs, "text"),
                        GetSQLValueString($colname_sescrs, "int"));
$error = mysql_query($query_error, $tams) or die(mysql_error());
$row_error = mysql_fetch_assoc($error);
$totalRows_error = mysql_num_rows($error);

$error_link = $totalRows_error > 0? 
        "<a target='_blank' href='result_error.php?sid={$colname_sescrs}&csid={$colname1_sescrs}'>View result errors</a>": '';
        
//Query for select boxes in the result view//
$sname = "";
do {
    if( $colname2_crs == $row_sess['sesid']) {
        $sname = $row_sess['sesname'];
    }
}while( $row_sess = mysql_fetch_assoc($sess) );

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")) {
    doLogout( $site_root );  
}

//TODO Format remark appropriately, based on the course's passmark
//TODO add linked matric no beside highest and lowest mark.
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
<script src="../scripts/bootstrap-modal.js"></script>
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Upload Result<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
      <tr>
        <td><?php echo $uploadstat?></td>
      </tr>
      <tr>
          <td align="right">
              <a data-toggle="modal" href="#modal-2" class="btn btn-primary btn-medium">Upload Procedure - Help</a>
          </td>
      </tr>
      <tr>
        <td>
          <form name="form1" action="<?php echo $editFormAction;?>" method="post" enctype="multipart/form-data">
          <fieldset>
          	<legend>Upload Result for <?php echo $sname;?></legend>  
            <?php if( getAccess() == 2 ){?>          
            <select name="deptid" onchange="deptfilt(this)">
                <?php	do { ?>
		<option value="<?php echo $row_dept['deptid']?>"
                    <?php if (!(strcmp($row_dept['deptid'], $colname_crs))) {echo "selected=\"selected\"";} ?>>
                    <?php echo $row_dept['deptname']?></option>
                <?php } while ($row_dept = mysql_fetch_assoc($dept));?>
            
            </select>
            <?php }?>
            
            <select name="sesid" onchange="sesfilt(this)">
              <?php
                  $rows = mysql_num_rows($sess);
                  if($rows > 0) {
                          mysql_data_seek($sess, 0);
                          $row_sess = mysql_fetch_assoc($sess);
                  }	
                  do {  
		?>
		<option value="<?php echo $row_sess['sesid']?>"
                    <?php if (!(strcmp($row_sess['sesid'], $colname2_crs))) {echo "selected=\"selected\"";} ?>>
                        <?php echo $row_sess['sesname']?></option>
		<?php
                    } while ($row_sess = mysql_fetch_assoc($sess));
                    
                    $rows = mysql_num_rows($sess);
                    if($rows > 0) {
                            mysql_data_seek($sess, 0);
                            $row_sess = mysql_fetch_assoc($sess);
                    }
                ?>
             
            </select>&nbsp;&nbsp;
           
            <br /><br />
            <p style="color:red">
                Please ensure your Excel/CSV file has just three columns, in this format 'Matric no| CA | Exam'. <br/>NOTE: No headers are required!
            </p>
          	<input name="filename" type="file" />
            <?php if ($totalRows_crs > 0) { // Show if recordset not empty ?>
              <select name="csid">
                <?php
                            do {  
                            ?>
                <option value="<?php echo $row_crs['csid']?>" 
                    <?php if (!(strcmp($row_crs['csid'], $colname_dept))) {echo "selected=\"selected\"";} ?>>
                        <?php echo $row_crs['csid']?></option>
                <?php } while ($row_crs = mysql_fetch_assoc($crs));	?> 
              </select>
		<?php }else{ // Show if recordset is empty ?>
            	No course available. &nbsp;&nbsp;&nbsp;&nbsp;
            <?php }?>
            <input type="submit" name="submit" value="Upload Result" />
          </fieldset>
          </form>
        </td>
      </tr>
      <tr>
        <td>
        	<p>&nbsp;</p>
        	<table width="690">
              <tr>
                <td align="right">&nbsp;</td>
                <td align="right">
                  Session &nbsp;
                  <select name="ssid" id="ssid" onchange="ssesfilt(this)">
                    <?php
                        do {  
                        ?>
                    <option value="<?php echo $row_ses['sesid']?>"
                        <?php if (!(strcmp($row_ses['sesid'], $colname_sescrs))) {echo "selected=\"selected\"";} ?>>
                            <?php echo $row_ses['sesname']?></option>
                    <?php
                        } while ($row_ses = mysql_fetch_assoc($ses));
                    ?>
                    
                  </select>
                   &nbsp;&nbsp;&nbsp;
                   Course &nbsp;
                   <select name="csid" onchange="crsfilt(this)">
                   	<option value="-1">----</option>
                     <?php do {  ?>
                        <option value="<?php echo $row_sescrs['csid']?>"
                            <?php if (!(strcmp($row_sescrs['csid'], $colname1_sescrs))) {echo "selected=\"selected\"";} ?>>
                                <?php echo $row_sescrs['csid']?></option>
                    <?php
                        } while ($row_sescrs = mysql_fetch_assoc($sescrs));
                    ?>
                   </select>
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
                <td colspan="2"><?php echo $error_link?></td>
              </tr>
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
                          <td align="center"></td>
                      </tr>
                    <?php if ($totalRows_rslt > 0) { $i = 0;// Show if recordset not empty  ?> 
                    <?php do{?>                
                        <tr>
                            <td>
                          <?php echo $i+1?>
                        </td>
                        <td class="matric" >
                            <a href="../student/profile.php?stid=<?php echo $row_rslt['stdid']?>">
                                <?php echo $row_rslt['stdid']?></a>
                        </td>
                        <td><?php echo $row_rslt['lname'].", ".$row_rslt['fname']?></td>
                        <td align="center" class="tscore">
                            <span><?php echo scoreValue($row_rslt['tscore']);?></span>                        
                        </td>
                        <td align="center" class="escore">
                            <span><?php echo scoreValue($row_rslt['escore']);?></span>                        
                        </td>
                        <td align="center">
                            <span class="totscore"><?php echo getScore( $row_rslt['tscore'], $row_rslt['escore'] );?></span>
                        </td>
                        <td align="center" class="rem">
                            <?php echo getRemark( getScore($row_rslt['tscore'], $row_rslt['escore']) );?>
                        </td>
                        <td align="center">
                            <?php echo ($row_rslt['edited'] == 'TRUE')? '<a target=\'_blank\' href=\'edithistory.php?stdid='
                            .$row_rslt['stdid'].'&csid='.$row_rslt['csid'].'&sid='.$row_rslt['sesid'].'\'>Edited</a>': '';?>
                        </td>
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
          </table>
            <script type="text/javascript">
                var failValue = 39;
                $(function(){
                    attach();	
                });
            	
                //var seloptions = new Array();
//				<?php //do{?>
//					seloptions["<?php echo $row_sescrs['sesid'];?>"] = ;
//				<?php //}while($row_sescrs = mysql_fetch_assoc($sescrs));?>
            </script>
        </td>
      </tr>
    </table>
  <!-- InstanceEndEditable --></div>
<div class="footer">
    <p><!-- end .footer -->   
    
    <?php require '../include/footer.php'; ?>
	
   </p>
  </div>
  <!-- end .container -->
</div>
    
    <div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" class="modal hide fade" id="modal-2" style="display: none;">
      <div class="modal-header">
          <button aria-hidden="true" data-dismiss="modal" class="close" type="button">X</button>
          <h3 id="myModalLabel">Upload Procedure - Help</h3>
      </div>
      <div class="modal-body">
          <h4>Step 1: Type Scores in Excel  </h4>
          <img src="../images/upload-help/1score.jpg"/>
          <br/>
          <h4>Step 2: Choose Save As</h4>
          <img src="../images/upload-help/2save-as.jpg"/>
          <br/>
          <h4>Step 3: Specify File Name</h4>
          <img src="../images/upload-help/3file-name.jpg"/>
          <br/>
          <h4>Step 4: Save as CSV Comma Delimiter</h4>
          <img src="../images/upload-help/4csv-comma-delimited.jpg"/>
          <br/>
          <h4>Step 5: Save on Your Computer</h4>
          <img src="../images/upload-help/5final-save.jpg"/>
          <br/>
          <h4>Step 6: Open the TAMS Result Upload Page</h4>
          <img src="../images/upload-help/6upload-page.jpg"/>
          <br/>
          <h4>Step 7: Choose Result File to Upload Result</h4>
          <img src="../images/upload-help/7choose-file.jpg"/>
          <br/>
          <h4>Step 8: Specify Session, Course & click Upload</h4>
          <img src="../images/upload-help/8choose-course-to-upload.jpg"/>
      </div>
      <div class="modal-footer">
          <button class="btn" data-dismiss="modal">Close</button>
      </div>
      
  </div>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($sess);

mysql_free_result($dept);

mysql_free_result($sescrs);

mysql_free_result($crs);

mysql_free_result($rslt);
?>