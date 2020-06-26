<?php

const DATABASE = 'exception';

require_once('../components/header.php');
require_once('../config/cache.php');
require_once('../components/Cache_query.php');
require_once('../refresh_data/exceptions.php');

$total_number_of_occurrences = [];
$exception_ids = [];
$file_location = [];
$keys = [];

$cache->get_status(LINK,'exceptions'); ?>

<table class="table table-striped mt-4 mb-4">
	<thead>
		<tr>
			<th>File Location</th>
			<th>Count</th>
			<th>Error</th>
			<th>Ids</th>
		</tr>
	</thead>
	<tbody> <?php

		foreach($data as $key => $row){


			if(!array_key_exists('stacktrace',$row))
				continue;

			$exception_id = $row['ExceptionID'];
			$exception_statement = $row['stacktrace'];

			foreach($empty_columns as $key_2 => $column)//hide sql cols that are always empty
				if(strlen($column)!==0)
					unset($empty_columns[$key_2]);


			$exception_statement_index = strpos($exception_statement, "edu.ku.brc");
			if($exception_statement_index !== FALSE){

				$file_name_begins = strpos($exception_statement, "(", $exception_statement_index);
				$file_name_ends = strpos($exception_statement, ")", $exception_statement_index);
				$end_of_line_position = strpos($exception_statement,"\n");

				$file_name = substr($exception_statement, $file_name_begins + 1, ($file_name_ends - $file_name_begins - 1));
				$error_string = substr($exception_statement, $exception_statement_index, ($file_name_begins-$exception_statement_index));

				if(array_key_exists($error_string,$total_number_of_occurrences))
					$total_number_of_occurrences[$error_string]++;
				else
					$total_number_of_occurrences[$error_string] = 1;

				$file_location[$error_string] = $file_name;
				$exception_ids[$error_string][] = $exception_id;

				$keys[] = $error_string;

			}

		}

		$mappings = [];
		$count_keys = [];
		foreach($total_number_of_occurrences as $error_string => $number_of_occurrences){

			$formatted_ids = '';
			foreach($exception_ids[$error_string] as $file_name_begins)
				if(strlen($file_name_begins) > 0)
					$formatted_ids .= '<a href="#'.$file_name_begins.'">'.$file_name_begins.'</a>, ';

			$formatted_ids = substr($formatted_ids,0,-strlen(', '));

			$s = '<tr>
					  <td>' . $file_location[$error_string] . '</td>
					  <td>' . $number_of_occurrences . '</td>
					  <td>' . $error_string . '</td>
				      <td>' . $formatted_ids . '</td>
				  </tr>';

			$count_keys[] = $number_of_occurrences;
			$mappings[$number_of_occurrences] = $s;
		}

		arsort($count_keys);

		foreach($count_keys as $n)
			echo $mappings[$n]; ?>

	</tbody>
</table>


<table class="table table-striped mb-5"> <?php

function print_headers($headers){

	global $empty_columns;?>

	<thead>
		<tr> <?php
			foreach($headers as $header)
				if(array_search($header,$empty_columns)===FALSE)
					echo "<th>$header</th>"; ?>
		</tr>
	</thead>
	<tbody> <?php

}


if(count($data)>1)
	print_headers(array_keys($data[0]));

foreach($data as $key => $row){

	echo '<tr>';
		foreach($row as $row_name => $value){

			if(array_search($row_name,$empty_columns)!==FALSE)
				continue;

			if($row_name =="ExceptionID")
				echo '<td><a id="'.$value.'">'.$value.'</td>';

			else
				echo "<td>$value</td>";

		}
	echo '</tr>';
} ?>

</tbody>
</table>

<?php
footer();