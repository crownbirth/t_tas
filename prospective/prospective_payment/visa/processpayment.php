<?php 
if ( isset($_POST['paynow']) && isset($_POST['jambregid']) && $_POST['form_trig']=='form1'){
//var_dump($_POST);
    // All required post data needed for transaction
    $percent = $_POST['percent'];
    $revhead = $_POST['revhead'];
    $canNo= $_POST['jambregid'];
    $canName = $_POST['canName'];
    $prg = $_POST['prg'];
    $sesid= $_POST['sesid'];
    
    $price = $_POST['amount'];
    $price *= 100; // multiply the price by 100 because TWPG deals price in kobo.
    $purpose = "APLLICATION FEE";
    
    $description =$revhead."^POST UTME-DE/".$canNo."/".$sesid."^".$purpose."^";
    
//echo $description. "<br/>\n";
		$xml = "<?xml version='1.0' encoding='UTF-8'?>
				<TKKPG>
				<Request>
				<Operation>CreateOrder</Operation>
				<Language>EN</Language>
				<Order>
				<Merchant>TASUEDEDU</Merchant>
				<Amount>".$price."</Amount>
				<Currency>566</Currency>
				<Description>".$description."</Description>
				<ApproveURL>http://portal.tasued.edu.ng/tams/prospective/prospective_payment/paid.php</ApproveURL>
				<CancelURL>http://portal.tasued.edu.ng/tams/prospective/prospective_payment/cancel.php</CancelURL>
				<DeclineURL>http://portal.tasued.edu.ng/tams/prospective/prospective_payment/declined.php</DeclineURL>
				</Order>
				</Request>
				</TKKPG>";

		$ch = curl_init(); 
		// former testing url curl_setopt($ch, CURLOPT_URL,"https://196.46.20.36:5443/Exec"); 
		curl_setopt($ch, CURLOPT_URL,"https://mpi.valucardnigeria.com:5443/Exec"); 

		curl_setopt($ch, CURLOPT_VERBOSE, '1');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5000);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, '1');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, '1');
		curl_setopt($ch, CURLOPT_CAINFO,  getcwd().'/tasuedcert/CAcert.crt');
		curl_setopt($ch, CURLOPT_SSLCERT, getcwd().'/tasuedcert/TASUEDEDU.pem');
		curl_setopt($ch, CURLOPT_SSLKEY, getcwd().'/tasuedcert/TASUEDEDU.key');
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		
		$response = curl_exec($ch); 
		echo curl_error($ch);

		if(!(curl_errno($ch)>0)){

			$parsedxml = simplexml_load_string($response);

			foreach($parsedxml->children() as $RESPONSENODE)
 			{	
			 	foreach($RESPONSENODE->children() as $ORDERNODE)
  				{
					foreach($ORDERNODE->children() as $child)
					{	
						if ($child->getName() == "OrderID")
							$orderid = $child;
							 
						if ($child->getName() == "SessionID")
							$sessionid = $child;

						if ($child->getName() == "URL")
							$url = $child;	
					}
				}	
  			 }//end all loop
$gateway_url = $url."?ORDERID=".$orderid."&SESSIONID=".$sessionid;
require_once('../../../Connections/tams.php'); 
$status="PENDING";
date_default_timezone_set('Africa/Lagos');
$date = date('d/m/Y h:i:s a', time());
$year=date('Y');
$ref=date("Ymd").$canNo.time().TF;
			 
mysql_select_db($database_tams, $tams);
$sql="INSERT INTO appfee_transactions (can_no,can_name, reference, year, status, date_time, ordid, sessionid,gatewayurl,percentPaid) VALUES('$canNo','$canName','$ref','$year' ,'$status' ,'$date','$orderid','$sessionid','$gateway_url','$percent')";
 mysql_query($sql, $tams) or die(mysql_error());


//echo $gateway_url;

  			 /*
  			 *
				THE ABOVE FORMED URL ($gateway_url) IS THE URL USED TO 
				CALL THE PAYMENT GATEWAY....
				YOU CAN USE THIS URL IN THE SOURCE OF AN IFRAME.
				E.G  
				<iframe src= "<?php echo $gateway_url ?>" frameborder="0" scrolling="no"></iframe>
  			 *
  			 */
 
  			 header("location: ".$gateway_url);
  	
		}

	}

?>