<?php require_once('../Connections/tams.php'); ?>
<?php 
if (!isset($_SESSION)) {
  session_start();
}

require_once('../param/param.php'); 
require_once('../functions/function.php');
require('../param/site.php'); 
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO message (sndid, rcvid, subject, body, `date`, status) VALUES (%s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['sender'], "text"),
                       GetSQLValueString($_POST['reciever'], "text"),
                       GetSQLValueString($_POST['subject'], "text"),
                       GetSQLValueString($_POST['body'], "text"),
                       GetSQLValueString($_POST['date'], "text"),
                       GetSQLValueString($_POST['status'], "text"));

  mysql_select_db($database_tams, $tams);
  $Result1 = mysql_query($insertSQL, $tams) or die(mysql_error());

  $insertGoTo = "/tams/message/index.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
}


if(getAccess()> 5){
	$colname_rsmsg = "-1";
	if (isset($_SESSION['stid'])) {
	  $colname_rsmsg = $_SESSION['stid'];
	}
}else{
	$colname_rsmsg = "-1";
	if (isset($_SESSION['lid'])) {
	  $colname_rsmsg = $_SESSION['lid'];
	}
	}
$colname_rsmsg = "-1";
if (isset($_SESSION['phone'])) {
  $colname_rsmsg = $_SESSION['phone'];
}
mysql_select_db($database_tams, $tams);
$query_rsmsg = sprintf("SELECT * FROM message WHERE rcvid = %s ORDER BY `date` ASC", GetSQLValueString($colname_rsmsg, "text"));
$rsmsg = mysql_query($query_rsmsg, $tams) or die(mysql_error());
$row_rsmsg = mysql_fetch_assoc($rsmsg);
$totalRows_rsmsg = mysql_num_rows($rsmsg);

$colname_rsmsgrecv = "-1";
if (isset($_SESSION['phone'])) {
  $colname_rsmsgrecv = $_SESSION['phone'];
}
mysql_select_db($database_tams, $tams);
$query_rsmsgrecv = sprintf("SELECT * FROM message WHERE sndid = %s", GetSQLValueString($colname_rsmsgrecv, "text"));
$rsmsgrecv = mysql_query($query_rsmsgrecv, $tams) or die(mysql_error());
$row_rsmsgrecv = mysql_fetch_assoc($rsmsgrecv);
$totalRows_rsmsgrecv = mysql_num_rows($rsmsgrecv);
 
$colname_rsmsgviw = "-1";
if (isset($_GET['msgid'])) {
  $colname_rsmsgviw = $_GET['msgid'];
}
mysql_select_db($database_tams, $tams);
$query_rsmsgviw = sprintf("SELECT * FROM message WHERE msgid = %s", GetSQLValueString($colname_rsmsgviw, "int"));
$rsmsgviw = mysql_query($query_rsmsgviw, $tams) or die(mysql_error());
$row_rsmsgviw = mysql_fetch_assoc($rsmsgviw);
$totalRows_rsmsgviw = mysql_num_rows($rsmsgviw);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/template.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
    <title><?php echo $university ?></title>
    <!-- InstanceEndEditable -->
<link href="../css/template.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
    <script src="../SpryAssets/SpryTabbedPanels.js" type="text/javascript"></script>
<link href="../SpryAssets/SpryTabbedPanels.css" rel="stylesheet" type="text/css" />
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
        <td><!-- InstanceBeginEditable name="pagetitle" -->Messages<!-- InstanceEndEditable --></td>
      </tr>
    </table>
  </div>
<div class="sidebar1">
   
    <?php include '../include/sidemenu.php'; ?>
  </div> 
  <div class="content"><!-- InstanceBeginEditable name="maincontent" -->
              <p>&nbsp;</p>
<div id="TabbedPanels1" class="TabbedPanels">
                <ul class="TabbedPanelsTabGroup">
                  <li class="TabbedPanelsTab" tabindex="0">Inbox</li>
                  <li class="TabbedPanelsTab" tabindex="0">Compose </li>
                  <li class="TabbedPanelsTab" tabindex="0">outbox</li>
                </ul>
                <div class="TabbedPanelsContentGroup">
                  <div class="TabbedPanelsContent" id="inbox">
                    <p>Inbox : </p>
                    <table width="87%"  border="0" >
                      <thead>
                        <tr>
                          <th width="18">S/n</th>
                          <th width="100">Sender</th>
                          <th width="100">Subject</th>
                          <th width="86">Date</th>
                          <th width="91">Action</th>
                        </tr>
                      </thead>
                      <tbody    >
                        <?php $i=1; do { ?>
                        <tr <?php if($row_rsmsg['status']=='Unread'){ echo 'style="font-weight:bold" ';}?>>
                          <td><?php echo $i;?></td>
                          <td><?php echo $row_rsmsg['sndid']; ?></td>
                          <td><a href="/temp2/message/read.php?msgid=<?php echo $row_rsmsg['msgid']; ?>"><?php echo $row_rsmsg['subject']; ?></a></td>
                          <td><?php echo $row_rsmsg['date']; ?></td>
                          <th>&nbsp;</th>
                        </tr>
                        <?php $i++;
	 } while ($row_rsmsg = mysql_fetch_assoc($rsmsg)); ?>
                      </tbody>
                    </table>
                    <p>&nbsp;</p>
                  </div>
                  <div class="TabbedPanelsContent" id="compose">
                    <p>Compose New Message</p>
                    <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1" role="form">
                  <table width="494" align="center">
                      <tr>
                        <td width="486" align="center"><input name="reciever" type="text" size="70"  placeholder="To :.."/></td>
                      </tr>
                      <tr>
                        <td align="center"><input name="subject" type="text" size="70"  placeholder="subject :.."/></td>
                      </tr>
                      <tr>
                        <td align="center"><textarea name="body" cols="70" rows="15" ></textarea></td>
                      </tr>
                      <tr>
                        <td align="center"><input type="submit" name="Send" value="send" />
                        <input type="button" name="Save" value="Save to draft" /></td>
                      </tr>
                      <input type="hidden" name="sender" value="<?php echo $_SESSION['phone']?>"/>
                      <input type="hidden" name="date" value="<?php echo date('F d,Y h:i:s')?>"/>
                      <input type="hidden" name="status" value="<?php echo 'Unread'?>"/>
                      <input type="hidden" name="MM_insert" value="form1" />
                      <input type="hidden" name="MM_update" value="form1" />
                       </table>
                    </form>
                   
                  </div>
                  <div class="TabbedPanelsContent">
                    <p>Your Sent Message</p>
                    <table width="87%"  border="0" align="center" >
                      <thead>
                        <tr>
                          <th width="18">S/n</th>
                          <th width="100">Reciver</th>
                          <th width="100">Subject</th>
                          <th width="96">Date</th>
                          <th width="96">Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php $i=1; do { ?>
                        <tr>
                          <td><?php echo $i; ?></td>
                          <td><?php echo $row_rsmsgrecv['rcvid']; ?></td>
                          <td><?php echo $row_rsmsgrecv['subject']; ?></td>
                          <td><?php echo $row_rsmsgrecv['date']; ?></td>
                          <td>&nbsp;</td>
                        </tr>
                        <?php $i++;
				} while ($row_rsmsgrecv = mysql_fetch_assoc($rsmsgrecv)); ?>
                      </tbody>
                    </table>
                    <p>&nbsp;</p>
                  </div>
                </div>
              </div>
              
              
              <p>&nbsp;</p></p>
  <script type="text/javascript">
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
//-->
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
mysql_free_result($rsmsg);

mysql_free_result($rsmsgrecv);
?>
