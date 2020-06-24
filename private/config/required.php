<?php


if($_SERVER['HTTP_HOST']=='localhost'){
	define('DEVELOPMENT',TRUE);
	define('CONFIGURATION','localhost');
}
elseif($_SERVER['HTTP_HOST']=='specify.maxxxxxdlp.ml'){
	define('DEVELOPMENT',TRUE);
	define('CONFIGURATION','ec2');
}
else {
	define('DEVELOPMENT',FALSE);
	define('CONFIGURATION','production');
}


if(CONFIGURATION==='localhost'){

	define('LINK', 'http://localhost:80/');

	define('TRACK_DAT_LOCATION', '/home/ec2-user/data/stats_databases/track.dat');

	define('REG_DAT_LOCATION', '/home/ec2-user/data/stats_databases/reg.dat');

	define('WORKING_DIRECTORY','/Users/mambo/Downloads/sp6-prod_data/');

}

elseif(CONFIGURATION==='ec2'){

	define('LINK', 'https://specify.maxxxxxdlp.ml/private/');

	define('TRACK_DAT_LOCATION', '/home/ec2-user/data/stats_databases/track.dat');

	define('REG_DAT_LOCATION', '/home/ec2-user/data/stats_databases/reg.dat');

	define('WORKING_DIRECTORY','/home/ec2-user/data/sp6-prod_data/');}

else {//production server

	define('LINK', 'http://biwebdbtest.nhm.ku.edu/sp6-prod/');

	define('TRACK_DAT_LOCATION','/home/anhalt/track.dat');

	define('REG_DAT_LOCATION','/home/anhalt/reg.dat');

	define('WORKING_DIRECTORY','/home/anhalt/sp6-prod_data/');

}