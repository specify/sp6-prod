<?php

const DATABASE = 'exception';

require_once('../components/header.php');

$date_24_days_ago = date('Y-m-d 00:00:00', time() - 86400*24);

$query = "
	SELECT * FROM `exception`
	WHERE 
		`TimestampCreated` > '" . $date_24_days_ago . "' AND
		`IP` NOT LIKE '129.237.%' AND
		`ClassName` <> 'edu.ku.brc.specify.ui.HelpMgr' AND
		`StackTrace` NOT LIKE 'java.lang.RuntimeException: Two controls have the same name%' AND
		`StackTrace` NOT LIKE 'Multiple %' AND
		`StackTrace` NOT LIKE 'edu.ku.brc.ui.forms.persist.FormCell%' AND
		`StackTrace` NOT LIKE 'java.lang.RuntimeException: Two controls have the same id%'
	ORDER BY `ExceptionID` DESC";

echo $query;
$result = $mysqli->query($query);

$data = [];
while($row = $result->fetch_assoc())
  $data[]=$row;
$result->close();

$total_number_of_occurrences = [];
$exception_ids = [];
$file_location = [];
$keys = []; ?>


<table class="table table-striped mt-5 mb-5">
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

			$exception_id = $row['ExceptionID'];
			$exception_statement = $row['stacktrace'];

			$exception_statement_index = strpos($exception_statement, "edu.ku.brc");
			if($exception_statement_index !== FALSE){

				$file_name_begins = strpos($exception_statement, "(", $exception_statement_index);
				$file_name_ends = strpos($exception_statement, ")", $exception_statement_index);
				$end_of_line_position = strpos($exception_statement,"\n");

				$file_name = substr($exception_statement, $file_name_begins + 1, ($file_name_ends - $file_name_begins - 1));
				$error_string = substr($exception_statement, $exception_statement_index, ($file_name_begins-$exception_statement_index));

				/*if($exception_id==412698){
					var_dump($end_of_line_position,
					substr($exception_statement, $end_of_line_position+1),
					strpos(substr($exception_statement, $end_of_line_position+1),"\n"),
					substr($exception_statement, $end_of_line_position, strpos(substr($exception_statement, $end_of_line_position+1),"\n")));exit();

				}*/

				//if(substr($error_string,0,-1)=="java.lang.NullPointerException")
				//	$error_string = substr($error_string,0,-1).' '.substr($exception_statement, $end_of_line_position, strpos(substr($exception_statement, $end_of_line_position+1),"\n"));

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

function print_headers($headers){ ?>

	<thead>
		<tr> <?php
			foreach($headers as $header)
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

			if($row_name =="ExceptionID")
				echo '<td><a id="'.$value.'">'.$value.'</td>';

			else
				echo "<td>$value</td>";

		}
	echo '</tr>';
}

echo '</tbody>';


require_once('../components/footer.php');