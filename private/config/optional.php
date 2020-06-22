<?php



# Cache will be regenerated when somebody tries to retrieve it and 7 days has passed since cache creation time
define('CACHE_DURATION',7*86400);//7 days



### FOR DEBUG ONLY ###

# This will show success actions for most actions performed while the data refresh is running
define('VERBOSE',FALSE);

# Whether to output all PHP errors while DEVELOPMENT is set to FALSE
define('SHOW_ERRORS_IN_PRODUCTION',TRUE);



### FOR DEVELOPMENT ONLY ###
/*
 *
 * CONSTANTS (should be defined before in each php file before including header.php):
 * CSS - links a specified CSS file from the 'css' folder (extension not required)
 * JQUERY - whether to include jQuery (bool)(default = false)
 * BOOTSTRAP - whether to include Bootstrap (bool)(default = true)
 * TIMEZONE - specifies the timezone to use (default = 'UTC)
 * NO_HEAD - stops outputting the <head> tag (bool)(default = false)
 *
 * */