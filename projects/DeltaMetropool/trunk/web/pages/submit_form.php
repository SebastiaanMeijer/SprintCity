<?php
require_once '../includes/master.inc.php';

if(!$Auth->loggedIn()) redirect('../login.php');

define('DEFAULT_HOME_TYPE', 3);
define('DEFAULT_WORK_TYPE', 7);
define('DEFAULT_LEISURE_TYPE', 12);

$action = isset($_REQUEST['Action']) ? $_REQUEST['Action'] : "";
$vars = preg_split('/,/', $action);

switch( $vars[0] )
{
	case "new_game":
		NewGame();
		header("Location: ../admin.php?view=games&page=" . (isset($_REQUEST['page']) ? $_REQUEST['page'] : 1));
		break;
	case "new_team":
		NewTeam();
		header("Location: ../admin.php?view=teams&page=" . (isset($_REQUEST['page']) ? $_REQUEST['page'] : 1));
		break;
	case "game_step_back":
		BackStepGame($vars);
		header("Location: ../admin.php?view=games&page=" . (isset($_REQUEST['page']) ? $_REQUEST['page'] : 1));
		break;
	case "game_step_next":
		NextStepGame($vars);
		header("Location: ../admin.php?view=games&page=" . (isset($_REQUEST['page']) ? $_REQUEST['page'] : 1));
		break;
	case "game_toggle_active":
		ToggleGame($vars);
		header("Location: ../admin.php?view=games&page=" . (isset($_REQUEST['page']) ? $_REQUEST['page'] : 1));
		break;
	case "edit_constants":
		EditConstants();
		header("Location: ../admin.php?view=constants");
		break;
	default:
}

