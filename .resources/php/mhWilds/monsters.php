<?php
require_once("helpers.php");
$bd = simplexml_load_file("/mnt/disk/.resources/bd/mhWilds/mhWilds.xml");
?>

<!DOCTYPE html>
<html>
<head>
	<title>Monstruos - Per-Server</title>
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
		<div id="content" style="flex-flow: wrap;">
			<?php
			$monsters = getAllMonsters($bd);
			sort($monsters);
			$i = 0;
			foreach ($monsters as $monster) {
				echo '
					<div class="monsterCell">
					<a href="monster.php?name=', $monster, '"><img src="/.resources/img/mhWilds/monsters/', strtolower($monster), '.png"></a>
					<a href="monster.php?name=', $monster, '">', $monster, '</a>
					</div>
				';
				$i++;
			}
			?>
		</div>
	</div>
</body>
</html>
