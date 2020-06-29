# Specify 6 Usage Statistics
This website provides some information on the usage of [Specify 6](https://github.com/specify/specify6).

## Requirements
1. PHP 7.2+ (older versions may work)
1. [PHP mbstring](https://stackoverflow.com/a/37441536/8584605)
1. Any Webserver
1. MySQL 5.7+ or MariaDB 10.4+ (older versions may work)

## `Private`
The `private` directory is responsible for displaying collected stats

### Installation
All of the configuration parameters you must change for the site to work are located in `./config/required.php`

Optional parameters are located in `./config/optional.php` and `./config/cache.php`

`./config/required.php` has several conditions present than define the `DEVELOPMENT` constant as well as the `ENVIRONMENT` constant.
Those are used to change the error reporting level as well as determine the necessary values for other required constants.
This is useful if you want the same code to work on multiple servers without any modifications.

1. Open the `./config/required.php` file.
Change the conditions present to make the server use necessary values for the required constants depending on the server that is running the stats reporting site.
1. Set `LINK` to an address the website would be served on.
1. Set `WORKING_LOCATION` to an empty folder.
This would be the destination for all cache files and other files created in the process.
Make sure the webserver has **READ** and **WRITE** permissions to this folder.
1. If you want to see raw tracking stats, set `TRACK_DAT_LOCATION` to your `track.dat` file.
1. If you want to see raw registration stats, set `REG_DAT_LOCATION` to your `reg.dat` file.
1. You will need to configure a database connection. Refer to the `Database connection` section below.
1. Configure your webserver to point to the directory where this repository is saved.
1. It is recommended to set up daily cron to the following location `http://<yourdomain>/cron/refresh_data.php`. This will automatically unzip the files and compile the information so that users can get up to date data.
1. It is also highly recommended to create indexes for `track` and `trackitem` tables in the `stats` database:
    ```sql
    SHOW INDEX FROM `track`; # Show existing indexes for `track`
    SHOW INDEX FROM `trackitem`; # Show existing indexes for `trackitem`
    CREATE UNIQUE INDEX `track_index` on `track` (`trackid`,`ip`,`timestampcreated`); # Create indexes for `track`
    CREATE UNIQUE INDEX `trackitem_index` on `trackitem` (`trackid`,`name`,`value`,`countamt`); # Create indexes for `trackitem`
    ```


#### Optional settings
You can go over the other settings in the `./config/optional.php` and `./config/cache.php` files and see if there is anything you would like to adjust.

For example, you can change the duration of time before compiled data is considered out of date by changing `CACHE_DURATION`.
The default value is set to 7 days[![analytics](http://www.google-analytics.com/collect?v=1&t=pageview&dl=https%3A%2F%2Fgithub.com%2Fspecify%2Fsp6-prod&uid=readme&tid=UA-169822764-7)]()


## `Public`
The `public` directory is meant to be served at `specify6-prod.nhm.ku.edu`.

## Database connection
There are identical PHP files `public/components/mysql.php` and `private/components/mysql.php`.

They get database credentials from a file located at `/etc/myauth.php`. If that file does not exist, `127.0.0.1` becomes a hostname and `root` becomes a login and a password. If the default values work for you, you don't have to change anything

If you want to create `/etc/myauth.php`, make sure it sets the following variables: `$mysql_hst`, `$mysql_usr` and `$mysql_pwd`

## Credit for used resources
There were snippets of code/files from the following resources used:
- [Bootstrap 4.5.0](https://github.com/twbs/bootstrap)
- [jQuery 3.5.1](https://github.com/jquery/jquery)
- [Chart.js](https://github.com/chartjs/Chart.js)
- [Specify 6 icon](https://www.sustain.specifysoftware.org/wp-content/uploads/2017/06/sp_project_square-1-150x150.png)
- [Cache_query.php](https://gist.github.com/maxxxxxdlp/91a39c6864365d7a8e813e19b819bb0d)
- [unix_time_to_human_time.php](https://gist.github.com/maxxxxxdlp/54b7d6648a60a21a635f902de7a5d6b4)