<?php
require_once '../includes/master.inc.php';

if(!$Auth->loggedIn()) redirect('../login.php');


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
		//echo '</br></br><a href="../admin.php?view=games&page=' . (isset($_REQUEST['page']) ? $_REQUEST['page'] : 1) . '">Continue</a>';
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
			$program_id = Program::getMaxId();
			
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
			$station_instance_id = StationInstance::getMaxId();
			$rounds = Round::getRoundsByStation($station_id);
			foreach ($rounds as $round_key => $round_value)
			{
				// rounds program
				$query = "INSERT INTO `Program` (type_home, type_work, type_leisure) VALUES (3, 7, 12);";
				$db->query($query);
				$program_id = Program::getMaxId();
				
				// round instance
				$query = "
					INSERT INTO `RoundInstance` 
						(`round_id`, `station_instance_id`, `plan_program_id`, `starttime`)
					VALUES 
						(:round_id, :station_instance_id, :plan_program_id, :starttime);";
				$args = array(
					'round_id' => $round_key, 
					'station_instance_id' => $station_instance_id, 
					'plan_program_id' => $program_id, 
					'starttime' => date( 'Y-m-d H:i:s'));
				$db->query($query, $args);
			}
		}
		
		// Add value instances for the participating teams
		$values = Value::getValues();		
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
	$temp_key = 1;
	
	// find id of previous round
	foreach ($rounds as $key => $value)
	{
		if ($game->current_round_id == $key)
			break;
		$temp_key = $key;
	}
	
	// set new round
	if ($game->current_round_id != $temp_key)
	{
		$query = "
			UPDATE `game` 
			SET `current_round_id` = :round_id
			WHERE `id` = :game_id;";
		$args = array(
			'round_id' => $temp_key, 
			'game_id' => $game->id);
		$db->query($query, $args);
	}
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
		SELECT *, RoundInstance.id AS round_instance_id 
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
	
	$types = array();
	
	// retrieve a list of types with the total amount of acres demanded for that type 
	// throughout all previous and current rounds. 
	$query = "
		SELECT Types.id AS id, SUM(Demand.amount) AS total_area 
		FROM Types 
		INNER JOIN Demand ON Types.id = Demand.type_id 
		INNER JOIN RoundInfo ON Demand.round_info_id = RoundInfo.id 
		INNER JOIN Game ON RoundInfo.id <= Game.current_round_id 
		WHERE Game.id = :game_id 
		GROUP BY Types.id;";
	$args = array('game_id' => $game_id);
	$result = $db->query($query, $args);
	
	while ($row = mysql_fetch_array($result))
	{
		$types[$row['id']] = $row['total_area'];
	}
	
	// retrieve a list of types with the total amount of acres assigned to that type 
	// in the previous rounds' exec programs with more than zero acres. 
	$query = "
		SELECT 
			Types.id AS id, 
			Types.type AS type, Types.density as density, 
			SUM(Program.area_home) AS area_home, 
			SUM(Program.area_work) AS area_work, 
			SUM(Program.area_leisure) AS area_leisure 
		FROM Types 
		INNER JOIN Program ON 
			Types.id = Program.type_home OR 
			Types.id = Program.type_work OR 
			Types.id = Program.type_leisure 
		INNER JOIN RoundInstance ON Program.id = RoundInstance.exec_program_id 
		INNER JOIN Round ON RoundInstance.round_id = Round.id 
		INNER JOIN RoundInfo ON Round.round_info_id = RoundInfo.id 
		INNER JOIN Game ON RoundInfo.id <= Game.current_round_id 
		WHERE Game.id = :game_id 
		GROUP BY Types.id 
		HAVING 
			Types.type = 'home' AND SUM(Program.area_home) > 0 OR 
			Types.type = 'work' AND SUM(Program.area_work) > 0 OR 
			Types.type = 'leisure' AND SUM(Program.area_leisure) > 0;";
	$args = array('game_id' => $game_id);
	$result = $db->query($query, $args);
	
	while ($row = mysql_fetch_array($result))
	{
		switch($row['type'])
		{
			case 'home':
				$types[$row['id']] -= $row['area_home'];
				if ($types[$row['id']] < 0)
					ReduceHomeTypes($game_id, $row['density'], -$types[$row['id']]);
				break;
			case 'work':
				$types[$row['id']] -= $row['area_work'];
				if ($types[$row['id']] < 0)
					ReduceWorkTypes($game_id, $row['density'], -$types[$row['id']]);
				break;
			case 'leisure':
				$total_area_type = $types[$row['id']];
				$types[$row['id']] -= $row['area_leisure'];
				if ($types[$row['id']] < 0)
					ReduceLeisureTypes($game_id, $row['density'], -$types[$row['id']], $row['area_leisure']);
				break;
			default:
		}
	}
}

