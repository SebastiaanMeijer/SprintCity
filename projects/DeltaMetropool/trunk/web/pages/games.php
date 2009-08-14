<?php
require_once './includes/master.inc.php';

function getPage()
{
	return isset($_GET['page']) ? $_GET['page'] : 1;
}
// TODO: Add admin check
?>

<div class="area">
	<h2>Spellen</h2>
<?php
	$class = new Loop('odd', 'even');
	$pager = new Pager(getPage(), 10, Team::rowCount());
	$objects = Game::getGames($pager->firstRecord, $pager->perPage);
	printPager($pager, isset($_REQUEST['view']) ? $_REQUEST['view'] : "start");
?>
	<table class="data">
		<tr>
			<th>ID</th>
			<th>Naam</th>
			<th>Opmerkingen</th>
			<th>Start</th>
			<th>Ronde</th>
			<th>Programma's</th>
			<th>Voortgang</th>
		</tr>
<?php	
	foreach ($objects as $key => $value) 
	{
		$round_info = new RoundInfo($value->current_round_id);
		$total_programs = StationInstance::rowCountByGame($key);
		$committed_programs = RoundInstance::getCommittedRounds($value->current_round_id);
?>
		<tr class="<?php echo $class; ?>">
			<td><?php echo $key; ?></td>
			<td><?php echo $value->name; ?></td>
			<td><?php echo $value->notes; ?></td>
			<td><?php echo $value->starttime; ?></td>
			<td><?php echo $round_info->name; ?></td>
			<td><?php echo $committed_programs . "/" . $total_programs; ?></td>
			<td><input type="button" name="unknown" value="Volgende ronde"></td>
		</tr>
<?php
	}
?>
	</table>
<?php
	printPager($pager, isset($_REQUEST['view']) ? $_REQUEST['view'] : "start");
?>

</div>