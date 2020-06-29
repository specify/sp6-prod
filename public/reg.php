<?php
include ("/etc/myauth.php");
date_default_timezone_set('America/Chicago');
ini_set("memory_limit", "200M");
function encodeToUtf8($string) {
	return mb_convert_encoding($string, "UTF-8", mb_detect_encoding($string, "UTF-8, ISO-8859-1, ISO-8859-15", true));
}

$myFile = "/home/anhalt/reg.dat";


if ($_POST != '') {
	$ipaddr = array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

	$cnt = 0;
	foreach (array_keys($_POST) as $p) {
		$cnt++;
	}

	$dateTime =  "date=" . date("y/m/d") ." " . date("H:i:s") . "\n";
	$data = "---------------\n" . $dateTime;
	$data = $data . "ip=" . $ipaddr . "\n";

	$reg_number = "";
	$key = "reg_number";
	if (isset($_POST[$key])) {

		$reg_number = $_POST[$key];

	} else {

		$uTime = microtime(true);
		$data  = $data . "reg_number=" . $uTime . "\n";
		$reg_number = $uTime;
	}

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

		$reg_type = $_POST['reg_type'];
		$query = $mysqli->prepare("SELECT RegisterID FROM register WHERE RegNumber = ?");
		$query->bind_param("s", $reg_number);
		if(!$query->execute()) throw new Exception($mysqli->error);

		$doInsert = 1;
		$result   = $query->get_result();
		if ($result) {
			$row = $result->fetch_row();
			$result->close();

			if ($row) {
				$doInsert = 0;
				$registerId  = $row[0];

				foreach (array_keys($_POST) as $p) {
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
							$doItemInsert = 0;
							$regItemId    = $row[0];
							$valStr       = encodeToUtf8($_POST[$p]);
							if (strlen($valStr) && is_numeric($valStr) && !stripos($p, "_number", 0))
							{
								$updateStr = $mysqli->prepare("UPDATE registeritem SET CountAmt=?, Value=NULL WHERE RegisterItemID = ?");
								$updateStr->bind_param("ii", $valStr, $regItemId);
								if(!$updateStr->execute()) throw new Exception($mysqli->error);
							} else {
								$updateStr = $mysqli->prepare("UPDATE registeritem SET Value=?, CountAmt=NULL WHERE RegisterItemID = ?");
								$updateStr->bind_param("si", $valStr, $regItemId);
								if(!$updateStr->execute()) throw new Exception($mysqli->error);
							}

						}
					}

					if ($doItemInsert) {
						$valStr = encodeToUtf8($_POST[$p]);

						if (strlen($valStr) && is_numeric($valStr) && !stripos($p, "_number", 0))
						{
							$updateStr = $mysqli->prepare(
								"INSERT INTO registeritem (RegisterID, Name, Value, CountAmt) VALUES (?, ?, NULL, ?)"
							);
							$updateStr->bind_param("isi", $registerId, $p, $valStr);
							if(!$updateStr->execute()) throw new Exception($mysqli->error);
						} else {
							$updateStr = $mysqli->prepare(
								"INSERT INTO registeritem (RegisterID, Name, Value, CountAmt) VALUES (?, ?, ?, NULL)"
							);
							$updateStr->bind_param("iss", $registerId, $p, $valStr);
							if(!$updateStr->execute()) throw new Exception($mysqli->error);
						}

					}
				}
			}
		}

		if ($doInsert) {
			$dateStr = date("Y-m-d H:i:s");
			$updateStr = $mysqli->prepare(
				"INSERT INTO register (RegNumber, RegType, IP, TimestampCreated) VALUES(?, ?, ?, ?)"
			);
			$updateStr->bind_param("ssss", $reg_number, $reg_type, $ipaddr, $dateStr);
			if(!$updateStr->execute()) throw new Exception($mysqli->error);

			$query = "SELECT RegisterID FROM register ORDER BY RegisterID DESC LIMIT 0,1";
			$result2 = $mysqli->query($query);
			if ($result2)
			{
				$row2 = $result2->fetch_row();
				$result2->close();
				if ($row2) {
					$registerId = $row2[0];

					foreach (array_keys($_POST) as $p) {
						$valStr = encodeToUtf8($_POST[$p]);

						if (strlen($valStr) && is_numeric($valStr) && !stripos($p, "_number", 0))
						{
							$updateStr = $mysqli->prepare(
								"INSERT INTO registeritem (RegisterID, Name, Value, CountAmt) VALUES (?, ?, NULL, ?)"
							);
							$updateStr->bind_param("isi", $registerId, $p, $valStr);
							if(!$updateStr->execute()) throw new Exception($mysqli->error);
						} else {
							$updateStr = $mysqli->prepare(
								"INSERT INTO registeritem (RegisterID, Name, Value, CountAmt) VALUES (?, ?, ?, NULL)"
							);
							$updateStr->bind_param("iss", $registerId, $p, $valStr);
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

		$mysqli->close();
	}

	echo "1 " . $uTime . "\n";

} else {
	echo "No arguments!<br>";
}

?>