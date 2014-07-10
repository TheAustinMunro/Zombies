<?php
class DBConnect {
	public $dbh;
	function __construct () {
		$root = realpath($_SERVER["DOCUMENT_ROOT"]);
		try {
			$this->dbh = new PDO("sqlite:".$root."/zombie/database/zombie.sqlite");
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}
}