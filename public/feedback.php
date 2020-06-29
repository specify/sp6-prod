<?php
include ("/etc/myauth.php");
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
		$mysqli = new mysqli($mysql_hst, $mysql_usr, $mysql_pwd, "feedback");

		if ($mysqli->connect_errno) {
			die("failed to connect to mysql" . $mysqli->connect_error);
		}

		$Timestamp   = date("Y-m-d H:i:s");
		$Subject     = encodeToUtf8($_POST['title']);
		$Issue       = encodeToUtf8($_POST['bug']);
		$Component   = encodeToUtf8($_POST['task_name']);
		$Comments    = encodeToUtf8($_POST['comments']);
		$Id          = encodeToUtf8($_POST['id']);
		$OSName      = encodeToUtf8($_POST['os_name']);
		$OSVersion   = encodeToUtf8($_POST['os_version']);
		$JavaVersion = encodeToUtf8($_POST['java_version']);
		$JavaVendor  = encodeToUtf8($_POST['java_vendor']);
		$AppVersion  = encodeToUtf8($_POST['app_version']);
		$Collection  = encodeToUtf8($_POST['collection']);
		$Discipline  = encodeToUtf8($_POST['discipline']);
		$Division    = encodeToUtf8($_POST['division']);
		$Institution = encodeToUtf8($_POST['institution']);

		$updateStr = $mysqli->prepare(
			"INSERT INTO feedback ( " .
			"TimestampCreated,Subject,Component,Issue,Comments,Id,OSName,OSVersion,JavaVersion," .
			"JavaVendor,AppVersion,Collection,Discipline,Division,Institution) " .
			"VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
		);
		$updateStr->bind_param(
			"sssssssssssssss",
			$Timestamp, $Subject, $Component, $Issue, $Comments, $Id, $OSName, $OSVersion, $JavaVersion,
			$JavaVendor, $AppVersion, $Collection, $Discipline, $Division, $Institution
		);
		if(!$updateStr->execute()) throw new Exception($mysqli->error);
		$mysqli->close();
	}
	echo "ok";

} else {
	echo "No arguments!<br>";
}

?>