// reduce home type in the last rounds's exec programs 
function ReduceHomeTypes($game_id, $type_density, $reduce_area)
{
	// get a list of programs of the current round of all stations, 
	// in the order of stations that fit the least to the most with the given type density
	$db = Database::getDatabase();
	$query = "
		SELECT Program.id AS id, Program.area_home AS area
		FROM Station
		INNER JOIN StationInstance ON Station.id = StationInstance.station_id
		INNER JOIN RoundInstance ON StationInstance.id = RoundInstance.station_instance_id
		INNER JOIN Program ON RoundInstance.exec_program_id = Program.id
		INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id
		INNER JOIN Game ON TeamInstance.game_id = Game.id
		INNER JOIN Round ON RoundInstance.round_id = Round.id
		WHERE Game.id = :game_id AND Round.round_info_id = Game.current_round_id
		ORDER BY ABS(Station.count_home_total / Station.area_cultivated_home - :density) DESC;";
	$args = array(
		'game_id' => $game_id, 
		'density' => $type_density);
	$result = $db->query($query, $args);
	while ($row = mysql_fetch_array($result))
	{
		$reduce_with = min($reduce_area, $row['area']);
		$updateQuery = "
			UPDATE `Program` 
			SET `area_home` = :area 
			WHERE `id` = :id;";
		$updateArgs = array(
			'id' => $row['id'], 
			'area' => $row['area'] - $reduce_with);
		$db->query($updateQuery, $updateArgs);
		$reduce_area -= $reduce_with;
		if ($reduce_area <= 0)
			break;
	}
}

// reduce work type in the last rounds's exec programs 
function ReduceWorkTypes($game_id, $type_density, $reduce_area)
{
	// get a list of programs of the current round of all stations, 
	// in the order of stations that fit the least to the most with the given type density
	$db = Database::getDatabase();
	$query = "
		SELECT Program.id AS id, Program.area_work AS area
		FROM Station
		INNER JOIN StationInstance ON Station.id = StationInstance.station_id
		INNER JOIN RoundInstance ON StationInstance.id = RoundInstance.station_instance_id
		INNER JOIN Program ON RoundInstance.exec_program_id = Program.id
		INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id
		INNER JOIN Game ON TeamInstance.game_id = Game.id
		INNER JOIN Round ON RoundInstance.round_id = Round.id
		WHERE Game.id = :game_id AND Round.round_info_id = Game.current_round_id
		ORDER BY ABS(Station.count_work_total / Station.area_cultivated_work - :density) DESC;";
	$args = array(
		'game_id' => $game_id, 
		'density' => $type_density);
	$result = $db->query($query, $args);
	while ($row = mysql_fetch_array($result))
	{
		$reduce_with = min($reduce_area, $row['area']);
		$updateQuery = "
			UPDATE `Program` 
			SET `area_work` = :area 
			WHERE `id` = :id;";
		$updateArgs = array(
			'id' => $row['id'], 
			'area' => $row['area'] - $reduce_with);
		$db->query($updateQuery, $updateArgs);
		$reduce_area -= $reduce_with;
		if ($reduce_area <= 0)
			break;
	}
}

// reduce leisure type in the last rounds's exec programs 
function ReduceLeisureTypes($game_id, $type_density, $reduce_area, $total_area)
{
	// get a list of programs of the current round of all stations, 
	// in the order of stations that have the most part in the excess area use
	$db = Database::getDatabase();
	$query = "
		SELECT Program.id AS id, Program.area_leisure AS area, 
			ROUND((Program.area_leisure / :total_area) * :reduce_area) AS reduce_area
		FROM Station
		INNER JOIN StationInstance ON Station.id = StationInstance.station_id
		INNER JOIN RoundInstance ON StationInstance.id = RoundInstance.station_instance_id
		INNER JOIN Program ON RoundInstance.exec_program_id = Program.id
		INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id
		INNER JOIN Game ON TeamInstance.game_id = Game.id
		INNER JOIN Round ON RoundInstance.round_id = Round.id
		WHERE Game.id = :game_id AND Round.round_info_id = Game.current_round_id
		ORDER BY (Program.area_leisure / :total_area) DESC;";
	$args = array(
		'game_id' => $game_id, 
		'total_area' => $total_area,
		'reduce_area' => $reduce_area);
	$result = $db->query($query, $args);
	
	// gather info in an array
	$programs = array();
	$index = 0;
	while ($row = mysql_fetch_array($result))
	{
		$programs[$index]['id'] = $row['id'];
		$programs[$index]['area'] = $row['area'];
		$programs[$index]['reduce_area'] = $row['reduce_area'];
		$index++;
	}

	// commit new areas
	foreach ($programs as $key => $value)
	{
		$query = "
			UPDATE `Program` 
			SET `area_leisure` = :area 
			WHERE `id` = :id;";
		$args = array(
			'id' => $value['id'], 
			'area' => $value['area'] - min($value['area'], $value['reduce_area']));
		$db->query($query, $args);
	}
}

function SetNextRound($game_id)
{
	$db = Database::getDatabase();
	$rounds = RoundInfo::GetRounds();
	$game = new Game($game_id);
	$temp_key = -1;
	
	// find id of next round
	foreach ($rounds as $key => $value)
	{
		if ($temp_key > -1)
		{
			$temp_key = $key;
			break;
		}
		if ($game->current_round_id == $key)
			$temp_key = $key;
	}
	
	// set new round
	if ($game->current_round_id != $temp_key)
	{
		$query = "
			UPDATE `game` 
			SET `current_round_id` = :round_id
			WHERE `id` = :game_id;";
		$args = array(
			'round_id' => $temp_key, 
			'game_id' => $game->id);
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