<?php
	require_once 'includes/master.inc.php';
	if(!$Auth->loggedIn()) redirect('../login.php');
	
	$class = new Loop('odd', 'even');
	$traintables = TrainTable::GetAllTrainTables();
?>
						<tr>
							<td><a href="admin.php?view=traintable&action=edit" class="button">Add time table</a></td>
						</tr>
						<tr>
							<td>
								<table class="data">
									<tr>
										<th>File name</th>
										<th>Added on</th>
									</tr>
<?php
	foreach ($traintables as $key => $value) 
	{
		echo "\t\t\t\t\t\t\t\t\t" . '<tr class="' . $class . '">' . "\n";
		echo "\t\t\t\t\t\t\t\t\t\t" . '<td>' . $value->filename . '</td>' . "\n";
		echo "\t\t\t\t\t\t\t\t\t\t" . '<td>' . $value->import_timestamp . '</td>' . "\n";
		echo "\t\t\t\t\t\t\t\t\t" . '</tr>' . "\n";
	}
?>
								</table>
							</td>
						</tr>