<?php
require_once("helpers.php");
$bd = simplexml_load_file("/mnt/disk/.resources/bd/mhWilds/mhWilds.xml");

function getTopDPS($bd){
	$devi = [];
	$huntCount = [];

	foreach ($bd->hunt as $hunt) {
		$interval = DPS_INTERVAL;
		if(count($hunt->otomo) == 0){
			$interval[2] = 50;
		}

		$hp = 0;
		foreach ($hunt->monster as $monster) {
			$hp = $hp + $monster->maxHP;
		}

		$numHunters = count($hunt->player);
		$damages = getTotalDamageFromHunt($hunt);

		foreach ($damages as $player => $value) {
			if(isset($huntCount[$player])){
				$huntCount[$player]++;
			}
			else{
				$huntCount[$player] = 1;
			}

			if(isset($devi[$player])){
				$devi[$player] = $devi[$player] + (($value/$hp) * 100) - $interval[$numHunters];
			}
			else{
				$devi[$player] = (($value/$hp) * 100) - $interval[$numHunters];
			}
		}
	}

	$media = [];
	foreach ($devi as $player => $value) {
		$media[$player] = $value / $huntCount[$player];
	}

	arsort($media);
	return array_key_first($media);
}

function getConsistent($bd){
	$ant = array();
	$cons = array();
	$count = array();
	$otomos = getAllOtomos($bd);

	foreach ($bd->hunt as $hunt) {
		$interval = DPS_INTERVAL;
		if(count($hunt->otomo) == 0){
			$interval[2] = 50;
		}

		$hp = 0;
		foreach ($hunt->monster as $monster) {
			$hp += $monster->maxHP;
		}

		$numHunters = count($hunt->player);
		$damages = getTotalDamageFromHunt($hunt);

		foreach ($damages as $player => $value) {
			if (in_array($player, $otomos)) {
				continue;
			}

			$devi = (($value/$hp) * 100) - $interval[$numHunters];

			if(isset($ant[$player])){
				$cons[$player] = $cons[$player] + abs($ant[$player] - $devi);
				$count[$player]++;
				$ant[$player] = $devi;
			}
			else{
				$cons[$player] = 0;
				$count[$player] = 1;
				$ant[$player] = $devi;
			}
		}
	}

	foreach ($cons as $player => $value) {
		$cons[$player] = $value / $count[$player];
	}

	asort($cons);
	return array_key_first($cons);
}

function getExpected($bd){
	$cons = array();
	$count = array();
	$otomos = getAllOtomos($bd);

	foreach ($bd->hunt as $hunt) {
		$interval = DPS_INTERVAL;
		if(count($hunt->otomo) == 0){
			$interval[2] = 50;
		}

		$hp = getTotalMaxHpFromHunt($hunt);
		$numHunters = count($hunt->player);
		$damages = getTotalDamageFromHunt($hunt);

		foreach ($damages as $player => $value) {
			if (in_array($player, $otomos)) {
				continue;
			}

			$devi = abs((($value/$hp) * 100) - $interval[$numHunters]);

			if(isset($cons[$player])){
				$cons[$player] += $devi;
				$count[$player]++;
			}
			else{
				$cons[$player] = $devi;
				$count[$player] = 1;
			}
		}
	}

	foreach ($cons as $player => $value) {
		$cons[$player] = $value / $count[$player];
	}

	asort($cons);
	return array_key_first($cons);
}

function getTopGato($bd){
	$tops1 = [];
	foreach ($bd->hunt as $hunt) {
		$max = 0;
		$name = "";
		$otomos = getOtomosFromHunt($hunt);

		if (count($otomos) == 0){
			continue;
		}

		foreach ($otomos as $otomo) {
			$count = getTotalDamageCountFromHunt($hunt, $otomo);

			if($count > $max){
				$max = $count;
				$name = $otomo;
			}
		}

		if(!isset($tops1[$name])){
			$tops1[$name] = 0;
		}

		$tops1[$name]++;
	}

	arsort($tops1);
	return array_key_first($tops1);
}

function getMostTop1($bd){
	$tops1 = [];
	foreach ($bd->hunt as $hunt) {
		$max = 0;
		$name = "";
		$hunters = getHuntersFromHunt($hunt);

		foreach ($hunters as $hunter) {
			$count = getTotalDamageCountFromHunt($hunt, $hunter);

			if($count > $max){
				$max = $count;
				$name = $hunter;
			}
		}

		if(!isset($tops1[$name])){
			$tops1[$name] = 0;
		}

		$tops1[$name]++;
	}

	arsort($tops1);
	return array_key_first($tops1);
}

