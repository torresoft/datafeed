<?php
	$hostdb="localhost";
	$userdb="root";
	$passdb="KernelPass530+";
	$site_dbase="datafeed2021";
	$node_ip = "192.168.101.86";
	$protocol = "http";
	$node_port = "3000";
	define("SITE_HOST",$hostdb);
	define("SITE_USER",$userdb);
	define("SITE_PASS",$passdb);
	define("SITE_DB",$site_dbase);
	define("SOCKET_SVR",$protocol."://".$node_ip.":".$node_port."/socket.io/socket.io.js");
	define("SERVER_URL",$protocol."://".$node_ip.":".$node_port."/datafeed");
?>