function NewGame()
{
	$db = Database::getDatabase();
	$firstRoundId = 'NULL';
	
	// add new game record to the Game table
	$query = "
		INSERT INTO `Game` 
			(`name`, `notes`, `starttime`, `active`)
		VALUES 
			(:name, :notes, :starttime, :active);";
	$args = array(
		'name' => $_REQUEST['name'], 
		'notes' => $_REQUEST['notes'], 
		'starttime' => date( 'Y-m-d H:i:s'), 
		'active' => 1);
	$db->query($query, $args);
	
	// create a game tree containing the team_instances which in turn contain the station_instances
	// game tree format: $game_tree[team id][index] = station id
	$game_id = mysql_insert_id($db->db);
	$stations = Station::getStations(0, Station::rowCount());
	$game_tree = Array();
	foreach ($stations as $station_key => $station_value) 
	{
		if (isset($_REQUEST['team_' . $station_key]) &&
			$_REQUEST['team_'. $station_key] != "null")
		{
			$game_tree[$_REQUEST['team_'. $station_key]][] = $station_key;
		}
	}
	
	// save gaurd for empty input
	if (sizeof($game_tree) <= 0)
		return;
		
	// create round info instances
	$rounds = RoundInfo::getRounds();
	foreach ($rounds as $round_key => $round_value) 
	{
		$query = "
			INSERT INTO `RoundInfoInstance` 
				(`game_id`, `round_info_id`, `mobility_report`) 
			VALUES 
				(:game_id, :round_info_id, :mobility_report);";
		$args = array(
			'game_id' => $game_id,
			'round_info_id' => $round_key,
			'mobility_report' => "");
		$db->query($query, $args);
	}
	
	// create mobility team
	$query = "
		INSERT INTO `TeamInstance` 
			(`game_id`, `team_id`) 
		VALUES
			(:game_id, :team_id);";
	$args = array(
		'game_id' => $game_id,
		'team_id' => MOBILITY_TEAM_ID);
	$db->query($query, $args);
	$team_instance_id = mysql_insert_id($db->db);
	
	// Add value instances for the mobility team
	$values = Value::getMobilityValues();
	foreach ($values as $value_key => $value_value) 
	{
		$query = "
			INSERT INTO `ValueInstance` 
				(`value_id`, `team_instance_id`, `checked`) 
			VALUES 
				(:value_id, :team_instance_id, :checked);";
		$args = array(
			'value_id' => $value_key,
			'team_instance_id' => $team_instance_id,
			'checked' => 0);
		$db->query($query, $args);
	}
	
	// insert the game tree in the database
	foreach($game_tree as $team_id => $station_collection)
	{
		// create team instances for participating teams
		$query = "
			INSERT INTO `TeamInstance` 
				(`game_id`, `team_id`) 
			VALUES
				(:game_id, :team_id);";
		$args = array(
			'game_id' => $game_id,
			'team_id' => $team_id);
		$db->query($query, $args);
		
		$team_instance_id = mysql_insert_id($db->db);
		
		// create station instances for participating stations
		foreach ($station_collection as $station_id)
		{
			// 'masterplan' program
			$query = "INSERT INTO `Program` () VALUES ();";
			$db->query($query);
			$program_id = mysql_insert_id();
			
			// station instance
			$query = "
				INSERT INTO `StationInstance` 
					(`station_id`, `team_instance_id`, `program_id`) 
				VALUES 
					(:station_id, :team_instance_id, :program_id);";
			$args = array(
				'station_id' => $station_id,
				'team_instance_id' => $team_instance_id,
				'program_id' => $program_id);
			$db->query($query, $args);
			
			// add empty round_instances and programs for every station
			$station_instance_id = mysql_insert_id();
			$rounds = Round::getRoundsByStation($station_id);
			foreach ($rounds as $round_key => $round_value)
			{
				// rounds program
				$query = "
					INSERT INTO `Program` 
						(type_home, type_work, type_leisure) 
					VALUES 
						(" . DEFAULT_HOME_TYPE . ", " . DEFAULT_WORK_TYPE . ", " . DEFAULT_LEISURE_TYPE . ");";
				$db->query($query);
				$program_id = mysql_insert_id();
				
				// round instance
				$query = "
					INSERT INTO `RoundInstance` 
						(`round_id`, `station_instance_id`, `plan_program_id`, `starttime`, `POVN`)
					VALUES 
						(:round_id, :station_instance_id, :plan_program_id, :starttime, :povn);";
				$args = array(
					'round_id' => $round_key, 
					'station_instance_id' => $station_instance_id, 
					'plan_program_id' => $program_id, 
					'starttime' => date( 'Y-m-d H:i:s'),
					'povn' => Station::getInitialPOVNByStationInstanceId($station_instance_id));
				$db->query($query, $args);
			}
		}
		
		// Add value instances for the participating teams
		$values = Value::getAreaValues();		
		foreach ($values as $value_key => $value_value) 
		{
			$query = "
				INSERT INTO `ValueInstance` 
					(`value_id`, `team_instance_id`, `checked`) 
				VALUES 
					(:value_id, :team_instance_id, :checked);";
			$args = array(
				'value_id' => $value_key,
				'team_instance_id' => $team_instance_id,
				'checked' => 0);
			$db->query($query, $args);
		}
	}
}

function BackStepGame($vars)
{
	$db = Database::getDatabase();
	$rounds = RoundInfo::GetRounds();
	$game = new Game($vars[1]);
	$previous_round = 1;
	
	// find id of previous round
	foreach ($rounds as $key => $value)
	{
		if ($game->current_round_id == $key)
			break;
		$previous_round = $key;
	}
	
	// reset exec programs previous round
	ResetExecPrograms($game->id, $previous_round);
	// reset plan programs current round
	ResetPlanPrograms($game->id, $game->current_round_id);
	
	// set previous round as current round
	if ($game->current_round_id != $previous_round)
	{
		$query = "
			UPDATE `game` 
			SET `current_round_id` = :round_id
			WHERE `id` = :game_id;";
		$args = array(
			'round_id' => $previous_round, 
			'game_id' => $game->id);
		$db->query($query, $args);
	}
}

