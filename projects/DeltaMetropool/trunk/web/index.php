<?php
require_once './includes/master.inc.php';
if(isset($_REQUEST['Action']))
	header("Location: ./game.htm");

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
	<div class="login">
		<h2>Login</h2>
		<form action="index.php" method="POST">
		<table>
			<tr>
				<td>Spel</td>
				<td>
					<select name="game" onChange="this.form.submit()">
<?php
	$games = Game::getActiveGames();
	$keys = array_keys($games);
	$selected_game = isset($_REQUEST['game']) ? $_REQUEST['game'] : $games[$keys[0]]->id;
	foreach ($games as $key => $value)
	{
		if ($key == $selected_game)
			echo "\t\t\t\t\t\t" . '<option value="' . $key . '" SELECTED>' . $value->name . '</option>' . "\n";
		else
			echo "\t\t\t\t\t\t" . '<option value="' . $key . '">' . $value->name . '</option>' . "\n";
	}
?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Team</td>
				<td>
<?php
	$teams = Team::getTeamsInGame($selected_game);
	echo "\t\t\t\t\t" . '<select name="team">' . "\n";
	foreach ($teams as $key => $value)
	{
		if ($value->cpu == 0)
			echo "\t\t\t\t\t\t" . '<option value="' . $key . '">' . $value->name . '</option>' . "\n";
	}
	echo "\t\t\t\t\t" . '</select>' . "\n";
?>
				</td>
			</tr>
			<tr>
				<td colspan="2"><button type="submit" name="Action" value="join_game">Start Spel</button></td>
			</tr>
		</table>
		</form>
		<a href="admin.php" style="display:block; margin:0 0 5px 5px; width: 95%; text-align: right;">admin</a>
	</div>
</body>
</html>