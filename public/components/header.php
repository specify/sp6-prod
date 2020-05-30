<?php

if(count($_POST) == 0)
	die('No arguments!<br>');

require_once('mysql.php');
date_default_timezone_set('America/Chicago');

function encodeToUtf8($string){

	if(is_array($string))
		$string = implode('',$string);

	return mb_convert_encoding($string, "UTF-8", mb_detect_encoding($string, "UTF-8, ISO-8859-1, ISO-8859-15", TRUE));

}

$ip_address = array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)
	? $_SERVER['HTTP_X_FORWARDED_FOR']
	: $_SERVER['REMOTE_ADDR'];