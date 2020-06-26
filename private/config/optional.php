<?php


##exceptions/index.php
# The amount of exceptions to fetch
define('EXCEPTIONS_LIMIT',300);


##chart/index.php
# Specifies the way timestamps would be displayed
define('TIMESTAMP_FORMATTER','Y F j D H:m:s');

# Specifies the way dates would be formatter
define('DATE_FORMATTER','Y F j D');

# See more here: https://www.php.net/manual/en/function.date.php



##stats/index.php
# Sets which collection object count would be shown when previewing the collection data
# 0 - shows the most recent collection count
# 1 - shows the highest recorded collection object count
define('CO_PREVIEW_MODE',1);



##refresh_data/index.php
# An array of usernames that should be excluded from the query results when fetching stats
define('USERNAMES_TO_EXCLUDE',['rods', 'tlammer', 'timo', 'mambo', 'm001p596', 'testuser', 'demouser', 'sp7demofish', 'testfish']);



##components/charts.php
# Specifies background and border colors for charts
# Both arrays should have the same number of elements since background and border colors go in pairs
const CHART_BACKGROUND_COLORS = ["rgba(86,206,255,0.2)", "rgba(162,235,54,0.2)", "rgba(86,255,206,0.2)", "rgba(235,54,162,0.2)", "rgba(54,162,235,0.2)", "rgba(192,192,75,0.2)", "rgba(162,54,235,0.2)", "rgba(255,206,86,0.2)", "rgba(75,192,192,0.2)", "rgba(99,255,132,0.2)", "rgba(206,255,86,0.2)", "rgba(255,99,132,0.2)", "rgba(153,255,102,0.2)", "rgba(64,159,255,0.2)", "rgba(235,162,54,0.2)", "rgba(64,255,159,0.2)", "rgba(99,132,255,0.2)", "rgba(153,102,255,0.2)", "rgba(192,192,75,0.2)", "rgba(192,75,192,0.2)", "rgba(255,132,99,0.2)", "rgba(255,86,206,0.2)", "rgba(255,102,153,0.2)", "rgba(132,99,255,0.2)", "rgba(159,64,255,0.2)", "rgba(255,64,159,0.2)", "rgba(102,255,153,0.2)", "rgba(54,235,162,0.2)", "rgba(255,153,102,0.2)", "rgba(75,192,192,0.2)", "rgba(255,159,64,0.2)", "rgba(159,255,64,0.2)", "rgba(192,75,192,0.2)", "rgba(132,255,99,0.2)", "rgba(102,153,255,0.2)", "rgba(206,86,255,0.2)"];
const CHART_BORDER_COLORS = ["rgba(86,206,255,1)", "rgba(162,235,54,1)", "rgba(86,255,206,1)", "rgba(235,54,162,1)", "rgba(54,162,235,1)", "rgba(192,192,75,1)", "rgba(162,54,235,1)", "rgba(255,206,86,1)", "rgba(75,192,192,1)", "rgba(99,255,132,1)", "rgba(206,255,86,1)", "rgba(255,99,132,1)", "rgba(153,255,102,1)", "rgba(64,159,255,1)", "rgba(235,162,54,1)", "rgba(64,255,159,1)", "rgba(99,132,255,1)", "rgba(153,102,255,1)", "rgba(192,192,75,1)", "rgba(192,75,192,1)", "rgba(255,132,99,1)", "rgba(255,86,206,1)", "rgba(255,102,153,1)", "rgba(132,99,255,1)", "rgba(159,64,255,1)", "rgba(255,64,159,1)", "rgba(102,255,153,1)", "rgba(54,235,162,1)", "rgba(255,153,102,1)", "rgba(75,192,192,1)", "rgba(255,159,64,1)", "rgba(159,255,64,1)", "rgba(192,75,192,1)", "rgba(132,255,99,1)", "rgba(102,153,255,1)", "rgba(206,86,255,1)"];



### FOR DEBUG ONLY ###

# This will show extra success messages
define('VERBOSE',FALSE);

# Whether to output all PHP errors while DEVELOPMENT is set to FALSE
define('SHOW_ERRORS_IN_PRODUCTION',TRUE);

# Use non-minified files in production
define('USE_NON_MINIFIED_FILES_IN_PRODUCTION',TRUE);



### FOR DEVELOPMENT ONLY ###
/*
 *
 * CONSTANTS (should be defined in each php file before including header.php):
 * CSS - links a specified CSS file from the 'css' folder (extension not required)
 * JQUERY - whether to include jQuery (bool)(default = false)
 * BOOTSTRAP - whether to include Bootstrap (bool)(default = true)
 * TIMEZONE - specifies the timezone to use (default = 'UTC)
 * NO_HEAD - stops outputting the <head> tag (bool)(default = false)
 *
 * */