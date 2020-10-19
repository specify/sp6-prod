<?php

$query = "SELECT * FROM `feedback` ORDER BY `FeedbackID` DESC";

$columns = ['FeedbackID','TimestampModified','Subject','Component','Issue','Comments','Id','OSName','OSVersion','JavaVersion','JavaVendor','AppVersion','Collection','Discipline','Division','Institution'];
$empty_columns = $columns;

$update_cache = array_key_exists('update_cache',$_GET) && $_GET['update_cache'] == 'true';
$cache = new Cache_query($query,'feedback.csv', $columns, $update_cache);
$data = $cache->get_result();

return "Feedback cache was refreshed<br>\n";