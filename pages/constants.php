<?php
	require_once 'includes/master.inc.php';

	if(!$Auth->loggedIn()) redirect('../login.php');
	
	$db = Database::getDatabase();	
	$query = "
		SELECT * 
		FROM `constants` 
		LIMIT 0 , 1";
	$db->query($query);
	if(!$db->hasRows())
	{
		$db->query("INSERT INTO `constants` () VALUES ()");
		$db->query($query);
	}
	$row = $db->getRow();
?>

<div class="area">
	<h2>Constant values</h2>
	<form action="pages/submit_form.php" method="POST">
		<table>
			<tr class="warning">
				<td>
					<b>Warning:</b> Changing these values will affect all scenarios and games. When already performed sessions are run again, different results will occur.
				</td>
			</tr>
		</table>
		<table>
			<tr>
				<td>
					<table class="data">
						<tr>
							<th colspan="2">Population</th>
						</tr>
						<tr class="odd">
							<td>Average number of residents per dwelling</td>
							<td><input type="text" name="average_citizens_per_home" maxlength="20" value="<?php echo $row['average_citizens_per_home'] ?>"></td>
						</tr>
						<tr class="even">
							<td>Average number of jobs per business floor space</td>
							<td><input type="text" name="average_workers_per_bvo" maxlength="20" value="<?php echo $row['average_workers_per_bvo'] ?>"></td>
						</tr>
						<tr>
							<th colspan="2">Ridership</th>
						</tr>
						<tr class="odd">
							<td>Average number of passengers per resident</td>
							<td><input type="text" name="average_travelers_per_citizen" maxlength="20" value="<?php echo $row['average_travelers_per_citizen'] ?>"></td>
						</tr>
						<tr class="even">
							<td>Average number of passengers per job</td>
							<td><input type="text" name="average_travelers_per_worker" maxlength="20" value="<?php echo $row['average_travelers_per_worker'] ?>"></td>
						</tr>
						<tr class="odd">
							<td>Average number of passengers per hectare amenities</td>
							<td><input type="text" name="average_travelers_per_ha_leisure" maxlength="20" value="<?php echo $row['average_travelers_per_ha_leisure'] ?>"></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td>
					<button type="submit" name="Action" value="edit_constants">Save</button>
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
					<span id="saved_message">Saving...</span>
<?php
				}
?>
				</td>
			</tr>
		</table>
	</form>
</div>
