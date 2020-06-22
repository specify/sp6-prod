<?php

class Cache_query {

	private $query;
	private $file_location;
	private $max_cache;
	private $file_name = FALSE;
	private $time = 0;
	private $cache_was_recreated = FALSE;
	private $columns;
	private $line_separator;
	private $column_separator;

	public function __construct($query, $file_location, $max_cache, $columns, $update_cache=FALSE, $column_separator="`%L&6", $line_separator="8#`/W"){

		$this->query = $query;
		$this->file_location = $file_location;
		$this->max_cache = $max_cache;
		$this->columns = $columns;
		$this->column_separator = $column_separator;
		$this->line_separator = $line_separator;

		if(!file_exists($file_location))
			mkdir($file_location,0755,TRUE);
		elseif($update_cache)
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

		$file_name = $this->get_file_name();

		if($file_name === FALSE)
			return FALSE;

		$unix_time = basename($file_name);
		$unix_time = explode('.',$unix_time);
		$unix_time = $unix_time[0];

		$this->time = time()-$unix_time;

		return $this->time;

	}

	private function get_file_name(){

		if($this->file_name!='')
			return $this->file_name;

		if(!file_exists($this->file_location))
			return FALSE;

		$file = glob($this->file_location.'*.json');

		if(count($file)!==1)
			return FALSE;

		$this->file_name = $file[0];

		if(strlen($this->file_name)==0)
			return FALSE;

		return $this->file_name;

	}

	public function get_status($result_count=null,$condensed=FALSE){

		if(!$condensed)
			echo '<div class="card bg-light text-dark">
				<div class="card-body">'.$this->query.'<br><br>';

		$time = $this->get_time();
		$time = $this->unix_time_to_human_time(time()-$time);

		echo '<div class="alert alert-success">Data was ';
		if($this->cache_was_recreated)
			echo 'recreated just ';
		else
			echo 'refreshed ';

		echo $time.'</div>';

			if($result_count!==null)
				echo 'Number of Entries: '.$result_count;

		if(!$condensed)
			echo '<br><br>
			<a href="'.substr(LINK,0,-1).$_SERVER['REQUEST_URI'].'?update_cache=true" class="btn btn-primary">Refresh Cache</a><br>

			</div>
		</div>';

	}

	private function read_cache(){

		$file_name = $this->get_file_name();

		if($this->get_time()>$this->max_cache ||
		   $file_name == FALSE ||
			!file_exists($file_name))
			return FALSE;

		$data = file_get_contents($file_name);

		if(strlen($data)==0)
			return FALSE;

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

		$file_name = $this->get_file_name();

		if($file_name===FALSE)
			return TRUE;

		$file_name = $this->file_location.$file_name;

		$this->file_name='';

		if($file_name !== FALSE && file_exists($file_name) && is_file($file_name))
			unlink($file_name);
		else
			return TRUE;

		if(file_exists($file_name) && is_file($file_name))
			return FALSE;

		return TRUE;

	}

	private function write_cache($data){

		$file_name = time().'.json';

		$string = '';
		if(count($this->columns)==1)
			$string .= implode($this->line_separator,$data);
		else
			foreach($data as $line)
				$string .= implode($this->column_separator,$line).$this->line_separator;

		if($this->delete_file() === FALSE){
			echo '<div class="alert alert-warning">Failed to delete '.$this->get_file_name().'. Permission denied</div>';
			return FALSE;
		}

		$target_file = $this->file_location.$file_name;
		file_put_contents($target_file,$string);

		if(!file_exists($target_file)){
			echo '<div class="alert alert-warning">Failed to create ' . $target_file . '. Permission denied</div>';
			return FALSE;
		}

		$this->file_name = $file_name;
		$this->time = 1;

		return TRUE;

	}

}