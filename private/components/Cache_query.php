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

	public function __construct($query, $file_location, $max_cache, $columns, $update_cache=FALSE, $column_separator="`FILdE~SEP_ARATOR", $line_separator="2`FIsLE~SEP_ARATOR2"){

		$this->query = $query;
		$this->file_location = $file_location;
		$this->max_cache = $max_cache;
		$this->columns = $columns;
		$this->column_separator = $column_separator;
		$this->line_separator = $line_separator;

		if($update_cache){
			$file_name = $this->get_file_name();

			if($file_name !== FALSE && file_exists($file_name))
				unlink($file_name);
		}

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

		return preg_replace('/(1 \w+)s( ago)/','$1$2',$result);

	}

	private function get_time(){

		if($this->time !== 0)
			return $this->time;

		if($this->get_file_name() === FALSE)
			return FALSE;

		$unix_time = basename($this->get_file_name());
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

	public function get_status($result_count=null){

		echo '<div class="card bg-light text-dark">
			<div class="card-body">'.$this->query.'<br><br>';

				$time = $this->get_time();

				if($time == FALSE){

					echo '<div class="alert alert-danger">There was an error reading cache. Please create cache again</div></div></div>';
					return FALSE;

				}

				$time = $this->unix_time_to_human_time(time()-$time);

				echo '<div class="alert alert-success">Cache was ';
				if($this->cache_was_recreated)
					echo 'recreated just ';
				else
					echo 'created ';

				echo $time.'. Press the button to update cache</div>';

					if($result_count!==null)
						echo 'Number of Entries: '.$result_count;

					echo '<br><br>
					<a href="'.substr(LINK,0,-1).$_SERVER['REQUEST_URI'].'?update_cache=true" class="btn btn-primary">Refresh Cache</a><br></div>
			</div>';

		return TRUE;
	}

	private function read_cache(){

		if($this->get_time()>$this->max_cache ||
			$this->get_file_name() == FALSE ||
			!file_exists($this->get_file_name()))
			return FALSE;

		$data = file_get_contents($this->get_file_name());

		if(strlen($data)==0)
			return FALSE;

		$lines = explode($this->line_separator, $data);
		$result = [];


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
		while($row = $result->fetch_assoc())
			$data[]=$row;
		$result->close();


		$this->cache_was_recreated = $this->write_cache($data);

		return $data;

	}

	private function delete_file(){

		if($this->get_file_name()===FALSE)
			return TRUE;

		$file_name = $this->file_location.$this->get_file_name();

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
		foreach($data as $line)
			$string .= implode($this->column_separator,$line).$this->line_separator;

		if(strlen($string)!=0)
			$string = substr($string,0,-1);


		if($this->delete_file() === FALSE){
			echo '<div class="alert alert-warning">Failed to delete '.$this->get_file_name().'. Permission denied</div>';
			return FALSE;
		}

		file_put_contents($this->file_location.$file_name,$string);

		if(!file_exists($file_name)){
			echo '<div class="alert alert-warning">Failed to create ' . $this->file_location.$file_name . '. Permission denied</div>';
			return FALSE;
		}

		$this->file_name = $file_name;
		$this->time = 1;

		return TRUE;

	}

}