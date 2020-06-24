<?php

ignore_user_abort(TRUE);
set_time_limit(300);

require_once('../config/required.php');

//var_dump(file_get_contents(LINK.'exceptions/index.php?update_cache=true')!='');
var_dump(file_get_contents(LINK.'feedback/index.php?update_cache=true')!='');
//var_dump(file_get_contents(LINK.'refresh_data/index.php?update_cache=true')!='');