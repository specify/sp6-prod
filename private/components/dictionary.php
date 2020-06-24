<?php

global $categories;
global $dictionary;

$dictionary_data = file_get_contents('../static/csv/dictionary.csv');
$dictionary_data = explode("\n", $dictionary_data);

$category = '';
$dictionary = [];

foreach($dictionary_data as $line){

	$line = explode(',', $line);

	if(count($line)==1){
		$category = $line[0];
		$dictionary[$category] = [];
	}
	else
		$dictionary[$category][$line[0]] = $line[1];

}

$categories = [
	'system_info' => 'System Information',
	'database_info' => 'Database Information',
	'database_stats' => 'Database Statistics',
	'usage_data' => 'Usage Information',
	'other' => 'Other',
];