// sets all area fields of exec programs of a certain round to zero
function ResetExecPrograms($game_id, $round_id)
{
	$db = Database::getDatabase();
	$query = "
		UPDATE Program 
		INNER JOIN RoundInstance ON Program.id = RoundInstance.exec_program_id 
		INNER JOIN Round ON RoundInstance.round_id = Round.id 
		INNER JOIN RoundInfo ON Round.round_info_id = RoundInfo.id 
		INNER JOIN StationInstance ON RoundInstance.station_instance_id = StationInstance.id
		INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id 
		SET 
			Program.area_home = :area_home, 
			Program.area_work = :area_work, 
			Program.area_leisure = :area_leisure, 
			Program.type_home = :type_home, 
			Program.type_work = :type_work, 
			Program.type_leisure = :type_leisure
		WHERE TeamInstance.game_id = :game_id AND RoundInfo.id = :round_id;";
	$args = array(
		'game_id' => $game_id, 
		'round_id' => $round_id, 
		'area_home' => 0, 
		'area_work' => 0, 
		'area_leisure' => 0, 
		'type_home' => DEFAULT_HOME_TYPE, 
		'type_work' => DEFAULT_WORK_TYPE, 
		'type_leisure' => DEFAULT_LEISURE_TYPE);
	$db->query($query, $args);
}

// sets all area fields of plan programs of a certain round to zero
function ResetPlanPrograms($game_id, $round_id)
{
	$db = Database::getDatabase();
	$query = "
		UPDATE Program 
		INNER JOIN RoundInstance ON Program.id = RoundInstance.plan_program_id 
		INNER JOIN Round ON RoundInstance.round_id = Round.id 
		INNER JOIN RoundInfo ON Round.round_info_id = RoundInfo.id 
		INNER JOIN StationInstance ON RoundInstance.station_instance_id = StationInstance.id
		INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id 
		SET 
			Program.area_home = :area_home, 
			Program.area_work = :area_work, 
			Program.area_leisure = :area_leisure, 
			Program.type_home = :type_home, 
			Program.type_work = :type_work, 
			Program.type_leisure = :type_leisure
		WHERE TeamInstance.game_id = :game_id AND RoundInfo.id = :round_id;";
	$args = array(
		'game_id' => $game_id, 
		'round_id' => $round_id, 
		'area_home' => 0, 
		'area_work' => 0, 
		'area_leisure' => 0, 
		'type_home' => DEFAULT_HOME_TYPE, 
		'type_work' => DEFAULT_WORK_TYPE, 
		'type_leisure' => DEFAULT_LEISURE_TYPE);
	$db->query($query, $args);
}

function NextStepGame($vars)
{
	CalculateFinalPrograms($vars[1]);
	SetNextRound($vars[1]);
}

