<?php

const MEMORY_LIMIT = '200M';
const BOOTSTRAP = FALSE;

require_once('../components/header.php');


$fh = fopen(REG_DAT_LOCATION, 'r') or die("Unable to open file.");

$data_reg = fread($fh, filesize(REG_DAT_LOCATION));
echo str_replace("\n", "<br>", $data_reg);

footer();