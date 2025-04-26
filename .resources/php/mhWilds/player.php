<?php
require_once("helpers.php");
$name = strval($_GET["name"]);
$bd = simplexml_load_file("../../bd/mhWilds/mhWilds.xml");
$isOtomo = isOtomo($bd, $name);

function getFastestHunt($bd, $p){
	$minTime = 999999.99999;
	foreach ($bd->hunt as $hunt) {
		if(!intval($hunt->failed) && floatval($hunt->time) < $minTime && in_array($p, getPlayersFromHunt($hunt))){
			$minTime = $hunt->time;
		}
	}

	return $minTime;
}

function getMedia($bd, $p, $numP){
	$percents = 0;
	$count = 0;
	foreach ($bd->hunt as $hunt) {
		if(count($hunt->player) != $numP){
			continue;
		}

		$damage = getTotalDamageCountFromHunt($hunt, $p);
		$maxHP = getTotalMaxHpFromHunt($hunt);

		$percents += $damage / $maxHP * 100;
		$count++;
	}

	if($count == 0){
		return 0;
	}

	return $percents / $count;
}

function getTotals($bd, $p){
	$r["phys"] = 0;
	$r["elem"] = 0;
	$r["poison"] = 0;
	$r["blast"] = 0;
	$r["true"] = 0;

	$r["stun"] = 0;
	$r["sleep"] = 0;
	$r["para"] = 0;

	$hunts = $bd->hunt;
	foreach ($hunts as $hunt) {
		foreach ($hunt->monster as $monster) {
			foreach ($monster->player as $player) {
				if($player->name == $p){
					$r["phys"] += $player->phys;
					$r["elem"] += $player->elem;
					$r["poison"] += $player->poison;
					$r["blast"] += $player->blast;
					$r["true"] += $player->true;

					$r["stun"] += $player->stun;
					$r["sleep"] += $player->sleep;
					$r["para"] += $player->para;
					break;
				}
			}
		}
	}

	return $r;
}