function CalculateFinalPrograms($game_id)
{
	/*
	 * Procedure:
	 * 1. Just copy all plan programs to the exec(final) programs
	 * 2. Add all demand area (D) of all types of all rounds until and including the current round
	 * 3. Add all exec (final) program area (P) of all types of all rounds until and including the current round
	 * 4. D - P = Shortage of area
	 * 5. If the shortage is lower than 0 reduce the station exec programs areas according to their area type.
	 */
	
	$db = Database::getDatabase();
	
	// set exec programs of current round based on the plan programs
	$query = "
		SELECT RoundInstance.id AS round_instance_id, exec_program_id, 
			area_home, area_work, area_leisure, type_home, type_work, type_leisure
		FROM Game 
		INNER JOIN RoundInfo ON Game.current_round_id = RoundInfo.id 
		INNER JOIN Round ON RoundInfo.id = Round.round_info_id 
		INNER JOIN RoundInstance ON Round.id = RoundInstance.round_id 
		INNER JOIN Program ON RoundInstance.plan_program_id = Program.id
		WHERE Game.id = :game_id;";
	$args = array('game_id' => $game_id);
	$result = $db->query($query, $args);
	
	while ($row = mysql_fetch_array($result))
	{
		if ($row['exec_program_id'] == "")
		{
			// create new program
			$query = "
				INSERT INTO `Program` 
					(`area_home`, `area_work`, `area_leisure`, `type_home`, `type_work`, `type_leisure`) 
				VALUES 
					(:area_home, :area_work, :area_leisure, :type_home, :type_work, :type_leisure);";
			$args = array(
				'area_home' => $row['area_home'], 
				'area_work' => $row['area_work'], 
				'area_leisure' => $row['area_leisure'], 
				'type_home' => $row['type_home'], 
				'type_work' => $row['type_work'], 
				'type_leisure' => $row['type_leisure']);
			$db->query($query, $args);
			
			// hook the program up to the round instance
			$query = "
				UPDATE `RoundInstance` 
				SET `exec_program_id` = :exec_program_id
				WHERE `id` = :id;";
			$args = array(
				'id' => $row['round_instance_id'], 
				'exec_program_id' => $db->insertId());
			$db->query($query, $args);
		}
		else
		{
			// the program already exists, update it
			$query = "
				UPDATE `Program` 
				SET 
					`area_home` = :area_home, 
					`area_work` = :area_work, 
					`area_leisure` = :area_leisure, 
					`type_home` = :type_home, 
					`type_work` = :type_work, 
					`type_leisure` = :type_leisure 
				WHERE `id` = :id;";
			$args = array(
				'id' => $row['exec_program_id'], 
				'area_home' => $row['area_home'], 
				'area_work' => $row['area_work'], 
				'area_leisure' => $row['area_leisure'], 
				'type_home' => $row['type_home'], 
				'type_work' => $row['type_work'], 
				'type_leisure' => $row['type_leisure']);
			$db->query($query, $args);
		}
	}
	
	// get total demand until current round
	$demand = array();
	$query = "
		SELECT Demand.type_id AS type, SUM(Demand.amount) AS demand
		FROM Demand 
		INNER JOIN RoundInfo ON Demand.round_info_id = RoundInfo.id 
		INNER JOIN Game ON RoundInfo.id <= Game.current_round_id 
		WHERE Game.id = :game_id 
		GROUP BY Demand.type_id;";
	$args = array('game_id' => $game_id);
	$result = $db->query($query, $args);
	while ($row = mysql_fetch_array($result))
		$demand[$row['type']] = $row['demand'];
	
	// get total executed program area per type
	$area_used = array();
	$query = "
		SELECT Types.id, Types.type, 
			IF(Program.type_home = Types.id, SUM(Program.area_home), 0) AS area_home, 
			IF(Program.type_work = Types.id, SUM(Program.area_work), 0) AS area_work, 
			IF(Program.type_leisure = Types.id, SUM(Program.area_leisure), 0) AS area_leisure 
		FROM Program 
		INNER JOIN Types ON Program.type_home = Types.id OR Program.type_work = Types.id OR Program.type_leisure = Types.id
		INNER JOIN RoundInstance ON Program.id = RoundInstance.exec_program_id 
		INNER JOIN Round ON RoundInstance.round_id = Round.id 
		INNER JOIN RoundInfo ON Round.round_info_id = RoundInfo.id 
		INNER JOIN StationInstance ON RoundInstance.station_instance_id = StationInstance.id 
		INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id 
		INNER JOIN Game ON RoundInfo.id < Game.current_round_id AND TeamInstance.game_id = Game.id 
		WHERE Game.id = :game_id
		GROUP BY Types.id;";
	$args = array('game_id' => $game_id);
	$result = $db->query($query, $args);
	while ($row = mysql_fetch_array($result))
	{
		switch($row['type'])
		{
			case 'home':
				$area_used[$row['id']] = $row['area_home'];
				break;
			case 'work':
				$area_used[$row['id']] = $row['area_work'];
				break;
			case 'leisure':
				$area_used[$row['id']] = $row['area_leisure'];
				break;
			default:
		}
	}
			
	// get plans per type for this round
	$area_planned = array();
	$type_id = array();
	$query = "
		SELECT Types.id, Types.type, 
			SUM(Program.area_home) AS area_home, 
			SUM(Program.area_work) AS area_work, 
			SUM(Program.area_leisure) AS area_leisure
		FROM Program
		INNER JOIN Types ON
			Types.id = Program.type_home OR 
			Types.id = Program.type_work OR 
			Types.id = Program.type_leisure
		INNER JOIN RoundInstance ON Program.id = RoundInstance.plan_program_id 
		INNER JOIN Round ON RoundInstance.round_id = Round.id 
		INNER JOIN RoundInfo ON Round.round_info_id = RoundInfo.id 
		INNER JOIN StationInstance ON RoundInstance.station_instance_id = StationInstance.id 
		INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id 
		INNER JOIN Game ON RoundInfo.id = Game.current_round_id AND TeamInstance.game_id = Game.id 
		WHERE Game.id = :game_id
		GROUP BY Types.id;";
	$args = array('game_id' => $game_id);
	$result = $db->query($query, $args);
	while ($row = mysql_fetch_array($result))
	{
		$type_id[$row['id']] = $row['type'];
		switch($row['type'])
		{
			case 'home':
				$area_planned[$row['id']] = $row['area_home'];
				break;
			case 'work':
				$area_planned[$row['id']] = $row['area_work'];
				break;
			case 'leisure':
				$area_planned[$row['id']] = $row['area_leisure'];
				break;
			default:
		}
	}

	// determine for each type if too much area has been allocated
	// redistribute the available area over the stations if necessary
	foreach ($area_planned as $type => $area)
	{
		$area_used_value = isset($area_used[$type]) ? $area_used[$type] : 0;
		if ($area_used_value + $area_planned[$type] > $demand[$type])
		{
			switch($type_id[$type])
			{
				case 'home':
					RedistributeAreaOfHomeType($game_id, $type, $demand[$type] - $area_used_value);
					break;
				case 'work':
					RedistributeAreaOfWorkType($game_id, $type, $demand[$type] - $area_used_value);
					break;
				case 'leisure':
					RedistributeAreaOfLeisureType($game_id, $type, $demand[$type] - $area_used_value);
					break;
				default:
			}
		}
	}
}

