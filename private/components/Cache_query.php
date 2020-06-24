<?php

class Cache_query {

	private $query;
	private $file_location;
	private $max_cache;
	private $file_name;
	private $file_exists = NULL;
	private $time = 0;
	private $columns;
	private $line_separator;
	private $column_separator;
	private $misc_file_location;

	public $cache_was_recreated = FALSE;

	public function __construct($query, $file_location, $file_name, $max_cache, $columns, $misc_file_location, $update_cache=FALSE, $column_separator="`%L&6", $line_separator="8#`/W"){

		$this->query = $query;
		$this->file_location = $file_location;
		$this->file_name = $file_name;
		$this->file_exists = $file_name;
		$this->max_cache = $max_cache;
		$this->columns = $columns;
		$this->column_separator = $column_separator;
		$this->line_separator = $line_separator;
		$this->misc_file_location = $misc_file_location;

		if(file_exists($file_location) && $update_cache)
			$this->delete_file();

	}

	private static function unix_time_to_human_time($time){

		$time_passed = time()-$time;

		if($time_passed<60)
			$result = $time_passed.' seconds ago';

		elseif($time_passed<3600)
			$result = intval($time_passed/60).' minutes ago';

		elseif($time_passed<86400)
			$result = intval($time_passed/3600).' hours ago';

		else
			$result = intval($time_passed/86400).' days ago';

		return preg_replace('/^(1 \w+)s( ago)/','$1$2',$result);

	}

	private function get_time(){

		if($this->time !== 0)
			return $this->time;

		if(!file_exists($this->misc_file_location))
			return FALSE;

		$data = file_get_contents($this->misc_file_location);
		$data = json_decode($data,TRUE);

		if(!array_key_exists($this->file_name,$data))
			return FALSE;

		$this->time = time()-$data[$this->file_name];
		return $this->time;

	}

	public function get_status($result_count=null,$condensed=FALSE){

		if(!$condensed)
			echo '<div class="card bg-light text-dark">
				<div class="card-body"><input type="hidden" id="query" value="'.$this->query.'">';

		$time = $this->get_time();
		$time = $this->unix_time_to_human_time(time()-$time);

		echo '<div class="alert alert-success">Data was ';
		if($this->cache_was_recreated)
			echo 'recreated just ';
		else
			echo 'refreshed ';

		if($result_count!==null)
			echo 'Number of Entries: '.$result_count;

		echo $time.'</div>';

		if(!$condensed)
			echo '
			<a href="'.substr(LINK,0,-1).$_SERVER['REQUEST_URI'].'?update_cache=true" class="btn btn-primary">Refresh Cache</a><br>

			</div>
		</div>';

	}

	private function read_cache(){

		$file_name = $this->file_location.$this->file_name;

		if(!file_exists($file_name) ||
		   $this->get_time()===FALSE ||
		   $this->get_time()>$this->max_cache)
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

	private function delete_file(){

		$target_file = $this->file_location.$this->file_name;

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
			echo '<div class="alert alert-warning">Failed to delete '.$this->file_location.$this->file_name.'. Permission denied</div>';
			return FALSE;
		}

		if(!file_exists($this->file_location))
			mkdir($this->file_location,0755,TRUE);

		file_put_contents($this->file_location.$this->file_name,$string);

		if(!file_exists($this->file_location.$this->file_name)){
			echo '<div class="alert alert-warning">Failed to create ' . $this->file_location.$this->file_name . '. Permission denied</div>';
			return FALSE;
		}

		$this->file_exists = TRUE;
		$this->time = 1;

		$data = [];
		if(file_exists($this->misc_file_location))
			$data = json_decode(file_get_contents($this->misc_file_location),TRUE);

		$data[$this->file_name] = time();

		return file_put_contents($this->misc_file_location,json_encode($data));

	}

}