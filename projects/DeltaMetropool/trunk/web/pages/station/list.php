<?php
	require_once 'includes/master.inc.php';
	if(!$Auth->loggedIn()) redirect('../login.php');
	
	$class = new Loop('odd', 'even');
	$stations = Station::getAllStations();
?>
						<tr>
							<td><a href="admin.php?view=station&action=edit" class="button">Nieuw Station</a></td>
						</tr>
						<tr>
							<td>
								<table class="data">
									<tr>
										<th></th>
										<th>Station</th>
										<th>Gemeente</th>
										<th>Regio</th>
									</tr>
<?php
	foreach ($stations as $station_key => $station_value) 
	{
		echo "\t\t\t\t\t\t\t\t\t" . '<tr class="' . $class . '">' . "\n";
		echo "\t\t\t\t\t\t\t\t\t\t" . '<td>' . "\n";
		echo "\t\t\t\t\t\t\t\t\t\t\t" . '<a href="admin.php?view=station&action=edit&station=' . $station_value->id . '" class="button">Wijzigen</a>' . "\n";
		echo "\t\t\t\t\t\t\t\t\t\t" . '</td>' . "\n";
		echo "\t\t\t\t\t\t\t\t\t\t" . '<td>' . $station_value->name . '</td>' . "\n";
		echo "\t\t\t\t\t\t\t\t\t\t" . '<td>' . $station_value->town . '</td>' . "\n";
		echo "\t\t\t\t\t\t\t\t\t\t" . '<td>' . $station_value->region . '</td>' . "\n";
		echo "\t\t\t\t\t\t\t\t\t" . '</tr>' . "\n";
	}
?>
								</table>
							</td>
						</tr>