// redistribute the available home type area over the programs requiring it
// $game_id: duh. $type: home type id. $distribute_area: the area in acres to distribute over the programs 
function RedistributeAreaOfHomeType($game_id, $type, $distribute_area)
{
	// get information needed for area distribution
	$db = Database::getDatabase();
	$query = "
		SELECT 
			Program.id AS id, 
			Program.area_home AS area, 
			ABS(Station.count_home_total / Station.area_cultivated_home - Types.density) AS density_delta, 
			ABS(RoundInstance.POVN - Types.POVN) AS povn_delta 
		FROM Station 
		INNER JOIN StationInstance ON Station.id = StationInstance.station_id 
		INNER JOIN RoundInstance ON StationInstance.id = RoundInstance.station_instance_id 
		INNER JOIN Program ON RoundInstance.exec_program_id = Program.id 
		INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id 
		INNER JOIN Game ON TeamInstance.game_id = Game.id 
		INNER JOIN Round ON RoundInstance.round_id = Round.id 
		INNER JOIN Types ON Program.type_home = Types.id 
		WHERE 
			Game.id = :game_id AND 
			Program.type_home = :type AND 
			Program.area_home > 0 AND 
			Round.round_info_id = Game.current_round_id;";
	$args = array(
		'game_id' => $game_id, 
		'type' => $type);
	$result = $db->query($query, $args);
	
	// structure the data
	$data = StructureRedistributeData($result);
	
	// distribute the available area based on the structured data
	$data = DistributeArea($data, $distribute_area);
	
	// commit the redistributed data in the programs
	foreach ($data as $key => $value)
	{
		$updateQuery = "
			UPDATE `Program` 
			SET `area_home` = :area 
			WHERE `id` = :id;";
		$updateArgs = array(
			'id' => $value['id'], 
			'area' => $value['def_area']);
		$db->query($updateQuery, $updateArgs);
	}
}

// redistribute the available work type area over the programs requiring it
// $game_id: duh. $type: work type id. $distribute_area: the area in acres to distribute over the programs 
function RedistributeAreaOfWorkType($game_id, $type, $distribute_area)
{
	// get information needed for area distribution
	$db = Database::getDatabase();
	$query = "
		SELECT 
			Program.id AS id, 
			Program.area_work AS area, 
			ABS(Station.count_work_total / Station.area_cultivated_work - Types.density) AS density_delta, 
			ABS(RoundInstance.POVN - Types.POVN) AS povn_delta
		FROM Station 
		INNER JOIN StationInstance ON Station.id = StationInstance.station_id 
		INNER JOIN RoundInstance ON StationInstance.id = RoundInstance.station_instance_id 
		INNER JOIN Program ON RoundInstance.exec_program_id = Program.id 
		INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id 
		INNER JOIN Game ON TeamInstance.game_id = Game.id 
		INNER JOIN Round ON RoundInstance.round_id = Round.id 
		INNER JOIN Types ON Program.type_work = Types.id 
		WHERE 
			Game.id = :game_id AND 
			Program.type_work = :type AND 
			Program.area_work > 0 AND 
			Round.round_info_id = Game.current_round_id;";
	$args = array(
		'game_id' => $game_id, 
		'type' => $type);
	$result = $db->query($query, $args);
	
	// structure the data
	$data = StructureRedistributeData($result);
		
	// distribute the available area based on the structured data
	$data = DistributeArea($data, $distribute_area);
	
	// commit the redistributed data in the programs
	foreach ($data as $key => $value)
	{
		$updateQuery = "
			UPDATE `Program` 
			SET `area_work` = :area 
			WHERE `id` = :id;";
		$updateArgs = array(
			'id' => $value['id'], 
			'area' => $value['def_area']);
		$db->query($updateQuery, $updateArgs);
	}
}


