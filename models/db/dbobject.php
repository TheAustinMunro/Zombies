<?php
class DBObject extends DBConnect {
	function __construct () {
		parent::__construct();
	}
	public function save () {
		$parameters = "";
		$values     = "";
		foreach ($this->fields as $field) {
			if (isset($this->{$field}) && !empty($this->{$field})) {
				$parameters .= $field.",";
				$values     .= "'".$this->{$field}."',";
			}
		}
		$parameters = rtrim($parameters,",");
		$values     = rtrim($values,",");
		$this->dbh->exec("INSERT OR REPLACE INTO " . $this->tableName . " ( " . $parameters . " ) VALUES ( " . $values . " )");
	}
	public static function delete ($where) {
		global $connection;
		$connection->dbh->exec("DELETE FROM " . get_called_class() . " WHERE " . $where);
	}
	public static function read ($what = " * ", $where = "") {
		global $connection;
		$array = array();
		$x = 0;
		if (!empty($where)) {$where = " WHERE " . $where;}
		$query = $connection->dbh->query("SELECT " . $what . " FROM " . get_called_class() . $where,PDO::FETCH_ASSOC);
		foreach($query as $row) { $array[$x] = (object)$row; $x++; }
		return (object)$array;
	}
}
?>