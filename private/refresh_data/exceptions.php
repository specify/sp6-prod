<?php

$query = "
	SELECT * FROM `exception`
	WHERE 
		`IP` NOT LIKE '129.237.%' AND
		`ClassName` <> 'edu.ku.brc.specify.ui.HelpMgr' AND
		`StackTrace` NOT LIKE 'java.lang.RuntimeException: Two controls have the same name%' AND
		`StackTrace` NOT LIKE 'Multiple %' AND
		`StackTrace` NOT LIKE 'edu.ku.brc.ui.forms.persist.FormCell%' AND
		`StackTrace` NOT LIKE 'java.lang.RuntimeException: Two controls have the same id%'
	ORDER BY `ExceptionID` DESC
	LIMIT ".EXCEPTIONS_LIMIT;

$columns = ['ExceptionID','TimestampCreated','TaskName','Title','Bug','Comments','stacktrace','ClassName','Id','OSName','OSVersion','JavaVersion','JavaVendor','UserName','IP','AppVersion','collection','discipline','division','institution','DoIgnore'];
$empty_columns = $columns;

$update_cache = array_key_exists('update_cache',$_GET) && $_GET['update_cache'] == 'true';
$cache = new Cache_query($query,'exceptions.csv', $columns, $update_cache);

$data = $cache->get_result();

return "Exceptions cache was refreshed<br>\n";