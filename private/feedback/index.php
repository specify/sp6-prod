<?php

const DATABASE = 'feedback';

require_once('../components/header.php');
require_once('../components/Cache_query.php');



$query = "SELECT * FROM `feedback` ORDER BY `FeedbackID` DESC";

$columns = ['FeedbackID','TimestampCreated','Subject','Component','Issue','Comments','Id','OSName','OSVersion','JavaVersion','JavaVendor','AppVersion','Collection','Discipline','Division','Institution'];
$empty_columns = $columns;

$update_cache = array_key_exists('update_cache',$_GET) && $_GET['update_cache'] == 'true';
$cache = new Cache_query($query,WORKING_DIRECTORY.'cache/','feedback.csv',CACHE_DURATION, $columns, WORKING_DIRECTORY.'cache_info.json', $update_cache);
$data = $cache->get_result();

$number_of_results = count($data);

$cache->get_status($number_of_results); ?>

<table class="table table-striped mt-5 mb-5"> <?php

if($number_of_results>0) { ?>

    <thead>
		<tr> <?php
            foreach(array_keys($data[0]) as $header)
                echo "<th>$header</th>"; ?>
		</tr>
	</thead>
    <tbody> <?php

}


foreach($data as $feedback){
    echo '<tr>';

    foreach($feedback as $value)
        echo '<td>'.$value.'</td>';

    echo '</tr>';
}

echo '</tbody></table>';

footer();