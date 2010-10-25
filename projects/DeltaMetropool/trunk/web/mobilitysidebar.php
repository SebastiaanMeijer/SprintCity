<?php
	/* ambitions */
	$motivation = $_POST['motivation'];
	$ambitionBoxes = $_POST['ambitionCheckbox'];
	
	require_once('includes/master.inc.php');
	require_once('mobilityheader.php');

	if (ClientSession::hasSession(session_id()))
	{
		if(isset($ambitionBoxes) && isset($ambitionMotivation))
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
			foreach($ambitionBoxes as $valueInstanceId)
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
				'motivation' => $motivation,
				'game_id' => $game_id,
				'team_id' => MOBILITY_TEAM_ID);
			 $db->query($query, $args);
		}
	}
	
	function PrintAmbitionForm()
	{
?>
		<form class="form" action="mobilitysidebar.php" method="post">
			<table class="ambitions">
			<caption>Ambities</caption>
<?php
				$game_id = Game::getGameIdOfSession(session_id());
				$motivation = TeamInstance::getValueDescription($game_id, MOBILITY_TEAM_ID);
				$result = ValueInstance::getValuesByGameAndTeam($game_id, MOBILITY_TEAM_ID);
				while ($row = mysql_fetch_array($result))
				{
?>
					<tr>
						<td class="checkbox"><input type="checkbox" name="ambitionCheckbox" value="<?php echo $row['id']; ?>" onClick="checkMax()" <?php echo $row['checked'] == 1 ? "checked" : ""; ?>></td>
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
	
	function PrintAmbitionText()
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

	function PrintStationForm()
	{
?>
		<form class="form" action="mobilitysidebar.php" method="post">
			<table class="ambitions">
			<caption>Ambities</caption>
<?php
		$game_id = Game::getGameIdOfSession(session_id());
		$motivation = TeamInstance::getValueDescription($game_id, MOBILITY_TEAM_ID);
		$result = ValueInstance::getValuesByGameAndTeam($game_id, MOBILITY_TEAM_ID);
		while ($row = mysql_fetch_array($result))
		{
?>
					<tr>
						<td class="checkbox"><input type="checkbox" name="ambitionCheckbox[]" value="<?php echo $row['id']; ?>" onClick="checkMax()" <?php echo $row['checked'] == 1 ? "checked" : ""; ?>></td>
						<td class="leftAlign"><?php echo $row['title']; ?></td>
					</tr>
<?php
		}
?>
			</table>
			<h1>Motivatie</h1>
			<p>
				<textarea class="textfield" type="text" name="motivation"><?php echo $motivation; ?></textarea>
			</p>
			<p class="inputbutton">
				<input type="submit" value="Ambities vastleggen" onClick="showConfirm()">
			</p>
		</form>
<?php
	}
?>

	<p class="ovTitle">Openbaar Vervoer</p>
	<div id="nslogo"></div>
	<div class="stationText">
	<div class="sidebarWindow">
<?php
	if(!isset($motivation))
		PrintAmbitionForm();
	else
		PrintAmbitionText();
?>
	</div>

<?php
	if(isset($motivation))
	{
?>
	<div class="sidebarWindow">
		<p class="ovTitle">Netwerkwaarden</p>
		<form class="form" name="input" action="mobilitysidebar.php" method="post">
			<table>
				<tr>
					<th>Station</th>
					<th>Netwerkwaarde</th>
				</tr>
<?php
		$stationCount = 5;
		for($i = 0; $i < $stationCount; $i++)
		{
?>
				<tr>
					<td>Station <?php echo $i; ?></td>
					<td><input class="input" type="text" name="povn1" value="old povn"/></td>
				</tr>
<?php
		}
?>		
			</table>
			<h1>Motivatie</h1>
			<p>
				<textarea class="textfield" type="text" name="networkmotivation">[ Plaats hier je motivatie voor de aangepaste netwerkwaarden! ]</textarea>
			</p>
			<p><input type="submit" value="Doorvoeren"></p>
		</form>
	</div>
<?php
	}
?>