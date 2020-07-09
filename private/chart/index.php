<?php

const DATABASE = 'stats';
const JQUERY = TRUE;
const NO_HEAD = TRUE;

require_once('../components/header.php');
require_once('../components/dictionary.php');

if( !array_key_exists('collection_number',$_GET) ||
	!is_numeric($_GET['collection_number']) ||
	$_GET['collection_number']=='' ||
	!array_key_exists('category_name',$_GET) ||
	!in_array($_GET['category_name'],array_keys($categories)) ||
	!array_key_exists('selected_field',$_GET) ||
	!in_array($_GET['selected_field'],array_keys($dictionary[$_GET['category_name']])))
	exit('Invalid URL');

$collection_number = $_GET["collection_number"];
$category_name = $_GET['category_name'];
$selected_field = $_GET['selected_field'];

$query = "
SELECT     `ti_co`.`countamt` AS 'count',
           `ti_co`.`value` AS 'value',
           `t`.`TimestampCreated` AS `date`,
           `t`.`trackid` AS `track_id`
FROM       `track` `t`
INNER JOIN `trackitem` `ti_coln`
      ON   `ti_coln`.`trackid` = `t`.`trackid`
      AND  `ti_coln`.`name` = 'Collection_number'
      AND  `ti_coln`.`value` = ".$collection_number."
INNER JOIN `trackitem` `ti_co`
      ON   `ti_co`.`trackid` = `t`.`trackid`
      AND  `ti_co`.`name` = '".$selected_field."'
ORDER BY   `t`.`TimestampCreated` DESC";

$result = $mysqli->query($query);
echo '<input id="query" type="hidden" value="' . $query . '">';

$show_chart = NULL;
$row = $result->fetch_assoc();

//if($row == NULL)
//	echo 'Not enough information';

//else
if($row['count']!==NULL){

	$track_ids = [];
	$labels = [];
	$data = [];

	do {

		if($row['count']==null)
			continue;

		$labels[] = date(TIMESTAMP_FORMATTER, strtotime($row['date']));
		$data[] = $row['count'];
		$track_ids[] = $row['track_id'];

	} while($row = $result->fetch_assoc());

	$track_ids = array_reverse($track_ids);
	$labels = array_reverse($labels);
	$data = array_reverse($data); ?>

	<canvas id="chart" width="1000" height="300"></canvas>
	<script>

		chart = $('#chart');
		labels = JSON.parse('<?=json_encode($labels)?>');
		data = JSON.parse('<?=json_encode($data)?>');
		track_ids = JSON.parse('<?=json_encode($track_ids)?>');

		chart_object = create_chart(chart,
			'<?=$dictionary[$_GET['category_name']][$_GET['selected_field']]?>',
			labels,
			data,{
				onClick: () => {//redirect to main page when clicked on the record

					const timestamp = chart_object.chart.getElementAtEvent(event)[0]._model.label;
					const track_id = track_ids[labels.indexOf(timestamp)];

					window.location.href = link+'track/?track_id='+track_id;

				}
			});
	</script> <?php

}
else { ?>

	<table class="table table-striped">

		<thead>
			<tr>
				<th>Timestamp</th>
				<th><?=$dictionary[$_GET['category_name']][$_GET['selected_field']]?></th>
			</tr>
		</thead>
		<tbody> <?php

			do {

				if($row['value']==null || $row['value']=='')
					continue; ?>

				<tr>
					<th><a href="<?=LINK?>track/?track_id=<?=$row['track_id']?>"><?=date(TIMESTAMP_FORMATTER, strtotime($row['date']))?></a></th>
					<th><?=$row['value']?></th>
				</tr>

				<?php
			} while($row = $result->fetch_assoc()) ?>

		</tbody>

	</table> <?php

}

$result->close();