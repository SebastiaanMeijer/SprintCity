<?php
	require_once 'includes/master.inc.php';

	if(!$Auth->loggedIn()) redirect('../login.php');
	
	$class = new Loop('odd', 'even');
	$stationTypes = StationTypes::getAllStationTypes();
?>

<div class="area">
	<h2>Stationstypen</h2>
	<form action="pages/submit_form.php" method="POST">
		<table>
			<tr class="warning">
				<td>
					<b>Waarschuwing:</b> Het wijzigen van deze waarden heeft effect op alle scenarios en games. Wanneer alreeds gespeelde games opnieuwe afgespeeld worden zullen er afwijkende resultaten optreden.
				</td>
			</tr>
		</table>
<?php
	if (isset($_GET['intent']) && $_GET['intent'] == "done")
	{
?>
		<script type="text/javascript">
			setTimeout(fadeSavedMessageOut, 2000);
			function fadeSavedMessageOut() {
				$('#saved_message').fadeOut().empty();
			}
		</script>
		<span id="saved_message">Bezig met opslaan...</span>
<?php
	}
?>
		<table>
			<tr>
				<td>
					<table class="data">
						<tr>
							<th colspan="3">Typen</th>
						</tr>
<?php
	foreach ($stationTypes as $stationType_key => $stationType_value)
	{
		echo "\t\t\t\t\t\t\t\t\t" . '<tr class="' . $class . '">' . "\n";
		echo "\t\t\t\t\t\t\t\t\t\t" . '<td><input type="text" name="name,' . $stationType_key . '" maxLength="255" style="width: 150px" value="' . $stationType_value->name . '"></td>' . "\n";
		echo "\t\t\t\t\t\t\t\t\t\t" . '<td><textarea name="description,' . $stationType_key . '" rows="12" style="width:350px;">' . $stationType_value->description . '</textarea></td>' . "\n";
		echo "\t\t\t\t\t\t\t\t\t\t" . '<td>
			<table>
				<tr>
					<td>POVN:</td>
					<td><input type="text" name="povn,' . $stationType_key . '" maxLength="20" style="width: 50px" value="' . $stationType_value->POVN . '"></td>
				</tr>
				<tr>
					<td>PWN:</td>
					<td><input type="text" name="pwn,' . $stationType_key . '" maxLength="20" style="width: 50px" value="' . $stationType_value->PWN . '"></td>
				</tr>
				<tr>
					<td>IWD:</td>
					<td><input type="text" name="iwd,' . $stationType_key . '" maxLength="20" style="width: 50px" value="' . $stationType_value->IWD . '"></td>
				</tr>
				<tr>
					<td>MNG:</td>
					<td><input type="text" name="mng,' . $stationType_key . '" maxLength="20" style="width: 50px" value="' . $stationType_value->MNG . '"></td>
				</tr>
			</table>
			</td>' . "\n";
		echo "\t\t\t\t\t\t\t\t\t" . '</tr>' . "\n";
	}
?>
					</table>
				</td>
			</tr>
			<tr>
				<td>
					<button type="submit" name="Action" value="edit_station_types">Opslaan</button>
				</td>
			</tr>
		</table>
	</form>
</div>
