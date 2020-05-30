<?php

const DATABASE = 'stats';
require_once('components/header.php');

# echo  "ip=" . $_SERVER['REMOTE_ADDR'] . "<BR>";

$Id = $_POST['id'];

if(strcmp($_POST["app_version"], "6.4.13") < 0){
	echo "It is highly recommended that you upgrade to the latest release.";

	return;
}

#if (strcmp($_POST["java_version"], "1.6.0_18") != 0 ) {
#echo "NOMSG";
#return;
#}
#if (1) return;


$query = "SELECT `messageid`,`message` FROM `messages` WHERE `type` = 1";
$result = $mysqli->query($query);

if($result){
	$row = $result->fetch_row();
	if($row){
		echo $row[1];
		$mysqli->close();

		return;
	}
}

$query = $mysqli->prepare("SELECT `message` FROM `messages` WHERE `type` = 0 AND `singleuserident` = ?");
$query->bind_param("s", $Id);
if(!$query->execute())
	die($mysqli->error);

$result = $query->get_result();
if($result){

	$row = $result->fetch_row();
	$result->close();
	if($row){
		$convInfoId = $row[0];
		$query = $mysqli->print("SELECT Message FROM messages WHERE MessagesID = ?");
		$query->bind_param("s", $Id);
		if(!$query->execute())
			die($mysqli->error);
		$result = $query->get_result();
		if($result){
			$row = $result->fetch_row();
			$result->close();
			if($row){
				$convInfoId = $row[0];
			}
		}

	}
	else
		die('couldn\'t find the row with the highest key\n');
}
else
	die('couldn\'t find the highest key\n');


require_once('components/footer.php');