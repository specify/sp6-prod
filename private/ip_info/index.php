<?php

require_once('../components/header.php');


if(!array_key_exists('ip',$_GET))
	header('Location: '.LINK);

$ip_address = $_GET['ip'];

$ip_data = json_decode(file_get_contents("http://ip-api.com/json/".$ip_address."?fields=country,regionName,city,org,reverse"),true);

$column_mapping = [
		'ip' => 'IP Address',
		'country' => 'Country',
		'regionName' => 'Region Name',
		'city' => 'City',
		'org' => 'Organization',
		'reverse' => 'Domain name',
]; ?>

<table class="table table-striped">

	<thead>

		<tr>

			<th>IP Address</th>
			<th><?=$ip_address?></th>

		</tr>

	</thead>

	<tbody> <?php

		foreach($ip_data as $key => $value){ ?>

			<tr>
				<td><?=$column_mapping[$key]?></td>
				<td><?=$value?></td>
			</tr> <?php

		} ?>

	</tbody>

</table>