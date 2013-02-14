<?php
	require_once '../includes/master.inc.php';
	require_once '../pages/mobility/mobility_service.php';

	if (ClientSession::hasSession(session_id()))
	{	
		printStations();
	}
	
	function printStations()
	{
		$station_fields = array(
			'id', 'code', 'name', 
			'description_facts', 'description_background', 'description_future', 
			'town', 'region', 
			'POVN', 'PWN', 'IWD', 'MNG',
			'area_cultivated_home', 'area_cultivated_work', 'area_cultivated_mixed', 
			'area_undeveloped_urban', 'area_undeveloped_rural',
			'transform_area_cultivated_home', 'transform_area_cultivated_work', 'transform_area_cultivated_mixed',
			'transform_area_undeveloped_urban', 'transform_area_undeveloped_rural',
			'count_home_total', 'count_home_transform',
			'count_work_total', 'count_work_transform',
			'count_worker_total', 'count_worker_transform', 
			'count_travelers', 'team_id'
		);
		
		$mobility_fields = array(
			'currentIU', 'capUnder', 'capOver'
		);
		
		$round_fields = array(
			'id', 'round_info_id', 'number', 'name', 'description', 'new_transform_area', 'POVN', 'PWN', 
			'citizen_bonus', 'worker_bonus', 'traveler_bonus', 'bonuses'
		);
		
		$program_fields = array(
			'program_id', 'area_home', 'area_work', 'area_leisure', 'type_home', 'type_work', 'type_leisure'
		);
		
		$db = Database::getDatabase();
		
		$game_id = Game::getGameIdOfSession(session_id());
		$station_result = getStations(session_id());
		$scenario = Scenario::getCurrentScenario();
		$train_data = getMobilityDataStations();
		
		echo '<stations>' . "\n";
		echo "\t" . '<mapx>' . $scenario[key($scenario)]->init_map_position_x . '</mapx>' . "\n";
		echo "\t" . '<mapy>' . $scenario[key($scenario)]->init_map_position_y . '</mapy>' . "\n";
		echo "\t" . '<mapscale>' . $scenario[key($scenario)]->init_map_scale . '</mapscale>' . "\n";
		while ($station_row = mysql_fetch_array($station_result))
		{
			echo "\t" . '<station>' . "\n";
			foreach ($station_fields as $station_field)
			{
				switch($station_field)
				{
					case 'POVN':
						echo "\t\t" . '<POVN>' . $station_row['initial_POVN'] . '</POVN>' . "\n";
						break;
					case 'name':
						echo "\t\t" . '<name>' . str_replace("&shy;", "", $station_row['name']) . '</name>' . "\n";
						break;
					case 'count_travelers':
						echo "\t\t" . '<count_travelers>' . getMobilityDataForStation($train_data, $station_row['code'])['totalTravelers'] . '</count_travelers>' . "\n";
						break;
					default:
						echo "\t\t" . '<' . $station_field . '>' . $station_row[$station_field] . '</' . $station_field . '>' . "\n";
						break;
				}
			}
			
			$station_mobility_data = getMobilityDataForStation($train_data, $station_row['code']);
			foreach ($mobility_fields as $mobility_field)
			{
				echo "\t\t" . '<' . $mobility_field . '>' . $station_mobility_data[$mobility_field] . '</' . $mobility_field . '>' . "\n";
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
				if ($round_row['plan_program_id'] != "")
				{
					$program_result = getProgram($round_row['plan_program_id']);
					$program_row = mysql_fetch_array($program_result);
					echo "\t\t\t\t" . '<plan_program>' . "\n";
					foreach ($program_fields as $program_field)
					{
						echo "\t\t\t\t\t" . '<' . $program_field . '>' . $program_row[$program_field] . '</' . $program_field . '>' . "\n";
					}
					echo "\t\t\t\t" . '</plan_program>' . "\n";
				}
				if ($round_row['exec_program_id'] != "")
				{
					$program_result = getProgram($round_row['exec_program_id']);
					$program_row = mysql_fetch_array($program_result);
					echo "\t\t\t\t" . '<exec_program>' . "\n";
					foreach ($program_fields as $program_field)
					{
						echo "\t\t\t\t\t" . '<' . $program_field . '>' . $program_row[$program_field] . '</' . $program_field . '>' . "\n";
					}
					echo "\t\t\t\t" . '</exec_program>' . "\n";
				}
				echo "\t\t\t" . '</round>' . "\n";
			}			
			echo "\t\t" . '</rounds>' . "\n";
			echo "\t\t" . '<restrictions>' . getRestrictions($game_id, $station_row['id']) . '</restrictions>' . "\n";
			echo "\t" . '</station>' . "\n";
		}
		
		echo '</stations>' . "\n";
	}

	function getMobilityDataForStation($train_data, $station_code)
	{
		foreach ($train_data as $station)
		{
			if ($station['code'] == $station_code)
				return $station;
		}
		return null;
	}
	
	function getStations($session_id)
	{
		$db = Database::getDatabase();
		$game_id = Game::getGameIdOfSession($session_id);
		$query = "
			SELECT Station.*,
				StationInstance.id AS station_instance_id,
				StationInstance.initial_POVN,
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
			SELECT Round.id, Round.new_transform_area, RoundInstance.POVN, Round.PWN, 
				RoundInfo.number, RoundInfo.name, RoundInfo.description, 
				RoundInfo.id AS round_info_id, 
				RoundInstance.plan_program_id, RoundInstance.exec_program_id, RoundInstance.exec_program_id,
				SUM(Facility.citizens) AS citizen_bonus, SUM(Facility.workers) AS worker_bonus, SUM(Facility.travelers) AS traveler_bonus, 
				GROUP_CONCAT(Facility.id SEPARATOR ',') AS bonuses
			FROM StationInstance 
			INNER JOIN RoundInstance ON StationInstance.id = RoundInstance.station_instance_id 
			INNER JOIN Round ON RoundInstance.round_id = Round.id 
			INNER JOIN RoundInfo ON Round.round_info_id = RoundInfo.id 
			LEFT JOIN FacilityInstance ON RoundInstance.id = FacilityInstance.round_instance_id
			LEFT JOIN Facility ON FacilityInstance.facility_id = Facility.id
			WHERE RoundInstance.station_instance_id = :station_instance_id
			GROUP BY Round.id
			ORDER BY RoundInfo.number";
		$args = array('station_instance_id' => $station_instance_id);
		return $db->query($query, $args);
	}
	
	function getProgram($program_id)
	{
		$db = Database::getDatabase();
		$query = "
			SELECT *, id AS program_id
			FROM Program 
			WHERE id = :program_id";
		$args = array('program_id' => $program_id);
		return $db->query($query, $args);
	}
	
	function getRestrictions($game_id, $station_id)
	{
		$restrictions = TypeRestriction::getActiveRestrictionsForStation($game_id, $station_id);
		$result = '';
		while ($restriction = mysql_fetch_array($restrictions))
		{
			$result .= $restriction['TypeId'] . ',';
		}
		return $result;
	}
?>