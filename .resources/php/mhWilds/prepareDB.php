<?php
$fl = fopen("/mnt/disk/.resources/bd/mhWilds/lock.txt", "w");
flock($fl, LOCK_EX);

require_once("/mnt/disk/.resources/php/lib/json_decode.php");

function getHunterName($fDecoded, $id){
	$hunters = $fDecoded->PLAYERINFO;
	$pets = $fDecoded->OTOMOINFO;
	$players = array_merge($hunters, $pets);
	
	foreach ($players as $player) {
		if($player->id == $id){
			return $player->name;
		}
	}

	return "";
}

function processOtomos($fDecoded){
	$pets = $fDecoded->OTOMOINFO;
	$otomos = array();
	foreach ($pets as $pet) {
		array_push($otomos, $pet->name);
	}

	$n = array();
	$monsters = $fDecoded->MONSTERS;
	foreach ($monsters as $monster) {
		$damages = $monster->damageSources;
		if(is_null($damages)){
			continue;
		}
		foreach ($damages as $damage) {
			$id = $damage->id;
			$name = getHunterName($fDecoded, $id);
			if(in_array($name, $otomos) && !in_array($name, $n)){
				array_push($n, $name);
			}
		}
	}

	return $n;
}

function processHunters($fDecoded){
	$hunters = $fDecoded->PLAYERINFO;
	$h = array();
	foreach ($hunters as $hunter) {
		if(isset($hunter->carts)){
			$h[strval($hunter->name)] = intval($hunter->carts);
		}
		else{
			$h[strval($hunter->name)] = -1;
		}
	}

	return $h;
}

function readFiletoDB($f){
	$rFile = file_get_contents("/mnt/disk/.resources/bd/mhWilds/logs/" . $f);
	$fDecoded = json_decode($rFile);

	$date = $fDecoded->date;
	$time = $fDecoded->questTime;
	$failed = false;
	$otomos = $fDecoded->otomos;
	$hunters = $fDecoded->hunters;

	if(count($hunters) == 1){
		return;
	}

	$mId = 0;
	foreach ($fDecoded->monsterInfo as $monster) {
		if($monster->hp > 0){
			$failed = true;
		}

		if(!is_null($monster->damage)){
			foreach ($monster->damage as $hunterName => $hunterDamage) {
				if($hunterName == ""){
					continue;
				}

				$player[$hunterName]["total"] = $hunterDamage->Total;
				$player[$hunterName]["phys"] = $hunterDamage->Physical;
				$player[$hunterName]["elem"] = $hunterDamage->Elemental;
				$player[$hunterName]["poison"] = $hunterDamage->PoisonDamage;
				$player[$hunterName]["blast"] = $hunterDamage->BlastDamage;
				$player[$hunterName]["true"] = $hunterDamage->Fixed;
				$player[$hunterName]["stun"] = $hunterDamage->Stun;
				$player[$hunterName]["sleep"] = $hunterDamage->Sleep;
				$player[$hunterName]["para"] = $hunterDamage->Paralyse;
			}
		}
		if(is_null($player)){
			$player = array();
		}
		$monsters[$mId]["name"] = strval($monster->name);
		$monsters[$mId]["damages"] = $player;
		$monsters[$mId]["maxHP"] = $monster->maxHp;
		$mId++;
	}
	
	
	if(!is_null($monsters)){
		saveBDAsXML($monsters, $date, $time, $failed, $hunters, $otomos);
	}
}

function saveBdAsXml($h, $date, $time, $failed, $hunters, $otomos){
	if (!file_exists("/mnt/disk/.resources/bd/mhWilds/mhWilds.xml")) {
		$xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\" ?><Hunts></Hunts>");
	}
	else{
		$xml = simplexml_load_file("/mnt/disk/.resources/bd/mhWilds/mhWilds.xml");
	}

	$hunt = $xml->addChild('hunt');
	$hunt->date = $date;
	$hunt->time = $time;
	if($failed){
		$hunt->failed = 1;
	}
	else{
		$hunt->failed = 0;
	}

	foreach ($hunters as $hunter) {
		$a = $hunt->addChild('player');
		$a->name = $hunter->name;
		$a->weapon = $hunter->weapon;
	}

	foreach ($otomos as $otomo) {
		$c = $hunt->addChild('otomo');
		$c->name = $otomo;
	}

	foreach ($h as $m) {
	    $monster = $hunt->addChild('monster');
	    $monster->name = $m["name"];
	    $monster->maxHP = $m["maxHP"];

	    $players = $m["damages"];

	    foreach ($players as $pName => $player) {
	    	$p = $monster->addChild('player');
	    	$p->name = $pName;
			$p->total = $player["total"];
	    	$p->phys = $player["phys"];
	    	$p->elem = $player["elem"];
	    	$p->poison = $player["poison"];
	    	$p->blast = $player["blast"];
	    	$p->true = $player["true"];
	    	$p->stun = $player["stun"];
			$p->sleep = $player["sleep"];
			$p->para = $player["para"];
	    }
	}

	$xml->saveXML("/mnt/disk/.resources/bd/mhWilds/mhWilds.xml");
}

function deleteFiles(){
	$files = glob('/mnt/disk/.resources/bd/mhWilds/logs/*'); // get all file names
	foreach($files as $file){ // iterate files
	  if(is_file($file)) {
	    unlink($file); // delete file
	  }
	}
}

$files = array_slice(scandir("/mnt/disk/.resources/bd/mhWilds/logs"), 2);
foreach ($files as $file) {
	readFiletoDB($file);
}

deleteFiles();

flock($fl, LOCK_UN);
fclose($fl);
?>

