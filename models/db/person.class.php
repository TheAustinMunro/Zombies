<?php
class Person extends DBObject {
	public $fields;
	public $tableName;
	function __construct () {
		parent::__construct();
		$this->tableName = "Person";
		$this->fields = array("PersonID","Latitude","Longitude","Age","Gender","FitnessLevel","Items");
	}
	public static function selectAll () {
		return self::read();
	}
	public static function selectLatest () {
		global $connection;$array = array();$x = 0;
		$query = $connection->dbh->query("SELECT * FROM " . get_called_class() . " ORDER BY ID DESC LIMIT 5",PDO::FETCH_ASSOC);
		foreach($query as $row) { $array[$x] = (object)$row; $x++; }
		return (object)$array;
	}
}