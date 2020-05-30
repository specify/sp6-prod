<?php


ini_set("memory_limit", "500M");
include("/etc/myauth.php");
date_default_timezone_set('America/Chicago');
$myFile = "/home/anhalt/track.dat";


function getArrCount(
	$arr,
	$depth = 1
){

	if(!is_array($arr) || !$depth)
		return 0;

	$res = count($arr);

	foreach($arr as $in_ar)
		$res += getArrCount($in_ar, $depth - 1);

	return $res;
}

function str_sandwich(
	$input,
	$leftString,
	$rightString
){

	$startsAt = strpos($input, $leftString) + strlen($leftString);
	$endsAt = strpos($input, $rightString, $startsAt);
	if($startsAt === FALSE or $endsAt === FALSE){
		return FALSE;
	}
	$result = substr($input, $startsAt, $endsAt - $startsAt);

	return $result;
}

function formatBytes(
	$bytes,
	$precision = 2
){

	$units = ['B', 'KB', 'MB', 'GB', 'TB'];

	$bytes = max($bytes, 0);
	$pow = floor(($bytes
		             ? log($bytes)
		             : 0) / log(1024)
	);
	$pow = min($pow, count($units) - 1);

	//Uncomment one of the following alternatives
	//$bytes /= pow(1024, $pow);
	$bytes /= (1 << (10 * $pow));

	return round($bytes, $precision) . ' ' . $units[$pow];
}

