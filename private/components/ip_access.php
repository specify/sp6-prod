<?php


$whitelist_ip_list = file(WHITELIST_IP_LOCATION);

if(!BLOCK_EXTERNAL_IPS || in_array($_SERVER['REMOTE_ADDR'],$whitelist_ip_list)){
	$ip_list = fopen(IPS_LOG_LOCATION, "a") or die("Unable to open file!");
	fwrite($ip_list, $_SERVER['REMOTE_ADDR']."\n");
	fclose($ip_list);
}

else {
	header('HTTP/1.0 403 Forbidden');
	echo 'Your IP address is not whitelisted. Please notify the administrator if you need to get access';
	$ip_list = fopen(BLOCKED_IPS_LOG_LOCATION, "a") or die("Unable to open file!");
	fwrite($ip_list, $_SERVER['REMOTE_ADDR']."\n");
	fclose($ip_list);
	exit();
}