<?php
	require_once './includes/master.inc.php';
	
	$db = Database::getDatabase();
	
	$game_id = isset($_GET['game']) ? $_GET['game'] : Game::getGameIdOfSession(session_id());
	$args = array('game_id' => $game_id);
	
	$rounds = array(); // [id] => name
	$teams = array(); // [id] => {'name' => name, 'color' => color}
	$stations = array(); // [id] => {'name' => name, 'team' => team_id}
	$data = array(); // [station_id] => { [round_id] => {'citizens', 'workers', 'travelers', 'povn'} }
	$types = Type::getTypes();
	$all_rounds = RoundInfo::getRounds();
	
	$query = "
	SELECT id, name
	FROM RoundInfo
	WHERE number >= 10;";
	$result = $db->query($query, array());
	if (mysql_num_rows($result) > 0)
	{
		while ($row = mysql_fetch_array($result))
		{
			$rounds[$row['id']] = $row['name'];
		}
	}
	
	$query = "
	SELECT Station.id AS StationId, Station.name AS StationName, TeamInstance.team_id AS TeamId, Team.name AS TeamName
	FROM Station
	INNER JOIN StationInstance ON Station.id = StationInstance.station_id
	INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id
	INNER JOIN Team ON TeamInstance.team_id = Team.id
	WHERE TeamInstance.game_id = :game_id;";
	$result = $db->query($query, $args);
	if (mysql_num_rows($result) > 0)
	{
		while ($row = mysql_fetch_array($result))
		{
			$station_id = $row['StationId'];
			$round_id = 2;
			$stations[$station_id] = array('name' => $row['StationName'], 'team' => $row['TeamId']);
			if (!in_array($row['TeamId'], $teams))
				$teams[$row['TeamId']] = $row['TeamName'];
			$data[$station_id] = array( 
				$round_id => array(
					'citizens' => Station::getInitialCitizenCount($station_id), 
					'workers' => Station::getInitialWorkerCount($station_id), 
					'travelers' => Station::getInitialTravelerCount($station_id), 
					'povn' => Station::getInitialPOVN($station_id)
				)
			);
		}
	}
	
	$query = "
	SELECT
		Station.id AS StationId,
		RoundInfo2.id AS RoundId,
		(
			(
				(
					(
						Station.area_cultivated_home - 
						(
							SUM(Round.new_transform_area) 
							* 
							(transform_area_cultivated_home / (transform_area_cultivated_home + transform_area_cultivated_work + transform_area_cultivated_mixed + transform_area_undeveloped_urban + transform_area_undeveloped_rural))
						)
					)
					* 
					(count_home_total / area_cultivated_home)
				) 
				+ 
				SUM(Program.area_home * TypesHome.area_density)
			)
			*
			Constants.average_citizens_per_home
			+
			IFNULL(SUM(Facility.citizens), 0)
		) AS CitizenCount,
		(
			(
				(
					(
						Station.area_cultivated_work - 
						(
							SUM(Round.new_transform_area) 
							* 
							(transform_area_cultivated_work / (transform_area_cultivated_home + transform_area_cultivated_work + transform_area_cultivated_mixed + transform_area_undeveloped_urban + transform_area_undeveloped_rural))
						)
					)
					* 
					(count_worker_total / (area_cultivated_work + area_cultivated_mixed))
				) 
				+ 
				SUM(Program.area_work * TypesWork.people_density)
			) 
			+
			(
				(
					(
						Station.area_cultivated_mixed - 
						(
							SUM(Round.new_transform_area) 
							* 
							(transform_area_cultivated_mixed / (transform_area_cultivated_home + transform_area_cultivated_work + transform_area_cultivated_mixed + transform_area_undeveloped_urban + transform_area_undeveloped_rural))
						)
					)
					* 
					(count_worker_total / (area_cultivated_work + area_cultivated_mixed))
				) 
				+ 
				SUM(Program.area_leisure * TypesLeisure.people_density)
			) 
			+
			IFNULL(SUM(Facility.workers), 0)
		) AS WorkerCount,
		RoundInstance.POVN AS Povn,
		ROUND
		(
			(
				(
					(
						(
							(
								(
									Station.area_cultivated_home - 
									(
										SUM(Round.new_transform_area) 
										* 
										(transform_area_cultivated_home / (transform_area_cultivated_home + transform_area_cultivated_work + transform_area_cultivated_mixed + transform_area_undeveloped_urban + transform_area_undeveloped_rural))
									)
								)
								* 
								IFNULL(count_home_total / area_cultivated_home, 0)
							) 
							+ 
							SUM(Program.area_home * TypesHome.area_density)
						) 
						* 
						Constants.average_citizens_per_home
						+
						IFNULL(SUM(Facility.citizens), 0)
					) 
					* Constants.average_travelers_per_citizen
				) 
				+
				(
					(
						(
							(
								Station.area_cultivated_work - 
								(
									SUM(Round.new_transform_area) 
									* 
									(transform_area_cultivated_work / (transform_area_cultivated_home + transform_area_cultivated_work + transform_area_cultivated_mixed + transform_area_undeveloped_urban + transform_area_undeveloped_rural))
								)
							)
							* 
							IFNULL(count_worker_total / (area_cultivated_work + area_cultivated_mixed), 0)
						) 
						+ 
						SUM(Program.area_work * TypesWork.people_density)
						+
						IFNULL(SUM(Facility.workers), 0)
					)
					*
					Constants.average_travelers_per_worker
				)
				+
				(
					(
						(
							(
								Station.area_cultivated_mixed - 
								(
									SUM(Round.new_transform_area) 
									* 
									(transform_area_cultivated_mixed / (transform_area_cultivated_home + transform_area_cultivated_work + transform_area_cultivated_mixed + transform_area_undeveloped_urban + transform_area_undeveloped_rural))
								)
							)
							* 
							IFNULL(count_worker_total / (area_cultivated_work + area_cultivated_mixed), 0)
						) 
						+ 
						SUM(Program.area_leisure * TypesLeisure.people_density)
					) 
					*
					Constants.average_travelers_per_worker
				)
				+
				IFNULL(SUM(Facility.travelers), 0)
			)
			*
			IFNULL
			(
				(RoundInstance2.POVN - StationInstance.initial_POVN) 
				/ 
				StationInstance.initial_POVN 
				/
				IF((RoundInstance2.POVN - StationInstance.initial_POVN) / StationInstance.initial_POVN > 5, 20, IF((RoundInstance2.POVN - StationInstance.initial_POVN) / StationInstance.initial_POVN > 1, 15, 10))
				+ 1
				, 1
			)
		) AS TravelerCount
	FROM Constants, Station
	INNER JOIN StationInstance ON Station.id = StationInstance.station_id 
	INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id
	INNER JOIN RoundInstance ON StationInstance.id = RoundInstance.station_instance_id
	INNER JOIN Program ON RoundInstance.exec_program_id = Program.id
	INNER JOIN Types AS TypesHome ON Program.type_home =TypesHome.id
	INNER JOIN Types AS TypesWork ON Program.type_work = TypesWork.id
	INNER JOIN Types AS TypesLeisure ON Program.type_leisure = TypesLeisure.id
	INNER JOIN Round ON RoundInstance.round_id = Round.id AND Station.id = Round.station_id
	INNER JOIN RoundInfo ON Round.round_info_id = RoundInfo.id
	INNER JOIN RoundInfo AS RoundInfo2 ON RoundInfo.id < RoundInfo2.id
	LEFT JOIN Round AS Round2 ON RoundInfo2.id = Round2.round_info_id AND Station.id = Round2.station_id
	LEFT JOIN RoundInstance AS RoundInstance2 ON Round2.id = RoundInstance2.round_id AND StationInstance.id = RoundInstance2.station_instance_id
	INNER JOIN Game ON TeamInstance.game_id = Game.id AND RoundInfo2.id <= Game.current_round_id
	LEFT JOIN FacilityInstance ON RoundInstance.id = FacilityInstance.round_instance_id
	LEFT JOIN Facility ON FacilityInstance.facility_id = Facility.id
	WHERE Game.id = :game_id
	GROUP BY Station.id, RoundInfo2.id
	ORDER BY RoundInfo2.id";
	$result = $db->query($query, $args);
	if (mysql_num_rows($result) > 0)
	{
		while ($row = mysql_fetch_array($result))
		{
			$station_id = $row['StationId'];
			$round_id = $row['RoundId'];
			$data[$station_id][$round_id] =  array('citizens' => $row['CitizenCount'], 'workers' => $row['WorkerCount'], 'travelers' => $row['TravelerCount'], 'povn' => $row['Povn']);
		}
	}
	
	function DataTable($header, $metric, $ignore2030, $rounds, $stations, $data)
	{
		echo "\t" . '<h2>'. $header . '</h2>' . "\n";
		echo "\t" . '<table border="2">' . "\n";
		echo "\t\t" . '<tr>' . "\n";
		echo "\t\t\t" . '<th class="first-column">Station</th>';
		foreach ($rounds as $key => $value)
		{
			echo "\t\t\t" . '<th>' . $value . '</td>' . "\n";
		}
		echo "\t\t" . '</tr>';
		foreach ($stations as $key => $value)
		{
			$station_data = $data[$key];
			echo "\t\t" . '<tr>' . "\n";
			echo "\t\t\t" . '<td class="first-column">' . $value['name'] . '</th>' . "\n";
			foreach($station_data as $round_id => $station_round)
			{
				if ($ignore2030 && $round_id == 7)
					echo "\t\t\t" . '<td></td>' . "\n";
				else
					echo "\t\t\t" . '<td>' . round($station_round[$metric]) . '</td>' . "\n"; 
			}
			echo "\t\t" . '</tr>' . "\n";
		}
		echo "\t" . '</table>' . "\n";
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
		<link rel="stylesheet" type="text/css" href="style/report.css">
		<script type="text/javascript" src="script/mobility/resize.js"></script>
	</head>
	
	<body>
	
		<div class="area">
			<h2>Teams</h2>

			<table border="2">
				<tr>
					<th class="first-column">TeamID</th>
					<th class="first-column">Teamnaam</th>
					<th>Waarden</th>
					<th>Waarden omschrijving</th>
					<th>Stations</th>
				</tr>
<?php
foreach ($teams as $key => $value)
{
	$teaminstance = TeamInstance::getTeamInstanceIdByGameAndTeam($game_id, $key);
?>
				<tr>
					<td class="first-column"><?php echo $key; ?></td>
					<td class="first-column"><?php echo $value; ?></td>
					<td>
<?php
	$values = ValueInstance::getCheckedValuesByTeam($teaminstance);
	foreach($values as $key => $instance)
	{
		echo Value::getValueDescription($instance->value_id); 
?>
						<br>
<?php
	}
?>
					</td>
					<td>
<?php
	echo TeamInstance::getValueDescription($game_id, $key);
?>
					</td>
					<td>
<?php
	$stations2 = Station::getStationNamesByTeam($teaminstance);
	foreach($stations2 as $key => $instance)
	{
		echo $instance->name;
?>
								<br>
<?php
	}
?>
					</td>
				</tr>
<?php
}
?>
			</table>
			
<?php
			DataTable("Inwoners", "citizens", false, $rounds, $stations, $data);
			DataTable("Werknemers", "workers", false, $rounds, $stations, $data);
			DataTable("Reizigers", "travelers", true, $rounds, $stations, $data);
			DataTable("Netwerkwaarde", "povn", true, $rounds, $stations, $data);
?>					
			
			<?php
			foreach ($stations as $station_id => $station)
			{
			?>
				<h2><?php echo $station['name']; ?></h2>
				
				<?php
				
				$initial = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,$station->transform_area_cultivated_home, $station->transform_area_cultivated_work, $station->transform_area_cultivated_mixed, $station->transform_area_undeveloped_urban, $station->transform_area_undeveloped_rural);
				$masterplan_small = Program::getMasterplan(Station::getStationInstanceId($station_id));
				if(isset($masterplan_small[1]))
					$masterplan = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,$masterplan_small[1]->area_home, $masterplan_small[1]->area_work, $masterplan_small[1]->area_leisure, 0, 0);
				else
					$masterplan = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
				
				$everyround = array(array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
									array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
									array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
									array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0));
				
				$simple_current = $initial;
				$current = $initial;
				$roundId = RoundInfo::getCurrentRoundIdByGameId($game_id);
				$programs = Program::getStationAppliedPrograms(Station::getStationInstanceId($station_id), $roundId);
				$total_special = 0;
				$total_transformed_area = 0;
				$total_home = 0;
				$total_leisure = 0;
				$total_work = 0;
				$index = 0;
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
					
					for($i = 0; $i < sizeof($current); $i++)
					{
						$everyround[$index][$i] = $current[$i];
					}
					$index = $index + 1;
					
				}
				
				$simple_current[14] += $total_home;
				$simple_current[15] += $total_work;
				$simple_current[16] += $total_leisure;
				
				?>
				
				<table border='2'>
				<tr><th class="first-column"></th>
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
				<tr><td class="first-column">Initieel</td>
					<?php
					foreach($initial as $key => $element)
					{
					?>
						<td><?php echo $element;?></td>
					<?php
					}
					?>
				<tr><td class="first-column">Masterplan</td>
					<?php
					foreach($masterplan as $key => $element)
					{
					?>
						<td><?php echo $element;?></td>
					<?php
					}
					?>
					
					
				<tr><td class="first-column">Huidig Simpel</td>
					<?php
					foreach($simple_current as $key => $element)
					{
					?>
						<td><?php echo $element; ?></td>
					<?php
					}
					?>
				<tr><td class="first-column">Huidig Compleet</td>
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
				foreach($everyround as $key => $currentround)
				{
				?>
					<tr><td class="first-column"><?php echo 2014 + 4*$key ?></td>
					<?php
					foreach($currentround as $key => $element)
					{
					?>
						<td><?php echo $element; ?></td>
					<?php
					}
				
				}
				?>
				</table>
				<img src=images/graphs/spacegraph.php?game=<?php echo $game_id; ?>&station=<?php echo $station_id; ?> />
				<img src=images/graphs/mobilitygraph.php?game=<?php echo $game_id; ?>&station=<?php echo $station_id; ?> />
			<?php
			}
			?>
		</div>
	</body>
</html>