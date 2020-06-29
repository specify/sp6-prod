<?php
ini_set("memory_limit", "500M");
include ("/etc/myauth.php");
date_default_timezone_set('America/Chicago');
$myFile = "/home/anhalt/track.dat";

function encodeToUtf8($val) {
	$string = is_array($val) ? implode($val) : $val;
	return mb_convert_encoding($string, "UTF-8", mb_detect_encoding($string, "UTF-8, ISO-8859-1, ISO-8859-15", true));
}


if ($_POST != '') {
	$ipaddr = array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
	$cnt = 0;
	foreach (array_keys($_POST) as $p) {
		$cnt++;
	}

	$dateTime =  "date=" . date("y/m/d") ." " . date("H:i:s") . "\n";
	$data = "---------------\n" . $dateTime;
	$data = $data . "ip=" . $ipaddr . "\n";
	if ($cnt == 0) {
		echo "No arguments!<br>";
	} else {
		foreach (array_keys($_POST) as $p) {
			$data = $data . "$p=$_POST[$p]\n";
		}
	}

	$fh = fopen($myFile, 'a') or die("can't open file");
	fwrite($fh, $data);
	fclose($fh);

	if ($cnt > 0)
	{
		$mysqli = new mysqli($mysql_hst, $mysql_usr, $mysql_pwd, "stats");

		if ($mysqli->connect_errno) {
			die("failed to connect to mysql" . $mysqli->connect_error);
		}

		$tr_id = $_POST['id'];
		$query = $mysqli->prepare("SELECT TrackID, CountAmt FROM track WHERE Id = ?");
		$query->bind_param("s", $tr_id);
		if(!$query->execute()) throw new Exception($mysqli->error);

		$rg_number    = "";
		$numStatsKeys = array();
		$doInsert     = 1;
		$result       = $query->get_result();
		if ($result) {
			$row = $result->fetch_row();
			$result->close();
			if ($row) {
				$doInsert = 0;
				$trackId  = $row[0];
				$count    = $row[1] + 1;
				$timestampModified = date("Y-m-d H:i:s");
				$updateStr = $mysqli->prepare("UPDATE track SET CountAmt=?, TimestampModified=?, IP=? WHERE TrackID = ?");
				$updateStr->bind_param("issi", $count, $timestampModified, $ipaddr, $trackId);
				if(!$updateStr->execute()) throw new Exception($mysqli->error);

				foreach (array_keys($_POST) as $p) {

					$doItemInsert = 1;

					if (substr($p, 0, 4) == "num_") {
						$numStatsKeys[] = $p;
					}

					if ($p == "Collection_number") {
						$rg_number = $_POST[$p];
					}

					$valStr = encodeToUtf8($_POST[$p]);

					$query = $mysqli->prepare("SELECT TrackItemID FROM trackitem WHERE TrackID = ? AND Name = ?");
					$query->bind_param("is", $trackId, $p);
					if(!$query->execute()) throw new Exception($mysqli->error);
					$result = $query->get_result();
					if ($result) {
						$row = $result->fetch_row();
						$result->close();
						if ($row)
						{
							$doItemInsert = 0;
							$trackItemId  = $row[0];
							if (strlen($valStr) && is_numeric($valStr) && !stripos($p, "_number", 0))
							{
								$updateStr = $mysqli->prepare("UPDATE trackitem SET CountAmt=?, Value=NULL WHERE TrackItemID = ?");
								$updateStr->bind_param("ii", $valStr, $trackItemId);
								if(!$updateStr->execute()) throw new Exception($mysqli->error);
							} else {
								$updateStr = $mysqli->prepare("UPDATE trackitem SET Value=?, CountAmt=NULL WHERE TrackItemID = ?");
								$updateStr->bind_param("si", $valStr, $trackItemId);
								if(!$updateStr->execute()) throw new Exception($mysqli->error);
							}
						}
					}

					if ($doItemInsert) {
						if (strlen($valStr) && is_numeric($valStr) && !stripos($p, "_number", 0))
						{
							$updateStr = $mysqli->prepare("INSERT INTO trackitem (TrackID, Name, Value, CountAmt) VALUES (?,?,NULL,?)");
							$updateStr->bind_param("isi", $trackId, $p, $valStr);
							if(!$updateStr->execute()) throw new Exception($mysqli->error);
						} else {
							$updateStr = $mysqli->prepare("INSERT INTO trackitem (TrackID, Name, Value, CountAmt) VALUES (?,?,?, NULL)");
							$updateStr->bind_param("iss", $trackId, $p, $valStr);
							if(!$updateStr->execute()) throw new Exception($mysqli->error);
						}
					}
				}
			}
		}

		if ($doInsert) {
			$dateStr = date("Y-m-d H:i:s");
			$updateStr = $mysqli->prepare("INSERT INTO track (Id, TimestampCreated, CountAmt, IP) VALUES(?, ?, 1, ?)");
			$updateStr->bind_param("sss", $tr_id, $dateStr, $ipaddr);
			if(!$updateStr->execute()) throw new Exception($mysqli->error);

			$query = "SELECT TrackID FROM track ORDER BY TrackID DESC LIMIT 0,1";
			$result2 = $mysqli->query($query);
			if ($result)
			{
				$row2 = $result2->fetch_row();
				$result2->close();
				if ($row2) {
					$trackId = $row2[0];

					foreach (array_keys($_POST) as $p) {
						$valStr = encodeToUtf8($_POST[$p]);

						if (substr($p, 0, 4) == "num_") {
							$numStatsKeys[] = $p;
						}

						if ($p == "Collection_number") {
							$rg_number = $_POST[$p];
						}

						if (strlen($valStr) && is_numeric($valStr) && !stripos($p, "_number", 0))
						{
							$updateStr = $mysqli->prepare(
								"INSERT INTO trackitem (TrackID, Name, Value, CountAmt) VALUES (?, ?, NULL, ?)"
							);
							$updateStr->bind_param("isi", $trackId, $p, $valStr);
							if(!$updateStr->execute()) throw new Exception($mysqli->error);
						} else {
							$updateStr = $mysqli->prepare(
								"INSERT INTO trackitem (TrackID, Name, Value, CountAmt) VALUES (?, ?, ?, NULL)"
							);
							$updateStr->bind_param("iss", $trackId, $p, $valStr);
							if(!$updateStr->execute()) throw new Exception($mysqli->error);
						}
					}
				} else {
					echo "couldn't find the row with the highest key\n";
				}
			} else {
				echo "`couldn't find the highest key\n";
			}
		}

		if (count($numStatsKeys) > 0) {

			$query = $mysqli->prepare("SELECT RegisterID FROM register WHERE RegNumber = ?");
			$query->bind_param("s", $rg_number);

			if(!$query->execute()) throw new Exception($mysqli->error);
			$result = $query->get_result();

			if ($result) {
				$row = $result->fetch_row();
				$result->close();

				if ($row) {
					$doInsert = 0;
					$registerId  = $row[0];
					$count    = $row[1] + 1;

					foreach ($numStatsKeys as $p) {

						$doItemInsert = 1;

						$query = $mysqli->prepare("SELECT RegisterItemID FROM registeritem WHERE RegisterID = ? AND Name = ?");
						$query->bind_param("is", $registerId, $p);
						if(!$query->execute()) throw new Exception($mysqli->error);

						$result = $query->get_result();
						if ($result) {
							$row = $result->fetch_row();
							$result->close();
							if ($row)
							{
								$doItemInsert    = 0;
								$registerItemId  = $row[0];
								$updateStr = $mysqli->prepare("UPDATE registeritem SET CountAmt=? WHERE RegisterItemID = ?");
								$updateStr->bind_param("si", $_POST[$p], $registerItemId);
								if(!$updateStr->execute()) throw new Exception($mysqli->error);
							}
						}

						if ($doItemInsert) {
							$updateStr = $mysqli->prepare(
								"INSERT INTO registeritem (RegisterID, Name, Value, CountAmt) VALUES (?, ?, NULL, ?)"
							);
							$updateStr->bind_param("isi", $registerId, $p, $_POST[$p]);
							if(!$updateStr->execute()) throw new Exception($mysqli->error);
						}
					}
				}
			}
		}

		$mysqli->close();
	}
	echo "ok";

} else {
	echo "No arguments!<br>";
}

?>