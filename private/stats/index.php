<?php

const DATABASE = 'stats';
const CSS = 'stats';
const JS = 'stats';
const JQUERY = TRUE;


require_once('../components/header.php');


//GET SPECIFY VERSIONS
$specify_versions = [];

$query_2 = "SELECT DISTINCT `ti`.`value` AS 'specify_version'
                           FROM `trackitem` `ti`
			   WHERE `ti`.`name` = 'app_version'
			   AND `ti`.`value` LIKE '6%'
			   ORDER BY `ti`.`value` DESC";

$info_2 = $mysqli->query($query_2);
while($results_2 = $info_2->fetch_row())
	$specify_versions[] = $results_2[0];
$info_2->close();


//GET PARAMETERS

$possible_parameters = ['date_1','date_2','version_1','version_2','isa','collection','track_id'];
foreach($possible_parameters as $parameter)
	if(!array_key_exists($parameter,$_GET))
		$_GET[$parameter] = '';

?>


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
		<input type="number" id="show_last_days" class="form-control" value="<?=$_GET['show_last_days']?>">
		days
	</label>
	<br><br>

	<label for="versions1">Specify versions between:</label>
	<select id="versions1"
	        class="form-control">
	  <option value="">Specify version</option> <?php
		foreach($specify_versions as $value){

			$selected = '';
			if($value==$_GET['version_1'])
				$selected = ' selected';

			echo "<option value=\"$value\" '.$selected.'>$value</option>";

		}?>
	</select>

	<label for="versions2">and</label>
	<select id="versions2"
	        class="form-control">
		<option value="">Specify version</option> <?php
		foreach($specify_versions as $value){

			$selected = '';
			if($value == $_GET['version_2'])
				$selected = ' selected';

			echo "<option value=\"$value\" '.$selected.'>$value</option>";

		}?>
	</select><br><br>

	<label for="isa">ISA:</label>
	<select id="isa"
	        class="form-control">
		<option value="both" <?php if($_GET['isa'] == 'both') echo 'selected'?>>All institutions</option>
		<option value="ISA" <?php if($_GET['isa'] == 'ISA') echo 'selected'?>>Institutions with ISA</option>
		<option value="not" <?php if($_GET['isa'] == 'not') echo 'selected'?>>Institutions without ISA</option>
	</select><br><br>

	<input
			id="submit"
			type="submit"
			value="Search"
			class="btn btn-primary btn-lg">

</form>

<label for="search">Filter:
	<input
			id="label"
			type="text"
			class="form-control"/>
</label><br><br>

<div id="tab"></div>

<script>
	const link = '<?=LINK.'stats/'?>';
</script>

<?php footer();