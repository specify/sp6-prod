<?php
include ("/etc/myauth.php");

if ($_POST != '') {

	$cnt = 0;
	foreach (array_keys($_POST) as $p) {
		$cnt++;
	}

	# echo  "ip=" . $_SERVER['REMOTE_ADDR'] . "<BR>";

	$Id = $_POST['id'];

	if ($cnt > -1)
	{
		if (strcmp($_POST["app_version"], "6.4.13") < 0 ) {
			echo "It is highly recommended that you upgrade to the latest release.";
			return;
		}

		#if (strcmp($_POST["java_version"], "1.6.0_18") != 0 ) {
		#echo "NOMSG";
		#return;
		#}
		#if (1) return;

		$mysqli = new mysqli($mysql_hst, $mysql_usr, $mysql_pwd, "stats");

		if ($mysqli->connect_errno) {
			die("failed to connect to mysql" . $mysqli->connect_error);
		}

		$query = "SELECT MessageID,Message FROM messages WHERE Type = 1";
		$result = $mysqli->query($query);
		if ($result)
		{
			$row = $result->fetch_row();
			if ($row) {
				echo $row[1];
				$mysqli->close();
				return;
			}
		}

		$query = $mysqli->prepare("SELECT Message FROM messages WHERE Type = 0 AND SingleUserIdent = ?");
		$query->bind_param("s", $Id);
		if(!$query->execute()) throw new Exception($mysqli->error);
		$result = $query->get_result();
		if ($result)
		{
			$row = $result->fetch_row();
			$result->close();
			if ($row) {
				$convInfoId = $row[0];
				$query = $mysqli->print("SELECT Message FROM messages WHERE MessagesID = ?");
				$query->bind_param("s", $Id);
				if(!$query->execute()) throw new Exception($mysqli->error);
				$result = $query->get_result();
				if ($result)
				{
					$row = $result->fetch_row();
					$result->close();
					if ($row) {
						$convInfoId = $row[0];
					}
				} else {
				}

			} else {
				#echo "couldn't find the row with the highest key\n";
			}
		} else {
			echo "`couldn't find the highest key\n";
		}
		$mysqli->close();
	}
	echo "NOMSG";

} else {
	echo "No arguments!<br>";
}

?>