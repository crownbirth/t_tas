<?php  //  $paytp = $_GET['paytp']; ?>
<?php
$hostname_conn_burmas = "localhost";
$database_conn_burmas = "tasueded_gradportal";
$username_conn_burmas = "tasueded_grad";
$password_conn_burmas = "123gradportal456";
$conn_burmas = mysql_pconnect($hostname_conn_burmas, $username_conn_burmas, $password_conn_burmas) or trigger_error(mysql_error(),E_USER_ERROR); 
?>
<?php  error_reporting(0);
//initialize the session
if (!isset($_SESSION)) {
  session_start();
}

if ((isset($_SESSION['session_mat_number'])) && isset($_SESSION['session_user_surname'])) {   

 $mat_number = $_SESSION['session_mat_number'];
 $surname = $_SESSION['session_user_surname'];



mysql_select_db($database_conn_burmas, $conn_burmas);
 $query_paid = "SELECT * FROM gradfees_transactions WHERE status = 'APPROVED'";
//$query_paid= sprintf($SQL);
$paid= mysql_query($query_paid, $conn_burmas) or die(mysql_error());
$total_paid= mysql_num_rows($paid);
$ref=$row_paid['reference'];
?>
<html><head><meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title> Print Receipt</title>

</head>

<body>

<div class="container" style="background-image:url(&#39;../images/bgtext-ci.php&#39;);">
  <div class="content">
  <link href="Print_files/printpages.css" rel="stylesheet" type="text/css">
  <style>
table.altrowstable td {
    background-color: #fff;
    border-color: #4286ad;
    border-style: solid;
    border-width: 1px;
    padding: 10px 10px;
}

table.tdpad td {
    padding: 3px 10px;
}

</style>
  <div id="headwrap">
  <div class="logo"><img src="Print_files/logo.png" alt="" width="115" height="115"></div>
  <div class="headings" style="padding:30px 0 0 30px">
        <h2>TAI SOLARIN UNIVERSITY OF EDUCATION </h2>
        <h4> </h4>
        <h4><span class="title">  </span></h4>
      </div>
      
		


    </div>
     <div class="line"></div>
	   <div class="title2"><b>LIST OF PAID STUDENTS FOR ICT SERVICE CHARGE</b></div>
        <div class="wrap2per">  
    

  <?php    
  if($total_paid>0) {?>
  
  <div class="wrap2">
	<table  class="altrowstable" style="clear: both; width:100%; font-family: &#39;verdana&#39;; font-size: 14; ">
	<tbody><tr>
		<td><span class="title"><b> CONVOCATION YEAR: 2014 </b></span></td>
		<td><span class="title"><b> DATE: <?php echo Date("d/M/Y")?></span></b></td>
		<td><span class="title"><b> TOTAL PAID: <?php echo $total_paid; ?></span></b></td>
	</tr>
	</tbody></table>
  </div>   


  <div class="wrap2">
	<table  class="altrowstable tdpad " style="clear: both; width:100%;   font-size: 12; ">
			<thead><tr>
					<td width="150"><span class=".lGrey"><b>Candidate Number</b></span></td> 
					<td><span class=".lGrey"><b>Programme</b></span></td> 
					<td><span class=".lGrey"><b> Status</b></span></td> 
					<td><span class=".lGrey"><b>Amount</b></span></td> 
					<td><span class=".lGrey"><b>Date & Time</b></span></td> 
					</tr>
			</thead>
			<tbody>
			<?php while($row_paid = mysql_fetch_assoc($paid)) {
			$Candidate_no=$row_paid['can_no'];

			mysql_select_db($database_conn_burmas, $conn_burmas);
			  $query_rs_personal = "SELECT * FROM student_details WHERE Candidate_no = $Candidate_no";
			$rs_personal = mysql_query($query_rs_personal, $conn_burmas) or die(mysql_error());
			$row_rs_personal = mysql_fetch_assoc($rs_personal);
			$totalRows_rs_personal = mysql_num_rows($rs_personal);?>
			<tr>
					<td> <?php echo $row_rs_personal["mat_number"];?></span></td> 
					<td><span class=".lGrey"><?php echo $row_rs_personal['Last_Name']." ".$row_rs_personal['First_Name']; ?></span></td>  
					<td><span class=".lGrey"><?php echo $row_paid['status']?></span></td> 
					<td><span class=".lGrey"><?php echo $row_paid['amt']?></span></td>  
					<td><span class=".lGrey"><?php echo $row_paid['date_time']?></span></td> 
			</tr>
			<?php } ?>
			</tbody>
	</table>

</div>
             <?php } else {?>
		 <td COLSPAN =6 > <font color ="red" align="center"> <h1>NO RECORD FOUND</h1></font></td>
    
     <?php } ?>   
</div>
<div class="clear"></div>

<?php }?>
    <!-- end .content --></div>
  <!-- end .container --></div>


</body></html>