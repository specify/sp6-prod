<?php

const DATABASE = 'stats';
const JS = 'collection';
const JQUERY = TRUE;

require_once('../components/header.php');
require_file('dictionary.php');
require_file('charts.php');

if(!array_key_exists('collection_number',$_GET) || !is_numeric($_GET['collection_number']) || $_GET['collection_number']=='')
	exit('Invalid URL');

$collection_number = $_GET["collection_number"];


foreach($dictionary as $category => $category_data){ ?>

	<label class="mb-4"><?=$categories[$category]?>
		<select
			name="<?=$category?>"
			class="form-control">

			<option value=""></option><?php
			foreach($category_data as $column_name => $human_name){

				if($category=='database_stats' && $column_name=='num_co')
					$selected = 'selected';
				else
					$selected = ''; ?>

				<option value="<?=$column_name?>" <?=$selected?>><?=$human_name?></option><?php

			} ?>

		</select>
	</label><br><?php

} ?>
<div id="alert" class="alert alert-info">Loading...</div>
<div id="result"></div>
<script>
	const link = '<?=LINK?>';
	const collection_number = '<?=$collection_number?>';
</script><?php


footer();