// reduce leisure type in the last rounds's exec programs 
function RedistributeAreaOfLeisureType($game_id, $type, $distribute_area)
{
	$db = Database::getDatabase();
	$query = "
		SELECT 
			Program.id AS id, 
			Program.area_leisure AS area,
			ABS(RoundInstance.POVN - Types.POVN) AS povn_delta 
		FROM Station 
		INNER JOIN StationInstance ON Station.id = StationInstance.station_id 
		INNER JOIN RoundInstance ON StationInstance.id = RoundInstance.station_instance_id 
		INNER JOIN Program ON RoundInstance.exec_program_id = Program.id 
		INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id 
		INNER JOIN Game ON TeamInstance.game_id = Game.id 
		INNER JOIN Round ON RoundInstance.round_id = Round.id 
		INNER JOIN Types ON Program.type_leisure = Types.id 
		WHERE 
			Game.id = :game_id AND 
			Program.type_leisure = :type AND 
			Program.area_leisure > 0 AND 
			Round.round_info_id = Game.current_round_id;";
	$args = array(
		'game_id' => $game_id, 
		'type' => $type);
	$result = $db->query($query, $args);
	
	// structure the data
	$data = StructureRedistributeData($result);
		
	// distribute the available area based on the structured data
	$data = DistributeArea($data, $distribute_area);
	
	// commit the redistributed data in the programs
	foreach ($data as $key => $value)
	{
		$updateQuery = "
			UPDATE `Program` 
			SET `area_leisure` = :area 
			WHERE `id` = :id;";
		$updateArgs = array(
			'id' => $value['id'], 
			'area' => $value['def_area']);
		$db->query($updateQuery, $updateArgs);
	}
}

function StructureRedistributeData($result)
{
	// structure the data
	$data = array();
	$index = 0;	
	while ($row = mysql_fetch_array($result))
	{
		// program id
		$data[$index]['id'] = $row['id'];
		// desired area
		$data[$index]['area'] = $row['area'];
		// definitive area assigned to this station
		$data[$index]['def_area'] = 0;
		// difference between the typical resident density of the type and 
		// the resient density of the station who issued this program
		if (isset($row['density_delta']))
			$data[$index]['density_delta'] = $row['density_delta'];
		else
			$data[$index]['density_delta'] = 0;
		// difference between the typical povn of the type and
		// the povn of the station who issued this program
		$data[$index]['povn_delta'] = $row['povn_delta'];
		// need more index!
		$index++;
	}
	
	// invert deltas
	$count = sizeof($data);
	for ($i = 0; $i < floor($count / 2); $i++)
	{
		$j = $count - $i - 1;
		// swap vars
		list($data[$i]['density_delta'], $data[$j]['density_delta']) = array($data[$j]['density_delta'], $data[$i]['density_delta']);
		list($data[$i]['povn_delta'], $data[$j]['povn_delta']) = array($data[$j]['povn_delta'], $data[$i]['povn_delta']);
	}
	
	return $data;
}

