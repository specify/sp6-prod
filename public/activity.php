<?php
include ("/etc/myauth.php");
date_default_timezone_set('America/Chicago');

// for testing
/*$_POST['id']                 = "123-456-789";
$_POST['Type']               = 0;
$_POST['Collection_number']  = "10";
$_POST['Institution_number'] = "11";
$_POST['user_name']          = "12";

$remoteIPAddr = "1.1.1.1";
*/
$remoteIPAddr = array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];


if ($_POST != '') {

	$cnt = 0;
	foreach (array_keys($_POST) as $p) {
		$cnt++;
	}

	if ($cnt == 0) {
		echo "No arguments!<br>";
	}

	if ($cnt > 0)
	{
		$mysqli = new mysqli($mysql_hst, $mysql_usr, $mysql_pwd, "stats");

		if ($mysqli->connect_errno) {
			die("failed to connect to mysql" . $mysqli->connect_error);
		}

		$now   = time();
		$currentDateTime = date("Y-m-d H:i:s");

		$tr_id   = $_POST['id'];
		$type    = $_POST['Type'];
		$colNum  = $_POST['Collection_number'];
		$instNum = $_POST['Institution_number'];
		$usrname = $_POST['user_name'];

		$unknownCount   = 0;
		$engagedMinutes = 0;

		// Now Insert or Update an activity record
		$query = $mysqli->prepare("SELECT TrackActivityID,LoginDate,LogoutDate,EngagedMinutes,UnknownCount FROM trackactivity WHERE Id = ?");
		$query->bind_param('s', $tr_id);
		if(!$query->execute()) throw new Exception($mysqli->error);
		$result = $query->get_result();

		$doInsert     = 1;
		if ($result) {
			$row = $result->fetch_row();
			$result->close();
			if ($row) {
				$doInsert       = 0;
				$trackId        = $row[0];
				$loginDT        = $row[1];
				$logoutDT       = $row[2];
				$engagedMinutes = $row[3];
				$unknownCount   = $row[4];

				//foreach ($row as $p) {
				//    echo $cnt . " => " . $p . "\n";
				//    $cnt++;
				//}

				if ($type == 0) // Login
				{
					if ($logoutDT != null) // Never logged out?
					{
						$unknownCount++;
					}
					$loginDTStr  = $currentDateTime;
					$logoutDTStr = null;
					$engagedMinutes = 0;

				} else // logging out
				{
					$loginDTStr  = null;
					$logoutDTStr = null;
					if ($loginDT == null) // Never logged in, strange?
					{
						$unknownCount++;
					} else
					{
						$loginTime = strtotime($loginDT);

						//echo "loginDT-> [" . $loginDT . "] $loginTime " . date("m/d/Y",strtotime($loginDT)) . "\n";
						if ($loginTime != null)
						{
							$deltaTime = round((($now - $loginTime) / 60) + 0.5);
							//echo "now-> [" . $now . "] loginTime-> [" . $loginTime . "] deltaTime-> [" . $deltaTime . "]\n";

							if ($deltaTime > 0)
							{
								$engagedMinutes += $deltaTime;
							}
						}

					}
				}

				$updateStr = $mysqli->prepare("UPDATE trackactivity SET LoginDate=?"  . // $loginDTStr .
				                              ", LogoutDate=?" .                        // $logoutDTStr .
				                              ", EngagedMinutes=?" .                    //$engagedMinutes .
				                              ", UnknownCount=?" .                      //$unknownCount .
				                              " WHERE TrackActivityID = ?");            // . $trackId;

				$updateStr->bind_param('ssdii', $loginDTStr, $logoutDTStr, $engagedMinutes, $unknownCount, $trackId);
				if(!$updateStr->execute()) throw new Exception($mysqli->error);
			}
		}

		if ($doInsert) {
			$updateStr = $mysqli->prepare("INSERT INTO trackactivity (LoginDate, LogoutDate, EngagedMinutes, ID, IP, InstReg, CollReg, Username, UnknownCount) VALUES( ?, NULL, 0, ?, ?, ?, ?, ?, 0)");
			$updateStr->bind_param('ssssss',  $currentDateTime, $tr_id, $remoteIPAddr, $instNum, $colNum, $usrname);
			if(!$updateStr->execute()) throw new Exception($mysqli->error);
		}

		// insert an activity entry record
		$updateStr = $mysqli->prepare("INSERT INTO trackactentry (ActivityDateTime, Type, EngagedMinutes, ID, IP, InstReg, CollReg, Username) VALUES(?, ?, ?, ?, ?, ?, ?, ?)");
		$updateStr->bind_param('ssisssss', $currentDateTime, $type, $engagedMinutes, $tr_id, $remoteIPAddr, $instNum, $colNum, $usrname);
		if(!$updateStr->execute()) throw new Exception($mysqli->error);
		$mysqli->close();
	}
	echo "ok";

} else {
	echo "No arguments!<br>";
}

?>