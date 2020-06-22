<?php

global $no_head;

const DATABASE = 'stats';
const CSS = 'stats';
const JS = 'stats';
const JQUERY = TRUE;
const TIMEZONE = 'UTC';

require_once('../components/header.php');
require_once('../components/Cache_query.php');


//GET SPECIFY VERSIONS (and cache them)
$specify_versions = [];

$query = "SELECT DISTINCT `ti`.`value` AS 'specify_version'
                           FROM `trackitem` `ti`
			   WHERE `ti`.`name` = 'app_version'
			   AND `ti`.`value` LIKE '6%'
			   ORDER BY `ti`.`value` DESC";
$columns = ['version'];
$versions_cache = new Cache_query($query,WORKING_DIRECTORY.'versions/',CACHE_DURATION, $columns,FALSE,'','A');
$specify_versions = $versions_cache->get_result();

//INITIALIZE GET PARAMETERS
$possible_parameters = ['date_1','date_2','show_last_days','update_cache','search_query','view'];
foreach($possible_parameters as $parameter)
	if(!array_key_exists($parameter,$_GET))
		$_GET[$parameter] = '';


$no_head = TRUE;
require_file('../refresh_data/index.php');

$date_1 = $_GET['date_1'];
$date_2 = $_GET['date_2'];

if(is_numeric($_GET['show_last_days'])){

	$show_last_days = $_GET['show_last_days'];

	$date_2 = $_SERVER['REQUEST_TIME'];
	$date_1 = $date_2 - 86400 * $show_last_days;

}

if(!is_numeric($date_1))
	$date_1 = 0;

if(!is_numeric($date_2))
	$date_2 = $_SERVER['REQUEST_TIME'];

if($date_1>$date_2)
	[$date_1,$date_2] = [$date_2,$date_1];


$_GET['date_1'] = date('Y-m-d',$date_1);
$_GET['date_2'] = date('Y-m-d',$date_2);?>


<form class="mb-4" id="controls">

	<label for="datepicker1">Accessed between:</label>
	<input
			type="date"
			id="datepicker1"
			placeholder="Date 1"
			class="form-control"
			value="<?=$_GET['date_1']?>">

	<label for="datepicker2">and</label>
	<input
			type="date"
			id="datepicker2"
			placeholder="Date 2"
			class="form-control"
			value="<?=$_GET['date_2']?>">

	<label>
		OR Show last
		<input list="browsers"  id="show_last_days" class="form-control">
		<datalist id="browsers">
			<option value="15">
			<option value="30">
			<option value="45">
			<option value="60">
		</datalist>
		days
	</label>
	<br><br>

	<div class="d-flex" style="align-items: flex-start">
		<a
			id="refresh_data_link"
			class="btn btn-success mr-4"
			href="#">Refresh Data</a>

		<label>
			<input
					id="filter"
					type="text"
					class="form-control"
					placeholder="Search Query"
					value="<?=$_GET['search_query']?>">
		</label>
	</div>

</form> <?php


$institutions = json_decode(file_get_contents(WORKING_DIRECTORY.'data.json'),TRUE);

if(count($institutions) == 0)
	exit();

//filtering and counting
$institutions_count = 0;
$disciplines_count = 0;
$collections_count = 0;
$records_count = 0;

foreach($institutions as $institution_name => &$disciplines){

	ksort($disciplines);
	foreach($disciplines as $discipline_name => &$collections){

		ksort($collections);
		foreach($collections as $collection_name => &$records){

			foreach($records as $key => $record)
				if($record[0]<$date_1 || $record[0]>$date_2)
					unset($records[$key]);

			$local_records_count = count($records);
			if($local_records_count==0)
				unset($collections[$collection_name]);
			else
				$records_count += $local_records_count;

		}

		$local_collections_count = count($collections);

		if($local_collections_count==0)
			unset($disciplines[$discipline_name]);
		else
			$collections_count += $local_collections_count;

	}

	$local_disciplines_count = count($disciplines);

	if($local_disciplines_count==0)
		unset($institutions[$institution_name]);
	else
		$disciplines_count += $local_disciplines_count;

	$institutions_count++;

}

unset($disciplines);
unset($collections);
unset($data); ?>


<div class="alert alert-info" id="stats">
	<?=$institutions_count?> institutions<br>
	<?=$disciplines_count?> disciplines<br>
	<?=$collections_count?> collections<br>
	<?=$records_count?> records<br>
</div>

<ol> <?php

	foreach($institutions as $institution_name => $disciplines){

		echo '<li>'.$institution_name.'
				<ul>';

		foreach($disciplines as $discipline_name => $collections){

			echo '
				<li>'.$discipline_name.'
					<ul>';

			foreach($collections as $collection_name => $data){

				echo '<li><a href="'.LINK.'collection/?collection_number=' . $record[2] . '">'.$collection_name.'</a>';
				$result = '<ul class="list_condensed">';

				$max_count = -1;
				foreach($data as $record){

					$result .= '<li><a target="_blank" href="'.LINK.'track/?track_id=' . $record[3] . '">' . date('Y F j D', $record[0]) . '</a> [' . $record[1] . ']</li>';

					if($max_count==-1 || $max_count<$record[1])
						$max_count = $record[1];

				}

				echo ' <a href="#" class="opener">['.$max_count.']</a>'.$result.'
					</ul>
				</li>';

			}

			echo '</ul>
				</li>';

		}

		echo '</ul>
	</li>';

	} ?>

</ol>

<script>

	const link = '<?=LINK.'stats/'?>';

	const view = '<?=$_GET['view']?>';
	let show_last_days_val = '';
	let search_query = '<?=$_GET['search_query']?>';
	let date1_val = <?=intval($date_1)?>;
	let date2_val = <?=intval($date_2)?>;

</script>

<?php footer();