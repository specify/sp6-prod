<?php

const DATABASE = 'feedback';

require_once('../components/header.php');


$sql = "SELECT * FROM `feedback` ORDER BY `FeedbackID` DESC";
$result = $mysqli->query($sql);
$data = [];

while ($row = $result->fetch_assoc())
    $data[] = $row;
$result->close();

$number_of_results = count($data); ?>

Number of Entries: <?=$number_of_results?>

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