<link href="../css/sidemenu.css" rel="stylesheet" type="text/css">
<?php 
$adminlink = "/".$site_root."/"."admin";
$ictlink = "/".$site_root."/"."ict";
$link = "/".$site_root;
?>

<span class="headline">General Information</span>
<ul class="sidemenu">
  <li><a href="<?php echo $link."/";?>college/" class="sidemenu">Colleges</a></li>
  <li><a href="<?php echo $link."/";?>department/" class="sidemenu">Departments</a></li>
  <li><a href="<?php echo $link."/";?>course/" class="sidemenu">Courses</a></li>
  <?php if((isset($_SESSION['MM_Username']))&&(getAccess() < 7)){?>
  <li><a href="<?php echo $link."/";?>student/" class="sidemenu">Students</a></li>
  <li><a href="<?php echo $link."/";?>staff/" class="sidemenu">Staff</a></li>
  <?php }?>
</ul>

<?php 
	//side menu filter for admin user
	if( getAccess() == 1 )
	{ 
	?>
<span class="headline"><br />Administration</span>
<ul class="sidemenu">
  <!--<li><a href="<?php echo $link."/";?>message/index.php" class="sidemenu">Message</a></li>-->
  <li><a href="<?php echo $adminlink."/";?>session/" class="sidemenu">Set Session</a></li>
 
  <li><a href="<?php echo $adminlink."/";?>college/" class="sidemenu">Colleges</a></li>
 <!-- <li><a href="#" class="sidemenu">Department</a></li>
  <li><a href="#" class="sidemenu">Programmes</a></li>-->
  <li><a href="<?php echo $adminlink."/";?>course/" class="sidemenu">Courses</a></li>
  <li><a href="<?php echo $adminlink."/";?>staff/" class="sidemenu">Staff</a></li>
  <li><a href="<?php echo $adminlink."/";?>student/" class="sidemenu">Student</a></li>
  <li><a href="<?php echo $adminlink."/";?>staff/appointment.php" class="sidemenu">Appointments</a></li>
   <li><a href="<?php echo $adminlink."/";?>disciplinary/index.php" class="sidemenu">Disciplinary</a></li>
  <li><a href="<?php echo $adminlink."/";?>course/courseassign.php" class="sidemenu">Assign Course</a></li>
  <li><a href="<?php echo $adminlink."/";?>teaching/" class="sidemenu">Allocate Course</a></li>
  <li><a href="<?php echo $adminlink."/";?>result/uploadresult.php" class="sidemenu">Upload Result</a></li>  
  <li><a href="<?php echo $adminlink."/";?>registration/reglist.php" class="sidemenu">Registration Info</a></li>  
  <li><a href="<?php echo $adminlink."/";?>registration/acadplanning.php" class="sidemenu">University Statistics</a></li>
  <li><a href="<?php echo $adminlink."/";?>result/" class="sidemenu">Consider Result</a></li>
  <li><a href="<?php echo $adminlink."/";?>result/summarysheet.php" class="sidemenu">Summary Sheet</a></li>
</ul>
<?php } 

	//side menu filter for dean
	if( getAccess() == 2 )
	{ 
?>

<span class="headline"><br />
Dean Profile</span>
<ul class="sidemenu">
  <li><a href="<?php echo $link."/";?>staff/profile.php?lid=<?php echo getSessionValue('lid');?>" class="sidemenu">Profile Info</a></li>
  <!--<li><a href="<?php echo $link."/";?>message/index.php" class="sidemenu">Message</a></li>-->
  <li><a href="<?php echo $link."/";?>course/courseassign.php" class="sidemenu">Assign Course</a></li>
  <li><a href="<?php echo $link."/";?>teaching/" class="sidemenu">Allocate Course</a></li>  
  <li><a href="<?php echo $link."/";?>staff/college_overview.php" class="sidemenu">College Overview</a></li>
  <li><a href="<?php echo $link."/";?>result/grading.php" class="sidemenu">Grading System</a></li>
  <li><a href="<?php echo $link."/";?>registration/viewform.php" class="sidemenu">Registration Form</a></li>
  <li><a href="<?php echo $link."/";?>result/uploadresult.php" class="sidemenu">Upload Result</a></li>
  <li><a href="<?php echo $link."/";?>result/" class="sidemenu">Consider Result</a></li>
  <li><a href="<?php echo $link."/";?>result/transcript.php" class="sidemenu">Statement of Result</a></li>
  <li><a href="<?php echo $link."/";?>result/summarysheet.php" class="sidemenu">Summary Sheet</a></li>
  <li><a href="<?php echo $link."/";?>result/broadsheet.php" class="sidemenu">Broadsheet</a></li>
</ul>

<?php }

	//side menu filter for hod
	if( getAccess() == 3 )
	{ 

?>

<span class="headline"> <br />HOD Profile</span>
<ul class="sidemenu">
  <li><a href="<?php echo $link."/";?>staff/profile.php?lid=<?php echo getSessionValue('lid');?>" class="sidemenu">Profile Info</a></li>
  <!--<li><a href="<?php echo $link."/";?>message/index.php" class="sidemenu">Message</a></li>-->
  <li><a href="<?php echo $link."/";?>teaching/adviser.php" class="sidemenu">Staff Adviser</a></li>
  <li><a href="<?php echo $link."/";?>course/courseassign.php" class="sidemenu">Assign Course</a></li>
  <li><a href="<?php echo $link."/";?>teaching/" class="sidemenu">Allocate Course</a></li>    
  <li><a href="<?php echo $link."/";?>staff/dept_overview.php" class="sidemenu">Department Overview</a></li>
  <li><a href="<?php echo $link."/";?>registration/viewform.php" class="sidemenu">Registration Form</a></li>
  <li><a href="<?php echo $link."/";?>result/uploadresult.php" class="sidemenu">Upload Result</a></li>
  <li><a href="<?php echo $link."/";?>result/" class="sidemenu">Consider Result</a></li>
  <li><a href="<?php echo $link."/";?>result/transcript.php" class="sidemenu">Statement of Result</a></li>
  <li><a href="<?php echo $link."/";?>result/printtranscript.php" class="sidemenu">Transcript</a></li>
  <li><a href="<?php echo $link."/";?>result/summarysheet.php" class="sidemenu">Summary Sheet</a></li>
  <li><a href="<?php echo $link."/";?>result/broadsheet.php" class="sidemenu">Broadsheet</a></li>
</ul>

<?php }

	//side menu filter for centre director
	if( getAccess() == 4 )
	{ 

?>

<span class="headline"> <br />Centre Director</span>
<ul class="sidemenu">
  <li><a href="<?php echo $link."/";?>staff/profile.php?lid=<?php echo getSessionValue('lid');?>" class="sidemenu">Profile Info</a></li>
  <li><a href="<?php echo $link."/";?>centre/" class="sidemenu">Assign Course</a></li>
  <li><a href="<?php echo $link."/";?>centre/teaching.php" class="sidemenu">Allocate Course</a></li>
  <!--<li><a href="<?php echo $link."/";?>result/uploadresult.php" class="sidemenu">Upload Result</a></li>-->
  <li><a href="<?php echo $link."/";?>centre/resultcon.php" class="sidemenu">Consider Result</a></li>
</ul>

<?php }

	//side menu filter for staffs
	if( getAccess() == 5 )
	{ 

?>

<span class="headline"> <br />Staff Profile</span>
<ul class="sidemenu">
  <li><a href="<?php echo $link."/";?>staff/profile.php?lid=<?php echo getSessionValue('lid');?>" class="sidemenu">Profile Info</a></li>
  <!--<li><a href="<?php echo $link."/";?>message/index.php" class="sidemenu">Message</a></li>-->
  <li><a href="<?php echo $link."/";?>result/uploadresult.php" class="sidemenu">Upload Result</a></li>
</ul>


<?php }

	//side menu filter for staff adviser
	if( getAccess() == 6 )
	{ 

?>

<span class="headline"><br />Staff Adviser Profile</span>
<ul class="sidemenu">
  <li><a href="<?php echo $link."/";?>staff/profile.php?lid=<?php echo getSessionValue('lid');?>" class="sidemenu">Profile Info</a></li>
  <!--<li><a href="<?php echo $link."/";?>message/index.php" class="sidemenu">Message</a></li>-->
  <li><a href="<?php echo $link."/";?>result/uploadresult.php" class="sidemenu">Upload Result</a></li>
  <li><a href="<?php echo $link."/";?>registration/processform.php" class="sidemenu">Process Course Forms</a></li>
</ul>

<?php }

        //side menu filter for students
	if( getAccess() == 10 )
	{ 

?>

<span class="headline"><br />Student Profile</span>
<ul class="sidemenu">
  <li><a href="<?php echo $link."/";?>student/profile.php?stid=<?php echo getSessionValue('stid');?>" class="sidemenu">Profile Info</a></li>
  <!--<li><a href="<?php echo $link."/";?>message/index.php" class="sidemenu">Message</a></li>-->
  <li><a href="<?php echo $link."/";?>registration/registercourse.php" class="sidemenu">Course Registration</a></li>
  <li><a href="<?php echo $link."/";?>result/transcript.php?stid=<?php echo getSessionValue('stid');?>" class="sidemenu">Statement of Result</a></li>
  <?php if((strtolower(getSessionValue('admode')) == 'de' && getSessionValue('level') == 2) || getSessionValue('level') == 1) {?>
  <li><a href="http://portal.tasued.edu.ng/schfee/main/history.php" class="sidemenu" target="_new">Payment History</a></li>
  <?php }else {?>
  <li><a href="http://portal.tasued.edu.ng/regular_students/schfee/main/history.php" class="sidemenu" target="_new">Payment History</a></li>
  <?php }?>
  
</ul>

<?php }

	//side menu filter for students
	if( getAccess() == 11 )
	{ 

?>

<span class="headline"><br />Prospective Student</span>
<ul class="sidemenu">
  <li><a href="<?php echo $link."/";?>prospective/termsandcon.php" class="sidemenu">Instruction</a></li>  
  <li><a href="<?php echo $link."/";?>prospective/admform.php" class="sidemenu">Application Form </a></li>
  <li><a href="<?php echo $link."/";?>prospective/viewform.php" class="sidemenu">View Form</a></li>
  <li><a href="<?php echo $link."/";?>prospective/status.php" class="sidemenu">Admission Status</a></li>
  <li><a href="<?php echo $link."/";?>prospective/payhistory.php" class="sidemenu">Payment History</a></li>
</ul>

<?php }

	//side menu filter for everybody
	if( getAccess() > 0 )
	{ 

?>
<!--
<span class="headline"><br />Resources</span>
<ul class="sidemenu">
  <li><a href="#" class="sidemenu">Past Questions</a></li>
  <li><a href="#" class="sidemenu">Projects</a></li>
</ul>-->
<?php } 
	//side menu filter for admin user
	if(getIctAccess() == 21 )
	{ 
	?>

<span class="headline">Unit Head </span>
<ul class="sidemenu">
  <li><a href="<?php echo $ictlink."/"?>srchstdnt.php" class="sidemenu">Search/Edit Student</a></li>  
  <li><a href="<?php echo $ictlink."/"?>addstdnt.php" class="sidemenu">Add New Student</a></li>
  <li><a href="<?php echo $ictlink."/"?>acadplanning.php" class="sidemenu">Academic Planning</a></li>
  <li><a href="<?php echo $ictlink."/addcourse.php"?>" class="sidemenu">Edit Course</a></li>
</ul>
<?php }?>

