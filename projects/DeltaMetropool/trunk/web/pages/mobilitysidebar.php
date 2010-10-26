<?php
	require_once('../includes/master.inc.php');

	if (ClientSession::hasSession(session_id()))
	{
		if (RoundInfo::getCurrentRoundIdBySessionId(session_id()) == MASTERPLAN_ROUND_ID && 
			isset($_POST['ambitionCheckbox']) && 
			isset($_POST['ambitionMotivation']))
		{
			SaveAmbitions();
		}
		
		if (isset($_POST['povn']) &&
			isset($_POST['povnMotivation']))
		{
			SavePOVN();
		}
	}
	
	function SaveAmbitions()
	{
		$db = Database::getDatabase();
		$game_id = Game::getGameIdOfSession(session_id());
		// uncheck all ambitions for the mobility player
		$query = "
			UPDATE ValueInstance
			INNER JOIN TeamInstance ON ValueInstance.team_instance_id = TeamInstance.id
			INNER JOIN Team ON TeamInstance.team_id = Team.id
			SET ValueInstance.checked = false
			WHERE TeamInstance.game_id = :game_id
				AND Team.id = :team_id";
		$args = array(
			'game_id' => $game_id,
			'team_id' => MOBILITY_TEAM_ID);
		$db->query($query, $args);
		
		// check selected ambitions
		foreach($_POST['ambitionCheckbox'] as $valueInstanceId)
		{
			$query = "
				UPDATE ValueInstance
				SET ValueInstance.checked = true
				WHERE ValueInstance.id = :value_instance_id";
			$args = array('value_instance_id' => $valueInstanceId);
			$db->query($query, $args);
		}
		
		// fill ambition motivation
		$query = "
			UPDATE TeamInstance
			SET TeamInstance.value_description = :motivation
			WHERE TeamInstance.game_id = :game_id
				AND TeamInstance.team_id = :team_id";
		 $args = array(
			'motivation' => $_POST['ambitionMotivation'],
			'game_id' => $game_id,
			'team_id' => MOBILITY_TEAM_ID);
		 $db->query($query, $args);
	}
	
	function SavePOVN()
	{
		$db = Database::getDatabase();
		$game_id = Game::getGameIdOfSession(session_id());
		$current_round_id = RoundInfo::getCurrentRoundIdBySessionId(session_id());
		$next_round_id = RoundInfo::getRoundInfoIdAfter($current_round_id);
		
		if ($next_round_id == '')
			return;
		
		// fill in new povn values in next round's round instance
		foreach($_POST['povn'] as $key => $value)
		{
			$query = "
				UPDATE RoundInstance
				INNER JOIN Round ON RoundInstance.round_id = Round.id
				INNER JOIN StationInstance ON RoundInstance.station_instance_id = StationInstance.id
				INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id
				SET RoundInstance.POVN = :new_povn
				WHERE Round.round_info_id = :next_round_id
					AND StationInstance.station_id = :station_id
					AND TeamInstance.game_id = :game_id;";
			$args = array(
				'new_povn' => $value,
				'next_round_id' => $next_round_id,
				'station_id' => $key,
				'game_id' => $game_id);
			$db->query($query, $args);
		}
		
		// fill in motivation
		$query = "
			UPDATE RoundInfoInstance
			SET RoundInfoInstance.mobility_report = :motivation
			WHERE RoundInfoInstance.game_id = :game_id
				AND RoundInfoInstance.round_info_id = :next_round_id";
		$args = array(
			'motivation' => $_POST['povnMotivation'],
			'next_round_id' => $next_round_id,
			'game_id' => $game_id);
		$db->query($query, $args);
	}
	
	function ShowAmbitionForm()
	{
?>
		<form class="form" id="ambitions" action="mobilitysidebar.php" method="post">
			<table>
				<caption>Ambities</caption>
<?php
				$game_id = Game::getGameIdOfSession(session_id());
				$motivation = TeamInstance::getValueDescription($game_id, MOBILITY_TEAM_ID);
				$result = ValueInstance::getValuesByGameAndTeam($game_id, MOBILITY_TEAM_ID);
				while ($row = mysql_fetch_array($result))
				{
?>
				<tr>
					<td class="checkbox"><input type="checkbox" name="ambitionCheckbox[]" value="<?php echo $row['id']; ?>" onClick="checkMax('ambitionCheckbox[]', 1, this)" <?php echo $row['checked'] == 1 ? "checked" : ""; ?>></td>
					<td class="leftAlign"><?php echo $row['title']; ?></td>
				</tr>
<?php
				}
?>
			</table>
			<h1>Motivatie</h1>
			<p>
				<textarea class="textfield" type="text" name="ambitionMotivation"><?php echo $motivation; ?></textarea>
			</p>
			<p class="inputbutton">
				<input type="submit" value="Ambities vastleggen" onClick="showConfirm()">
			</p>
		</form>
<?php
	}
	
	function ShowAmbitionText()
	{
?>
		<table>
			<caption>Ambities</caption>
<?php
			$game_id = Game::getGameIdOfSession(session_id());
			$motivation = TeamInstance::getValueDescription($game_id, MOBILITY_TEAM_ID);
			$result = ValueInstance::getValuesByGameAndTeam($game_id, MOBILITY_TEAM_ID);
			while ($row = mysql_fetch_array($result))
			{
				if ($row['checked'] == 1)
				{
?>
					<tr>
						<td><?php echo $row['title']; ?></td>
					</tr>
<?php
				}
			}
?>
		</table>
		<h1>Motivatie</h1>
		<p>
			<?php echo $motivation; ?>
		</p>
<?php
	}

	function ShowStationForm()
	{
		$gameId = Game::getGameIdOfSession(session_id());
		$motivation = RoundInfoInstance::getMobilityReport($gameId);
		$result = Station::getStationsAndPOVNUsedInGame($gameId);

		if (mysql_num_rows($result) == 0)
			return;
?>
		<form class="form" action="mobilitysidebar.php" method="post">
			<table class="ambitions">
				<caption>Netwerkwaarde</caption>
<?php
		$gameId = Game::getGameIdOfSession(session_id());
		$motivation = RoundInfoInstance::getMobilityReport($gameId);
		$result = Station::getStationsAndPOVNUsedInGame($gameId);
		while ($row = mysql_fetch_array($result))
		{
?>
				<tr>
					<td><?php echo $row['name']; ?></td>
					<td><input class="input" type="text" name="povn[<?php echo $row['id']; ?>]" value="<?php echo $row['POVN']; ?>"/></td>
				</tr>
<?php
		}
?>
			</table>
			<h1>Motivatie</h1>
			<p>
				<textarea class="textfield" type="text" name="povnMotivation"><?php echo $motivation; ?></textarea>
			</p>
			<p class="inputbutton">
				<input type="submit" value="Doorvoeren">
			</p>
		</form>
<?php
	}
?>

<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<meta name="keywords" content="">
		<meta name="description" content="">
		<link rel="stylesheet" type="text/css" href="../style/reset-fonts-grids.css">
		<link rel="stylesheet" type="text/css" href="../style/mobility.css">
		<script type="text/javascript" src="../script/mobility/ambition.js"></script>
	</head>
	<body>
		<p class="ovTitle">Openbaar Vervoer</p>
		<div id="nslogo"></div>
		<div class="stationText"></div>
		<div class="sidebarWindow">
<?php
	if(RoundInfo::getCurrentRoundIdBySessionId(session_id()) == MASTERPLAN_ROUND_ID)
	{
		ShowAmbitionForm();
	}
	else
	{
		ShowAmbitionText();
		ShowStationForm();
	}
?>
		<p>
			<a href="javascript:location.reload(true)">Volgende ronde</a>
		</p>
		</div>
	</body>
</html>