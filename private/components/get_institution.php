<?php

if(count($_GET)==0)
	exit;

define('NO_HEAD',TRUE);

const DATABASE = 'stats';
require_once('header.php');

$possible_parameters = ['date_1','date_2','version_1','version_2','isa','institution','track_id','show_last_days'];
foreach($possible_parameters as $parameter)
	if(!array_key_exists($parameter,$_GET))
		$_GET[$parameter] = '';

if(is_numeric($_GET['show_last_days'])){

	$show_last_days = $_GET['show_last_days'];

	$_GET['date_2'] = $_SERVER['REQUEST_TIME'];
	$_GET['date_1'] = $_GET['date_2'] - 86400 * $show_last_days;

}

if(!is_numeric($_GET['date_1']))
	unset($_GET['date_1']);

if(!is_numeric($_GET['date_2']))
	unset($_GET['date_2']);

if(array_key_exists('date_1',$_GET) &&
   array_key_exists('date_2',$_GET) &&
   $_GET['date_1']>$_GET['date_2'])
	[$_GET['date_1'],$_GET['date_2']] = [$_GET['date_2'],$_GET['date_1']];

if($_GET['version_1']>$_GET['version_2'])
	[$_GET['version_1'],$_GET['version_2']] = [$_GET['version_2'],$_GET['version_1']];


function getFormattedDate(
	$date,
	$default
){

	if(!array_key_exists($date, $_GET) || $_GET[$date] == '')
		return "$default";
	else
		return date('Y-m-d H:i:s', $_GET[$date]);

}

$date_1 = getFormattedDate('date_1', '0000-00-00 00:00:00');
$date_2 = getFormattedDate('date_2', date("Y-m-d H:i:s", time()));


function getVersion(
	$version,
	$default
){

	if(!array_key_exists($version, $_GET) || $_GET[$version] == '')
		return "$default";
	else
		return strval($_GET[$version]);
}

$version_1 = getVersion('version_1', '6.0.01');
$version_2 = getVersion('version_2', '6.9.99');


$hide_invalid_institutions_query = "
	AND `ti2`.`value` != ''
	AND `ti2`.`value` != '\n'
	AND `ti2`.`value` != ' '
	AND `ti2`.`value` != '.'
	AND `ti2`.`value` != '?'
	AND `ti2`.`value` != '-'
	AND `ti2`.`value` IS NOT NULL
";


// 'date_1','date_2','version_1','version_2','isa','institution','track_id'

if($_GET['track_id']!='') {

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

	$result = [];
	$dictionary_raw = file_get_contents('../static/csv/dictionary.csv');
	$dictionary_raw = explode("\n",$dictionary_raw);

	$dictionary = [];
	$current_category = '';
	foreach($dictionary_raw as $line){
		$line = explode(',',$line);

		if(count($line)==1){
			$current_category = $line[0];
			$dictionary[$current_category] = [];
		}
		else
			$dictionary[$current_category][$line[0]]=$line[1];

	}

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

		<script>
			const xhttp = new XMLHttpRequest();
			xhttp.onreadystatechange = function() {
				if (this.status !== 200)//request.readyState == 4
					return;

				if(this.responseText==='')
					return;

				$( "#org" ).text( "Possible Institution: " + $( "<div>" ).html(this.responseText).text() );
			};
			xhttp.open("GET", "../components/get_url.php?ip=<?=$ip?>", true);
			xhttp.send();
		</script> <?php

	}

	foreach($result as $name => $data){ ?>

		<table class="table table-striped">
			<thead>
				<tr>
					<th><?=$name?></th>
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


}

