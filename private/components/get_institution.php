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
	AND `ti_inst`.`value` != ''
	AND `ti_inst`.`value` != '\n'
	AND `ti_inst`.`value` != ' '
	AND `ti_inst`.`value` != '.'
	AND `ti_inst`.`value` != '?'
	AND `ti_inst`.`value` != '-'
	AND `ti_inst`.`value` IS NOT NULL
";


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

else {
	$isa = $_GET['isa']; ?>

	<h2>Select an Institution:</h2><?php

	if($isa == 'true')
		$query = "
SELECT   `ti_inst`.`value`                                                                             AS 'institution_name',
         `ti_disc`.`value`                                                                             AS 'discipline_name',
         `ti_coll`.`value`                                                                             AS 'collection_name',
         `ti_isa`.`value`                                                                              AS 'isa_number',
         `ti_co`.`countamt`                                                                            AS 'co_count',
         SUBSTRING_INDEX(GROUP_CONCAT(`ti_disc`.`trackid` ORDER BY `timestampmodified` DESC), ',', 1 ) AS 'track_id',
         MAX(`t`.`timestampmodified`)                                                                  AS 'date'
FROM     `trackitem` `ti_disc`, 
         `trackitem` `ti_inst`, 
         `trackitem` `ti_coll`, 
         `trackitem` `ti_isa`, 
         `trackitem` `ti_co`, 
         `track` `t`, 
         ( 
                  SELECT   MAX(`ti_disc`.`trackid`) AS 'maxtid' 
                  FROM     `trackitem` `ti_disc`, 
                           `trackitem` `ti_inst`, 
                           `trackitem` `ti_coll`, 
                           `track` `t` 
                  WHERE    `t`.`trackid` = `ti_disc`.`trackid` 
	                  AND  `ti_inst`.`trackid` = `ti_disc`.`trackid` 
	                  AND  `ti_coll`.`trackid` = `ti_disc`.`trackid` 
	                  AND  `ti_disc`.`name` = 'ISA_number' 
	                  AND      NOT ( ( 
	                                             `t`.`ip` <= '129.237.201.999' 
	                                    AND      `t`.`ip` >= '129.237.201.0' ) 
	                           OR       ( 
	                                             `t`.`ip` <= '129.237.229.999' 
	                                    AND      `t`.`ip` >= '129.237.229.0' ) ) 
	                  AND  `t`.`timestampmodified` >= '" . $date_1 . "' 
	                  AND  `t`.`timestampmodified` <= '" . $date_2 . "' 
	                  AND  `ti_disc`.`value` != '' 
	                  AND  `ti_inst`.`name` = 'app_version' 
	                  AND  `ti_inst`.`value` >= '" . $version_1 . "' 
	                  AND  `ti_inst`.`value` <= '" . $version_2 . "' 
	                  AND  `ti_coll`.`name` = 'user_name' 
	                  AND  `ti_coll`.`value` NOT IN ( 'rods', 
	                                                     'tlammer' ) 
                  GROUP BY `ti_disc`.`value`) `ti6` 
WHERE    `t`.`trackid` = `ti_disc`.`trackid` 
	AND  `ti_inst`.`trackid` = `ti_disc`.`trackid` 
	AND  `ti_coll`.`trackid` = `ti_disc`.`trackid` 
	AND  `ti_isa`.`trackid` = `ti_disc`.`trackid` 
	AND  `ti6`.`maxtid` = `ti_disc`.`trackid` 
	AND  `ti_co`.`trackid` = `ti_disc`.`trackid` 
	AND  `ti_co`.`name` = 'num_co' 
	AND  `ti_disc`.`name` = 'Discipline_name' 
	AND  `ti_inst`.`name` = 'Institution_name' 
	AND  `ti_coll`.`name` = 'Collection_name' 
	AND  `ti_isa`.`name` = 'ISA_number' 
	AND  `ti_isa`.`value` != '' ".$hide_invalid_institutions_query." 
GROUP BY `ti_isa`.`value` 
ORDER BY max(`timestampmodified`) DESC";

	else
		$query = "
SELECT   `ti_inst`.`value`                                                                             AS 'institution_name',
         `ti_disc`.`value`                                                                             AS 'discipline_name',
         `ti_coll`.`value`                                                                             AS 'collection_name',
         `ti_co`.`countamt`                                                                            AS 'co_count',
         SUBSTRING_INDEX(GROUP_CONCAT(`ti_disc`.`trackid` ORDER BY `timestampmodified` DESC), ',', 1 ) AS 'track_id',
         MAX(`t`.`timestampmodified`)                                                                  AS 'date'
FROM     `trackitem` `ti_disc`, 
         `trackitem` `ti_inst`, 
         `trackitem` `ti_coll`, 
         `trackitem` `ti_coln`, 
         `trackitem` `ti_co`, 
         `track` `t`, 
         ( 
                  SELECT   MAX(`ti_disc`.`trackid`) AS 'maxtid', 
                           COUNT(*) 
                  FROM     `trackitem` `ti_disc`, 
                           `track` `t`, 
                           `trackitem` `ti_inst`, 
                           `trackitem` `ti_coll` 
                  WHERE    `t`.`trackid` = `ti_disc`.`trackid` 
	                  AND  `ti_inst`.`trackid` = `ti_disc`.`trackid` 
	                  AND  `ti_coll`.`trackid` = `ti_disc`.`trackid` 
	                  AND  NOT ( ( 
	                                             `t`.`ip` <= '129.237.201.999' 
	                                    AND      `t`.`ip` >= '129.237.201.0' ) 
	                           OR       ( 
	                                             `t`.`ip` <= '129.237.229.999' 
	                                    AND      `t`.`ip` >= '129.237.229.0' ) ) 
	                  AND  `t`.`timestampmodified` >= '" . $date_1 . "' 
	                  AND  `t`.`timestampmodified` <= '" . $date_2 . "' 
	                  AND  `ti_coll`.`name` = 'app_version' 
	                  AND  `ti_coll`.`value` >= '" . $version_1 . "' 
	                  AND  `ti_coll`.`value` <= '" . $version_2 . "' 
	                  AND  `ti_disc`.`name` = 'user_name' 
	                  AND  `ti_disc`.`value` NOT IN ( 'rods', 
	                                                     'tlammer' ) 
                  GROUP BY `ti_inst`.`trackid`) `ti6` 
WHERE    `t`.`trackid` = `ti_disc`.`trackid` 
	AND  `ti_inst`.`trackid` = `ti_disc`.`trackid` 
	AND  `ti_coll`.`trackid` = `ti_disc`.`trackid` 
	AND  `ti_coln`.`trackid` = `ti_disc`.`trackid` 
	AND  `ti6`.`maxtid` = `ti_disc`.`trackid` 
	AND  `ti_co`.`trackid` = `ti_disc`.`trackid` 
	AND  `ti_co`.`name` = 'num_co' 
	AND  `ti_disc`.`name` = 'Discipline_name' 
	AND  `ti_inst`.`name` = 'Institution_name' 
	AND  `ti_coll`.`name` = 'Collection_name' 
	AND  `ti_coln`.`name` = 'Collection_number' ".$hide_invalid_institutions_query." 
GROUP BY `ti_coln`.`value` 
ORDER BY max(`timestampmodified`) DESC";

	echo '<input id="query" type="hidden" value="' . $query . '">';

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
		$discipline_name = $results['discipline_name'];
		$track_id = $results['track_id'];
		$collection_name = $results['collection_name'];
		$date = $results['date'];
		$co_count = $results['co_count'];

		if(!array_key_exists($institution_name,$institutions))
			$institutions[$institution_name] = [];

		if(!array_key_exists($discipline_name,$institutions[$institution_name]))
			$institutions[$institution_name][$discipline_name] = [];

		$result = [];

		$result['Date Accessed'] = $date;
		if(array_key_exists('isa_number',$results))
			$result['ISA Number'] = $results['isa_number'];

		$institutions[$institution_name][$discipline_name][] = [$track_id,$collection_name,$co_count,$result];

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
							foreach($collections as $data){

								echo '<li>
									<a target="_blank" href="?track_id=' . $data[0] . '">' . $data[1] . '</a> [' . $data[2] . ']<br>
									<ul>';

										foreacH($data[3] as $key => $value)
											echo '<li>'.$key.': '.$value.'</li>';

									echo '</ul>
								</li>';

							}

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