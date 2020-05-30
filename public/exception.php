<?php


const DATABASE = 'feedback';
require_once('components/header.php');

$Timestamp = date("Y-m-d H:i:s");
$TaskName = encodeToUtf8($_POST['task_name']);
$Title = encodeToUtf8($_POST['title']);
$Bug = encodeToUtf8($_POST['bug']);
$Comments = encodeToUtf8($_POST['comments']);
$StackTrace = encodeToUtf8($_POST['stack_trace']);
$ClassName = encodeToUtf8($_POST['class_name']);
$Id = encodeToUtf8($_POST['id']);
$OSName = encodeToUtf8($_POST['os_name']);
$OSVersion = encodeToUtf8($_POST['os_version']);
$JavaVersion = encodeToUtf8($_POST['java_version']);
$JavaVendor = encodeToUtf8($_POST['java_vendor']);
$UserName = encodeToUtf8($_POST['user_name']);
$IP = encodeToUtf8($_POST['ip']);
$AppVersion = encodeToUtf8($_POST['app_version']);
$Collection = encodeToUtf8($_POST['collection']);
$Discipline = encodeToUtf8($_POST['discipline']);
$Division = encodeToUtf8($_POST['division']);
$Institution = encodeToUtf8($_POST['institution']);

if(!isset($IP) || strlen($IP) == 0)
	$IP = $ip_address;


$updateStr = $mysqli->prepare(
	"INSERT INTO `exception` ( " .
	"`timestampcreated`,`taskname`,`title`,`bug`,`comments`,`id`,`stacktrace`,`classname`,`osname`,`osversion`,`javaversion`," .
	"`javavendor`,`username`,`ip`,`appversion`,`collection`,`discipline`,`division`,`institution`) " .
	"VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

$updateStr->bind_param(
	"sssssssssssssssssss",
	$Timestamp,
	$TaskName,
	$Title,
	$Bug,
	$Comments,
	$Id,
	$StackTrace,
	$ClassName,
	$OSName,
	$OSVersion,
	$JavaVersion,
	$JavaVendor,
	$UserName,
	$IP,
	$AppVersion,
	$Collection,
	$Discipline,
	$Division,
	$Institution
);

if(!$updateStr->execute())
	die($mysqli->error);

require_once('components/footer.php');