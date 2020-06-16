<?php

const DATABASE = 'stats';
const CSS = 'stats';
const JS = 'stats';
const JQUERY = TRUE;


require_once('../components/header.php');


//GET SPECIFY VERSIONS (and cache them)
$specify_versions = [];

if(array_key_exists('versions',$_COOKIE))
	$specify_versions = explode('A',$_COOKIE['versions']);
else {

	$query_2 = "SELECT DISTINCT `ti`.`value` AS 'specify_version'
	                           FROM `trackitem` `ti`
				   WHERE `ti`.`name` = 'app_version'
				   AND `ti`.`value` LIKE '6%'
				   ORDER BY `ti`.`value` DESC";

	$info_2 = $mysqli->query($query_2);
	while($results_2 = $info_2->fetch_row())
		$specify_versions[] = $results_2[0];
	$info_2->close();

	setcookie('versions',implode('A',$specify_versions),time()+86400*30,'/');
}


//GET PARAMETERS

$possible_parameters = ['date_1','date_2','version_1','version_2','isa','institution','track_id','show_last_days'];
foreach($possible_parameters as $parameter)
	if(!array_key_exists($parameter,$_GET))
		$_GET[$parameter] = ''; ?>


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
		<input list="browsers"  id="show_last_days" class="form-control" value="<?=$_GET['show_last_days']?>">
		<datalist id="browsers">
			<option value="15">
			<option value="30">
			<option value="45">
			<option value="60">
		</datalist>
		days
	</label>
	<br><br>

	<label for="versions1">Specify versions between:</label>
	<select id="versions1"
	        class="form-control"> <?php
		foreach($specify_versions as $value){

			$selected = '';
			if($value==$_GET['version_1'])
				$selected = ' selected';

			echo '<option value="'.$value.'" '.$selected.'>'.$value.'</option>';

		} ?>
	</select>

	<label for="versions2">and</label>
	<select id="versions2"
	        class="form-control"> <?php
		foreach($specify_versions as $value){

			$selected = '';
			if($value == $_GET['version_2'])
				$selected = ' selected';

			echo '<option value="'.$value.'" '.$selected.'>'.$value.'</option>';

		} ?>
	</select><br><br>


	<label class="form-check">
		<input type="checkbox" class="form-check-input" id="isa" <?=($_GET['isa']==='true')?'checked':''?>>
		Only show institutions that have an ISA Number
	</label><br>

	<a
		id="submit"
		class="btn btn-primary btn-lg"
		href="#">Search</a>

</form>

<img
		id="loading"
		style="display: none"
		src="<?=LINK?>static/img/loading.gif"
		alt="Loading...">

<?php exit();
if($_SERVER['QUERY_STRING']!=''){

	if($_GET['track_id'] != '' || $_GET['institution']!=='')
		echo '<script>$(\'#controls\').hide()</script>';
	else {?>
		<label for="search" id="filter">Filter:
			<input
					type="text"
					class="form-control"/>
		</label><br><br><?php
	} ?>

	<div id="tab">
		<?=file_get_contents(LINK.'components/get_institution.php?'.$_SERVER['QUERY_STRING']);?>
	</div> <?php

} ?>

<script>
	const target_link = '<?=LINK.'stats/'?>';
</script>

<?php footer();