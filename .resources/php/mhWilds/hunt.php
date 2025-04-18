<?php
require_once("helpers.php");
$id = intval($_GET["id"]);
$bd = simplexml_load_file("../../bd/mhWilds/mhWilds.xml");
$hunt = $bd->hunt[$id];
?>

<!DOCTYPE html>
<html>
<head>
	<title>Cacería - Per-Server</title>
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
				<h1><?php 
						$monsters = getMonstersFromHunt($hunt);  
						for ($i=0; $i < count($monsters); $i++) { 
							if($i == count($monsters) - 1){
								echo $monsters[$i];
							}
							else{
								echo $monsters[$i], " - ";
							}
						}
						echo " en ";
						humanTime($hunt->time);
					?>
				</h1>
			</div>
			<div id="fail">
				<?php
					if(intval($hunt->failed)){
						echo '<img src="../../img/mhWilds/fail.png">';
					}
				?>
			</div>
			<div id="icons">
				<?php
					foreach ($monsters as $monster) {
						echo '<img src="../../img/mhWilds/monsters/', strtolower($monster), '.png">';
					}
				?>
			</div>
			<div id="graph">
				<canvas id="damageGraph" height="100"></canvas>

				<script>
					var width = document.getElementById('graph').clientWidth;
                	var size = Math.round(width / 40);

					<?php
						$damages = getTotalDamageFromHunt($hunt);
						arsort($damages);

						$player = [];
						$damage = [];
						foreach ($damages as $p => $d) {
							$player[] = '"' . $p . '"';
							$damage[] = '"' . $d . '"';
						}

						echo 'var xValues = [', implode(", ", $player), "];\n";
						echo 'var yValues = [', implode(", ", $damage), "];\n";
					?>

					var barColors = ["red", "blue", "green", "yellow"];

					new Chart("damageGraph", {
					  type: "bar",
					  data: {
					    labels: xValues,
					    datasets: [{
					      backgroundColor: barColors,
					      data: yValues
					    }]
					  },
					  options: {
					  	indexAxis: 'y',
					    scales: {
				            y: {
				                ticks: { 
				                	color: "white",
				            		font: {
					                    size: size,
					                }}
				            },
				            x: {
				                ticks: { color: "white"}
				            }
				        },
				        plugins: {
				        	legend: {
				        		display: false
				        	}
				        }
				        
					  }
					});
					</script>
			</div>
			<div id="desglose">
				<?php
				$players = getPlayersFromHunt($hunt);
				$weapons = getWeaponsFromHunt($hunt);
				$damage = getDamageTypeFromHunt($hunt);
				foreach ($players as $player) {
					echo '<div class="desg">';
						echo '<div class="player_heading">';
							echo "<h1>", $player, "</h1>";
							if (count($weapons) != 0 && !isOtomo($bd, $player)){
								echo "<img src=\"/.resources/img/mhWilds/weapons/", WEAPONS_IMG[$weapons[$player]], "\">";
							}
						echo '</div>';
						echo "<span>Daño físico: ", round($damage[$player]["phys"], 2), "</span><br>";
						echo "<span>Daño elemental: ", round($damage[$player]["elem"], 2), "</span><br>";
						echo "<span>Daño veneno: ", round($damage[$player]["poison"], 2), "</span><br>";
						echo "<span>Daño nitro: ", round($damage[$player]["blast"], 2), "</span><br>";
						echo "<span>Daño true: ", round($damage[$player]["true"], 2), "</span><br>";
						echo "<br>";
						echo "<span>Aturdimiento: ", round($damage[$player]["stun"], 2), "</span><br>";
						echo "<span>Sueño: ", round($damage[$player]["sleep"], 2), "</span><br>";
						echo "<span>Paralisis: ", round($damage[$player]["para"], 2), "</span><br>";
					echo '</div>';
				}

				?>
			</div>
		</div>
	</div>
</body>
</html>