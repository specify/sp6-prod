<?php

const DATABASE = 'stats';
require_once('components/header.php');

// for testing
/*$_POST['id']                 = "123-456-789";
$_POST['Type']               = 0;
$_POST['Collection_number']  = "10";
$_POST['Institution_number'] = "11";
$_POST['user_name']          = "12";

$ip_address = "1.1.1.1";
*/


$now = time();
$currentDateTime = date("Y-m-d H:i:s");

$tr_id = $_POST['id'];
$type = $_POST['Type'];
$column_number = $_POST['Collection_number'];
$institution_number = $_POST['Institution_number'];
$username = $_POST['user_name'];

$unknownCount = 0;
$engagedMinutes = 0;

// Now Insert or Update an activity record
$query = $mysqli->prepare("SELECT `trackactivityid`,`logindate`,`logoutdate`,`engagedminutes`,`unknowncount` FROM `trackactivity` WHERE `id` = ?");
$query->bind_param('s', $tr_id);
if(!$query->execute())
	die($mysqli->error);
$result = $query->get_result();

$doInsert = 1;
if($result){

	$row = $result->fetch_row();
	$result->close();

	if($row){

		$doInsert = 0;
		$trackId = $row[0];
		$loginDT = $row[1];
		$logoutDT = $row[2];
		$engagedMinutes = $row[3];
		$unknownCount = $row[4];

		if($type == 0){ // Login

			if($logoutDT != NULL) // Never logged out?
				$unknownCount++;

			$loginDTStr = $currentDateTime;
			$logoutDTStr = NULL;
			$engagedMinutes = 0;

		}

		else { // logging out

			$loginDTStr = NULL;
			$logoutDTStr = NULL;
			if($loginDT == NULL) // Never logged in, strange?
				$unknownCount++;
			else {

				$loginTime = strtotime($loginDT);

				//echo "loginDT-> [" . $loginDT . "] $loginTime " . date("m/d/Y",strtotime($loginDT)) . "\n";
				if($loginTime != NULL){
					$deltaTime = round((($now - $loginTime) / 60) + 0.5);
					//echo "now-> [" . $now . "] loginTime-> [" . $loginTime . "] deltaTime-> [" . $deltaTime . "]\n";

					if($deltaTime > 0)
						$engagedMinutes += $deltaTime;
				}

			}
		}

		$updateStr = $mysqli->prepare("UPDATE `trackactivity` SET `logindate`=?" . // $loginDTStr .
		                              ", `logoutdate`=?" .                               // $logoutDTStr .
		                              ", `engagedminutes`=?" .                           //$engagedMinutes .
		                              ", `unknowncount`=?" .                             //$unknownCount .
		                              " WHERE `trackactivityid` = ?"                     // . $trackId;
		);

		$updateStr->bind_param('ssdii', $loginDTStr, $logoutDTStr, $engagedMinutes, $unknownCount, $trackId);
		if(!$updateStr->execute())
			die($mysqli->error);
	}
}

if($doInsert){
	$updateStr = $mysqli->prepare("INSERT INTO `trackactivity` (`logindate`, `logoutdate`, `engagedminutes`, `id`, `ip`, `instreg`, `collreg`, `username`, `unknowncount`) VALUES( ?, NULL, 0, ?, ?, ?, ?, ?, 0)");
	$updateStr->bind_param('ssssss', $currentDateTime, $tr_id, $ip_address, $institution_number, $column_number, $username);
	if(!$updateStr->execute())
		die($mysqli->error);
}

// insert an activity entry record
$updateStr = $mysqli->prepare("INSERT INTO `trackactentry` (`activitydatetime`, `type`, `engagedminutes`, `id`, `ip`, `instreg`, `collreg`, `username`) VALUES(?, ?, ?, ?, ?, ?, ?, ?)");
$updateStr->bind_param('ssisssss', $currentDateTime, $type, $engagedMinutes, $tr_id, $ip_address, $institution_number, $column_number, $username);
if(!$updateStr->execute())
	die($mysqli->error);

require_once('components/footer.php');