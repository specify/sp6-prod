<?php

/*
 * CACHE QUERY
 * This class will execute the given SQL query, save the resulting data into a file and retrieve it
 *
 *
 * CONFIGURATION
 * Static method `config` accepts the following configuration parameters:
 *     working_directory - the location where the cache file as well as the misc file would be saved. Example: /Users/mambo/site/stats/
 *     max_cache - Max cache age in seconds. If the cache file is older than this, the new SQL request would be run upon the next call to get_result(). Example: 86400*7 (7 days)
 *     default_column_separator - default separator between columns that are going to be used in the resulting CSV file. You should use a separator that does not occur in your dataset. Example: `%L&6
 *     default_line_separator   - default separator between lines that are going to be used in the resulting CSV file. You should use a separator that does not occur in your dataset. Example: 8#`/W
 *     misc_file_name - the name of the JSON file that would store the timestamps of when the cache was created. You can use the same file for multiple caches. Example: cache_info.json
 *     unix_time_formatter_function_name - the name of the function that would format the resulting UNIX dates. You can use the 'unix_time_to_human_time' function located here https://gist.github.com/maxxxxxdlp/54b7d6648a60a21a635f902de7a5d6b4
 *
 *
 * USAGE
 * Create an object: $cache = new Cache_query($query,$cache_file_name, $columns, $update_cache);
 * Then, get query results as an array: $data = $cache->get_result();
 * Then, run this to get the status message (Bootstap 3 or 4 is needed for it to display properly): get_status($result_count,$condensed)
 *
 * Variables:
 * $query - the query you want to run. Example: SELECT 1 FROM `dual`
 * $cache_file_name - the name of the file that would store the cache data. Example: data.csv
 * $columns - an array of columns that your query is expected to return. The name of each column does not have to represent to the names from the query, as long as each column name is unique. Example: ['id','name','value']
 * $update_cache - whether to force recreate the cache. Example: TRUE
 * $result_count - (optional)(int) the number of rows of data you that was fetched. This number is not calculated automatically because you may want to calculate it differently. Example: 1407
 * $condensed - (optional)(bool) whether to output a simplified status message
 *
 * */



Cache_query::config(WORKING_DIRECTORY,
                    CACHE_DURATION,
                    CACHE_DEFAULT_COLUMN_SEPARATOR,
                    CACHE_DEFAULT_LINE_SEPARATOR,
                    CACHE_MISC_FILE_NAME,
                    'unix_time_to_human_time');



class Cache_query {

	private static $working_directory;
	private static $max_cache;
	private static $default_column_separator;
	private static $default_line_separator;
	private static $misc_file_location;
	private static $unix_time_formatter_function_name;

	private $query;
	private $file_name;
	private $columns;
	private $column_separator;
	private $line_separator;

	private $file_exists;
	private $time = 0;

	public $cache_was_recreated = FALSE;


	public static function config($working_directory,$max_cache,$default_column_separator,$default_line_separator,$misc_file_name,$unix_time_formatter_function_name){
		self::$working_directory = $working_directory;
		self::$max_cache = $max_cache;
		self::$default_column_separator = $default_column_separator;
		self::$default_line_separator = $default_line_separator;
		self::$misc_file_location = self::$working_directory.$misc_file_name;
		self::$unix_time_formatter_function_name = $unix_time_formatter_function_name;
	}

	public function __construct($query, $file_name, $columns, $update_cache=FALSE, $column_separator='', $line_separator=''){

		$this->query = $query;
		$this->file_name = $file_name;
		$this->file_exists = $file_name;
		$this->columns = $columns;
		$this->column_separator = $column_separator;
		$this->line_separator = $line_separator;


		if($column_separator=='')
			$this->column_separator = self::$default_column_separator;
		else
			$this->column_separator = $column_separator;


		if($line_separator=='')
			$this->line_separator = self::$default_line_separator;
		else
			$this->line_separator = $line_separator;


		if(self::$working_directory=='')
			exit('Run config() first');

		if(file_exists(self::$working_directory) && $update_cache)
			$this->delete_file();

	}

