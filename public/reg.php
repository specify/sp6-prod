<?php

const DATABASE = 'stats';
require_once('components/header.php');
ini_set("memory_limit", "200M");

$myFile = "/home/anhalt/reg.dat";


$dateTime = "date=" . date("y/m/d") . " " . date("H:i:s") . "\n";
$data = "---------------\n" . $dateTime;
$data = $data . "ip=" . $ip_address . "\n";

$reg_number = "";
$key = "reg_number";
if(isset($_POST[$key])){

	$reg_number = $_POST[$key];

}
else {

	$uTime = microtime(TRUE);
	$data = $data . "reg_number=" . $uTime . "\n";
	$reg_number = $uTime;
}

foreach(array_keys($_POST) as $p)
	$data = $data . "$p=$_POST[$p]\n";

$fh = fopen($myFile, 'a') or die("can't open file");
fwrite($fh, $data);
fclose($fh);


$reg_type = $_POST['reg_type'];
$query = $mysqli->prepare("SELECT `registerid` FROM `register` WHERE `regnumber` = ?");
$query->bind_param("s", $reg_number);
if(!$query->execute())
	die($mysqli->error);

$doInsert = 1;
$result = $query->get_result();
if($result){
	$row = $result->fetch_row();
	$result->close();

	if($row){
		$doInsert = 0;
		$registerId = $row[0];

		foreach(array_keys($_POST) as $p){
			$doItemInsert = 1;

			$query = $mysqli->prepare("SELECT `registeritemid` FROM `registeritem` WHERE `registerid` = ? AND `name` = ?");
			$query->bind_param("is", $registerId, $p);
			if(!$query->execute())
				die($mysqli->error);
			$result = $query->get_result();

			if($result){

				$row = $result->fetch_row();
				$result->close();
				if($row){

					$doItemInsert = 0;
					$regItemId = $row[0];
					$valStr = encodeToUtf8($_POST[$p]);
					if(strlen($valStr) && is_numeric($valStr) && !stripos($p, "_number", 0)){

						$updateStr = $mysqli->prepare("UPDATE `registeritem` SET `countamt`=?, `value`=NULL WHERE `registeritemid` = ?");
						$updateStr->bind_param("ii", $valStr, $regItemId);
						if(!$updateStr->execute())
							die($mysqli->error);

					}
					else {

						$updateStr = $mysqli->prepare("UPDATE `registeritem` SET `value`=?, `countamt`=NULL WHERE `registeritemid` = ?");
						$updateStr->bind_param("si", $valStr, $regItemId);
						if(!$updateStr->execute())
							die($mysqli->error);

					}

				}
			}

			if($doItemInsert){

				$valStr = encodeToUtf8($_POST[$p]);

				if(strlen($valStr) && is_numeric($valStr) && !stripos($p, "_number", 0)){

					$updateStr = $mysqli->prepare(
						"INSERT INTO `registeritem` (`registerid`, `name`, `value`, `countamt`) VALUES (?, ?, NULL, ?)"
					);
					$updateStr->bind_param("isi", $registerId, $p, $valStr);
					if(!$updateStr->execute())
						die($mysqli->error);

				}
				else {

					$updateStr = $mysqli->prepare(
						"INSERT INTO registeritem (RegisterID, Name, Value, CountAmt) VALUES (?, ?, ?, NULL)"
					);
					$updateStr->bind_param("iss", $registerId, $p, $valStr);
					if(!$updateStr->execute())
						die($mysqli->error);

				}

			}
		}
	}
}

if($doInsert){

	$dateStr = date("Y-m-d H:i:s");
	$updateStr = $mysqli->prepare(
		"INSERT INTO `register` (`regnumber`, `regtype`, `ip`, `timestampcreated`) VALUES(?, ?, ?, ?)"
	);
	$updateStr->bind_param("ssss", $reg_number, $reg_type, $ip_address, $dateStr);
	if(!$updateStr->execute())
		die($mysqli->error);

	$query = "SELECT `registerid` FROM `register` ORDER BY `registerid` DESC LIMIT 0,1";
	$result2 = $mysqli->query($query);
	if($result2){

		$row2 = $result2->fetch_row();
		$result2->close();
		if($row2){
			$registerId = $row2[0];

			foreach(array_keys($_POST) as $p){
				$valStr = encodeToUtf8($_POST[$p]);

				if(strlen($valStr) && is_numeric($valStr) && !stripos($p, "_number", 0)){

					$updateStr = $mysqli->prepare(
						"INSERT INTO registeritem (RegisterID, Name, Value, CountAmt) VALUES (?, ?, NULL, ?)"
					);
					$updateStr->bind_param("isi", $registerId, $p, $valStr);
					if(!$updateStr->execute())
						die($mysqli->error);

				}
				else {

					$updateStr = $mysqli->prepare(
						"INSERT INTO `registeritem` (`registerid`, `name`, `value`, `countamt`) VALUES (?, ?, ?, NULL)"
					);
					$updateStr->bind_param("iss", $registerId, $p, $valStr);
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


echo "1 " . $uTime . "\n";


require_once('components/footer.php');