//elseif($_GET['institution']!='') {
//
//	$isa = $_GET['isa'];
//	$institution_name = $_GET['institution'];
//	echo '<h2>Select a Collection</h2>
//		<h4>Note:<br>
//		Each entry is in the form of: [ISA number] Collection name (Discipline name): Date Last accessed.<br>
//		The entries are sorted by most recent collection accessed.</h4>';
//
//	$institution_name = str_replace('%20', '', $institution_name);
//	$institution_name = str_replace("\\", '', $institution_name);
//	$institution_name = str_replace("'s", "\\'s", $institution_name);
//
//	if($institution_name == '-null-')
//		$institution_name = 'is null';
//	elseif($institution_name == '-blank-')
//		$institution_name = "= ''";
//	elseif($institution_name == '-new-line-')
//		$institution_name = "= '\n'";
//	elseif($institution_name == '-dash-')
//		$institution_name = "= '-'";
//	elseif($institution_name == '-space-')
//		$institution_name = "= ' '";
//	else
//		$institution_name = "= '$institution_name'";
//
//	$query2 = "
//		SELECT   `ti`.`value`                                                                               AS 'Disc name',
//		         `ti3`.`value`                                                                              AS 'Col name',
//		         SUBSTRING_INDEX( GROUP_CONCAT( `ti`.`trackid` ORDER BY `timestampmodified` DESC ), ',', 1) AS 'tid',
//		         MAX(`t`.`timestampmodified`)                                                               AS 'time'
//		FROM     `trackitem` `ti`,
//		         `trackitem` `ti2`,
//		         `trackitem` `ti3`,
//		         `trackitem` `ti5`,
//		         `track` `t`,
//		         (
//		                  SELECT   MAX(`ti`.`trackid`) AS 'maxtid',
//		                           COUNT(*)
//		                  FROM     `trackitem` `ti`,
//		                           `track` `t`,
//		                           `trackitem` `ti2`,
//		                           `trackitem` `ti3`
//		                  WHERE    `t`.`trackid` = `ti`.`trackid`
//		                  AND      `ti`.`trackid` = `ti2`.`trackid`
//		                  AND      `ti`.`trackid` = `ti3`.`trackid`
//		                  AND      NOT ((
//		                                             `t`.`ip` <= '129.237.201.999'
//		                                    AND      `t`.`ip` >= '129.237.201.0')
//		                           OR       (
//		                                             `t`.`ip` <= '129.237.229.999'
//		                                    AND      `t`.`ip` >= '129.237.229.0'))
//		                  AND      `t`.`timestampmodified` >= '" . $date_1 . "'
//		                  AND      `t`.`timestampmodified` <= '" . $date_2 . "'
//		                  AND      `ti3`.`name` = 'app_version'
//		                  AND      `ti3`.`value` >= '" . $version_1 . "'
//		                  AND      `ti3`.`value` <= '" . $version_2 . "'
//		                  AND      `ti`.`name` = 'user_name'
//		                  AND      `ti`.`value` NOT IN ('rods',
//		                                            'tlammer')
//		                  GROUP BY `ti2`.`trackid`) `ti6`
//		WHERE    `t`.`trackid` = `ti`.`trackid`
//		AND      `ti2`.`trackid` = `ti`.`trackid`
//		AND      `ti3`.`trackid` = `ti`.`trackid`
//		AND      `ti5`.`trackid` = `ti`.`trackid`
//		AND      `ti6`.`maxtid` = `ti`.`trackid`
//		AND      `ti`.`name` = 'Discipline_name'
//		AND      `ti2`.`name` = 'Institution_name'
//		AND      `ti3`.`name` = 'Collection_name'
//		AND      `ti5`.`name` = 'Collection_number'
//		AND      `ti2`.`value` $institution_name
//		".$hide_invalid_institutions_query."
//		GROUP BY ti5.value
//		ORDER BY max(timestampmodified) DESC";
//
//	echo '<input id="query" type="hidden" value="' . $query2 . '">';
//
//	$collections = [];
//
//
//	$info2 = $mysqli->query($query2);
//
//	while($results2 = $info2->fetch_row()){
//
//		$disName = $results2[0];
//		$collName = $results2[1];
//
//		if(array_key_exists(4, $results2)){
//			$isa_number = $results2[2];
//			$track_id = $results2[3];
//			$date = $results2[4];
//			$collections[$date . $track_id] = '<a target="_blank" href="?track_id=' . $track_id . '">['.$isa_number.'] '.$collName.' ('.$disName.'): '.$date.'</a><br>';
//		}
//
//		else {
//			$track_id = $results2[2];
//			$date = $results2[3];
//			$collections[$date . $track_id] = '<a target="_blank" href="?track_id=' . $track_id . '">'.$collName.' ('.$disName.'): '.$date.'</a><br>';
//		}
//
//	}
//
//	$info2->close();
//
//
//	krsort($collections);
//	foreach($collections as $value)
//		echo $value . "\n";
//
//}

