<?php


const DATABASE = 'stats';
require_once('components/header.php');

$tr_id = $_POST['id'];
$query = $mysqli->prepare("SELECT `colstatsid`, `countamt` FROM `colstats` WHERE `id` = ?");
$query->bind_param('s', $tr_id);
if(!$query->execute())
	die($mysqli->error);


$rg_number = "";
$numStatsKeys = [];
$doInsert = 1;
$result = $query->get_result();

if($result){

	$row = $result->fetch_row();
	$result->close();

	if($row){
		$doInsert = 0;
		$colstatsId = $row[0];
		$count = $row[1] + 1;
		$timestampModified = date("Y-m-d H:i:s");
		$updateStr = $mysqli->prepare("UPDATE `colstats` SET `countamt`=?, `timestampmodified`=?, `ip`=? WHERE `colstatsid` = ?");
		$updateStr->bind_param('issi', $count, $timestampModified, $ip_address, $colstatsId);
		if(!$updateStr->execute())
			die($mysqli->error);
		$result = $updateStr->get_result();

		foreach(array_keys($_POST) as $p){
			$valStr = encodeToUtf8($_POST[$p]);
			$doItemInsert = 1;
			$prefix = substr($p, 0, 5);
			$isStat = $prefix == "catby" || $prefix == "audit";
			//echo $p . " [" . $prefix . "]  isStat[" . $isStat . "]\n";

			if(substr($p, 0, 4) == "num_")
				$numStatsKeys[] = $p;

			if($p == "Collection_number")
				$rg_number = $_POST[$p];

			$query = $mysqli->prepare("SELECT `colstatsitemid` FROM `colstatsitem` WHERE `colstatsid` = ? AND `name` = ?");
			$query->bind_param('is', $colstatsId, $p);
			if(!$query->execute())
				die($mysqli->error);

			$result = $query->get_result();
			if($result){
				$row = $result->fetch_row();
				$result->close();
				if($row){
					$doItemInsert = 0;
					$colstatsItemId = $row[0];

					if(strlen($valStr) && is_numeric($valStr) && !stripos($p, "_number", 0)){
						$updateStr = $mysqli->prepare("UPDATE colstatsitem SET CountAmt=?, Value=NULL, Stat=NULL WHERE ColStatsItemID = ?");
						$updateStr->bind_param("ii", $valStr, $colstatsItemId);
						if(!$updateStr->execute())
							die($mysqli->error);

					}
					elseif($isStat){
						$updateStr = $mysqli->prepare("UPDATE colstatsitem SET Stat=?, CountAmt=NULL, Value=NULL WHERE ColStatsItemID = ?");
						$updateStr->bind_param("si", $valStr, $colstatsItemId);
						if(!$updateStr->execute())
							die($mysqli->error);

					}
					else {
						$updateStr = $mysqli->prepare("UPDATE colstatsitem SET Value=?, CountAmt=NULL, Stat=NULL WHERE ColStatsItemID = ?");
						$updateStr->bind_param("si", $valStr, $colstatsItemId);
						if(!$updateStr->execute())
							die($mysqli->error);
					}
				}
			}

			if($doItemInsert){

				if($isStat){
					$updateStr = $mysqli->prepare("INSERT INTO colstatsitem (ColStatsID, Name, Value, CountAmt, Stat) VALUES (?, ?, NULL, NULL, ?)");
					$updateStr->bind_param("iss", $colstatsId, $p, $valStr);
					if(!$updateStr->execute())
						die($mysqli->error);

				}
				elseif(strlen($valStr) && is_numeric($valStr) && !stripos($p, "_number", 0)){
					$updateStr = $mysqli->prepare("INSERT INTO `colstatsitem` (`colstatsid`, `name`, `value`, `countamt`, `stat`) VALUES (?, ?, NULL, ?, NULL)");
					$updateStr->bind_param("isi", $colstatsId, $p, $valStr);
					if(!$updateStr->execute())
						die($mysqli->error);

				}
				else {
					$updateStr = $mysqli->prepare("INSERT INTO colstatsitem (ColStatsID, Name, Value, CountAmt, Stat) VALUES (?, ?, ?, NULL, NULL)");
					$updateStr->bind_param("iss", $colstatsId, $p, $valStr);
					if(!$updateStr->execute())
						die($mysqli->error);
				}
			}
		}
	}
}

