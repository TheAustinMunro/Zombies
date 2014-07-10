<?php
class Zombie {
	public $ZombieID;
	public $Location;
	public $SpeedLevel;
	public $IntelligenceLevel;

	public function __construct ($Location="",$SpeedLevel="",$IntelligenceLevel="") {
		$this->Location = $Location;
		$this->SpeedLevel = $SpeedLevel;
		$this->IntelligenceLevel = $IntelligenceLevel;
	}

	public static function selectZombieByID ($id) {
		return "SELECT * FROM Zombie WHERE ZombieID = " . $id;
	}
}