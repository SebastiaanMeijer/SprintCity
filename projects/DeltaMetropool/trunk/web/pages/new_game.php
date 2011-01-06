<?php
	require_once 'includes/master.inc.php';
	
	if(!$Auth->loggedIn()) redirect('../login.php');
	
	$class = new Loop('odd', 'even');
	$teams = Team::getTeams(0, Team::rowCount());
	$scenarios = Scenario::getScenarios(0, Scenario::rowCount());
	$current_scenario = isset($_REQUEST['scenario']) ? $_REQUEST['scenario'] : key(Scenario::getScenarios(0, 1));
	$stations = Station::getStationsOfScenario($current_scenario);
?>

<div class="area">
	<h2>Nieuw Spel</h2>
	
		<table>
			<form action="admin.php?view=new_game" method="POST">
			<tr>
				<td>Scenario</td>
				<td>
					<select name="scenario" onChange="this.form.submit()">
<?php
	foreach ($scenarios as $key => $value)
	{
		if ($key == $current_scenario)
			echo "\t\t\t\t\t\t" . '<option value="' . $key . '" selected>' . $value->name . '</option>' . "\n";
		else
			echo "\t\t\t\t\t\t" . '<option value="' . $key . '">' . $value->name . '</option>' . "\n";
	}
?>
					</select>
				</td>
			</tr>
			</form>
			<form action="pages/submit_form.php" method="POST">
			<tr>
				<td>Naam</td>
				<td><input type="text" name="name" maxlength="255"></td>
			</tr>
			<tr>
				<td>Opmerkingen</td>
				<td><textarea name="notes" rows="6"></textarea></td>
			</tr>
			<tr>
				
				<td colspan="2">
					<table class="data">
						<tr>
							<th>Code</th>
							<th>Station</th>
							<th>Gemeente</th>
							<th>Regio</th>
							<th>Team</th>
						</tr>
<?php
	foreach ($stations as $station_key => $station_value) 
	{
		echo "\t\t\t\t\t" . '<tr class="' . $class . '">' . "\n";
		echo "\t\t\t\t\t\t" . '<td>' . $station_value->code . '</td>' . "\n";
		echo "\t\t\t\t\t\t" . '<td>' . $station_value->name . '</td>' . "\n";
		echo "\t\t\t\t\t\t" . '<td>' . $station_value->town . '</td>' . "\n";
		echo "\t\t\t\t\t\t" . '<td>' . $station_value->region . '</td>' . "\n";
		echo "\t\t\t\t\t\t" . '<td>' . "\n";
		echo "\t\t\t\t\t\t\t" . '<select name="team_' . $station_key . '">' . "\n";
		echo "\t\t\t\t\t\t\t\t" . '<option value="null" selected>-geen-</option>'. "\n";
		foreach ($teams as $team_key => $team_value)
		{
			echo "\t\t\t\t\t\t\t\t" . '<option value="' . $team_key . '">' . $team_value->name . '</option>' . "\n";
		}
		echo "\t\t\t\t\t\t\t" . '</select>' . "\n";
		echo "\t\t\t\t\t\t" . '</td>' . "\n";
		echo "\t\t\t\t\t" . '</tr>' . "\n";
	}
?>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<input type="hidden" name="scenario" value="<?php echo $current_scenario; ?>"/>
					<button type="submit" name="Action" value="new_game">Start Spel</button>
				</td>
			</tr>
			</form>
		</table>
</div>