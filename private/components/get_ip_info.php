<?php

if(!array_key_exists('ip', $_GET) || !preg_match('/^(?:(?:\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])$/',$_GET['ip']))
	exit('Error: Wrong URL');

$request_url = "http://ip-api.com/json/".$_GET['ip']."?fields=org";//country,regionName,city,org,reverse
$data = file_get_contents($request_url);
$data = json_decode($data,true);

if(!is_array($data) || !array_key_exists('org',$data))
	exit('Error: wrong response received');

echo $data['org'];