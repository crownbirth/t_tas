<?php 
if (!isset($_SESSION)) {
  session_start();
}

require_once('../Connections/tams.php');
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
$query_rschk = sprintf("SELECT jambregid, admtype, formsubmit, formpayment 
						FROM prospective p 
						WHERE p.jambregid=%s",
						GetSQLValueString(getSessionValue('MM_Username'), "text"));
$rschk = mysql_query($query_rschk, $tams) or die(mysql_error());
$row_rschk = mysql_fetch_assoc($rschk);
$totalRows_rschk = mysql_num_rows($rschk);

if($row_rschk['formsubmit'] == 'No' ) {
	header('Location: admform.php');
}
   

if($row_rschk['admtype']=='DE'){
    mysql_select_db($database_tams, $tams);
    $query_rspros = sprintf("SELECT p.*, st.stname,  pr.progname AS prog1, pr2.progname AS prog2 
                                                    FROM prospective p 
                                                    JOIN programme pr ON p.progid1 = pr.progid
                                                    JOIN programme pr2 ON p.progid2 = pr2.progid
                                                    JOIN state st ON st.stid = p.stid
                                                    WHERE p.jambregid=%s",
                                                    GetSQLValueString($row_rschk['jambregid'], "text"));
    $rspros = mysql_query($query_rspros, $tams) or die(mysql_error());
    $row_rspros = mysql_fetch_assoc($rspros);
    $totalRows_rspros = mysql_num_rows($rspros);
}
else{
    mysql_select_db($database_tams, $tams);
    $query_rspros = sprintf("SELECT p.*, st.stname, sbj1.subjname as jamb1, sbj2.subjname as jamb2, sbj3.subjname as jamb3, sbj4.subjname as jamb4, pr.progname AS prog1, pr2.progname AS prog2 
                                                    FROM prospective p 
                                                    JOIN programme pr ON p.progid1 = pr.progid
                                                    JOIN programme pr2 ON p.progid2 = pr2.progid
                                                    JOIN subject sbj1 ON p.jambsubj1 = sbj1.subjid
                                                    JOIN subject sbj2 ON p.jambsubj2 = sbj2.subjid
                                                    JOIN subject sbj3 ON p.jambsubj3 = sbj3.subjid
                                                    JOIN subject sbj4 ON p.jambsubj4 = sbj4.subjid
                                                    JOIN state st ON st.stid = p.stid
                                                    WHERE p.jambregid=%s",
                                                    GetSQLValueString($row_rschk['jambregid'], "text"));
    $rspros = mysql_query($query_rspros, $tams) or die(mysql_error());
    $row_rspros = mysql_fetch_assoc($rspros);
    $totalRows_rspros = mysql_num_rows($rspros);
}



$jambtotal = ($row_rspros['jambscore1']+$row_rspros['jambscore2']+$row_rspros['jambscore3']+$row_rspros['jambscore4']);

mysql_select_db($database_tams, $tams);
$query_rssit1 = sprintf("SELECT * 
						FROM olevel o 
						JOIN olevelresult l ON o.olevelid = l.olevelid 
						JOIN subject s ON l.subject = s.subjid 
						JOIN grade g ON l.grade = g.grdid 
						WHERE o.jambregid=%s
						AND sitting='first'",
						GetSQLValueString(getSessionValue('MM_Username'), "text"));
$rssit1 = mysql_query($query_rssit1, $tams) or die(mysql_error());
$row_rssit1 = mysql_fetch_assoc($rssit1);
$totalRows_rssit1 = mysql_num_rows($rssit1);

mysql_select_db($database_tams, $tams);
$query_rssit2 = sprintf("SELECT * 
						FROM olevel o 
						JOIN olevelresult l ON o.olevelid = l.olevelid 
						JOIN subject s ON l.subject = s.subjid 
						JOIN grade g ON l.grade = g.grdid 
						WHERE o.jambregid=%s
						AND sitting='second'",
						GetSQLValueString(getSessionValue('MM_Username'), "text"));

$rssit2 = mysql_query($query_rssit2, $tams) or die(mysql_error());
$row_rssit2 = mysql_fetch_assoc($rssit2);
$totalRows_rssit2 = mysql_num_rows($rssit2);

