<?php

$_GET['update_cache'] = TRUE;

echo require_once('../refresh_data/stats.php');

$database = 'exception';
require('../components/mysql.php');
echo require_once('../refresh_data/exceptions.php');

$database = 'feedback';
require('../components/mysql.php');
echo require_once('../refresh_data/feedback.php');

echo "Done!<br>\n";