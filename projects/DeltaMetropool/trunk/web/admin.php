<?php
// TODO: Add admin check
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta name="keywords" content="">
<meta name="description" content="">
<title>Sprintstad Server</title>
<link rel="stylesheet" type="text/css" href="style/reset-fonts-grids.css">
<link rel="stylesheet" type="text/css" href="style/base.css">
<link rel="stylesheet" type="text/css" href="style/style.css">
</head>
<body>
<div id="doc4" class="yui-t2">
	<div id="hd">
		<h1>Sprintstad Server</h1>
	</div>
	<div id="bd">
		<div id="yui-main">
			<div class="yui-b">
				<?php
					if ($_GET['page'] && 
						file_exists('pages/' . $_GET['page'] . '.php')) 
						include('pages/' . $_GET['page'] . '.php');
					else 
						include('pages/start.php');
				?>
			</div>
		</div>
		<div class="yui-b">
			<div class="menu-body">
			<div class="menu-header">Spel</div>
				<ul>
					<li><a href="admin.php?page=new_game">Nieuw Spel</a></li>
					<li><a href="admin.php?page=games">Spellen</a></li>
					<li><a href="admin.php?page=teams">Teams</a></li>
				</ul>
			</div>
			<div class = "menu-footer"></div>
			<div class="menu-header">Data</div>
			<div class="menu-body">
				<ul>
					<li><a href="admin.php?page=stations">Stationen</a></li>
					<li><a href="admin.php?page=rounds">Ronden</a></li>
					<li><a href="admin.php?page=station_types">Stationstypen</a></li>
					<li><a href="admin.php?page=build_options">Bouw opties</a></li>
					<li><a href="admin.php?page=constants">Constante gegevens</a></li>
				</ul>
			</div>
			<div class = "menu-footer"></div>
		</div>
	</div>
	<div id="ft">
		<div class="credits"></div>
	</div>
</div>
</body>
</html>
