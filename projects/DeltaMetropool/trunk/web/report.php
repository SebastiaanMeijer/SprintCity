<?php
	require_once './includes/master.inc.php';
	
	function getPage()
	{
		return isset($_GET['page']) ? $_GET['page'] : 1;
	}
?>

<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<meta name="keywords" content="">
		<meta name="description" content="">
		<title>Sprintstad Rapportage</title>
		<link href="images/mobility/ns-logo.ico" rel="shortcut icon"/> 
		<link rel="stylesheet" type="text/css" href="style/reset-fonts-grids.css">
		<link rel="stylesheet" type="text/css" href="style/mobility.css">
		<script type="text/javascript" src="script/mobility/resize.js"></script>
	</head>
	
	<body>
	
		<div class="area">
			<h2>Teams</h2>
			<?php
				$class = new Loop('odd', 'even');
				$pager = new Pager(getPage(), 10, Team::rowCount());
				$teams = Team::getTeams($pager->firstRecord, $pager->perPage);
				$allstations = Station::getStations($pager->firstRecord, $pager->perPage);
				$types = Type::getTypes();
				$rounds = RoundInfo::getRounds();
			?>
				
			
			<table border="2">
			<tr>
				<th>TeamID</th>
				<th>Teamnaam</th>
				<th>Waarden</th>
				<th>Stations</th>
			</tr>
			<?php
			foreach ($teams as $key => $value)
			{
				$teaminstance = TeamInstance::getTeamInstanceIdByGameAndTeam(Game::getGameIdOfSession(session_id()), $value->id);
				?>
					<tr class="<?php echo $class; ?>">
					<td><?php echo $key; ?></td>
					<td><?php echo $value->name; ?></td>
					<td>
						<?php
						{
							$values = ValueInstance::getCheckedValuesByTeam($teaminstance);
							foreach($values as $key => $instance)
							{
								echo Value::getValueDescription($instance->value_id); 
								?>
								<br>
								<?php
							}
						}
						?></td>
					<td>
						<?php
						{
							$stations = Station::getStationNamesByTeam($teaminstance);
							foreach($stations as $key => $instance)
							{
								echo $instance->name;
								?>
								<br>
								<?php
							}
						}
						?></td>
				</tr>
			<?php
			}
			?>

			</table>
			<br>
			<br>
			
			<?php
			foreach ($allstations as $key => $station)
			{
			?>
				<h2><?php echo $station->name; ?></h2>
				
				<?php
				
				$initial = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,$station->transform_area_cultivated_home, $station->transform_area_cultivated_work, $station->transform_area_cultivated_mixed, $station->transform_area_undeveloped_urban, $station->transform_area_undeveloped_mixed);
				$masterplan_small = Program::getMasterplan(Station::getStationInstanceId($key));
				if(isset($masterplan_small[1]))
					$masterplan = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,$masterplan_small[1]->area_home, $masterplan_small[1]->area_work, $masterplan_small[1]->area_leisure, 0, 0);
				else
					$masterplan = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
				
				$simple_current = $initial;
				$current = $initial;
				$roundId = RoundInfo::getCurrentRoundIdBySessionId(session_id());
				$programs = Program::getStationAppliedPrograms(Station::getStationInstanceId($key), $roundId);
				$total_special = 0;
				$total_transformed_area = 0;
				$total_home = 0;
				$total_leisure = 0;
				$total_work = 0;
				foreach($programs as $key => $program)
				{
					if($program->type_home < 15)
					{
						$total_special += $program->area_home;
					}
					
					if($program->type_work < 15)
					{
						$total_special += $program->area_work;
					}
			
					if($program->type_leisure < 15)
					{
						$total_special += $program->area_leisure;
					}
					
					$current[$program->type_home-1] += $program->area_home;
					$current[$program->type_work-1] += $program->area_work;
					$current[$program->type_leisure-1] += $program->area_leisure;
					
					$total_home += $program->area_home;
					$total_work += $program->area_work;
					$total_leisure += $program->area_leisure;
					
					$total_transformed_area += $program->area_home;
					$total_transformed_area += $program->area_work;
					$total_transformed_area += $program->area_leisure;
					
					for($i = 14; $i < 19; $i++)
					{	
						$simple_current[$i] -= round(($program->area_home + $program->area_work + $program->area_leisure) * ($initial[$i] / array_sum($initial)));
						$current[$i] -= round(($program->area_home + $program->area_work + $program->area_leisure) * ($current[$i] / array_sum($initial)));
					}
				}
				
			
				$simple_current[14] += $total_home;
				$simple_current[15] += $total_work;
				$simple_current[16] += $total_leisure;
				
				
				?>
				
				<table border='2'>
				<tr><td>
					<?php
					foreach($types as $key => $value)
					{
					?>
						<th><?php echo $value->name ?></th>
					<?php
					}
					?>
					<th>Urban</th>
					<th>Rural</th>
				<tr><th>Initieel</th>
					<?php
					foreach($initial as $key => $element)
					{
					?>
						<td><?php echo $element;?></td>
					<?php
					}
					?>
				<tr><th>Masterplan</th>
					<?php
					foreach($masterplan as $key => $element)
					{
					?>
						<td><?php echo $element;?></td>
					<?php
					}
					?>
					
					
				<tr><th>Huidig Simpel</th>
					<?php
					foreach($simple_current as $key => $element)
					{
					?>
						<td><?php echo $element; ?></td>
					<?php
					}
					?>
				<tr><th>Huidig Compleet</th>
					<?php
					foreach($current as $key => $element)
					{
					?>
						<td><?php echo $element; ?></td>
					<?php
					}
					?>
				
				<?php
				
				$area = $initial;
				foreach($rounds as $key => $round)
				{
				?>
					<?php 
					
		
	/* NEEDS FIXING				
					if(!($round->id == 1 || $round->id == 7) && $round->id < $roundId)
					{
						
						$program = Program::getProgram(Station::getStationInstanceId($station->id), $round->id);
						$area[$program->type_home-1] += $program->area_home;
						$area[$program->type_work-1] += $program->area_work;
						$area[$program->type_leisure-1] += $program->area_leisure;
						
						for($i = 14; $i < 19; $i++)
						{
							$area[$i] -= round(($program->area_home + $program->area_work + $program->area_leisure) * ($area[$i] / array_sum($initial)));
						}
					
					?>
						<tr><th><?php echo $round->name ?></th>
						<?php
						foreach($area as $key => $element)
						{
							?>
							<td><?php echo $element; ?></td>
						<?php
						}
					}*/
				}
				?>
				
				
				</table>
				
				<img src=images/graphs/spacegraph.php?session=<?php echo session_id() ?>&station=<?php echo $station->id ?> />
				
			<?php
			}
			?>

			
		</div>
					
	</body>
</html>