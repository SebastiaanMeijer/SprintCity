<?php
require_once './includes/master.inc.php';

// TODO: Add admin check

	$class = new Loop('odd', 'even');
	$teams = Team::getTeams(0, Team::rowCount());
	$stations = Station::getStations(0, Station::rowCount());
?>

<div class="area">
	<h2>Nieuw Spel</h2>
	<form action="pages/submit_form.php" method="POST">
		<table>
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
				<td colspan = "2">
					<button type="submit" name="Action" value="new_game">Start Spel</button>
				</td>
			</tr>
		</table>
		
	</form>
</div>