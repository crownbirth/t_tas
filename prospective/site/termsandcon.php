<?php
require_once('../Connections/tams.php');
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
    $query = sprintf("SELECT * FROM session ORDER BY sesid DESC LIMIT 1 ");
    $session= mysql_query($query, $tams) or die(mysql_error());
    $row_session = mysql_fetch_assoc($session);
    $totalRows_session = mysql_num_rows($session);
    
    //set the new Admission session Name
    $split = explode('/',  $row_session['sesname']);
    $adm_ses_name = ($split[0]+1).'/'.($split[1]+1);

mysql_select_db($database_tams, $tams);
$query_rschk = sprintf("SELECT jambregid, admtype, formsubmit, formpayment 
						FROM prospective p 
						WHERE p.jambregid=%s",
						GetSQLValueString(getSessionValue('MM_Username'), "text"));
$rschk = mysql_query($query_rschk, $tams) or die(mysql_error());
$row_rschk = mysql_fetch_assoc($rschk);
$totalRows_rschk = mysql_num_rows($rschk);
	

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
        <td><!-- InstanceBeginEditable name="pagetitle" --><?php echo $adm_ses_name ?> Prospective  Application Instructions<!-- InstanceEndEditable --></td>
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
                <table class="table table-bordered">
                    <tr>
                        <td>
                            <p>
                                <ol style="list-style: decimal">
                                    <li style="font-weight: bold">
                                        <u> THOSE WHO MAY APPLY </u>
                                    </li>
                                    <P>
                                        <ol style="list-style: lower-roman">
                                            <li>
                                                <p> 
                                                    Candidates who chose TASUED as their <u>first most preferred</u> institution in the
                                                    2014 Unified Tertiary  Matriculation Examination (UTME), and scored a minimum 
                                                    of <strong>180</strong> in 2014 UTME.
                                                </p>
                                            </li>
                                            <li>
                                                <p> 
                                                    Direct Entry candidates who chose TASUED as their first most preferred and/or second
                                                    most preferred institution for the 2014/2015 Academic Session and have applied through 
                                                    Joint Admission And Matriculation Board (JAMB).
                                                </p>
                                            </li>
                                            <li>
                                                <p> 
                                                    Candidates seeking a change of institution to TASUED through JAMB, having scored a minimum 
                                                    of 180 in 2014 UTME.
                                                </p>
                                            </li>
                                            <li>
                                                <p> 
                                                    Direct Entry candidates seeking a change of institution to Tai Solarin University of Education
                                                    and obtained 2014 Direct Entry JAMB form.
                                                </p>
                                            </li>
                                        </ol>
                                    </P>
                                    <li style="font-weight: bold">
                                        <u> METHOD OF APPLICATION (LOG IN PROCEDURE)   </u>
                                    </li>
                                    <P>
                                        <ol style="list-style: lower-roman">
                                            <li>
                                                <p> 
                                                    Candidates should apply online with the payment of application fee of One thousand naira only (#1,000.00). 
                                                    In addition, a processing fee of four thousand two hundred naira only (#4,200) for first choice candidates 
                                                    and Nine thousand two hundred naira (#9200) for categories of candidates in (iii) and (iv) above seeking 
                                                    change of institution to TASUED, payable with Master card or VISA ATM card
                                                    (Please print out your receipt after payment)
                                                </p>
                                            </li>
                                            <li>
                                                <p> 
                                                    Visit the TASUED website by typing www.tasued.edu.ng on the web page. 
                                                    NB: Only the www.tasued.edu.ng that has the legitimate and authentic 
                                                    platform for the post UTME registration form. 
                                                </p>
                                            </li>
                                            <li>
                                                <p> 
                                                    Click Post-UTME Registration Form. 
                                                    Candidates are strongly advised to click the University Degree Brochure and carefully study the 
                                                    O’Level requirements for the courses applied for, before registration on the TASUED website, www.tasued.edu.ng

                                                </p>
                                            </li>
                                            <li>
                                                <p> 
                                                    Complete the Registration Form by providing the required information; 
                                                </p>
                                            </li>
                                            <li>
                                                <p> 
                                                    Submit your form by clicking submit button. 
                                                </p>
                                            </li>
                                            <li>
                                                <p> 
                                                    Print out an identification slip containing your colour passport photograph.
                                                    The printed slip will serve as candidate’s
                                                    identification/admission card for the screening test
                                                </p>
                                            </li>
                                            <li>
                                                <p> 
                                                    The sale of online form/registration commences on Monday, 7th July, 2014 and closes 
                                                    Friday 8th August, 2014.
                                                </p>
                                            </li>
                                        </ol>
                                        <p>
                                            Note that false information provided by any candidate shall be detected by the special 
                                            TASUED software application and such candidates shall be disqualified.
                                        </p>
                                    </P>
                                    <li style="font-weight: bold">
                                        <u> SCREENING DATES</u>
                                    </li>
                                    <p>
                                        Screening shall be conducted on Saturday 9th 
                                        August and Sunday 10th August, 2014 for all 
                                        UTME candidates at the Ososa Campus of the 
                                        University, by 7.00am The screening date for Direct Entry (200 Level) 
                                        applicants shall be on Tuesday 2nd September and Wednesday 3rd September, 2014 at
                                        the University Main Campus, Ijagun by 8.00am.
                                        <p style="font-weight: bold">
                                            Candidates’ participation in the screening exercise is a mandatory requirement 
                                            for entry into Tai Solarin University of Education.  
                                        </p>
                                    </p>
                                    <li style="font-weight: bold">
                                        <u> REQUIRED INFORMATION FOR POST UTME/DIRECT ENTRY SCREENING REGISTRATION</u>
                                    </li>
                                    <p>
                                        <ol style="list-style: lower-roman">
                                            <li style="font-weight: bold">Bio-Data</li>
                                            <p>
                                                <ol style="list-style: lower-alpha">
                                                    <li>Surname</li>
                                                    <li>First Name</li>
                                                    <li>Middle Name</li>
                                                    <li>sex</li>
                                                    <li>Date of Birth</li>
                                                    <li>Age</li>
                                                    <li>Passport Size Picture (File Size: <strong>20KB</strong> max, File Format:
                                                        <strong>JPEG</strong> (i.e. <strong>jpg</strong>)</li>
                                                </ol>
                                            </p>
                                            <li style="font-weight: bold">Academic Data </li>
                                            <p>
                                                <ol style="list-style: lower-alpha">
                                                    <li>JAMB Registration Number</li>
                                                    <li>Course </li>
                                                    <li>Post O’Level Qualifications obtained with grade (where applicable)</li>
                                                    <li>UTME Score (Range: 180-400) (where applicable)</li>
                                                    <li>Detailed O’Level Results</li>
                                                </ol>
                                            </p>
                                        </ol>
                                    </p>
                                </ol>
                            </p>    
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
      <tr>
          <td>
              <table>
                  <?php if($row_rschk['formpayment']== 'Yes'){?>
                  <tr>
                      <td> Our record shows that you have already paid for an Application.</td>
                  </tr>
                  <?php }else{?>
                  <tr>
                      <td><input type="button"  onclick="javascript:location='admform.php'"name="goto" value="Proceed to Payment"/></td>
                  </tr>
                  <?php }?>
              </table>
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
</body>
<!-- InstanceEnd --></html>