if(isset($_POST['frmsubmit'])){
    mysql_select_db($database_tams, $tams);
    $query_update = sprintf("UPDATE prospective SET formsubmit =%s WHERE jambregid=%s",
                        GetSQLValueString("Yes", "text"),
                        GetSQLValueString(getSessionValue('MM_Username'), "text"));
    $update = mysql_query($query_update, $tams) or die(mysql_error());
    
    $updateGoTo = "viewform.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
}
$imgname = $row_rspros['jambregid'];
$image_url = '../images/student/profile.png';
$image = array("../images/student/{$imgname}.jpg", 
                "../images/student/{$imgname}.JPG", 
                "../images/student/{$imgname}.png",
                "../images/student/{$imgname}.PNG");
for($idx = 0; $idx < count($image); $idx++) {
    if(realpath("{$image[$idx]}")) {
        $image_url = $image[$idx];
        break;
    }
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
<?php require('../param/site.php'); ?>
<title><?php echo $university ?> </title>
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Post UTME/DE Application Form<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
    <table width="690">
    <?php if($row_rschk['formpayment']=='Yes'){?>
      <tr>
        <td>
            <table width="670" class="table table-condensed table-bordered">
                <tr>
                    <td colspan="2">
                        <table class="table table-condensed table-striped table-bordered">
                            <thead>
                            <tr>
                                <th colspan="4"> BIO-DATA</th>
                            </tr>
                            </thead>    
                            <tr>
                                <th width="90">Surname :</th>
                                <td><?php echo $row_rspros['lname']?></td>
                                <td colspan="2" rowspan="5"> <img  style="alignment-adjust: central"src="<?php echo $image_url;?>" alt="Image"  id="placeholder" name="placeholder" width="160" height="160" align="top"/></td> 
                            </tr>
                            <tr>
                                <th>First Name :</th>
                                <td><?php echo $row_rspros['fname']?> </td>
                            </tr>
                            <tr>
                                <th>Middle Name :</th>
                                <td><?php echo $row_rspros['mname']?></td>
                            </tr>
                            <tr>
                                <th>Email :</th>
                                <td><?php echo $row_rspros['email']?></td>
                            </tr>
                            <tr>
                                <th>Phone :</th>
                                <td><?php echo $row_rspros['phone']?></td>
                            </tr>
                            <tr>
                                <th>Addresss :</th>
                                <td><?php echo $row_rspros['address']?></td>
                                <td><strong>State of Origin : </strong><?php echo $row_rspros['stname']?></td>
                                <td><strong>Sex : </strong><?php echo getSex($row_rspros['Sex']);?> </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <?php if($row_rspros['admtype']== 'UTME'){?>
                <tr>
                    <td colspan="1">
                        <table width="320" class="table table-hover table-striped table-bordered">
                            <tr>
                                <th colspan="2"> UTME RESULT</th>
                            </tr>
                            <tr>
                                <td>UTME Reg No. :</td>
                                <td align="left"><?php echo $row_rspros['jambregid']?></td>
                            </tr>
                            <tr>
                                <td>UTME Year. : </td>
                                <td align="left"><?php echo $row_rspros['jambyear']?></td>
                            </tr>
                            <tr>
                                <th colspan="2" align="center">Subjects / Scores </th>
                                
                            </tr>
                            <tr>
                                <td><?php echo $row_rspros['jamb1']?></td>
                                <td align="left"><?php echo $row_rspros['jambscore3']?></td>
                            </tr>
                            <tr>
                                <td><?php echo $row_rspros['jamb2']?></td>
                                <td align="left"><?php echo $row_rspros['jambscore2']?></td>
                            </tr>
                            <tr>
                                <td><?php echo $row_rspros['jamb3']?></td>
                                <td align="left"><?php echo $row_rspros['jambscore3']?></td>
                            </tr>
                            <tr>
                                <td><?php echo $row_rspros['jamb4']?></td>
                                <td align="left"><?php echo $row_rspros['jambscore4']?></td>
                            </tr>
                             <tr>
                                <th>Total </th>
                                <td style="color:green; font-weight: bold"><?php echo $jambtotal?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <?php } if($row_rspros['admtype']=='DE'){?>
                <tr>
                    <td colspan="2">
                        <p>&nbsp;</p>
                        <table width="320" class="table table-hover table-striped table-bordered">
                            <tr>
                                <th colspan="2"> DIRECT ENTRY </th>
                            </tr>
                            <tr>
                                <td>UTME Reg No.</td>
                                <td align="left"><?php echo $row_rspros['jambregid']?></td>
                            </tr>
                            <tr>
                                <td>UTME Year.</td>
                                <td align="left"><?php echo $row_rspros['jambyear']?></td>
                            </tr>
                            <tr>
                                <td colspan="2"style="font-weight: bold" align="center"> Previous Qualification </td>
                            </tr>
                            <tr>
                                <td>School Name :</td>
                                <td align="left"><?php echo $row_rspros['deschname']?></td>
                            </tr>
                            <tr>
                                <td>Graduation year :</td>
                                <td align="left"><?php echo $row_rspros['degradyear']?></td>
                            </tr>
                            <tr>
                                <td>Garde : </td>
                                <td align="left">
                                    <?php echo getDeGrade($row_rspros['degrade']); ?>
                                </td>
                            </tr>
                        </table>
                        <p>&nbsp;</p>
                    </td>
                </tr>
                <?php }?>
                <tr>
                    <td colspan="2"><strong> Programe Choices  </strong></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <p>&nbsp;</p>
                        <table width="320" class="table table-hover table-striped table-bordered">
                            <tr>
                                <th colspan="2">1st choice programme: </th>
                                <td><?php echo $row_rspros['prog1']?></td>
                            </tr>
                            <tr>
                                <th colspan="2">2nd choice programme: </th>
                                <td><?php echo $row_rspros['prog2']?></td>
                            </tr>
                        </table>
                    </td>
                </tr> 
              <tr>
                  <th colspan='2' align="center" > O'LEVEL</th>
              </tr>
              <tr>
                <td>
                	<table width='320' class="table table-hover table-striped table-bordered">
                    	<tr><th colspan="2">First Sitting</th></tr>
                        <?php 						
							if($totalRows_rssit1 > 0) {
								for($i = 0; $i < $totalRows_rssit1; $i++){
						?>
                        <tr>
                        	<td><?php echo  $row_rssit1['subjname']?></td>
                            <td><?php echo  $row_rssit1['grdname']?></td>
                        </tr>
                        <?php 
							$row_rssit1 = mysql_fetch_assoc($rssit1);
							}}else{
						?>
                        <tr><td colspan='2'>No result</td></tr>
                        <?php }?>
                    </table>                    
                </td>
                <td>
                	<table width='320' class="table table-hover table-striped table-bordered">
                    	<tr><th colspan="2">Second Sitting</th></tr>
                        <?php 						
							if($totalRows_rssit2 > 0) {
								for($i = 0; $i < $totalRows_rssit2; $i++){
						?>
                        <tr>
                        	<td><?php echo  $row_rssit2['subjname']?></td>
                            <td><?php echo  $row_rssit2['grdname']?></td>
                        </tr>
                        <?php 
							$row_rssit2 = mysql_fetch_assoc($rssit2);
							}}else{
						?>
                        <tr><td colspan='2'>No result</td></tr>
                        <?php }?>
                    </table>
                </td>
              </tr>
              <tr>
                    <form name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']?>">
                        <td align="center"  colspan="2">
                            <?php if($row_rschk['formsubmit'] == 'No' ) {?>
                            <input type="submit" name="submit" id="Save" value="Submit application"  />
                             
                                <a class="" href="admform.php">Go Back To Application</a>
                            <?php }?>
                        </td>
                        <input type="hidden" name="frmsubmit" />
                    </form>
              </tr>
                <tr>
                    <td>
                        <a target="_blank" href="printform.php">
                            <button>Print Form</button>
                        </a>
                    </td>
                </tr> 
            </table>
        </td>
      </tr>
    <?php }  else {?>
        <tr>
            <td>
                <br/>You have not made Payment for your Application. <a href="prospective_payment/index.php">click here</a> to make payment.
            </td>
        </tr>  
    <?php }?>   
    </table>
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