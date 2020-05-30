</body>
</html><?php

if(defined('DATABASE')){
	$thread = $mysqli->thread_id;
	$mysqli->kill($thread);
	$mysqli->close();
}