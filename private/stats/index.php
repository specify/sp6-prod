<?php

global $no_head;

const DATABASE = 'stats';
const CSS = 'stats';
const JS = 'stats';
const JQUERY = TRUE;
const TIMEZONE = 'UTC';

require_once('../components/header.php');
require_file('../config/cache.php');
require_file('Cache_query.php');


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
$_GET['date_2'] = date('Y-m-d',$date_2); ?>


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
		<input list="days"  id="show_last_days" class="form-control">
		<datalist id="days">
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
$reports_count = 0;

foreach($institutions as $institution_number => &$disciplines){

	ksort($disciplines);
	foreach($disciplines as $discipline_number => &$collections){

		if($discipline_number=='institution_name')
			continue;

		ksort($collections);
		foreach($collections as $collection_number => &$reports){

			if($collection_number=='discipline_name')
				continue;

			krsort($reports);
			foreach($reports as $key => $report)
				if($key!='collection_name' && ($report[0]<$date_1 || $report[0]>$date_2))
					unset($reports[$key]);

			$local_reports_count = count($reports);
			if($local_reports_count==0)
				unset($collections[$collection_number]);
			else
				$reports_count += $local_reports_count;


		}

		$local_collections_count = count($collections);

		if($local_collections_count==0)
			unset($disciplines[$discipline_number]);
		else
			$collections_count += $local_collections_count;

	}

	$local_disciplines_count = count($disciplines);

	if($local_disciplines_count==0)
		unset($institutions[$institution_number]);
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
	<?=$reports_count?> reports<br>
</div>

<ol> <?php

	foreach($institutions as $institution_number => $institution_data){

		echo '<li><span>'.$institution_data['institution_name'].'</span>
				<ul>';
		unset($institution_data['institution_name']);

		foreach($institution_data as $discipline_number => $discipline_data){

			echo '<li><span>'.$discipline_data['discipline_name'].'</span>
					<ul>';
			unset($discipline_data['discipline_name']);

			foreach($discipline_data as $collection_number => $collection_data){

				echo '<li><a href="'.LINK.'collection/?collection_number=' . $collection_number . '">'.$collection_data['collection_name'].'</a>';
				$result = '<ul class="list_condensed">';
				unset($collection_data['collection_name']);

				$max_count = -1;
				foreach($collection_data as $unix_time => $report_data){

					$result .= '<li><a target="_blank" href="'.LINK.'track/?track_id=' . $report_data[1] . '">' . date(DATE_FORMATTER, $unix_time) . '</a> [' . $report_data[0] . ']</li>';

					if($max_count==-1 || $max_count<$report_data[0])
						$max_count = $report_data[0];

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

	const link = '<?=LINK?>';

	const view = '<?=$_GET['view']?>';
	let show_last_days_val = '';
	let search_query = '<?=$_GET['search_query']?>';
	let date1_val = <?=intval($date_1)?>;
	let date2_val = <?=intval($date_2)?>;

	const initial_institutions_count = '<?=$institutions_count?>';
	const initial_disciplines_count = '<?=$disciplines_count?>';
	const initial_collections_count = '<?=$collections_count?>';
	const initial_reports_count = '<?=$reports_count?>';

</script>
<script src="<?=LINK?>static/js/search<?=JS_EXTENSION?>"></script>

<?php footer();