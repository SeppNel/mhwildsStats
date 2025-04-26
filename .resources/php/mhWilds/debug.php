<?php
require_once("helpers.php");
$bd = simplexml_load_file("/mnt/disk/.resources/bd/mhWilds/mhWilds.xml");

$otomosPercentSum = 0;
$i = 0;
foreach ($bd->hunt as $hunt) {
	$total = getTotalDamageFromHunt($hunt);

	$otomoDamage = 0;
	$totalDamage = 0; 
	foreach ($total as $player => $damage){
		if (isOtomo($bd, $player)){
			$otomoDamage += $damage;
		}
		$totalDamage += $damage;
	}

	$otomosPercentSum += $otomoDamage / $totalDamage;
	$i++;
}

$op = $otomosPercentSum / $i;

echo "Otomo percent = ", $op * 100, "%";

echo "<br>";

echo "Hunter percent = ", (1 - $op) * 100, "%";

echo "<br>";

echo "Indi Hunter percent = ", (1 - $op) / 2;

?>