	public function get_status($base_url='',$target_link='', $result_count=null){

		$condensed = $base_url === FALSE;

		if(!$condensed)
			echo '<div class="card bg-light text-dark">
				<div class="card-body"><input type="hidden" id="query" value="'.$this->query.'">';

		$time = $this->get_time();
		$time = (self::$unix_time_formatter_function_name)(time()-$time);

		echo '<div class="alert alert-success" id="cache_status">Data was ';
		if($this->cache_was_recreated)
			echo 'recreated just ';
		else
			echo 'refreshed ';
		echo $time;

		if($result_count!==null)
			echo '<br>Number of Entries: '.$result_count;

		echo '</div>';

		if(!$condensed)
			echo '<a href="'.$base_url.'" class="btn btn-info">Home Page</a>
				<a href="'.$base_url.$target_link.'?update_cache=true" class="btn btn-primary">Refresh Cache</a>
				<br>
			</div>
		</div>';

	}

	public function get_result(){

		global $mysqli;

		$data = $this->read_cache();

		if(is_array($data))
			return $data;

		$result = $mysqli->query($this->query);

		$data = [];
		if(count($this->columns)==1)
			while($row = $result->fetch_row())
				$data[]=$row[0];
		else
			while($row = $result->fetch_assoc())
				$data[]=$row;
		$result->close();


		$this->cache_was_recreated = $this->write_cache($data);

		return $data;

	}


	private function get_time(){

		if($this->time !== 0)
			return $this->time;

		if(!file_exists(self::$misc_file_location))
			return FALSE;

		$data = file_get_contents(self::$misc_file_location);
		$data = json_decode($data,TRUE);

		if(!array_key_exists($this->file_name,$data))
			return FALSE;

		$this->time = time()-$data[$this->file_name];
		return $this->time;

	}

	private function read_cache(){

		$file_name = self::$working_directory.$this->file_name;

		if(!file_exists($file_name) ||
		   $this->get_time()===FALSE ||
		   $this->get_time()>self::$max_cache)
			return FALSE;

		$data = file_get_contents($file_name);

		$lines = explode($this->line_separator, $data);
		$result = [];

		if(count($this->columns)==1)
			$result=$lines;
		else
			foreach($lines as $line){
				$temp_result = [];
				$data = explode($this->column_separator,$line);

				$count_columns = count($data);

				if($count_columns != count($this->columns))
					continue;

				for($i=0;$i<$count_columns;$i++)
					$temp_result[$this->columns[$i]] = $data[$i];

				$result[] = $temp_result;

			}

		if(count($result)==0)
			return FALSE;

		return $result;

	}

	private function delete_file(){

		$target_file = self::$working_directory.$this->file_name;

		if(file_exists($target_file))
			unlink($target_file);

		return !file_exists($target_file);

	}

	private function write_cache($data){

		$string = '';
		if(count($this->columns)==1)
			$string .= implode($this->line_separator,$data);
		else
			foreach($data as $line)
				$string .= implode($this->column_separator,$line).$this->line_separator;

		if($this->delete_file() === FALSE){
			echo '<div class="alert alert-warning">Failed to delete '.self::$working_directory.$this->file_name.'. Permission denied</div>';
			return FALSE;
		}

		if(!file_exists(self::$working_directory))
			mkdir(self::$working_directory,0755,TRUE);

		file_put_contents(self::$working_directory.$this->file_name,$string);

		if(!file_exists(self::$working_directory.$this->file_name)){
			echo '<div class="alert alert-warning">Failed to create ' . self::$working_directory.$this->file_name . '. Permission denied</div>';
			return FALSE;
		}

		$this->file_exists = TRUE;
		$this->time = 1;

		$data = [];
		if(file_exists(self::$misc_file_location))
			$data = json_decode(file_get_contents(self::$misc_file_location),TRUE);

		$data[$this->file_name] = time();

		return file_put_contents(self::$misc_file_location,json_encode($data));

	}


}