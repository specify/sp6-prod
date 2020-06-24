<?php

global $no_head;
global $mysqli;

#ignore_user_abort(TRUE);
#set_time_limit ( 120 );

if(!defined('LINK')){

	define('DATABASE','stats');
	define('NO_HEAD',TRUE);
	define('TIMEZONE','UTC');

	require_once('../components/header.php');
	require_file('../config/cache.php');
	require_file('Cache_query.php');

}


if (!$mysqli->set_charset('utf8'))
	exit('Error loading character set utf8: '.$mysqli->error);

$query = "
SELECT     `ti_co`.`CountAmt`      AS `co_count`,
           `ti_inst`.`value`       AS 'institution_name',
           `ti_insn`.`value`       AS 'institution_number',
           `ti_disc`.`value`       AS 'discipline_name',
           `ti_disn`.`value`       AS 'discipline_number',
           `ti_coll`.`value`       AS 'collection_name',
           `ti_coln`.`value`       AS 'collection_number',
           `t`.`trackid`           AS 'track_id',
           `t`.`TimestampCreated`  AS 'timestamp'
FROM       `track` `t`
INNER JOIN `trackitem` `ti_coll`
      ON   `ti_coll`.`trackid` = `t`.`trackid`
      AND  `ti_coll`.`name` = 'Collection_name'    
INNER JOIN `trackitem` `ti_coln`
      ON   `ti_coln`.`trackid` = `t`.`trackid`
      AND  `ti_coln`.`name` = 'Collection_number'
INNER JOIN `trackitem` `ti_disc`
      ON   `ti_disc`.`trackid` = `t`.`trackid`
      AND  `ti_disc`.`name` = 'Discipline_name'
INNER JOIN `trackitem` `ti_disn`
      ON   `ti_disn`.`trackid` = `t`.`trackid`
      AND  `ti_disn`.`name` = 'Discipline_number'
	  AND  `ti_disn`.`value` <> ''
INNER JOIN `trackitem` `ti_inst`
      ON   `ti_inst`.`trackid` = `t`.`trackid`
      AND  `ti_inst`.`name` = 'Institution_name'
      AND  `ti_inst`.`value` NOT IN ('',' ','.','?','-','\n')
      AND  `ti_inst`.`value` IS NOT NULL
INNER JOIN `trackitem` `ti_insn`
      ON   `ti_insn`.`trackid` = `t`.`trackid`
      AND  `ti_insn`.`name` = 'Institution_number'
	  AND  `ti_insn`.`value` <> ''
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
                 AND       `ti_coln`.`value` <> ''
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
    )
ORDER BY `t`.`TimestampCreated` DESC";

$columns = ['co_count','institution_name','institution_number','discipline_name','discipline_number','collection_name','collection_number','track_id','timestamp'];

$update_cache = array_key_exists('update_cache',$_GET) && $_GET['update_cache']=='true';

$cache = new Cache_query($query,'stats.csv', $columns, $update_cache);
$data = $cache->get_result();
$cache->get_status(null,TRUE);

$target_file = WORKING_DIRECTORY.'data.json';
if($cache->cache_was_recreated){

	$institutions = [];

	foreach($data as $row){

		if(!array_key_exists($row['institution_number'],$institutions))
			$institutions[$row['institution_number']] = ['institution_name' => $row['institution_name']];

		if(!array_key_exists($row['discipline_number'],$institutions[$row['institution_number']]))
			$institutions[$row['institution_number']][$row['discipline_number']] = ['discipline_name' => $row['discipline_name']];

		if(!array_key_exists($row['collection_number'],$institutions[$row['institution_number']][$row['discipline_number']]))
			$institutions[$row['institution_number']][$row['discipline_number']][$row['collection_number']] = [ 'collection_name' => $row['collection_name']];

		$institutions[$row['institution_number']][$row['discipline_number']][$row['collection_number']][strtotime($row['timestamp'])] = [$row['co_count'],$row['track_id']];

	}

	function utf8ize( $mixed ) {

		if (is_array($mixed))
			foreach ($mixed as $key => $value)
				$mixed[$key] = utf8ize($value);

		elseif (is_string($mixed))
			return mb_convert_encoding($mixed, "UTF-8", "UTF-8");

		return $mixed;
	}

	$institutions = json_encode(utf8ize($institutions));
	file_put_contents($target_file,$institutions);

}
else
	$institutions = json_decode(file_get_contents($target_file),TRUE);