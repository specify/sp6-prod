<?php

ignore_user_abort(TRUE);
set_time_limit(300);

const NO_HEAD = TRUE;
require_once('../components/header.php');

var_dump(file_get_contents(LINK.'refresh_data/index.php')!='');
var_dump(file_get_contents(LINK.'exceptions/index.php?update_cache=true')!='');
var_dump(file_get_contents(LINK.'feedback/index.php?update_cache=true')!='');