function getMostDamageType($bd, $type){
	$result = array();
	$players = getAllPlayers($bd);
	foreach ($players as $p) {
		$result[$p] = 0;
	}

	foreach ($bd->hunt as $hunt) {
		$damages = getDamageTypeFromHunt($hunt);
		foreach ($damages as $player => $nan) {
			$result[$player] = $result[$player] + $damages[$player][$type];
		}
	}

	arsort($result);
	return array_key_first($result);
}


function getMostKilledMonster($bd){
	$n = array();

	foreach ($bd->hunt as $hunt) {
		foreach ($hunt->monster as $monster) {
			$mName = strval($monster->name);
			if(isset($n[$mName])){
				$n[$mName]++;
			}
			else{
				$n[$mName] = 1;
			}
		}
	}

	return array_search(max($n), $n);
}

function getMostVicio($bd){
	$n = array();

	foreach ($bd->hunt as $hunt) {
		$date = strval($hunt->date);
		if(isset($n[$date])){
			$n[$date]++;
		}
		else{
			$n[$date] = 1;
		}
	}

	return array_search(max($n), $n);
}

function timeSpent($bd){
	$time = 0;
	foreach ($bd->hunt as $hunt) {
		$time = $time + $hunt->time;
	}

	return $time;
}

function bombMoney($bd){
	$c = 0;
	foreach ($bd->hunt as $hunt) {
		foreach ($hunt->monster as $monster) {
			foreach ($monster->player as $player) {
				 $c = $c + $player->bombs;
			}
		}
	}

	return $c * 518;
}

function carts($bd, $t){
	$h = "";
	$max = 0;
	$min = 999999999;

	$hunters = getAllHunters($bd);
	if($t == "max"){
		foreach ($hunters as $hunter) {
			$avg = avgCarts($bd, $hunter);
			if($avg > $max){
				$max = $avg;
				$h = $hunter;
			}
		}
	}
	else{
		foreach ($hunters as $hunter) {
			$avg = avgCarts($bd, $hunter);
			if($avg < $min){
				$min = $avg;
				$h = $hunter;
			}
		}
	}
	
	return $h;
}

function failQuest($bd){
	$c = 0;
	foreach ($bd->hunt as $hunt) {
		$c = $c + $hunt->failed;
	}

	return $c;
}

function victoryStar($bd, $t){
	$h = "";
	$max = 0;
	$min = 100;

	$players = getAllHunters($bd);
	if($t == "max"){
		foreach ($players as $p) {
			$ratio = questCompleteRatio($bd, $p);
			if($ratio > $max){
				$max = $ratio;
				$h = $p;
			}
		}
	}
	else{
		foreach ($players as $p) {
			$ratio = questCompleteRatio($bd, $p);
			if($ratio < $min){
				$min = $ratio;
				$h = $p;
			}
		}
	}

	return $h;
}

function easyHardMonster($bd, $t){
	$fail = [];
	$count = [];

	foreach (getAllMonsters($bd) as $name) {
		$fail[$name] = 0;
		$count[$name] = 0;
	}

	foreach ($bd->hunt as $hunt) {
		if(count($hunt->monster) != 1){
			continue;
		}

		$mName = strval($hunt->monster->name);
		$count[$mName]++;
		if(intval($hunt->failed)){
			$fail[$mName]++;
		}
	}

	foreach ($fail as $name => $fails) {
		if($count[$name] != 0){
			$count[$name] = $fails / $count[$name];
		}
	}

	if($t == "hard"){
		arsort($count);
	}
	else{
		asort($count);
	}

	return array_key_first($count);	
}


function slowestMonster($bd){
	$timesAdded = [];
	$count = [];

	foreach (getAllMonsters($bd) as $name) {
		$timesAdded[$name] = 0;
		$count[$name] = 0;
	}

	foreach ($bd->hunt as $hunt) {
		if(count($hunt->monster) != 1 || intval($hunt->failed)){
			continue;
		}

		$mName = strval($hunt->monster->name);
		$count[$mName]++;
		$timesAdded[$mName] += $hunt->time;
	}

	foreach ($timesAdded as $name => $time) {
		if($count[$name] != 0){
			$count[$name] = $time / $count[$name];
		}
	}

	arsort($count);
	return array_key_first($count);
}

