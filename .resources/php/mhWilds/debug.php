<?php
require_once("helpers.php");
$bd = simplexml_load_file("/mnt/disk/.resources/bd/mhWilds/mhWilds.xml");

$otomosPercentSum = 0;
$i = 0;
foreach ($bd->hunt as $hunt) {
	if(count($hunt->player) != 2){
		continue;
	}

	$otomos = getOtomosFromHunt($hunt);
	$hp = getTotalMaxHpFromHunt($hunt);

	$otomoDamage = 0;
	foreach($otomos as $otomo){
		$otomoDamage += getTotalDamageCountFromHunt($hunt, $otomo);
	}

	$otomosPercentSum += $otomoDamage / $hp;
	$i++;
}

$op = $otomosPercentSum / $i;

echo "Otomo percent = ", $op * 100, "%";

echo "<br>";

echo "Hunter percent = ", (1 - $op) * 100, "%";

echo "<br>";

echo "Indi Hunter percent = ", (1 - $op) / 2;

?>
