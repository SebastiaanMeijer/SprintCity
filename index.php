<?php
require_once './includes/master.inc.php';

$db = Database::getDatabase();

// if something needs to happen with the client session
if (isset($_REQUEST['action']))
{
	// if the game is closed from the flash app
	if ($_REQUEST['action'] == "close")
	{
		$query = "
			DELETE FROM `ClientSession` 
			WHERE `id`=:id;";
		$args = array('id' => session_id());
		$db->query($query, $args);
	}
	// if a game needs to be joined
	else if ($_REQUEST['action'] == "join")
	{
		$team_instance_id = TeamInstance::getTeamInstanceIdByGameAndTeam($_REQUEST['game'], $_REQUEST['team']);
		$query = "
			INSERT IGNORE INTO `ClientSession` 
				(`id`, `team_instance_id`, `created`) 
			VALUES
				(:id, :team_instance_id, :created);";
		$args = array(
			'id' => session_id(),
			'team_instance_id' => $team_instance_id,
			'created' => time());
		$db->query($query, $args);
	}
}

// if in a game, go to the game
if (ClientSession::hasSession(session_id()))
{
	if(ClientSession::isMobilityTeam(session_id()))
		header('Location: ./mobility.php');
	else if (ClientSession::isProvinceTeam(session_id()))
		header('Location: ./province.php');
	else
		header('Location: ./game.php');
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta name="keywords" content="">
<meta name="description" content="">
<title>SprintCity Server</title>
<link rel="stylesheet" type="text/css" href="style/reset-fonts-grids.css">
<link rel="stylesheet" type="text/css" href="style/base.css">
<link rel="stylesheet" type="text/css" href="style/style.css">
</head>
<body>
	<div class="login">
		<h2>Login</h2>
<?php
	$games = Game::getActiveGames();
	if ($games != null)
	{
?>
		<form action="index.php" method="POST">
		<table>
			<tr>
				<td>Session</td>
				<td>
					<select name="game" onChange="this.form.submit()">
<?php
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
				<td colspan="2"><button type="submit" name="action" value="join">Start Session</button></td>
			</tr>
		</table>
		</form>
<?php
	}
	else
	{
		echo 'There are no active sessions.';
	}
?>
		<a href="admin.php" style="float: right; display:block; margin:0 5px 5px 5px; width: 45%; text-align: right;">admin</a>
		<a href="report.php?game=<?php echo $selected_game; ?>" style="float: left; display:block; margin:0 5px 5px 5px; width: 45%; text-align: left;">report</a>
	</div>
</body>
</html>