else {
	$isa = $_GET['isa']; ?>

	<h2>Select an Institution:</h2><?php

	if($isa == 'both')
//		$query = "
//			SELECT `ti2`.`value`                AS 'institution_name',
//			       `ti`.`value`                 AS 'discipline_name',
//			       `ti3`.`value`                AS 'collection_name',
//			       `ti7`.`value`                AS 'co_count',
//			       SUBSTRING_INDEX(GROUP_CONCAT(`ti`.`trackid` ORDER BY `timestampmodified` DESC),
//			       ',', 1
//			       )                        AS 'track_id',
//			       MAX(`t`.`timestampmodified`) AS 'date'
//			FROM   `trackitem` `ti`,
//			       `trackitem` `ti2`,
//			       `trackitem` `ti3`,
//			       `trackitem` `ti5`,
//			       `trackitem` `ti7`,
//			       `track` `t`,
//			       (SELECT MAX(`ti`.`trackid`) AS 'maxtid',
//			               COUNT(*)
//			        FROM   `trackitem` `ti`,
//			               `track` `t`,
//			               `trackitem` `ti2`,
//			               `trackitem` `ti3`
//			        WHERE  `t`.`trackid` = `ti`.`trackid`
//			               AND `ti`.`trackid` = `ti2`.`trackid`
//			               AND `ti`.`trackid` = `ti3`.`trackid`
//			               AND NOT ( ( `t`.`ip` <= '129.237.201.999'
//			                           AND `t`.`ip` >= '129.237.201.0' )
//			                          OR ( `t`.`ip` <= '129.237.229.999'
//			                               AND `t`.`ip` >= '129.237.229.0' ) )
//			               AND `t`.`timestampmodified` >= '" . $date_1 . "'
//			               AND `t`.`timestampmodified` <= '" . $date_2 . "'
//			               AND `ti3`.`name` = 'app_version'
//			               AND `ti3`.`value` >= '" . $version_1 . "'
//			               AND `ti3`.`value` <= '" . $version_2 . "'
//			               AND `ti`.`name` = 'user_name'
//			               AND `ti`.`value` NOT IN ( 'rods', 'tlammer' )
//			        GROUP  BY `ti2`.`trackid`) `ti6`
//			WHERE  `t`.`trackid` = `ti`.`trackid`
//			       AND `ti2`.`trackid` = `ti`.`trackid`
//			       AND `ti3`.`trackid` = `ti`.`trackid`
//			       AND `ti5`.`trackid` = `ti`.`trackid`
//			       AND `ti6`.`maxtid` = `ti`.`trackid`
//	               AND `ti7`.`name` = 'num_co'
//			       AND `ti`.`name` = 'Discipline_name'
//			       AND `ti2`.`name` = 'Institution_name'
//			       AND `ti3`.`name` = 'Collection_name'
//			       AND `ti5`.`name` = 'Collection_number'
//			       ".$hide_invalid_institutions_query."
//			GROUP  BY `ti5`.`value`
//			ORDER  BY MAX(`timestampmodified`) DESC";

		$query = "
			SELECT `ti2`.`value`                AS 'institution_name',
			       `ti`.`value`                 AS 'discipline_name',
			       `ti3`.`value`                AS 'collection_name',
			       SUBSTRING_INDEX(GROUP_CONCAT(`ti`.`trackid` ORDER BY `timestampmodified` DESC),
			       ',', 1
			       )                        AS 'track_id',
			       MAX(`t`.`timestampmodified`) AS 'date'
			FROM   `trackitem` `ti`,
			       `trackitem` `ti2`,
			       `trackitem` `ti3`,
			       `trackitem` `ti5`,
			       `track` `t`,
			       (SELECT MAX(`ti`.`trackid`) AS 'maxtid',
			               COUNT(*)
			        FROM   `trackitem` `ti`,
			               `track` `t`,
			               `trackitem` `ti2`,
			               `trackitem` `ti3`
			        WHERE  `t`.`trackid` = `ti`.`trackid`
			               AND `ti`.`trackid` = `ti2`.`trackid`
			               AND `ti`.`trackid` = `ti3`.`trackid`
			               AND NOT ( ( `t`.`ip` <= '129.237.201.999'
			                           AND `t`.`ip` >= '129.237.201.0' )
			                          OR ( `t`.`ip` <= '129.237.229.999'
			                               AND `t`.`ip` >= '129.237.229.0' ) )
			               AND `t`.`timestampmodified` >= '" . $date_1 . "'
			               AND `t`.`timestampmodified` <= '" . $date_2 . "'
			               AND `ti3`.`name` = 'app_version'
			               AND `ti3`.`value` >= '" . $version_1 . "'
			               AND `ti3`.`value` <= '" . $version_2 . "'
			               AND `ti`.`name` = 'user_name'
			               AND `ti`.`value` NOT IN ( 'rods', 'tlammer' )
			        GROUP  BY `ti2`.`trackid`) `ti6`
			WHERE  `t`.`trackid` = `ti`.`trackid`
			       AND `ti2`.`trackid` = `ti`.`trackid`
			       AND `ti3`.`trackid` = `ti`.`trackid`
			       AND `ti5`.`trackid` = `ti`.`trackid`
			       AND `ti6`.`maxtid` = `ti`.`trackid`
			       AND `ti`.`name` = 'Discipline_name'
			       AND `ti2`.`name` = 'Institution_name'
			       AND `ti3`.`name` = 'Collection_name'
			       AND `ti5`.`name` = 'Collection_number'
			       ".$hide_invalid_institutions_query."
			GROUP  BY `ti5`.`value`
			ORDER  BY MAX(`timestampmodified`) DESC";

	elseif($isa == 'ISA')
		$query = "
			SELECT `ti2`.`value`                AS 'institution_name', 
			       `ti`.`value`                 AS 'discipline_name', 
			       `ti3`.`value`                AS 'collection_name', 
			       `ti4`.`value`                AS 'isa', 
			       `ti7`.`value`                AS 'co_count', 
			       SUBSTRING_INDEX(GROUP_CONCAT(`ti`.`trackid` ORDER BY `timestampmodified` DESC), 
			       ',', 1 
			       )                        AS 'track_id', 
			       MAX(`t`.`timestampmodified`) AS 'date' 
			FROM   `trackitem` `ti`, 
			       `trackitem` `ti2`, 
			       `trackitem` `ti3`, 
			       `trackitem` `ti4`, 
			       `trackitem` `ti7`, 
			       `track` `t`, 
			       (SELECT MAX(`ti`.`trackid`) AS 'maxtid' 
			        FROM   `trackitem` `ti`, 
			               `trackitem` `ti2`, 
			               `trackitem` `ti3`, 
			               `track` `t` 
			        WHERE  `t`.`trackid` = `ti`.`trackid` 
			               AND `ti2`.`trackid` = `ti`.`trackid`
			               AND `ti3`.`trackid` = `ti`.`trackid` 
			               AND `ti`.`name` = 'ISA_number' 
			               AND NOT ( ( `t`.`ip` <= '129.237.201.999' 
			                           AND `t`.`ip` >= '129.237.201.0' ) 
			                          OR ( `t`.`ip` <= '129.237.229.999' 
			                               AND `t`.`ip` >= '129.237.229.0' ) ) 
			               AND `t`.`timestampmodified` >= '" . $date_1 . "' 
			               AND `t`.`timestampmodified` <= '" . $date_2 . "' 
			               AND `ti`.`value` != '' 
			               AND `ti2`.`name` = 'app_version' 
			               AND `ti3`.`value` >= '" . $version_1 . "' 
			               AND `ti3`.`value` <= '" . $version_2 . "' 
			               AND `ti3`.`name` = 'user_name' 
			               AND `ti3`.`value` NOT IN ( 'rods', 'tlammer' )  
			        GROUP  BY `ti`.`value`) `ti6` 
			WHERE  `t`.`trackid` = `ti`.`trackid` 
			       AND `ti2`.`trackid` = `ti`.`trackid` 
			       AND `ti3`.`trackid` = `ti`.`trackid` 
			       AND `ti4`.`trackid` = `ti`.`trackid` 
			       AND `ti6`.`maxtid` = `ti`.`trackid` 
			       AND `ti`.`name` = 'Discipline_name' 
			       AND `ti2`.`name` = 'Institution_name' 
			       AND `ti3`.`name` = 'Collection_name' 
			       AND `ti4`.`name` = 'ISA_number' 
	               AND `ti7`.`name` = 'num_co'
			       AND `ti4`.`value` != ''  
			       ".$hide_invalid_institutions_query."
			GROUP  BY `ti4`.`value` 
			ORDER  BY MAX(`timestampmodified`) DESC";

	elseif($isa == 'not')
		$query = "
			SELECT `ti2`.`value`                AS 'institution_name', 
			       `ti`.`value`                 AS 'discipline_name', 
			       `ti3`.`value`                AS 'collection_name', 
			       `ti7`.`value`                AS 'co_count', 
			       SUBSTRING_INDEX(GROUP_CONCAT(`ti`.`trackid` ORDER BY `timestampmodified` DESC), 
			       ',', 1 
			       )                        AS 'track_id', 
			       MAX(`t`.`timestampmodified`) AS 'date' 
			FROM   `trackitem` `ti`, 
			       `trackitem` `ti2`, 
			       `trackitem` `ti3`, 
			       `trackitem` `ti5`, 
			       `trackitem` `ti7`, 
			       `track` `t`, 
			       (SELECT MAX(`ti`.`trackid`) AS 'maxtid' 
			        FROM   `trackitem` `ti`, 
			               `track` `t`, 
			               `trackitem` `ti3`, 
			               ((SELECT DISTINCT `trackid` 
			                 FROM   `trackitem` 
			                 WHERE  `trackid` NOT IN (SELECT `trackid` 
			                                        FROM   `trackitem` 
			                                        WHERE  `name` = 'ISA_number')) 
			                UNION 
			                (SELECT DISTINCT `trackid` 
			                 FROM   `trackitem`
			                 WHERE  `name` = 'ISA_number' 
			                        AND `value` = '')) `ti2` 
			        WHERE  `t`.`trackid` = `ti`.`trackid` 
			               AND `ti`.`trackid` = `ti2`.`trackid` 
			               AND `ti`.`trackid` = `ti3`.`trackid` 
			               AND NOT ( ( `t`.`ip` <= '129.237.201.999' 
			                           AND `t`.`ip` >= '129.237.201.0' ) 
			                          OR ( `t`.`ip` <= '129.237.229.999' 
			                               AND `t`.`ip` >= '129.237.229.0' ) ) 
			               AND `t`.`timestampmodified` >= '" . $date_1 . "' 
			               AND `t`.`timestampmodified` <= '" . $date_2 . "' 
			               AND `ti3`.`name` = 'app_version' 
			               AND `ti3`.`value` >= '" . $version_1 . "' 
			               AND `ti3`.`value` <= '" . $version_2 . "' 
			               AND `ti`.`name` = 'user_name' 
			               AND `ti`.`value` NOT IN ( 'rods', 'tlammer' ) 
			        GROUP  BY `ti2`.`trackid`) `ti6` 
			WHERE  `t`.`trackid` = `ti`.`trackid` 
			       AND `ti`.`trackid` = `ti2`.`trackid` 
			       AND `ti2`.`trackid` = `ti3`.`trackid` 
			       AND `ti3`.`trackid` = `ti5`.`trackid` 
			       AND `ti5`.`trackid` = `ti6`.`maxtid` 
			       AND `ti`.`name` = 'Discipline_name' 
			       AND `ti2`.`name` = 'Institution_name' 
			       AND `ti3`.`name` = 'Collection_name' 
			       AND `ti5`.`name` = 'Collection_number'  
	               AND `ti7`.`name` = 'num_co'
			       ".$hide_invalid_institutions_query."
			GROUP  BY `ti5`.`value` 
			ORDER  BY MAX(`timestampmodified`) DESC";


	echo '<input id="query" type="hidden" value="' . $query . '">';

	//exit();
	$info = $mysqli->query($query);

	if($info === FALSE)
		exit();

	$number_of_columns = $info->num_rows;

	echo '<div class="alert alert-info" id="stats">'.$number_of_columns.' collections</div>
	<ol>';

	if($number_of_columns == 0)
		exit();

	$institutions = [];
	while($results = $info->fetch_assoc()){

		$institution_name = $results['institution_name'];

		if(!array_key_exists($institution_name,$institutions))
			$institutions[$institution_name] = [];

		$discipline_name = $results['discipline_name'];
		if(!array_key_exists($discipline_name,$institutions[$institution_name]))
			$institutions[$institution_name][$discipline_name] = [];

		$isa_number = '';
		if(array_key_exists('isa_number',$results))
			$isa_number = '['.$results['isa_number'].']';

		$collection_name = $results['collection_name'];
		$institutions[$institution_name][$discipline_name][$collection_name] = [$results['track_id'],$isa_number.' '.$results['collection_name'].' ('.$results['discipline_name'] . '): ' . $results['date'] . ' ['.$results['co_count'].']'];

	}

	ksort($institutions);

	$institutions_count = count($institutions);
	$disciplines_count = 0;
	foreach($institutions as $institution_name => $disciplines){

		echo '
		<li>'.$institution_name.'
			<ul>';

				ksort($disciplines);
				foreach($disciplines as $discipline_name => $collections){

					echo '
					<li>'.$discipline_name.'
						<ul>';

							ksort($collections);
							foreach($collections as $collection_name => $data)
								echo '<li><a target="_blank" href="?track_id=' . $data[0] . '">'.$data[1].'</a></li>';

						echo '</ul>
					</li>';

					$disciplines_count++;

				}

			echo '</ul>
		</li>';

	}


	$info->close();
	echo '</ol>
	<script>
		const stats = $("#stats")[0];
		stats.innerHTML = "'.$institutions_count.' institutions<br>"+
			"'.$disciplines_count.' disciplines<br>"+ 
			stats.innerHTML;
	</script>';

}


footer();