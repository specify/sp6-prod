</body>
</html><?php

if(defined('DATABASE')){

	global $mysqli;

	$thread = $mysqli->thread_id;
	$mysqli->kill($thread);
	$mysqli->close();

}