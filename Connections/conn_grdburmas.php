<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL" username = gbamilago password = egbamilagojo
# HTTP="true"
$hostname_conn_burmas = "localhost";
$database_conn_burmas = "tasueded_gradportal";
$username_conn_burmas = "tasueded_grad";
$password_conn_burmas = "123gradportal456";
$conn_grdburmas = mysql_pconnect($hostname_conn_burmas, $username_conn_burmas, $password_conn_burmas) or trigger_error(mysql_error(),E_USER_ERROR); 
?>