if($_GET != ''){
	if(isset($_GET["dmp"])){
		if($_GET["dmp"] == 1){
			$fh = fopen($myFile, 'r') or die("Unable to open file.");
			if($fh){
				fseek($fh, -1024 * 1000, SEEK_END);
				$array = explode("---------------", fread($fh, 1024 * 1000));
			}
			$results = array_slice($array, 0);
			$size = count($results) - 1;
			echo "Number of records shown: " . $size . "<br>Total Records (in last 1MB of file): " . count($array) . "<br>File Size: " . formatBytes(filesize($myFile), 2) . "<br>";
			foreach($results as $key => $value){
				if($key != 0){
					echo str_replace("\n", "<br>", $value);
				}
			}
			fclose($fh);
		}
		elseif($_GET["dmp"] == 2) {
			$fh = fopen($myFile, 'r') or die("Unable to open file.");
			//Read the data for Registration into a string
			$data_reg = fread($fh, filesize($myFile));
			$data_reg = substr($data_reg, strpos($data_reg, "date=13/07/29 09:45:23"));
			//echo str_sandwich($data_reg, "ip=", "\n");
			$data_reg = str_replace("\n", "<br>", $data_reg);
			$array = explode("---------------<br>", $data_reg);
			$count = 0;
			foreach($array as $key => $value){
				$ip = str_sandwich($value, "ip=", "<br>");
				if(strpos($ip, "129.237.201.") === FALSE){
					$count++;
					if(str_sandwich($value, "app_version=", "<br>") == "6.5.00"){
						$array2[$ip] = $value;
					}
				}
			}
			echo "Workstations at 6.5.00: " . count($array2) . "<br>";
			foreach($array2 as $key => $value){
				$os = str_sandwich($value, "os_name=", "<br>");
				$array3[$key] = $os;
				$array4[$os] = 0;

			}
			echo "-----Workstations running 6.5.00-----<br>";
			foreach($array4 as $osType => $osCount){
				foreach($array3 as $value){
					if($value == $osType){
						$osCount += 1;
					}
				}
				echo $osType . " workstations: " . $osCount . "<br>";
			}
			echo "===================================<br>";
			foreach($array2 as $value){
				echo $value . "===================================<br>";
			}
			fclose($fh);
		}
		elseif($_GET["dmp"] == 3) {
			$fh = fopen($myFile, 'r') or die("Unable to open file.");
			//Read the data for Registration into a string
			$data_reg = fread($fh, filesize($myFile));
			$data_reg = substr($data_reg, strpos($data_reg, "date=13/01/01"));
			//echo str_sandwich($data_reg, "ip=", "\n");
			$data_reg = str_replace("\n", "<br>", $data_reg);
			$array = explode("---------------<br>", $data_reg);
			//$count = 0;
			foreach($array as $key => $value){
				$ip = str_sandwich($value, "ip=", "<br>");
				if(strpos($ip, "129.237.201.") === FALSE){
					//$count++;
					//if(str_sandwich($value, "app_version=", "<br>") != "6.5.00")
					//{
					$array2[$ip] = $value;
					//}
				}
			}
			echo "Workstations logged in from Jan 1, 2013 to now: " . count($array2)/* . "<br>Entries since the 6.5.00 release date: " . $count*/ . "<br>";
			foreach($array2 as $key => $value){
				$os = str_sandwich($value, "os_name=", "<br>");
				if(substr_count($value, "<br>") > 3){
					$array3[$key] = $os;
					$array4[$os] = 0;
				}

			}
			//echo "-----Workstations NOT running 6.5.00-----<br>";
			echo "-----Workstations-----<br>";
			foreach($array4 as $osType => $osCount){
				foreach($array3 as $value){
					if($value == $osType){
						$osCount += 1;
					}
				}
				echo $osType . " workstations: " . $osCount . "<br>";
			}
			echo "===================================<br>";
			foreach($array2 as $value){
				echo $value . "===================================<br>";
			}
			fclose($fh);
		}
		elseif($_GET["dmp"] == 4) {
			$fh = fopen($myFile, 'r') or die("Unable to open file.");
			//Read the data for Registration into a string
			$data_reg = fread($fh, filesize($myFile));
			$data_reg = substr($data_reg, strpos($data_reg, "date=13/01/01"));
			//echo str_sandwich($data_reg, "ip=", "\n");
			$data_reg = str_replace("\n", "<br>", $data_reg);
			$array = explode("---------------<br>", $data_reg);
			foreach($array as $key => $value){
				$ip = str_sandwich($value, "ip=", "<br>");
				if(strpos($ip, "129.237.201.") === FALSE){
					$array2[$ip] = $value;
				}
			}
			foreach($array2 as $key => $value){
				$inst_name = str_sandwich($value, "Institution_name=", "<br>");
				if(substr_count($value, "<br>") > 3){
					if(strpos($value, "Institution_name=") === FALSE){
						$inst_name = "Unknown";
					}
					$array3[$key] = $inst_name;
					$array4[$inst_name] = 0;
				}

			}
			echo "Number of Institutions tracked since Jan 1, 2013: " . count($array4) . "<br>-----Workstations per Institution-----<br>";
			foreach($array4 as $instType => $instCount){
				foreach($array3 as $value){
					if($value == $instType){
						$instCount += 1;
					}
				}
				echo $instType . ": " . $instCount . "<br>";
			}
			echo "===================================<br>";
			/*foreach($array2 as $value)
			{
				echo  $value . "===================================<br>";
			}*/
			fclose($fh);
		}
		elseif($_GET["dmp"] == 5) {
			$fh = fopen($myFile, 'r') or die("Unable to open file.");
			$data_reg = fread($fh, filesize($myFile));
			$data_reg = substr($data_reg, strpos($data_reg, "date=13/01/01"));
			$data_reg = str_replace("\n", "<br>", $data_reg);
			$array = explode("---------------<br>", $data_reg);
			foreach($array as $key => $value){
				$ip = str_sandwich($value, "ip=", "<br>");
				$usr = str_sandwich($value, "specifyuser=", "<br>");
				$inst = str_sandwich($value, "Institution_name=", "<br>");
				if(strpos($ip, "129.237.201.") === FALSE){
					if(strpos($value, "Institution_name=") === FALSE){
						$inst = "Unknown";
					}
					if(strpos($value, "specifyuser=") === FALSE){
						$usr = "Unknown";
					}
					$array5[$inst][$usr] = 0;
				}
			}
			$count = count($array5, COUNT_RECURSIVE) - count($array5);
			echo "Users who logged in from Jan 1, 2013 to now: " . $count . "<br>";
			foreach($array5 as $key => $usrCount){
				echo $key . " (" . count($usrCount) . "):<br>";
				foreach($usrCount as $subkey => $value){
					echo $subkey . "<br>";
				}
				echo "===================================<br>";
			}
			echo "===================================<br>";
			fclose($fh);
		}
		elseif($_GET["dmp"] == 6) {
			$fh = fopen($myFile, 'r') or die("Unable to open file.");
			$data_reg = fread($fh, filesize($myFile));
			$data_reg = substr($data_reg, strpos($data_reg, "date=13/01/01"));
			$data_reg = str_replace("\n", "<br>", $data_reg);
			$array = explode("---------------<br>", $data_reg);
			foreach($array as $key => $value){
				$os = str_sandwich($value, "os_name=", "<br>");
				$ip = str_sandwich($value, "ip=", "<br>");
				$usr = str_sandwich($value, "specifyuser=", "<br>");
				$inst = str_sandwich($value, "Institution_name=", "<br>");
				if(strpos($ip, "129.237.201.") === FALSE){
					if(strpos($value, "os_name=") === FALSE){
						$os = "Unknown";
					}
					if(strpos($value, "specifyuser=") === FALSE){
						$usr = "Unknown";
					}
					$array5[$os][$usr] = 0;
				}
			}
			$count = count($array5, COUNT_RECURSIVE) - count($array5);
			echo "Users who logged in from Jan 1, 2013 to now: " . $count . "<br>";
			foreach($array5 as $key => $usrCount){
				echo $key . " (" . count($usrCount) . "):<br>";
				foreach($usrCount as $subkey => $value){
					echo $subkey . ", ";
				}
				echo "<br>===================================<br>";
			}
			echo "===================================<br>";
			fclose($fh);
		}

		return;
	}
}