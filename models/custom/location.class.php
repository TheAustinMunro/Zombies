<?php
class Location {
	public static function generateLatitude () {
		$base = 35;
		$second = mt_rand(28661,58034);
		return (float)$base.'.0'.$second;
	}
	public static function generateLongitude () {
		$base = -85;
		$second = mt_rand(279698,332742);
		return (float)$base.'.'.$second;
	}
	public static function calculateDistance ($lat1, $lon1, $lat2, $lon2) {
		// This returns distance in feet between two sets of given lats and longs
		$theta = $lon1 - $lon2;
		$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
		$dist = acos($dist);
		$dist = rad2deg($dist);
		return ($dist * 60 * 1.1515) * 5280;
	}
	public static function moveCloserToPoint ($currentLat="", $currentLong="", $targetLat="", $targetLong="", $multiplier=1) {
		//.000016 in one direction = ~5.8ft = 4mph = human speed
		//.0000113 in one direction = ~4.1ft + ~4.1ft in another direct = ~5.8ft diagonal
		if (empty($targetLat))  { $targetLat  =  35.041930; }
		if (empty($targetLong)) { $targetLong = -85.304669; }
		if ($currentLat > $targetLat)   { $tempLat  = $currentLat  - (0.0000113 * $multiplier);	} 
		else                            { $tempLat  = $currentLat  + (0.0000113 * $multiplier); }
		if ($currentLong > $targetLong) { $tempLong = $currentLong - (0.0000113 * $multiplier);	} 
		else                            { $tempLong = $currentLong + (0.0000113 * $multiplier);	}
		return array($tempLat, $tempLong);
	}
	public static function moveRandomly ($currentLat="", $currentLong="", $targetLat="", $targetLong="", $multiplier="") {
		//.000016 in one direction = ~5.8ft = 4mph = human speed
		//.0000113 in one direction = ~4.1ft + ~4.1ft in another direct = ~5.8ft diagonal
		$random1 = mt_rand(0,1);
		$random2 = mt_rand(0,1);
		if ($random1)                   { $tempLat  = $currentLat  - (0.0000113 * $multiplier);	} 
		else                            { $tempLat  = $currentLat  + (0.0000113 * $multiplier); }
		if ($random2)                   { $tempLong = $currentLong - (0.0000113 * $multiplier);	} 
		else                            { $tempLong = $currentLong + (0.0000113 * $multiplier);	}
		return array($tempLat, $tempLong);
	}
}