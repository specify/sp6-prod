<?php
include ("/etc/myauth.php");

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

	if ($cnt > 0)
	{
		$mysqli = new mysqli($mysql_hst, $mysql_usr, $mysql_pwd, "stats");

		if ($mysqli->connect_errno) {
			die("failed to connect to mysql" . $mysqli->connect_error);
		}

		$tr_id = $_POST['id'];
		$query = $mysqli->prepare("SELECT ColStatsID, CountAmt FROM colstats WHERE Id = ?");
		$query->bind_param('s', $tr_id);
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
				$colstatsId  = $row[0];
				$count    = $row[1] + 1;
				$timestampModified = date("Y-m-d H:i:s");
				$updateStr = $mysqli->prepare("UPDATE colstats SET CountAmt=?, TimestampModified=?, IP=? WHERE ColStatsID = ?");
				$updateStr->bind_param('issi', $count, $timestampModified, $ipaddr, $colstatsId);
				if(!$updateStr->execute()) throw new Exception($mysqli->error);
				$result = $updateStr->get_result();

				foreach (array_keys($_POST) as $p) {
					$valStr = encodeToUtf8($_POST[$p]);
					$doItemInsert = 1;
					$prefix       = substr($p, 0, 5);
					$isStat       = $prefix == "catby" || $prefix == "audit";
					//echo $p . " [" . $prefix . "]  isStat[" . $isStat . "]\n";

					if (substr($p, 0, 4) == "num_") {
						$numStatsKeys[] = $p;
					}

					if ($p == "Collection_number") {
						$rg_number = $_POST[$p];
					}

					$query = $mysqli->prepare("SELECT ColStatsItemID FROM colstatsitem WHERE ColStatsID = ? AND Name = ?");
					$query->bind_param('is', $colstatsId, $p);
					if(!$query->execute()) throw new Exception($mysqli->error);

					$result = $query->get_result();
					if ($result) {
						$row = $result->fetch_row();
						$result->close();
						if ($row)
						{
							$doItemInsert = 0;
							$colstatsItemId  = $row[0];

							if (strlen($valStr) && is_numeric($valStr) && !stripos($p, "_number", 0))
							{
								$updateStr = $mysqli->prepare("UPDATE colstatsitem SET CountAmt=?, Value=NULL, Stat=NULL WHERE ColStatsItemID = ?");
								$updateStr->bind_param("ii", $valStr, $colstatsItemId);
								if(!$updateStr->execute()) throw new Exception($mysqli->error);

							} else if ($isStat) {
								$updateStr = $mysqli->prepare("UPDATE colstatsitem SET Stat=?, CountAmt=NULL, Value=NULL WHERE ColStatsItemID = ?");
								$updateStr->bind_param("si", $valStr, $colstatsItemId);
								if(!$updateStr->execute()) throw new Exception($mysqli->error);

							} else {
								$updateStr = $mysqli->prepare("UPDATE colstatsitem SET Value=?, CountAmt=NULL, Stat=NULL WHERE ColStatsItemID = ?");
								$updateStr->bind_param("si", $valStr, $colstatsItemId);
								if(!$updateStr->execute()) throw new Exception($mysqli->error);
							}
						}
					}

					if ($doItemInsert) {

						if ($isStat) {
							$updateStr = $mysqli->prepare("INSERT INTO colstatsitem (ColStatsID, Name, Value, CountAmt, Stat) VALUES (?, ?, NULL, NULL, ?)");
							$updateStr->bind_param("iss", $colstatsId, $p, $valStr);
							if(!$updateStr->execute()) throw new Exception($mysqli->error);

						} else if (strlen($valStr) && is_numeric($valStr) && !stripos($p, "_number", 0))
						{
							$updateStr = $mysqli->prepare("INSERT INTO colstatsitem (ColStatsID, Name, Value, CountAmt, Stat) VALUES (?, ?, NULL, ?, NULL)");
							$updateStr->bind_param("isi", $colstatsId, $p, $valStr);
							if(!$updateStr->execute()) throw new Exception($mysqli->error);

						} else {
							$updateStr = $mysqli->prepare("INSERT INTO colstatsitem (ColStatsID, Name, Value, CountAmt, Stat) VALUES (?, ?, ?, NULL, NULL)");
							$updateStr->bind_param("iss", $colstatsId, $p, $valStr);
							if(!$updateStr->execute()) throw new Exception($mysqli->error);
						}
					}
				}
			}
		}

		if ($doInsert) {

			$dateStr = date("Y-m-d H:i:s");

			$updateStr = $mysqli->prepare("INSERT INTO colstats (Id, TimestampCreated, CountAmt, IP) VALUES(?, ?, 1, ?)");
			$updateStr->bind_param("sss", $tr_id, $dateStr, $ipaddr);
			if(!$updateStr->execute()) throw new Exception($mysqli->error);

			if ($result)
			{
				$query = "SELECT ColStatsID FROM colstats ORDER BY ColStatsID DESC LIMIT 0,1";
				$result2 = $mysqli->query($query);
				if ($result2)
				{
					$row2 = $result2->fetch_row();
					$result2->close();
					if ($row2) {
						$colstatsId = $row2[0];

						foreach (array_keys($_POST) as $p) {
							$valStr = encodeToUtf8($_POST[$p]);
							$prefix       = substr($p, 0, 5);
							$isStat       = $prefix == "catby" || $prefix == "audit";
							//echo $p . " [" . $prefix . "]  isStat[" . $isStat . "]\n";

							if (substr($p, 0, 4) == "num_") {
								$numStatsKeys[] = $p;
							}

							if ($p == "Collection_number") {
								$rg_number = $_POST[$p];
							}

							if ($isStat) {
								$updateStr = $mysqli->prepare("INSERT INTO colstatsitem (ColStatsID, Name, Value, CountAmt, Stat) VALUES (?, ?, NULL, NULL, ?)");
								$updateStr->bind_param("iss", $colstatsId, $p, $valStr);
								if(!$updateStr->execute()) throw new Exception($mysqli->error);

							} else if (strlen($valStr) && is_numeric($valStr) && !stripos($p, "_number", 0))
							{
								$updateStr = $mysqli->prepare("INSERT INTO colstatsitem (ColStatsID, Name, Value, CountAmt, Stat) VALUES (?, ?, NULL, ?, NULL)");
								$updateStr->bind_param("isi", $colstatsId, $p, $valStr);
								if(!$updateStr->execute()) throw new Exception($mysqli->error);

							} else {
								$updateStr = $mysqli->prepare("INSERT INTO colstatsitem (ColStatsID, Name, Value, CountAmt, Stat) VALUES (?, ?, ?, NULL, NULL)");
								$updateStr->bind_param("iss", $colstatsId, $p, $valStr);
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
		}
		#echo "Count " . count($numStatsKeys) . " RN: " . $rg_number . "\n";

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
								$updateStr->bind_param("ii", $_POST[$p], $registerItemId);
								if(!$updateStr->execute()) throw new Exception($mysqli->error);
							}
						}

						if ($doItemInsert) {
							$updateStr->prepare("INSERT INTO registeritem (RegisterID, Name, Value, CountAmt) VALUES (?, ?, NULL, ?)");
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