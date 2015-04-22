<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
$hostname_tams = "localhost";
$database_tams = "tamsonline";
$username_tams = "root";
$password_tams = "";
$tams = mysql_pconnect($hostname_tams, $username_tams, $password_tams) or trigger_error(mysql_error(),E_USER_ERROR); 
?>