<?php

const DATABASE = 'stats';
const CSS = 'stats';
const JQUERY = TRUE;
const JS = 'stats';

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

require_once('../components/header.php');


//GET SPECIFY VERSIONS
$specify_versions = [];

$query_2 = "SELECT DISTINCT `ti`.`value` AS 'spversion'
                           FROM `trackitem` `ti`
			   WHERE `ti`.`name` = 'app_version'
			   AND `ti`.`value` LIKE '6%'
			   ORDER BY `ti`.`value` DESC";

$info_2 = $mysqli->query($query_2);
while($results_2 = $info_2->fetch_row())
	$specify_versions[] = $results_2[0];
$info_2->close(); ?>


<form>

	<label for="datepicker1">Accessed between:</label>
	<input
			type="date"
			id="datepicker1"
			placeholder="Date 1"
			class="form-control">

	<label for="datepicker2">and</label>
	<input
			type="date"
			id="datepicker2"
			placeholder="Date 2"
			class="form-control"><br><br>

	<label for="versions1">Specify versions between:</label>
	<select id="versions1"
	        class="form-control">
	  <option value="">Select a Specify version:</option> <?php
		foreach($specify_versions as $value)
			echo "<option value=\"$value\">$value</option>"; ?>
	</select>

	<label for="versions2">and</label>
	<select id="versions2"
	        class="form-control">
		<option value="">Select a Specify version:</option> <?php
		foreach($specify_versions as $value)
			echo "<option value=\"$value\">$value</option>"; ?>
	</select><br><br>

	<label for="isa">ISA:</label>
	<select id="isa"
	        class="form-control">
		<option value="both">Either ISA or no ISA</option>
		<option value="ISA">Only ISA</option>
		<option value="not">No ISA</option>
	</select><br><br>

	<label for="box">Filter instructions:</label>
	<input
			placeholder="Filter Institutions"
			id="box"
			type="text"
			class="form-control"/><br><br>

	<input
			id="submit"
			type="submit"
			value="Search"
			class="btn btn-primary">

</form>

<img
		id="loadingImg"
		src="../images/loading.gif"
		style="display: none;"
		alt="Loading...">
<div id="Insts"></div>

<?php require_once('../components/footer.php');