if($doInsert){

	$dateStr = date("Y-m-d H:i:s");

	$updateStr = $mysqli->prepare("INSERT INTO colstats (Id, TimestampCreated, CountAmt, IP) VALUES(?, ?, 1, ?)");
	$updateStr->bind_param("sss", $tr_id, $dateStr, $ip_address);
	if(!$updateStr->execute())
		die($mysqli->error);

	if($result){
		$query = "SELECT ColStatsID FROM colstats ORDER BY ColStatsID DESC LIMIT 0,1";
		$result2 = $mysqli->query($query);
		if($result2){
			$row2 = $result2->fetch_row();
			$result2->close();

			if($row2){

				$colstatsId = $row2[0];

				foreach(array_keys($_POST) as $p){
					$valStr = encodeToUtf8($_POST[$p]);
					$prefix = substr($p, 0, 5);
					$isStat = $prefix == "catby" || $prefix == "audit";
					//echo $p . " [" . $prefix . "]  isStat[" . $isStat . "]\n";

					if(substr($p, 0, 4) == "num_")
						$numStatsKeys[] = $p;

					if($p == "Collection_number")
						$rg_number = $_POST[$p];

					if($isStat){
						$updateStr = $mysqli->prepare("INSERT INTO colstatsitem (ColStatsID, Name, Value, CountAmt, Stat) VALUES (?, ?, NULL, NULL, ?)");
						$updateStr->bind_param("iss", $colstatsId, $p, $valStr);
						if(!$updateStr->execute())
							die($mysqli->error);

					}
					elseif(strlen($valStr) && is_numeric($valStr) && !stripos($p, "_number", 0)){
						$updateStr = $mysqli->prepare("INSERT INTO colstatsitem (ColStatsID, Name, Value, CountAmt, Stat) VALUES (?, ?, NULL, ?, NULL)");
						$updateStr->bind_param("isi", $colstatsId, $p, $valStr);
						if(!$updateStr->execute())
							die($mysqli->error);

					}
					else {
						$updateStr = $mysqli->prepare("INSERT INTO colstatsitem (ColStatsID, Name, Value, CountAmt, Stat) VALUES (?, ?, ?, NULL, NULL)");
						$updateStr->bind_param("iss", $colstatsId, $p, $valStr);
						if(!$updateStr->execute())
							die($mysqli->error);
					}
				}
			}
			else {
				echo 'couldn\'t find the row with the highest key\n';
			}
		}
		else {
			echo 'couldn\'t find the highest key';
		}
	}
}
#echo "Count " . count($numStatsKeys) . " RN: " . $rg_number . "\n";

if(count($numStatsKeys) > 0){

	$query = $mysqli->prepare("SELECT RegisterID FROM register WHERE RegNumber = ?");
	$query->bind_param("s", $rg_number);
	if(!$query->execute())
		die($mysqli->error);
	$result = $query->get_result();

	if($result){
		$row = $result->fetch_row();
		$result->close();

		if($row){
			$doInsert = 0;
			$registerId = $row[0];
			$count = $row[1] + 1;

			foreach($numStatsKeys as $p){

				$doItemInsert = 1;

				$query = $mysqli->prepare("SELECT RegisterItemID FROM registeritem WHERE RegisterID = ? AND Name = ?");
				$query->bind_param("is", $registerId, $p);
				if(!$query->execute())
					die($mysqli->error);
				$result = $query->get_result();

				if($result){

					$row = $result->fetch_row();
					$result->close();
					if($row){

						$doItemInsert = 0;
						$registerItemId = $row[0];
						$updateStr = $mysqli->prepare("UPDATE registeritem SET CountAmt=? WHERE RegisterItemID = ?");
						$updateStr->bind_param("ii", $_POST[$p], $registerItemId);

						if(!$updateStr->execute())
							die($mysqli->error);

					}

				}

				if($doItemInsert){

					$updateStr->prepare("INSERT INTO registeritem (RegisterID, Name, Value, CountAmt) VALUES (?, ?, NULL, ?)");
					$updateStr->bind_param("isi", $registerId, $p, $_POST[$p]);

					if(!$updateStr->execute())
						die($mysqli->error);

				}
			}
		}
	}
}


require_once('components/footer.php');