<?php 
	//side menu filter for admin user
	if( getIctAccess() == 20 )
	{ 
	?>
<span class="headline"><br />Administration</span>
<ul class="sidemenu">
    <li><a href="<?php echo $ictlink."/"?>srchstdnt.php" class="sidemenu">Search/Edit Student</a></li>
    <li><a href="<?php echo $ictlink."/"?>addstaff.php" class="sidemenu">Add new ICT Staff</a></li>
    <li><a href="<?php echo $ictlink."/"?>addlect.php" class="sidemenu">Add new Lecturer</a></li>
    <li><a href="<?php echo $ictlink."/"?>addstdnt.php" class="sidemenu">Add New Student</a></li>
    <li><a href="<?php echo $ictlink."/"?>acadplanning.php" class="sidemenu">Academic Planning</a></li>
    <li><a href="<?php echo $ictlink."/"?>reglist.php" class="sidemenu">Registration Info</a></li>
    <li><a href="<?php echo $ictlink."/addcourse.php"?>" class="sidemenu">Add / Edit Course</a></li>
    <li><a href="<?php echo $ictlink."/addpayment.php"?>" class="sidemenu">Payment History</a></li>
    <li><a href="<?php echo $ictlink."/prospective_mgt/index.php"?>" class="sidemenu">Prospective Students</a></li>
    
</ul>
<?php }
        //side menu filter for admin user
	if( getIctAccess() == 22 )
	{
?>
        <span class="headline"><br />Staff</span>
        <ul class="sidemenu">
           <li><a href="<?php echo $ictlink."/"?>srchstdnt.php" class="sidemenu">Search/Edit Student</a></li> 
           <!--<li><a href="<?php //echo $ictlink."/"?>reglist.php" class="sidemenu">Registration Info</a></li>-->
           <!--<li><a href="<?php echo $ictlink."/addcourse.php"?>" class="sidemenu">Edit Course</a></li>-->
        </ul>
	
<?php }
        //side menu filter for admin user
	if( getIctAccess() == 24 )
	{
?>
        <span class="headline"><br />Admission Staff</span>
        <ul class="sidemenu">
           <li><a href="<?php echo $ictlink."/prospective_mgt/index.php"?>" class="sidemenu">Prospective Students</a></li>
        </ul>
	
 <?php }?>