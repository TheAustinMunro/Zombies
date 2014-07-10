<?php 
require_once 'autoloader.php'; 
if (strpos("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", "bad-location") !== FALSE) { $error = "<span class='alert alert-danger'>The latitude or longitude provided is out of the range requested.</span><br/><br/>"; }
elseif (strpos("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", "bad-zombies") !== FALSE) { $error = "<span class='alert alert-danger'>There is something wrong with the number of zombies provided.</span><br/><br/>"; }
else { $error = ""; }
?>
<!DOCTYPE html>
<html>
<head>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDscTHPjnxLYvTI4Yxj6dCRPUtXqGt_5kI"></script>
<link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
<style type="text/css">
	html { height: 100% }
	body { height: 100%; margin: 0; padding: 0 }
	#map-canvas { height: 100% }
</style>
<script>
$(function(){
	$("#randomZombie").click(function(e){
		randomZombie();
	});
	$("#randomHuman").click(function(e){
		randomHuman();
	});
	$("#randomAll").click(function(e){
		randomZombie();
		randomHuman();
	});
});
function getRandomInt(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}
function randomHuman () {
	$("#latitude").val('35.0' + getRandomInt(28661, 58034));
	$("#longitude").val('-85.' + getRandomInt(279698, 332742));
	$("#intelligence").val(Math.ceil(Math.random()*10));
	$("#speed").val(Math.ceil(Math.random()*10));
	$("#weapon").val(Math.ceil(Math.random()*5));
}
function randomZombie () {
	$("#zombieNumber").val(Math.ceil(Math.random()*50));
}
</script>
</head>
<body>
	<?php if (isset($_POST['latitude']) && !empty($_POST['latitude'])): ?>
	<?php
		// Make sure latitude matches 35.xxxx
		preg_match('^35\.[0-9]^',$_POST['latitude'],$matches);
		// Make sure longitude matches -85.xxxx
		preg_match('^-85\.[0-9]^',$_POST['longitude'],$matches2);
		// If the lat/long dont match pattern send them back
		if (empty($matches) || empty($matches2)) { header('Location: /zombie/bad-location/'); exit; }
		// Store default lat and long
		$latitude = $_POST['latitude'];	$longitude = $_POST['longitude'];
		// Grab decimal from lat and long
		$dlat = explode(".", $latitude)[1];	$dlong = explode(".", $longitude)[1]; $dlat = substr($dlat,1);
		// Strip anything but numbers from zombies field
		$zombies = preg_replace('~[^0-9]+~',"",strtolower($_REQUEST['zombieNumber']));
		// Check lat/long decimals to make sure they are in a given range
		if (($dlat < 28661 || $dlat > 58034) || ($dlong < 279698 || $dlong > 332742)) {
			header('Location: /zombie/bad-location/');
		} 
		// Check to make sure zombies is a number and less than 50
		elseif (!is_numeric($zombies) || $zombies > 50) {
			header('Location: /zombie/bad-zombies/');
		} else {
			// If all checks pass, start simulation
			$mapString="";
			// Generate a zombie array for number of zombies requested to spawn.
			$zombieArray = [];
			for ($y = 0; $y < $zombies; $y++) {
				$zombieArray[$y] = new Zombie();
				$zombieArray[$y]->ZombieID = $y;
				$zombieArray[$y]->Location = array(Location::generateLatitude(),Location::generateLongitude());
				$zombieArray[$y]->SpeedLevel = mt_rand(1,100);
				$zombieArray[$y]->IntelligenceLevel = mt_rand(1,10);
				$mapString .= '
					var ZSI'.$y.' = "images/zombie2.gif";
					var ZSE'.$y.' = "<div>Zombie '.$y.' Spawn Point</div>";
					var ZIW'.$y.' = new google.maps.InfoWindow({
						content: ZSE'.$y.'
					});
					var Zmarker'.$y.' = new google.maps.Marker({
						position: new google.maps.LatLng('.$zombieArray[$y]->Location[0].', '.$zombieArray[$y]->Location[1].'),
						map: map,
						title: \'Zombie '.$y.' Spawn Point!\',
						icon: ZSI'.$y.'
					});
					google.maps.event.addListener(Zmarker'.$y.', \'click\', function() {
						ZIW'.$y.'.open(map,Zmarker'.$y.');
					});
				';
			}

			// In this simulation the safezone is TFP
			$safeZone = array(35.041930, -85.304669);

			// Assign human a speed multiplier based on what was selected.
			if      ($_REQUEST['speed'] === "1")  { $speed = .1;   }
			else if ($_REQUEST['speed'] === "2")  { $speed = .25;  }
			else if ($_REQUEST['speed'] === "3")  { $speed = .4;   }
			else if ($_REQUEST['speed'] === "4")  { $speed = .7;   }
			else if ($_REQUEST['speed'] === "5")  { $speed =  1;   }
			else if ($_REQUEST['speed'] === "6")  { $speed =  1.2; }
			else if ($_REQUEST['speed'] === "7")  { $speed =  1.5; }
			else if ($_REQUEST['speed'] === "8")  { $speed =  1.9; }
			else if ($_REQUEST['speed'] === "9")  { $speed =  2.4; }
			else if ($_REQUEST['speed'] === "10") { $speed =  3;   }

			// Start google maps string
			$mapString .= '
							var safeZoneImage = "images/ClearSafezone.png";
							var safeZone = "<div>Safe Zone</div>";
							var infowindowSZ = new google.maps.InfoWindow({
								content: safeZone
							});
							var SZMarker = new google.maps.Marker({
								position: new google.maps.LatLng(35.041930, -85.304669),
								map: map,
								title: \'Safe Zone\',
								icon: safeZoneImage
							});
							google.maps.event.addListener(SZMarker, \'click\', function() {
								infowindowSZ.open(map,SZMarker);
							});
						';

			// Set x for incrementing, distance for distance from spawn point to safe point where the human will be traveling
			$x = 0;
			$distance = Location::calculateDistance($safeZone[0], $safeZone[1], $latitude, $longitude);
			while ($distance > (10*$speed)) {
				// Check for starting location
				if (!isset($newLocation[0])) { 
					// Set the new location to starting location
					$newLocation = array($latitude, $longitude); 
					// Add the start location to map
					$mapString .= '
							var startImage = "images/agt_start_here.png";
							var startLocation = "<div>Start Location</div>";
							var infowindowSL = new google.maps.InfoWindow({
								content: startLocation
							});
							var SLMarker = new google.maps.Marker({
								position: new google.maps.LatLng('.$latitude.', '.$longitude.'),
								map: map,
								title: \'Start Location\',
								icon: startImage
							});
							google.maps.event.addListener(SLMarker, \'click\', function() {
								infowindowSL.open(map,SLMarker);
							});
						';
				}
				// Random number 0-9 for human movement
				$randomH = mt_rand(0,9);
				// Random number 0-9 for human movement
				$randomZ = mt_rand(0,9);

				// Move human
				if ($randomH < $_REQUEST['intelligence']) {
					// Move in a random direction
					$newLocation = Location::moveCloserToPoint($newLocation[0], $newLocation[1], $safeZone[0], $safeZone[1], $speed);
				} else {
					// Move closer to safe zone
					$newLocation = Location::moveRandomly($newLocation[0], $newLocation[1], $safeZone[0], $safeZone[1], $speed);
				}
				// See if safe
				$distance = Location::calculateDistance(35.041930, -85.304669, $newLocation[0], $newLocation[1]);
				if ($distance < (10*$speed)) { echo "<br/><br/><strong>Made it successfully</strong>"; }
				// If not safe, move zombies
				else {
					foreach ($zombieArray as $zombie) {
						if ($randomZ < $zombie->IntelligenceLevel) {
							$zombie->Location = Location::moveCloserToPoint($zombie->Location[0], $zombie->Location[1], $newLocation[0], $newLocation[1], ($zombie->SpeedLevel/100));
						} else {
							$zombie->Location = Location::moveCloserToPoint($zombie->Location[0], $zombie->Location[1], $newLocation[0], $newLocation[1], ($zombie->SpeedLevel/100));
						}
						if (Location::calculateDistance($zombie->Location[0], $zombie->Location[1], $newLocation[0], $newLocation[1]) < 10) {

							// Zombie and Human Encounter
							echo "There is an encounter with Zombie ".($zombie->ZombieID+1)." at the $x second mark!<br/>";
							
							// Flipping coin to see if human can kill zombie
							$fight = mt_rand(0,6);
							
							// If zombie wins
							if ($fight > $_REQUEST['weapon']) {
								echo "<strong>Eaten by Zombie ".($zombie->ZombieID+1)."</strong><br/><br/>";
								$distance = 0;

								// Add zombie win encounter to map
								$mapString .= '
									var image'.$x.' = "images/skull.gif";
									var encounter'.$x.' = "<div>Zombie Encounter<br/>You got eaten by zombie '.($zombie->ZombieID+1).' here :(</div>";
									var infowindow'.$x.' = new google.maps.InfoWindow({
										content: encounter'.$x.'
									});
									var marker'.$x.' = new google.maps.Marker({
										position: new google.maps.LatLng('.$newLocation[0].', '.$newLocation[1].'),
										map: map,
										title: \'Zombie Encounter!\',
										icon: image'.$x.'
									});
									google.maps.event.addListener(marker'.$x.', \'click\', function() {
										infowindow'.$x.'.open(map,marker'.$x.');
									});
								';
							} else {
								// If zombie loses
								echo "<strong>Killed Zombie ".($zombie->ZombieID+1)."</strong><br/><br/>";
								unset($zombieArray[$zombie->ZombieID]);

								// Add zombie lose encounter to map
								$mapString .= '
									var image'.$x.' = "images/crosshair.png";
									var encounter'.$x.' = "<div>Zombie Encounter<br/>You killed zombie '.($zombie->ZombieID+1).' here :)</div>";
									var infowindow'.$x.' = new google.maps.InfoWindow({
										content: encounter'.$x.'
									});
									var marker'.$x.' = new google.maps.Marker({
										position: new google.maps.LatLng('.$newLocation[0].', '.$newLocation[1].'),
										map: map,
										title: \'Zombie Encounter!\',
										icon: image'.$x.'
									});
									google.maps.event.addListener(marker'.$x.', \'click\', function() {
										infowindow'.$x.'.open(map,marker'.$x.');
									});
								';
							}
						}
					}
				}
				$x++;
			}
			// Google maps
			echo '<div id="map-canvas"/>';
			echo '
				<script>
					var mapOptions = {
						zoom: 14,
						center: new google.maps.LatLng(35.041930, -85.304669)
					};
					var map = new google.maps.Map(document.getElementById(\'map-canvas\'), mapOptions);
					'.$mapString.'
				</script>
			';
		}
	?>
	<?php else: ?>
		<?=$error?>
		<h3>Cell Tower Statistics</h3>
		Please enter a latitude between 35.028661 and 35.058034.<br/>
		Please enter a longitude between -85.279698 and -85.332742.<br/>
		<form method="post" action="/zombie/index.php">
			Latitude:  <input type="text" name="latitude" id="latitude">
			Longitude: <input type="text" name="longitude" id="longitude"><br/><br/>
			Intelligence Level: 
			<select name="intelligence" id="intelligence">
				<option>1</option>
				<option>2</option>
				<option>3</option>
				<option>4</option>
				<option>5</option>
				<option>6</option>
				<option>7</option>
				<option>8</option>
				<option>9</option>
				<option>10</option>
			</select>
			Speed Level:
			<select name="speed" id="speed">
				<option>1</option>
				<option>2</option>
				<option>3</option>
				<option>4</option>
				<option>5</option>
				<option>6</option>
				<option>7</option>
				<option>8</option>
				<option>9</option>
				<option>10</option>
			</select>
			<br/><br/>
			Cell Tower Weapon:
			<select name="weapon" id="weapon">
				<option value="1">Nothing</option>
				<option value="2">Butter Knife</option>
				<option value="3">Crossbow</option>
				<option value="4">AR15</option>
				<option value="5">Plasma Gun</option>
			</select>
			<br/><br/>
			<input type="button" value="Randomize Human Attributes" id="randomHuman">

		<h3>Number of tablet holders to Spawn</h3>
			Please enter a number no larger than 50.<br/>
			<input type="text" name="zombieNumber" id="zombieNumber"><input type="button" value="Randomize Zombie Size" id="randomZombie">

		<h3>Run Simulation Below</h3>
			<input type="button" value="Random All" id="randomAll">
			<input type="submit" value="Run Simulation">
		</form>
	<?php endif ?>
</body>
</html>