function mostTankyMonster($bd){
	$maxHP = [];

	foreach (getAllMonsters($bd) as $name) {
		$maxHP[$name] = 0;
	}

	foreach ($bd->hunt as $hunt) {
		foreach ($hunt->monster as $monster) {
			$mName = strval($hunt->monster->name);
			$mHP = intval($monster->maxHP);

			if($mHP > $maxHP[$mName]){
				$maxHP[$mName] = $mHP;
			}
		}
	}

	arsort($maxHP);
	return array_key_first($maxHP);
}

?>

<!DOCTYPE html>
<html>
<head>
	<title>MH Stats - Per-Server</title>
	<meta charset="utf-8">
	<link rel="stylesheet" href="/.resources/css/mhW_style.css">
	<link rel="shortcut icon" type="image/x-icon" href="/.resources/img/media/favicon.webp">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
</head>
<body>
<div class="image-hero-area" ></div>
<div id="container">
<div id="menu">
	<?php include("/mnt/disk/.resources/php/mhWilds/menu.html"); ?>
</div>
<div id="content">
	<table id="huntersTable" class="listTable">
		<tr>
			<th>Dato</th><th>Valor</th>
		</tr>
		<tr>
			<td>John Monster Hunter</td><td><?php echo getTopDPS($bd); ?></td>
		</tr>
		<tr>
			<td>Top Consistencia</td><td><?php echo getConsistent($bd); ?></td>
		</tr>
		<tr>
			<td>Hace lo justo</td><td><?php echo getExpected($bd); ?></td>
		</tr>
		<tr>
			<td>Top Gato</td><td><?php echo getTopGato($bd); ?></td>
		</tr>
		<tr>
			<td>Vencedor</td><td><?php echo victoryStar($bd, "max"); ?></td>
		</tr>
		<tr>
			<td>Perdedor</td><td><?php echo victoryStar($bd, "min"); ?></td>
		</tr>
		<tr>
			<td>Top 1 Máster</td><td><?php echo getMostTop1($bd); ?></td>
		</tr>
		<tr>
			<td>Más Elemental</td><td><?php echo getMostDamageType($bd, "elem"); ?></td>
		</tr>
		<tr>
			<td>Adicto a las Explosiones</td><td><?php echo getMostDamageType($bd, "blast"); ?></td>
		</tr>
		<tr>
			<td>Más Tóxico</td><td><?php echo getMostDamageType($bd, "poison"); ?></td>
		</tr>
		<tr>
			<td>Verdaderamente verdadero</td><td><?php echo getMostDamageType($bd, "true"); ?></td>
		</tr>
		<tr>
			<td>Más Aturullante</td><td><?php echo getMostDamageType($bd, "stun"); ?></td>
		</tr>
		<tr>
			<td>Más Aburrido</td><td><?php echo getMostDamageType($bd, "sleep"); ?></td>
		</tr>
		<tr>
			<td>Paralisis Demon</td><td><?php echo getMostDamageType($bd, "para"); ?></td>
		</tr>
		<tr>
			<td>Monstruo más cazado</td><td><?php echo getMostKilledMonster($bd); ?></td>
		</tr>
		<tr>
			<td>Monstruo más chungo</td><td><?php echo easyHardMonster($bd, "hard"); ?></td>
		</tr>
		<tr>
			<td>Monstruo más easy</td><td><?php echo easyHardMonster($bd, "easy"); ?></td>
		</tr>
		<tr>
			<td>Monstruo más lento</td><td><?php echo slowestMonster($bd); ?></td>
		</tr>
		<tr>
			<td>Monstruo más tanque</td><td><?php echo mostTankyMonster($bd); ?></td>
		</tr>
		<tr>
			<td>Dia de mayor vicio</td><td><?php echo date("d-m-Y", intval(getMostVicio($bd))); ?></td>
		</tr>
		<tr>
			<td>Misiones Fallidas</td><td><?php echo failQuest($bd); ?></td>
		</tr>
		<tr>
			<td>Tiempo en misiones</td><td><?php $time = timeSpent($bd); echo floor($time/3600), ":", floor(($time / 60) % 60), ":", $time%60; ?></td>
		</tr>
	</table>
</div>
</div>
</body>
</html>
