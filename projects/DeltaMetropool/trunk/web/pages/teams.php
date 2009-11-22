<?php
	require_once '/includes/master.inc.php';
	
	if(!$Auth->loggedIn()) redirect('../login.php');
	
	function getPage()
	{
		return isset($_GET['page']) ? $_GET['page'] : 1;
	}
?>

<div class="area">
	<h2>Nieuw team</h2>
	<form action="pages/submit_form.php" method="POST">
		<input type="hidden" name="page" value="<?php getPage(); ?>">
		<table>
			<tr>
				<td>Naam</td>
				<td><input type="text" name="name" maxlength="255"></td>
			</tr>
			<tr>
				<td>Opmerkingen</td>
				<td><textarea name="description" rows="6"></textarea></td>
			</tr>
			<tr>
				<td>Kleur</td>
				<td><input class="color" name="color"></input></td>
			</tr>
			<tr>
				<td>Computer</td>
				<td><input type="checkbox" name="cpu"></td>
			</tr>
			<tr>
				<td colspan="2"><button type="submit" name="Action" value="new_team">Toevoegen</button></td>
			<tr>
		</table>
	</form>
</div>
<div class="area">
	<h2>Teams</h2>
<?php
	$class = new Loop('odd', 'even');
	$pager = new Pager(getPage(), 10, Team::rowCount());
	$objects = Team::getTeams($pager->firstRecord, $pager->perPage);
	printPager($pager, isset($_REQUEST['view']) ? $_REQUEST['view'] : "start");
?>
	<table class="data">
		<tr>
			<th>ID</th>
			<th>Naam</th>
			<th>Kleur</th>
			<th>Opmerkingen</th>
			<th>Datum</th>
			<th>Computer</th>
		</tr>
<?php	
	foreach ($objects as $key => $value) {
?>
		<tr class="<?php echo $class; ?>">
			<td><?php echo $key; ?></td>
			<td><?php echo $value->name; ?></td>
			<td><div style="display:block; 
							width: 16px; 
							height: 16px; 
							background-color: <?php echo '#' . $value->color; ?>"></div></td>
			<td><?php echo $value->description; ?></td>
			<td><?php echo $value->created; ?></td>
			<td><?php echo $value->cpu; ?></td>
		</tr>
<?php
	}
?>
	</table>
<?php
	printPager($pager, isset($_REQUEST['view']) ? $_REQUEST['view'] : "start");
?>

</div>