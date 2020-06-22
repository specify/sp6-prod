<?php
//define('DEVELOPMENT',TRUE);

# You can have different constant's values for your development and production servers
# You can modify the code to read the value for `DEVELOPMENT` constant from a file
if($_SERVER['HTTP_HOST']=='biwebdbtest.nhm.ku.edu')
	define('DEVELOPMENT',FALSE);
else
	define('DEVELOPMENT',TRUE);

if(DEVELOPMENT){ # these settings would be used during development

	//define('LINK', 'https://specify.maxxxxxdlp.ml/private/');
	define('LINK', 'http://localhost:80/');

	define('TRACK_DAT_LOCATION', '/home/ec2-user/data/stats_databases/track.dat');

	define('REG_DAT_LOCATION', '/home/ec2-user/data/stats_databases/reg.dat');

	define('WORKING_DIRECTORY','/Users/mambo/Downloads/data/');
	//define('WORKING_DIRECTORY','/home/ec2-user/data/private/');
}

else {

	define('LINK', 'http://biwebdbtest.nhm.ku.edu/sp6-prod/');
	define('TRACK_DAT_LOCATION','/home/anhalt/track.dat');
	define('REG_DAT_LOCATION','/home/anhalt/reg.dat');
	define('WORKING_DIRECTORY','/home/anhalt/specify6-prod/');

}