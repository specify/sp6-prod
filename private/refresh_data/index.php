<?php

global $no_head;
global $mysqli;

#ignore_user_abort(TRUE);
#set_time_limit ( 120 );

$target_dir = WORKING_DIRECTORY.'stats/';

if (!$mysqli->set_charset('utf8'))
	exit('Error loading character set utf8: '.$mysqli->error);

$query = "
SELECT     `ti_co`.`CountAmt`      AS `co_count`,
           `ti_inst`.`value`       AS 'institution_name',
           `ti_disc`.`value`       AS 'discipline_name',
           `ti_coll`.`value`       AS 'collection_name',
           `ti_coln`.`value`       AS 'collection_number',
           `t`.`trackid`           AS 'track_id',
           `t`.`TimestampCreated` AS 'timestamp'
FROM       `track` `t`
INNER JOIN `trackitem` `ti_coll`
      ON   `ti_coll`.`trackid` = `t`.`trackid`
      AND  `ti_coll`.`name` = 'Collection_name'
INNER JOIN `trackitem` `ti_disc`
      ON   `ti_disc`.`trackid` = `t`.`trackid`
      AND  `ti_disc`.`name` = 'Discipline_name'
INNER JOIN `trackitem` `ti_inst`
      ON   `ti_inst`.`trackid` = `t`.`trackid`
      AND  `ti_inst`.`name` = 'Institution_name'
      AND  `ti_inst`.`value` NOT IN ('',' ','.','?','-','\n')
      AND  `ti_inst`.`value` IS NOT NULL
INNER JOIN `trackitem` `ti_coln`
      ON   `ti_coln`.`trackid` = `t`.`trackid`
      AND  `ti_coln`.`name` = 'Collection_number'
INNER JOIN `trackitem` `ti_co`
      ON   `ti_co`.`trackid` = `t`.`trackid`
      AND  `ti_co`.`name` = 'num_co'
WHERE      `ti_coln`.`value` IN (
           SELECT DISTINCT `ti_coln`.`value`
           FROM            `track` `t`
           INNER JOIN      `trackitem` `ti_user`
                 ON        `ti_user`.`trackid` = `t`.`trackid`
                 AND       `ti_user`.`name` = 'user_name'
                 AND       `ti_user`.`value` NOT IN ('rods', 'tlammer' )
           INNER JOIN      `trackitem` `ti_coln`
                 ON        `ti_coln`.`trackid` = `t`.`trackid`
                 AND       `ti_coln`.`name` = 'Collection_number'
           WHERE NOT (
                (
                           `t`.`ip` <= '129.237.201.999' AND
                           `t`.`ip` >= '129.237.201.0'
                )
                OR (
                           `t`.`ip` <= '129.237.229.999' AND
                           `t`.`ip` >= '129.237.229.0'
                )
           )
    );";

$columns = ['co_count','institution_name','discipline_name','collection_name','collection_number','track_id','timestamp'];

$update_cache = array_key_exists('update_cache',$_GET);
$cache = new Cache_query($query,$target_dir,CACHE_DURATION, $columns, $update_cache);
$data = $cache->get_result();
$cache->get_status(null,TRUE);

$institutions = [];

foreach($data as $row){

	if(!array_key_exists($row['institution_name'],$institutions))
		$institutions[$row['institution_name']] = [];

	if(!array_key_exists($row['discipline_name'],$institutions[$row['institution_name']]))
		$institutions[$row['institution_name']][$row['discipline_name']] = [];

	if(!array_key_exists($row['collection_name'],$institutions[$row['institution_name']][$row['discipline_name']]))
		$institutions[$row['institution_name']][$row['discipline_name']][$row['collection_name']] = [];

	$institutions[$row['institution_name']][$row['discipline_name']][$row['collection_name']][] = [strtotime($row['timestamp']),$row['co_count'],$row['collection_number'],$row['track_id']];

}

foreach($institutions as $institution_name => &$disciplines)
	foreach($disciplines as $discipline_name => &$collections)
		foreach($collections as $collection_name => &$data)
			array_multisort($data,SORT_DESC);


function utf8ize( $mixed ) {

	if (is_array($mixed))
		foreach ($mixed as $key => $value)
			$mixed[$key] = utf8ize($value);

	elseif (is_string($mixed))
		return mb_convert_encoding($mixed, "UTF-8", "UTF-8");

	return $mixed;
}

$institutions = json_encode(utf8ize($institutions));
file_put_contents(WORKING_DIRECTORY.'data.json',$institutions);