// tries to distribute the $distribute_area as honoust as possible 
// over the programs based on the provided density and povn data
function DistributeArea($data, $distribute_area)
{
	$total_density_delta = 0;
	$total_povn_delta = 0;
	$remainder = 0;
	$start_distribute_area = $distribute_area;
	
	// no area left to distribute, we're done here.
	if ($distribute_area == 0)
		return $data;
	
	// calc the total density delta to properly scale over all stations
	foreach ($data as $index => $row)
	{
		// only do this when the station can actually accept more area
		if ($row['def_area'] < $row['area'])
		{
			$total_density_delta += $row['density_delta'];
			$total_povn_delta += $row['povn_delta'];
		}
	}
	
	// distribute!
	foreach ($data as $index => $row)
	{
		// only distribute if the station can actually accept more area
		if ($row['def_area'] < $row['area'])
		{
			// calc fraction of total based on density/povn
			$density_fraction = $total_density_delta != $row['density_delta'] ? $row['density_delta'] / $total_density_delta : 1;
			$povn_fraction = $total_povn_delta != $row['povn_delta'] ? $row['povn_delta'] / $total_povn_delta : 1;
			// calc what this fraction means in terms of area
			$piece_of_the_pie = round(($density_fraction + $povn_fraction) * 0.5 * $distribute_area);
			// make sure not more area is given than requested in the program
			$final_value = min($piece_of_the_pie, $row['area'] - $row['def_area']);
			$data[$index]['def_area'] += $final_value;
			// if more was given, store how much of area is left
			$remainder += $piece_of_the_pie - $final_value;
			
			// exclude the current program from having a part in further calculations in this loop.
			$distribute_area -= $piece_of_the_pie;
		}
	}
		
	// stop if the area can't be devided honoustly
	if ($start_distribute_area == ($distribute_area + $remainder))
		return $data;
	// keep iterating until no area remains, 
	return DistributeArea($data, $distribute_area + $remainder);
}

function SetNextRound($game_id)
{
	$db = Database::getDatabase();
	$current_round_id = RoundInfo::getCurrentRoundIdBySessionId(session_id());
	
	// find id of next round
	$next_round_id = RoundInfo::getRoundInfoIdAfter($current_round_id);
	
	// set new round
	if ($next_round_id != '')
	{
		// copy RoundInstance POVN from next round to the round after that
		$round_after_next_round_id = RoundInfo::getRoundInfoIdAfter($next_round_id);
		if ($round_after_next_round_id != '')
		{
			$rounds = RoundInstance::getRoundInstances($game_id, $next_round_id);
			foreach ($rounds as $key => $value)
			{
				$query = "
					UPDATE RoundInstance
					INNER JOIN Round ON RoundInstance.round_id = Round.id
					SET RoundInstance.POVN = :povn
					WHERE Round.round_info_id = :round_after_next_round_id AND 
						RoundInstance.station_instance_id = :station_instance_id;";
				$args = array(
					'povn' => $value->POVN,
					'round_after_next_round_id' => $round_after_next_round_id,
					'station_instance_id' => $value->station_instance_id);
				$db->query($query, $args);
			}
		}
		
		// update round
		$query = "
			UPDATE `game` 
			SET `current_round_id` = :round_id
			WHERE `id` = :game_id;";
		$args = array(
			'round_id' => $next_round_id, 
			'game_id' => $game_id);
		$db->query($query, $args);
	}
}

function ToggleGame($vars)
{
	$db = Database::getDatabase();
	$game = new Game($vars[1]);
	
	$query = "
		UPDATE `game` 
		SET `active` = :active
		WHERE `id` = :game_id;";
	$args = array(
		'active' => !$game->active, 
		'game_id' => $game->id);
	$db->query($query, $args);
}

function NewTeam()
{
	$db = Database::getDatabase();
	
	$query = "
		INSERT INTO `team` 
			(`name` , `description` , `color` , `cpu` , `created`)
		VALUES 
			(:name, :description, :color, :cpu, :created);";
	$args = array(
		'name' => $_REQUEST['name'], 
		'description' => $_REQUEST['description'], 
		'color' => $_REQUEST['color'], 
		'cpu' => isset($_REQUEST['cpu']),
		'created' => date('Y-m-d H:i:s'));
	$db->query($query, $args);
}

function EditConstants()
{
	$db = Database::getDatabase();	
	
	$query = "
		UPDATE `constants` 
		SET `average_citizens_per_home` = :average_citizens_per_home, 
			`average_workers_per_bvo` = :average_workers_per_bvo 
		LIMIT 1;";
	$args = array(
		'average_citizens_per_home' => $_REQUEST['average_citizens_per_home'], 
		'average_workers_per_bvo' => $_REQUEST['average_workers_per_bvo']);
	$db->query($query, $args);
}
?>