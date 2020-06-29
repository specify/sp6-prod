<?php
include ("/etc/myauth.php");
date_default_timezone_set('America/Chicago');

function encodeToUtf8($string) {
	return mb_convert_encoding($string, "UTF-8", mb_detect_encoding($string, "UTF-8, ISO-8859-1, ISO-8859-15", true));
}


if ($_POST != '') {

	$cnt = 0;
	foreach (array_keys($_POST) as $p) {
		$cnt++;
	}

	if ($cnt > 0)
	{
		$mysqli = new mysqli($mysql_hst, $mysql_usr, $mysql_pwd, "exception");

		if ($mysqli->connect_errno) {
			die("failed to connect to mysql" . $mysqli->connect_error);
		}


		$Timestamp   = date("Y-m-d H:i:s");
		$TaskName    = encodeToUtf8($_POST['task_name']);
		$Title       = encodeToUtf8($_POST['title']);
		$Bug         = encodeToUtf8($_POST['bug']);
		$Comments    = encodeToUtf8($_POST['comments']);
		$StackTrace  = encodeToUtf8($_POST['stack_trace']);
		$ClassName   = encodeToUtf8($_POST['class_name']);
		$Id          = encodeToUtf8($_POST['id']);
		$OSName      = encodeToUtf8($_POST['os_name']);
		$OSVersion   = encodeToUtf8($_POST['os_version']);
		$JavaVersion = encodeToUtf8($_POST['java_version']);
		$JavaVendor  = encodeToUtf8($_POST['java_vendor']);
		$UserName    = encodeToUtf8($_POST['user_name']);
		$IP          = encodeToUtf8($_POST['ip']);
		$AppVersion  = encodeToUtf8($_POST['app_version']);
		$Collection  = encodeToUtf8($_POST['collection']);
		$Discipline  = encodeToUtf8($_POST['discipline']);
		$Division    = encodeToUtf8($_POST['division']);
		$Institution = encodeToUtf8($_POST['institution']);

		if (!isset($IP) || strlen($IP) == 0) {
			$IP = array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
		}


		$updateStr = $mysqli->prepare(
			"INSERT INTO exception ( " .
			"TimestampCreated,TaskName,Title,Bug,Comments,Id,StackTrace,ClassName,OSName,OSVersion,JavaVersion," .
			"JavaVendor,UserName,IP,AppVersion,Collection,Discipline,Division,Institution) " .
			"VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
		);

		$updateStr->bind_param(
			"sssssssssssssssssss",
			$Timestamp, $TaskName, $Title, $Bug, $Comments, $Id, $StackTrace, $ClassName, $OSName, $OSVersion,
			$JavaVersion, $JavaVendor, $UserName, $IP, $AppVersion, $Collection, $Discipline, $Division, $Institution
		);

		if(!$updateStr->execute()) throw new Exception($mysqli->error);
		$mysqli->close();
	}
	echo "ok";

} else {
	echo "No arguments!<br>";
}

?>