<?php
	require_once './includes/master.inc.php';

	// TODO: Add admin check

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
	<h2>Constante waarden</h2>
	<form action="pages/submit_form.php" method="POST">
		<table>
			<tr>
				<td>Gemiddeld aantal bewoners per woning</td>
				<td><input type="text" name="average_citizens_per_home" maxlength="20" value="<?php echo $row['average_citizens_per_home'] ?>"></td>
			</tr>
			<tr>
				<td>Gemiddeld aantal werknemers per bvo</td>
				<td><input type="text" name="average_workers_per_bvo" maxlength="20" value="<?php echo $row['average_workers_per_bvo'] ?>"></td>
			</tr>
			<tr>
				<td colspan="2"><button type="submit" name="Action" value="edit_constants">Wijzigen</button></td>
			<tr>
		</table>
	</form>
</div>
