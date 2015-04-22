<link href="../css/sidemenu.css" rel="stylesheet" type="text/css">
<?php 
$ictlink = "/".$site_root."/"."ict";
$link = "/".$site_root;
?>
<?php 
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
  <li><a href="<?php echo $ictlink."/prospective_mgt/index.php"?>" class="sidemenu">Admission Overview</a></li>
    
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
    <li><a href="<?php echo $ictlink."/prospective_mgt/searcheditapplicant.php"?>" class="sidemenu">Search/Edit Applicant</a></li>
    <li><a href="<?php echo $ictlink."/"?>addstaff.php" class="sidemenu">Add new ICT Staff</a></li>
    <li><a href="<?php echo $ictlink."/"?>addlect.php" class="sidemenu">Add new Lecturer</a></li>
    <li><a href="<?php echo $ictlink."/"?>addstdnt.php" class="sidemenu">Add New Student</a></li>
    <li><a href="<?php echo $ictlink."/"?>acadplanning.php" class="sidemenu">Academic Planning</a></li>
    <li><a href="<?php echo $ictlink."/"?>reglist.php" class="sidemenu">Registration Info</a></li>
    <li><a href="<?php echo $ictlink."/addcourse.php"?>" class="sidemenu">Add / Edit Course</a></li>
    <li><a href="<?php echo $ictlink."/payment.php"?>" class="sidemenu">Payment History</a></li>
    <li><a href="<?php echo $ictlink."/prospective_mgt/index.php"?>" class="sidemenu">Admission Overview</a></li>
    <li><a href="<?php echo $ictlink."/prospective_mgt/prsmgt.php"?>" class="sidemenu">Admission Mgt.</a></li>
    

    
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
        
        //side menu filter for academic planning
	if( getIctAccess() == 23)
	{
?>
        <span class="headline"><br />Academic Planning</span>
        <ul class="sidemenu">
            <li><a href="<?php echo $ictlink."/"?>acadplanning.php" class="sidemenu">Academic Planning</a></li>
           <li><a href="<?php echo $ictlink."/prospective_mgt/index.php"?>" class="sidemenu">Admission Overview</a></li>
        </ul>
	
 <?php }
 
        //side menu filter for admin user
	if( getIctAccess() == 24 )
	{
?>
        <span class="headline"><br />Admission Staff</span>
        <ul class="sidemenu">
            <li><a href="<?php echo $ictlink."/prospective_mgt/searcheditapplicant.php"?>" class="sidemenu">Search/Edit Applicant</a></li>
            <li><a href="<?php echo $ictlink."/prospective_mgt/index.php"?>" class="sidemenu">Admission Overview</a></li>
            <li><a href="<?php echo $ictlink."/prospective_mgt/prsmgt.php"?>" class="sidemenu">Admission Management</a></li>
        </ul>
	
 <?php }?>