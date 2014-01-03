<?php
Class Usermo {
	public function connect() {
		//$conn = new Mongo("mongodb://localhost:27017",array("username" => "snapstate", "password" => "snapstate","db" => "snapstate"));
		$conn = new \Mongo(HOST, array("username" => USERNAME, "password" => PASSWORD, "db" => DATABASE));
		return $conn;
	}
	public function selectUser($conn, $username, $password) {
		$collection	= $conn->users;
		$document	= array('user_email' => $username, 'password' => $password);
		$cursor		= $collection->find($document);
		return $cursor;
	}
}
?>