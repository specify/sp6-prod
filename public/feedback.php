<?php

const DATABASE = 'feedback';
require_once('components/header.php');

$Timestamp = date("Y-m-d H:i:s");
$Subject = encodeToUtf8($_POST['title']);
$Issue = encodeToUtf8($_POST['bug']);
$Component = encodeToUtf8($_POST['task_name']);
$Comments = encodeToUtf8($_POST['comments']);
$Id = encodeToUtf8($_POST['id']);
$OSName = encodeToUtf8($_POST['os_name']);
$OSVersion = encodeToUtf8($_POST['os_version']);
$JavaVersion = encodeToUtf8($_POST['java_version']);
$JavaVendor = encodeToUtf8($_POST['java_vendor']);
$AppVersion = encodeToUtf8($_POST['app_version']);
$Collection = encodeToUtf8($_POST['collection']);
$Discipline = encodeToUtf8($_POST['discipline']);
$Division = encodeToUtf8($_POST['division']);
$Institution = encodeToUtf8($_POST['institution']);

$updateStr = $mysqli->prepare(
	"INSERT INTO `feedback` ( " .
	"`timestampcreated`,`subject`,`component`,`issue`,`comments`,`id`,`osname`,`osversion`,`javaversion`," .
	"`javavendor`,`appversion`,`collection`,`discipline`,`division`,`institution`) " .
	"VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

$updateStr->bind_param(
	"sssssssssssssss",
	$Timestamp,
	$Subject,
	$Component,
	$Issue,
	$Comments,
	$Id,
	$OSName,
	$OSVersion,
	$JavaVersion,
	$JavaVendor,
	$AppVersion,
	$Collection,
	$Discipline,
	$Division,
	$Institution
);

if(!$updateStr->execute())
	die($mysqli->error);

require_once('components/footer.php');