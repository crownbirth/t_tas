<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL" username = gbamilago password = egbamilagojo
# HTTP="true"
$hostname_conn_burmas = "localhost";
$database_conn_burmas = "tasueded_cepep";
$username_conn_burmas = "tasueded_ict";
$password_conn_burmas = "shawns3606";
$conn_burmas = mysql_pconnect($hostname_conn_burmas, $username_conn_burmas, $password_conn_burmas) or trigger_error(mysql_error(),E_USER_ERROR); 
?>