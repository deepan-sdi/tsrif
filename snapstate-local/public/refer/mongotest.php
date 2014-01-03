<?php
// connect
//$m = new Mongo();
// Config vars
error_reporting(E_ALL);
if($_SERVER['HTTP_HOST'] == 'snapstatelocal.com') {
	define('HOST', 'mongodb://localhost:27017');
	define('USERNAME', 'snapstate');
	define('PASSWORD', 'snapstate');
	define('DATABASE', 'snapstate');
} else {
	define('HOST', 'mongodb://snapstate.sdiphp.com:27017');
	define('USERNAME', 'snapstate');
	define('PASSWORD', '1et2s!tp3831!o');
	define('DATABASE', 'snapstate');
}
$conn		= new Mongo(HOST, array("username" => USERNAME, "password" => PASSWORD, "db" => DATABASE));
if(!$conn) {
	echo "Failed to connect!";
}
$db			= $conn->snapstate;
$collection = $db->users;
$cursor = $collection->find();

foreach ($cursor as $document) {
	echo '<pre>===>'; print_r($document); echo '</pre>';
}

echo MongoClient::VERSION;
?>