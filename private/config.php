<?php

/*
 *
 * CONSTANTS (should be defined before including this file):
 * CSS - links a specified CSS file from the 'css' folder (extension not required)
 * JQUERY - whether to include jQuery (bool)(default = false)
 * BOOTSTRAP - whether to include Bootstrap (bool)(default = true)
 * TIMEZONE - specifies the timezone to use (default = 'UTC)
 * NO_HEAD - stops outputting the <head> tag (bool)(default = false)
 *
 * */

#define('LINK', 'http://biwebdbtest.nhm.ku.edu/sp6-prod/');
define('LINK', 'https://private.maxxxxxdlp.ml/');

#define('TRACK_DAT_LOCATION','/home/anhalt/track.dat');
define('TRACK_DAT_LOCATION','/home/ec2-user/data/stats_databases/track.dat');

#define('TRACK_DAT_LOCATION','/home/anhalt/reg.dat');
define('REG_DAT_LOCATION','/home/ec2-user/data/stats_databases/reg.dat');


define('LOG_IPS',TRUE);
define('BLOCK_EXTERNAL_IPS',FALSE);
define('IPS_LOG_LOCATION','/home/ec2-user/data/ip_list.txt');
define('BLOCKED_IPS_LOG_LOCATION','/home/ec2-user/data/blocked_ip_list.txt');
define('WHITELIST_IP_LOCATION','/home/ec2-user/data/whitelist_ip_list.txt');
