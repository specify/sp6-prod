<?php

if(array_key_exists('ip', $_GET) && preg_match('/(\d{1,3}\.){3}\d{1,3}/',$_GET['ip']))
	echo json_decode(file_get_contents("http://ip-api.com/json/".$_GET['ip']."?fields=org"),true)['org'];//country,regionName,city,org,reverse