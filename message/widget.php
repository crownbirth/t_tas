<?php  
if (!isset($_SESSION)) {
  session_start();
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

$colname_msgwdgt = "-1";
if (isset($_SESSION['phone'])) {
  $colname_msgwdgt = $_SESSION['phone'];
}
mysql_select_db($database_tams, $tams);
$query_msgwdgt = sprintf("SELECT * FROM message WHERE rcvid = %s AND status=%s" , GetSQLValueString($colname_msgwdgt, "text"),GetSQLValueString("Unread", "text"));
$msgwdgt = mysql_query($query_msgwdgt, $tams) or die(mysql_error());
$row_msgwdgt = mysql_fetch_assoc($msgwdgt);
$totalRows_msgwdgt = mysql_num_rows($msgwdgt);
?>
 
 
 <ul class="nav navbar-nav navbar-right navbar-user">
            <li class="dropdown messages-dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-envelope"></i> Messages <span class="badge"><?php echo $totalRows_msgwdgt;?></span> <b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li class="dropdown-header"><?php echo $totalRows_msgwdgt."New Message(s)";?> </li>
                <li class="message-preview">
                  <a href="#">
                    <span class="avatar"><img src="http://placehold.it/50x50"></span>
                    <span class="name"><?php echo "From: ".$row_msgwdgt['sndid']; ?></span>
                    <span class="message"> <?php echo "Subject: ".$row_msgwdgt['subject']; ?></span>
                    <span class="time"><i class="fa fa-clock-o"></i> <?php echo"Date: ".$row_msgwdgt['date']; ?></span>
                  </a>
                </li>
                <li class="divider"></li>
                <li><a href="/temp2/message/index.php">View Inbox <span class="badge"><?php echo $totalRows_msgwdgt;?></span></a></li>
              </ul>
              <?php
mysql_free_result($msgwdgt);
?>
            