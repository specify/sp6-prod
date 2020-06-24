<?php

const DATABASE='stats';
const JQUERY=TRUE;

require_once('../components/header.php');
require_once('../components/dictionary.php');

if(!array_key_exists('track_id',$_GET) || !is_numeric($_GET['track_id']) || $_GET['track_id']=='')
	exit('Invalid URL');

$track_id = $_GET["track_id"];

$query = 'SELECT `t`.`TimestampModified` as "date", `t`.`IP` as "ip" FROM `track` `t` WHERE `t`.`TrackID` = '.$track_id;
$info = $mysqli->query($query);
$results = $info->fetch_assoc();

$date = $results['date'];
$ip = $results['ip'];
$date = 'Date Last Accessed: ' . $date . '<br>';

$query2 = 'SELECT `Name`, `CountAmt`, `Value` FROM `trackitem` `t` WHERE `trackid` = '.$track_id;
echo '<input id="query" type="hidden" value="' . $query . ';\n' . $query2 . ';">';
$info = $mysqli->query($query2);


$keys_dictionary = [];

foreach($dictionary as $category => $result_data)
	$keys_dictionary[$category] = array_keys($result_data);

while($results = $info->fetch_assoc()){

	$name = $results['Name'];

	if($results['CountAmt'] === NULL)
		$value = $results['Value'];
	else
		$value = $results['CountAmt'];

	if($value==='' || $value===0)
		continue;

	$found = FALSE;
	foreach($dictionary as $category => $result_data){

		$position = array_search($name,$keys_dictionary[$category]);

		if($position !== false){
			$result[$category][$result_data[$keys_dictionary[$category][$position]]] = $value;
			$found = TRUE;
			break;
		}

	}

	if(!$found)
		$result['other'][$name] = $value;

}

$info->close(); ?>

<h1><?=$result['database_info']['Institution name']?></h1>
<h4><?=$date?></h4> <?php

unset($result['database_info']['Institution name']);//remove redundant data
if(array_key_exists('system_info',$result)){

	//make ip address into a link
	if(array_key_exists('IP',$result['system_info']))
		$result['system_info']['IP'] = '<a href="'.LINK.'ip_info/?ip='.$result['system_info']['IP'].'">'.$result['system_info']['IP'].'</a>';

	//combine OS Name and Version
	if(array_key_exists('OS Name',$result['system_info']) && array_key_exists('OS Version',$result['system_info'])){
		if(strpos($result['system_info']['OS Name'],$result['system_info']['OS Version'])===FALSE)
			$result['system_info']['OS Name'] .= ' ('.$result['system_info']['OS Version'].')';
		else
			unset($result['system_info']['OS Version']);
	}

}


if($ip != ''){ ?>

	<p>
		IP address: <a target="_blank" href="<?=LINK?>ip_info/?ip=<?=$ip?>"><?=$ip?></a>
	</p>
	<p id="org"></p>
	<script src="<?=LINK?>static/js/track<?=JS_EXTENSION?>"></script><?php

}

foreach($result as $name => $data){ ?>

	<table class="table table-striped">
		<thead>
			<tr>
				<th><?=$categories[$name]?></th>
				<th>Value</th>
			</tr>
		</thead>
		<tbody> <?php
		foreach($data as $key => $value)
			echo '<tr><td>'.$key.'</td><td>'.$value . '<td></tr>'; ?>
		</tbody>
	</table> <?php

}

/*echo '<table class="table table-striped">';
foreach($result as $name => $data){ ?>

	<tr>
	<th><?=$name?></th>
		<th>Value</th>
	</tr><?php
	foreach($data as $key => $value)
		echo '<tr><td>'.$key.'</td><td>'.$value . '<td></tr>';

}
echo '</table>';*/


footer();