function getMostKilledMonster($bd, $p){
	$n = array();

	$hunts = $bd->hunt;
	foreach ($hunts as $hunt) {
		$players = getPlayersFromHunt($hunt);
		if(!in_array($p, $players)){
			continue;
		}

		$monsters = $hunt->monster;
		foreach ($monsters as $monster) {
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

function getMinMaxMonster($bd, $p, $type){
	$percents = array();
	$count = array();

	$hunts = $bd->hunt;
	foreach ($hunts as $hunt) {
		$interval = DPS_INTERVAL;
		if(count($hunt->otomo) == 0){
			$interval[2] = 50;
		}

		$numHunters = count($hunt->player);
		foreach ($hunt->monster as $monster) {
			foreach ($monster->player as $player) {
				if($player->name != $p){
					continue;
				}

				$mName = strval($monster->name);
				$damage = $player->total;

				if(isset($percents[$mName])){
					$percents[$mName] = $percents[$mName] + (($damage/$monster->maxHP)*100) - $interval[$numHunters];
					$count[$mName]++;
				}
				else{
					$percents[$mName] = (($damage/$monster->maxHP)*100) - $interval[$numHunters];
					$count[$mName] = 1;
				}
				break;
			}
		}
	}

	foreach ($percents as $mName => $value) {
		$results[$mName] = $value / $count[$mName];
	}

	if($type == "min"){
		asort($results);
	}
	else{
		arsort($results);
	}

	return array_key_first($results);
}

function getMostUsedWeapon($bd, $p){
	$wCount = [];
	foreach ($bd->hunt as $hunt) {
		$w = getWeaponsFromHunt($hunt);
		if (isset($w[$p])){
			$wCount[$w[$p]]++;
		}
	}

	arsort($wCount);
	return array_key_first($wCount);
}

function getUsedWeapons($bd, $p){
	$wCount = [];
	foreach ($bd->hunt as $hunt) {
		$w = getWeaponsFromHunt($hunt);
		if (isset($w[$p])){
			$wCount[$w[$p]]++;
		}
	}

	return $wCount;
}

function getDamageAvgPerWeapon($bd, $p){
	$wSum = [];
	$wCount = [];
	foreach ($bd->hunt as $hunt) {
		$w = getWeaponsFromHunt($hunt);
		if (isset($w[$p])){
			$wCount[$w[$p]]++;
			$wSum[$w[$p]] += getTotalDamageCountFromHunt($hunt, $p);
		}
	}

	$avg = [];
	foreach($wSum as $weapon => $sum){
		$avg[$weapon] = $sum / $wCount[$weapon];
	}
	return $avg;
}

?>

<!DOCTYPE html>
<html>
<head>
	<title><?php echo $name; ?> - Per-Server</title>
	<meta charset="utf-8">
	<link rel="stylesheet" href="../../css/mhW_style.css">
	<link rel="shortcut icon" type="image/x-icon" href="/.resources/img/media/favicon.webp">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
	<script src="/.resources/js/chart.min.js"></script>
</head>
<body>
	<div class="image-hero-area"></div>
	<div id="container">
		<div id="menu">
			<?php include("/mnt/disk/.resources/php/mhWilds/menu.html"); ?>
		</div>
		<div id="content">
			<div class="title">
				<h1><?php echo $name;?></h1>
				<?php
					$w = getMostUsedWeapon($bd, $name);
					if ($w != ""){
						echo "<img src=\"/.resources/img/mhWilds/weapons/", WEAPONS_IMG[$w], "\">";
					}
				?>
			</div>
			<div id="top">
				<div id="hunterPhoto">
					<?php
						if(file_exists("/mnt/disk/.resources/img/mhWilds/players/" . strtolower($name) . ".webp")){
							echo '<img src="/.resources/img/mhWilds/players/', strtolower($name), '.webp">';
						}
						else{
							echo '<img src="/.resources/img/mhWilds/players/default.jpg">';
						}
					?>
				</div>
				<div id="dataTop">
					<div class="complem">
						<h1>General</h1>
						<span>Cacerías: <?php echo getHuntCount($bd, $name); ?></span><br>
						<span>Speedrun: <?php $time = getFastestHunt($bd, $name); echo floor($time / 60), ":", sprintf('%02d', $time % 60); ?></span><br>
						<span>Tops 1: <?php echo countTops1($bd, $name); ?></span>
					</div>
					<div class="complem">
						<h1>Medias</h1>
						<span>Media daño 2J: <?php echo round(getMedia($bd, $name, 2), 2), "%"; ?></span><br>
						<span>Media daño 3J: <?php echo round(getMedia($bd, $name, 3), 2), "%"; ?></span><br>
						<span>Media daño 4J: <?php echo round(getMedia($bd, $name, 4), 2), "%"; ?></span><br>
						<span>% Victorias: <?php echo round(questCompleteRatio($bd, $name), 2), "%"; ?></span><br>
					</div>
					<div class="complem">
						<h1>Totales</h1>
						<?php $totals = getTotals($bd, $name); ?>
						<span>Daño físico: <?php echo round($totals["phys"], 2); ?></span><br>
						<span>Daño elemental: <?php echo round($totals["elem"], 2); ?></span><br>
						<span>Daño veneno: <?php echo round($totals["poison"], 2); ?></span><br>
						<span>Daño nitro: <?php echo round($totals["blast"], 2); ?></span><br>
						<span>Daño true: <?php echo round($totals["true"], 2); ?></span><br>
					</div>
					<div class="complem">
						<h1>Estado</h1>
						<span>Aturdimiento: <?php echo round($totals["stun"], 2); ?></span><br>
						<span>Sueño: <?php echo round($totals["sleep"], 2); ?></span><br>
						<span>Paralisis: <?php echo round($totals["para"], 2); ?></span><br>
					</div>
				</div>
			</div>
			<div id="bot">
				<div>
					<h1>Monstruo más Cazado</h1>
					<?php
						echo '<img src="/.resources/img/mhWilds/monsters/', strtolower(getMostKilledMonster($bd, $name)), '.png">';
					?>
				</div>
				<div>
					<h1>Te abusa</h1>
					<?php
						echo '<img src="/.resources/img/mhWilds/monsters/', strtolower(getMinMaxMonster($bd, $name, "min")), '.png">';
					?>
				</div>
				<div>
					<h1>Abusas de</h1>
					<?php
						echo '<img src="/.resources/img/mhWilds/monsters/', strtolower(getMinMaxMonster($bd, $name, "max")), '.png">';
					?>
				</div>
			</div>
			<?php
				if (!isOtomo($bd, $name)){

			?>
			<div id="weaponStats">
				<h1>Estadisticas de Armas</h1>
				<h2><span>Uso<span></h2>
				<div id="graph">
					<canvas id="useGraph" height="200"></canvas>
					<script>
						var size = Math.round(document.getElementById('graph').clientWidth / 40);

						<?php
							$w = getUsedWeapons($bd, $name);
							arsort($w);

							$names = [];
							$counts = [];
							$images = [];
							foreach ($w as $weapon => $count) {
								$names[] = '"' . WEAPONS_NAME[$weapon] . '"';
								$counts[] = '"' . $count . '"';
								$images[] = '"/.resources/img/mhWilds/weapons/' . WEAPONS_IMG[$weapon] . '"';
							}

							echo 'var xValues = [', implode(", ", $names), "];\n";
							echo 'var yValues = [', implode(", ", $counts), "];\n";
							echo 'var weaponImages = [', implode(", ", $images), "];\n";
						?>

						// Create image objects
						const weaponImageObjs = weaponImages.map(src => {
							const img = new Image();
							img.src = src;
							return img;
						});

						// Draw icons plugin
						const drawWeaponIcons = {
							id: 'drawWeaponIcons',
							afterDraw(chart) {
								const { ctx, scales: { y } } = chart;
								ctx.save();

								xValues.forEach((label, index) => {
									const img = weaponImageObjs[index];
									const imgSize = 45; // adjust as needed
									const yPos = y.getPixelForTick(index); // ✅ This works with indexAxis: 'y'

									if (img.complete && img.naturalWidth > 0) {
										ctx.drawImage(
											img,
											y.left - imgSize,
											yPos - imgSize / 2,
											imgSize,
											imgSize
										);
									} else {
										img.onload = () => chart.draw(); // ensure reload if image wasn't ready
									}
								});

								ctx.restore();
							}
						};

						var barColors = [
						"#e74c3c", // red
						"#FF6384", // vivid red
						"#36A2EB", // bright blue
						"#FFCE56", // golden yellow
						"#4BC0C0", // teal
						"#9966FF", // purple
						"#FF9F40", // orange
						"#C9CBCF", // light gray
						"#2ecc71", // emerald green
						"#f39c12", // amber
						"#1abc9c", // turquoise
						"#8e44ad", // deep purple
						"#34495e"  // navy blue
						];

						// Create chart
						new Chart("useGraph", {
							type: "bar",
							data: {
								labels: xValues,
								datasets: [{
									data: yValues,
									backgroundColor: barColors
								}]
							},
							options: {
								indexAxis: 'y',
								layout: {
									padding: {
										left: 60 // give space for icon
									}
								},
								scales: {
									y: {
										ticks: {
											color: "white",
											font: { size: 0 }
										}
									},
									x: {
										ticks: { color: "white" }
									}
								},
								plugins: {
									legend: { display: false }
								}
							},
							plugins: [drawWeaponIcons]
						});
					</script>
				</div>
				<h2><span>Daño medio</span></h2>
				<div id="graph">
					<canvas id="damageGraph" height="200"></canvas>
					<script>
						var size = Math.round(document.getElementById('graph').clientWidth / 40);

						<?php
							$w = getDamageAvgPerWeapon($bd, $name);
							arsort($w);

							$names = [];
							$avgs = [];
							$images = [];
							foreach ($w as $weapon => $avg) {
								$names[] = '"' . WEAPONS_NAME[$weapon] . '"';
								$avgs[] = '"' . $avg . '"';
								$images[] = '"/.resources/img/mhWilds/weapons/' . WEAPONS_IMG[$weapon] . '"';
							}
							//var_dump($names);
							//var_dump($images);
							echo 'var xValues = [', implode(", ", $names), "];\n";
							echo 'var yValues = [', implode(", ", $avgs), "];\n";
							echo 'var weaponImages = [', implode(", ", $images), "];\n";
						?>

						const weaponImageObjs2 = weaponImages.map(src => {
							const img = new Image();
							img.src = src;
							return img;
						});

						// Draw icons plugin
						const drawWeaponIcons2 = {
							id: 'drawWeaponIcons',
							afterDraw(chart) {
								const { ctx, scales: { y } } = chart;
								ctx.save();

								xValues.forEach((label, index) => {
									const img = weaponImageObjs2[index];
									const imgSize = 45; // adjust as needed
									const yPos = y.getPixelForTick(index); // ✅ This works with indexAxis: 'y'

									if (img.complete && img.naturalWidth > 0) {
										ctx.drawImage(
											img,
											y.left - imgSize,
											yPos - imgSize / 2,
											imgSize,
											imgSize
										);
									} else {
										img.onload = () => chart.draw(); // ensure reload if image wasn't ready
									}
								});

								ctx.restore();
							}
						};

						// Create chart
						new Chart("damageGraph", {
							type: "bar",
							data: {
								labels: xValues,
								datasets: [{
									data: yValues,
									backgroundColor: barColors
								}]
							},
							options: {
								indexAxis: 'y',
								layout: {
									padding: {
										left: 60 // give space for icon
									}
								},
								scales: {
									y: {
										ticks: {
											color: "white",
											font: { size: 0 }
										}
									},
									x: {
										ticks: { color: "white" }
									}
								},
								plugins: {
									legend: { display: false }
								}
							},
							plugins: [drawWeaponIcons2]
						});
					</script>
				</div>
			</div>
			<?php
				}
			?>
		</div>
	</div>
</body>
</html>