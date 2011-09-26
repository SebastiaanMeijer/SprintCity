<?php
	require_once 'includes/master.inc.php';
	if(!$Auth->loggedIn()) redirect('../login.php');
	
	$class = new Loop('odd', 'even');
	$scenarios = Scenario::getAllScenarios();
?>
						<tr>
							<td><a href="admin.php?view=scenario&action=edit" class="button">Nieuw Scenario</a></td>
						</tr>
						<tr>
							<td>
								<table class="data">
									<tr>
										<th></th>
										<th>Naam</th>
										<th>Omschrijving</th>
									</tr>
<?php
	foreach ($scenarios as $key => $value) 
	{
		echo "\t\t\t\t\t\t\t\t\t" . '<tr class="' . $class . '">' . "\n";
		echo "\t\t\t\t\t\t\t\t\t\t" . '<td>' . "\n";
		echo "\t\t\t\t\t\t\t\t\t\t\t" . '<a href="admin.php?view=scenario&action=edit&scenario=' . $value->id . '" class="button">Wijzigen</a>' . "\n";
		echo "\t\t\t\t\t\t\t\t\t\t" . '</td>' . "\n";
		echo "\t\t\t\t\t\t\t\t\t\t" . '<td>' . $value->name . '</td>' . "\n";
		echo "\t\t\t\t\t\t\t\t\t\t" . '<td>' . $value->description . '</td>' . "\n";
		echo "\t\t\t\t\t\t\t\t\t" . '</tr>' . "\n";
	}
?>
								</table>
							</td>
						</tr>