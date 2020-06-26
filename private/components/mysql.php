<?php

global $mysqli;

if(file_exists("/etc/myauth.php"))
	include("/etc/myauth.php");

if(!isset($mysql_hst))
	$mysql_hst = '127.0.0.1';

if(!isset($mysql_usr))
	$mysql_usr = 'root';

if(!isset($mysql_pwd))
	$mysql_pwd = 'root';

if(!isset($database) && !defined('DATABASE'))
	exit('DATABASE constant is not defined');
elseif(!isset($database))
	$database = DATABASE;


$mysqli = new mysqli($mysql_hst, $mysql_usr, $mysql_pwd, $database);

if ($mysqli->connect_error)
	die("Connection failed: " . $mysqli->connect_error);