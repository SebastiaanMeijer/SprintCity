<?php
	require_once '../includes/master.inc.php';

	if (isset($_REQUEST['session']) &&
		$_REQUEST['session'] == session_id() &&
		ClientSession::hasSession($_REQUEST['session']))
	{
		printStations();
	}
	
	function printStations()
	{
		$station_fields = array(
		'id', 'code', 'name', 
		'description_facts', 'description_background', 'description_future', 
		'image', 'town', 'region', 
		'POVN', 'PWN', 'IWD', 'MNG',
		'area_cultivated_home', 'area_cultivated_work', 'area_cultivated_mixed', 
		'area_undeveloped_urban', 'area_undeveloped_rural',
		'transform_area_cultivated_home', 'transform_area_cultivated_work', 'transform_area_cultivated_mixed',
		'transform_area_undeveloped_urban', 'transform_area_undeveloped_mixed',
		'count_home_total', 'count_home_transform',
		'count_work_total', 'count_work_transform',
		'team_id'
		);
		
		$round_fields = array(
			'id', 'number', 'name', 'description', 'new_transform_area', 'POVN', 'PWN'
		);
		
		$program_fields = array(
			'program_id', 'area_home', 'area_work', 'area_leisure', 'type_home', 'type_work', 'type_leisure'
		);
		
		$db = Database::getDatabase();
		
		$station_result = getStations(session_id());
		
		echo '<stations>' . "\n";
		
		while ($station_row = mysql_fetch_array($station_result))
		{
			echo "\t" . '<station>' . "\n";
			foreach ($station_fields as $station_field)
			{
				echo "\t\t" . '<' . $station_field . '>' . $station_row[$station_field] . '</' . $station_field . '>' . "\n";
			}
			
			echo "\t\t" . '<program>' . "\n";
			foreach ($program_fields as $program_field)
			{
				echo "\t\t\t" . '<' . $program_field . '>' . $station_row[$program_field] . '</' . $program_field . '>' . "\n";
			}
			echo "\t\t" . '</program>' . "\n";
			
			echo "\t\t" . '<rounds>' . "\n";
			$round_result = getRoundsOfStation($station_row['station_instance_id']);
			while ($round_row = mysql_fetch_array($round_result))
			{
				echo "\t\t\t" . '<round>' . "\n";
				foreach ($round_fields as $round_field)
				{
					echo "\t\t\t\t" . '<' . $round_field . '>' . $round_row[$round_field] . '</' . $round_field . '>' . "\n";
				}
				echo "\t\t\t\t" . '<program>' . "\n";
				foreach ($program_fields as $program_field)
				{
					echo "\t\t\t\t\t" . '<' . $program_field . '>' . $round_row[$program_field] . '</' . $program_field . '>' . "\n";
				}
				echo "\t\t\t\t" . '</program>' . "\n";
				echo "\t\t\t" . '</round>' . "\n";
			}			
			echo "\t\t" . '</rounds>' . "\n";
			
			echo "\t" . '</station>' . "\n";
		}
		
		echo '</stations>' . "\n";
	}
	
	function getStations($session_id)
	{
		$db = Database::getDatabase();
		$game_id = Game::getGameIdOfSession($session_id);
		$query = "
			SELECT Station.*, 
				StationInstance.id AS station_instance_id, 
				TeamInstance.team_id, 
				Program.id AS program_id, 
				Program.area_home, Program.area_work, Program.area_leisure, 
				Program.type_home, Program.type_work, Program.type_leisure 
			FROM Station 
			INNER JOIN StationInstance 
			ON StationInstance.station_id = Station.id 
			INNER JOIN TeamInstance 
			ON TeamInstance.id = StationInstance.team_instance_id 
			INNER JOIN Program 
			ON StationInstance.program_id = Program.id 
			WHERE TeamInstance.game_id = :game_id";
		$args = array('game_id' => $game_id);
		return $db->query($query, $args);
	}
	
	function getRoundsOfStation($station_instance_id)
	{
		$db = Database::getDatabase();
		$query = "
			SELECT Round.id, Round.new_transform_area, Round.POVN, Round.PWN, 
				RoundInfo.number, RoundInfo.name, RoundInfo.description, 
				Program.id AS program_id, 
				Program.area_home, Program.area_work, Program.area_leisure, 
				Program.type_home, Program.type_work, Program.type_leisure 
			FROM StationInstance 
			INNER JOIN RoundInstance 
			ON StationInstance.id = RoundInstance.station_instance_id 
			INNER JOIN Round 
			ON RoundInstance.round_id = Round.id 
			INNER JOIN RoundInfo 
			ON Round.round_info_id = RoundInfo.id 
			INNER JOIN Program
			ON RoundInstance.program_id = Program.id 
			WHERE RoundInstance.station_instance_id = :station_instance_id
			ORDER BY RoundInfo.number";
		$args = array('station_instance_id' => $station_instance_id);
		return $db->query($query, $args);
	}
?>