<?php
	require_once './includes/master.inc.php';

	if(!$Auth->loggedIn()) redirect('login.php');
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
<script type="text/javascript" src="script/jscolor/jscolor.js"></script>
<script type="text/javascript" src="script/jquery/jquery.min.js"></script>
<script type="text/javascript" src="script/jquery/jquery-ui.min.js" ></script>

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
	if (isset($_GET['view']) && 
		file_exists('pages/' . $_GET['view'] . '.php')) 
		include('pages/' . $_GET['view'] . '.php');
	else 
		include('pages/games.php');
?>			
			</div>
		</div>
		<div class="yui-b">
			<div class="menu-header">Spel</div>
			<div class="menu-body">
				<ul>
					<li><a href="admin.php?view=new_game">Nieuw Spel</a></li>
					<li><a href="admin.php?view=games">Spellen</a></li>
					<li><a href="admin.php?view=teams">Teams</a></li>
				</ul>
			</div>
			<div class = "menu-footer"></div>
			<div class="menu-header">Data</div>
			<div class="menu-body">
				<ul>
					<li><a href="admin.php?view=station">Stations</a></li>
					<li><a href="admin.php?view=scenario">Scenario's</a></li>
					<li><a href="admin.php?view=traintable">Dienstregelingen</a></li>
					<li><a href="admin.php?view=constants">Constanten</a></li>
					<li><a href="admin.php?view=station_types">Stationstypen</a></li>
				</ul>
			</div>
			<div class = "menu-footer"></div>
			<div class="menu-header">Logout</div>
			<div class="menu-body">
				<ul>
					<li><a href="logout.php">Logout</a></li>
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
