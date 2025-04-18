<?php
include(".resources/php/mhWilds/prepareDB.php");
require_once(".resources/php/mhWilds/helpers.php");
$bd = simplexml_load_file(".resources/bd/mhWilds/mhWilds.xml");

function getAllDates(){
	global $bd;
	$n = array();
	foreach ($bd->hunt as $hunt) {
		// Normalize timestamp to start of day (00:00:00)
		$day = strtotime(date("Y-m-d", intval($hunt->date)));
		if (!in_array($day, $n)) {
			$n[] = $day;
		}
	}
	return $n;
}

?>

<!DOCTYPE html>
<html>
<head>
	<title>MH Stats - Per-Server</title>
	<meta charset="utf-8">
	<link rel="stylesheet" href=".resources/css/mhW_style.css">
	<link rel="shortcut icon" type="image/x-icon" href=".resources/img/media/favicon.webp">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
	<script>
		function init(){
			loadHuntTable();
		}

		function loadHuntTable(){
	        var request = new XMLHttpRequest();
	        request.addEventListener("load", completeHandler, false);
	        request.open("POST", ".resources/php/mhWilds/huntTable.php");
	        request.send();
    	}

    	function completeHandler(event){
    		document.getElementById("huntTable").innerHTML = event.target.responseText;
      	}

      	function filterTable(){
      		var fMonster = document.getElementById("monsterSelect").value;
      		var fDate = document.getElementById("dateSelect").value;
      		var fHunter = document.getElementById("hunterSelect").value;
      		var fOtomo = document.getElementById("otomoSelect").value;
      		var fNum = document.getElementById("numSelect").value;
      		var fSort = document.getElementById("sortSelect").value;

      		var formdata = new FormData();
	        formdata.append("fMonster", fMonster);
	        formdata.append("fDate", fDate);
	        formdata.append("fHunter", fHunter);
	        formdata.append("fOtomo", fOtomo);
	        formdata.append("fNum", fNum);
	        formdata.append("fSort", fSort);
	        var request = new XMLHttpRequest();
	        request.addEventListener("load", completeHandler, false);
	        request.open("POST", ".resources/php/mhWilds/huntTable.php");
	        request.send(formdata);

      	}
	</script>
</head>
<body>
<div class="image-hero-area"></div>
<div id="container">
<div id="menu">
	<?php include("/mnt/disk/.resources/php/mhWilds/menu.html"); ?>
</div>
<div id="content">
	<div id="filters">
		<div class="filter">
			<span>Ordenar</span>
			<select id="sortSelect" onchange="filterTable()">
				<option value="1" selected>Nuevo</option>
				<option value="0">Viejo</option>
			</select>
		</div>
		<div class="filter">
			<span>Nº Cazadores</span>
			<select id="numSelect" onchange="filterTable()">
				<option value="0" selected>No Filtrar</option>
				<option value="2">2</option>
				<option value="3">3</option>
				<option value="4">4</option>
			</select>
		</div>
		<div class="filter">
			<span>Otomos</span>
			<select id="otomoSelect" onchange="filterTable()">
				<option value="0" selected>No Filtrar</option>
				<?php
					foreach (getAllOtomos($bd) as $otomo) {
						echo '<option value="', $otomo, '">', $otomo, '</option>';
					}
				?>
			</select>
		</div>
		<div class="filter">
			<span>Monstruo</span>
			<select id="monsterSelect" onchange="filterTable('monsterSelect')">
				<option value="0" selected>No Filtrar</option>
				<?php
					$monsters = getAllMonsters($bd);
					sort($monsters);
					foreach ($monsters as $monster) {
						echo '<option value="', $monster, '">', $monster, '</option>';
					}
				?>
			</select>
		</div>
		<div class="filter">
			<span>Fecha</span>
			<select id="dateSelect" onchange="filterTable('dateSelect')">
				<option value="0" selected>No Filtrar</option>
				<?php
					foreach (getAllDates() as $date) {
						echo '<option value="', $date, '">', date("d-m-Y", intval($date)), '</option>';
					}
				?>
			</select>
		</div>
		<div class="filter">
			<span>Cazador</span>
			<select id="hunterSelect" onchange="filterTable('hunterSelect')">
				<option value="0" selected>No Filtrar</option>
				<?php
					foreach (getAllHunters($bd) as $hunter) {
						echo '<option value="', $hunter, '">', $hunter, '</option>';
					}
				?>
			</select>
		</div>
	</div>

	<table id="huntTable" class="listTable">
		<!--Ajax Response Here -->
		<?php include(".resources/php/mhWilds/huntTable.php"); ?>
	</table>
</div>
</div>
</body>
</html>
