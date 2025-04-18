<?php
require_once("helpers.php");
$bd = simplexml_load_file("/mnt/disk/.resources/bd/mhWilds/mhWilds.xml");
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
			<th>Cazador</th><th>Cacerías</th>
		</tr>
		<?php
			$hunters = getAllHunters($bd);
			foreach ($hunters as $hunter) {
				echo '<tr>
						<td><a href="player.php?name=', $hunter, '">', $hunter, '</a></td><td>', getHuntCount($bd, $hunter), '</td>
					  </tr>';
			}

		?>
	</table>
</div>
</div>
</body>
</html>
