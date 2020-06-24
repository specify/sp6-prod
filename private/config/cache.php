<?php



# Cache will be regenerated when somebody tries to retrieve it and 7 days has passed since cache creation time
define('CACHE_DURATION',7*86400);//7 days

# Default column separator that would be used in the resulting csv files
# You can use , only if this symbol is not used in your datasets, or modify Cache_query.php to escape ,
define('CACHE_DEFAULT_COLUMN_SEPARATOR','`%L&6');

# Default line separator that would be used in the resulting csv files
# You can use \n only if this symbol is not used in your datasets, or modify Cache_query.php to escape \n
define('CACHE_DEFAULT_LINE_SEPARATOR','8#`/W');

# The name of the file that would store timestamps of when files were last refreshed
define('CACHE_MISC_FILE_NAME','cache_info.json');