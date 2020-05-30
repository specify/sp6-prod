<?php

const DATABASE = 'stats';
require_once('components/header.php');
ini_set("memory_limit", "500M");
$myFile = "/home/anhalt/track.dat";


$dateTime = "date=" . date("y/m/d") . " " . date("H:i:s") . "\n";
$data = "---------------\n" . $dateTime;
$data = $data . "ip=" . $ip_address . "\n";
foreach(array_keys($_POST) as $p)
	$data = $data . "$p=$_POST[$p]\n";

$fh = fopen($myFile, 'a') or die("can't open file");
fwrite($fh, $data);
fclose($fh);

$tr_id = $_POST['id'];
$query = $mysqli->prepare("SELECT `trackid`, `countamt` FROM `track` WHERE `id` = ?");
$query->bind_param("s", $tr_id);
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
		$trackId = $row[0];
		$count = $row[1] + 1;
		$timestampModified = date("Y-m-d H:i:s");
		$updateStr = $mysqli->prepare("UPDATE track SET CountAmt=?, TimestampModified=?, IP=? WHERE TrackID = ?");
		$updateStr->bind_param("issi", $count, $timestampModified, $ip_address, $trackId);
		if(!$updateStr->execute())
			die($mysqli->error);

		foreach(array_keys($_POST) as $p){

			$doItemInsert = 1;

			if(substr($p, 0, 4) == "num_"){
				$numStatsKeys[] = $p;
			}

			if($p == "Collection_number"){
				$rg_number = $_POST[$p];
			}

			$valStr = encodeToUtf8($_POST[$p]);

			$query = $mysqli->prepare("SELECT `trackitemid` FROM `trackitem` WHERE `trackid` = ? AND `name` = ?");
			$query->bind_param("is", $trackId, $p);
			if(!$query->execute())
				die($mysqli->error);
			$result = $query->get_result();

			if($result){
				$row = $result->fetch_row();
				$result->close();
				if($row){

					$doItemInsert = 0;
					$trackItemId = $row[0];
					if(strlen($valStr) && is_numeric($valStr) && !stripos($p, "_number", 0)){

						$updateStr = $mysqli->prepare("UPDATE `trackitem` SET `countamt`=?, `value`=NULL WHERE `trackitemid` = ?");
						$updateStr->bind_param("ii", $valStr, $trackItemId);
						if(!$updateStr->execute())
							die($mysqli->error);

					}
					else {

						$updateStr = $mysqli->prepare("UPDATE `trackitem` SET `value`=?, `countamt`=NULL WHERE `trackitemid` = ?");
						$updateStr->bind_param("si", $valStr, $trackItemId);
						if(!$updateStr->execute())
							die($mysqli->error);

					}

				}
			}

			if($doItemInsert){

				if(strlen($valStr) && is_numeric($valStr) && !stripos($p, "_number", 0)){
					$updateStr = $mysqli->prepare("INSERT INTO `trackitem` (`trackid`, `name`, `value`, `countamt`) VALUES (?,?,NULL,?)");
					$updateStr->bind_param("isi", $trackId, $p, $valStr);
					if(!$updateStr->execute())
						die($mysqli->error);

				}
				else {

					$updateStr = $mysqli->prepare("INSERT INTO trackitem (TrackID, Name, Value, CountAmt) VALUES (?,?,?, NULL)");
					$updateStr->bind_param("iss", $trackId, $p, $valStr);
					if(!$updateStr->execute())
						die($mysqli->error);

				}
			}
		}
	}
}

if($doInsert){

	$dateStr = date("Y-m-d H:i:s");
	$updateStr = $mysqli->prepare("INSERT INTO track (Id, TimestampCreated, CountAmt, IP) VALUES(?, ?, 1, ?)");
	$updateStr->bind_param("sss", $tr_id, $dateStr, $ip_address);
	if(!$updateStr->execute())
		die($mysqli->error);

	$query = "SELECT TrackID FROM track ORDER BY TrackID DESC LIMIT 0,1";
	$result2 = $mysqli->query($query);
	if($result){

		$row2 = $result2->fetch_row();
		$result2->close();
		if($row2){

			$trackId = $row2[0];

			foreach(array_keys($_POST) as $p){

				$valStr = encodeToUtf8($_POST[$p]);

				if(substr($p, 0, 4) == "num_")
					$numStatsKeys[] = $p;

				if($p == "Collection_number")
					$rg_number = $_POST[$p];

				if(strlen($valStr) && is_numeric($valStr) && !stripos($p, "_number", 0)){

					$updateStr = $mysqli->prepare(
						"INSERT INTO trackitem (TrackID, Name, Value, CountAmt) VALUES (?, ?, NULL, ?)"
					);
					$updateStr->bind_param("isi", $trackId, $p, $valStr);
					if(!$updateStr->execute())
						die($mysqli->error);

				}
				else {

					$updateStr = $mysqli->prepare(
						"INSERT INTO trackitem (TrackID, Name, Value, CountAmt) VALUES (?, ?, ?, NULL)"
					);
					$updateStr->bind_param("iss", $trackId, $p, $valStr);
					if(!$updateStr->execute())
						die($mysqli->error);
				}

			}
		}
		else
			die('couldn\'t find the row with the highest key\n');
	}
	else
		die('couldn\'t find the highest key\n');
}

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
						$updateStr->bind_param("si", $_POST[$p], $registerItemId);
						if(!$updateStr->execute())
							die($mysqli->error);

					}

				}

				if($doItemInsert){

					$updateStr = $mysqli->prepare(
						"INSERT INTO registeritem (RegisterID, Name, Value, CountAmt) VALUES (?, ?, NULL, ?)"
					);
					$updateStr->bind_param("isi", $registerId, $p, $_POST[$p]);
					if(!$updateStr->execute())
						die($mysqli->error);

				}
			}
		}
	}
}


require_once('components/footer.php');