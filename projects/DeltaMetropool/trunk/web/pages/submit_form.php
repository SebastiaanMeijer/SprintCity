<?php
require_once '../includes/master.inc.php';

if(!$Auth->loggedIn()) redirect('login.php');


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
				$query = "INSERT INTO `Program` () VALUES ();";
				$db->query($query);
				$program_id = Program::getMaxId();
				
				// round instance
				$query = "
					INSERT INTO `RoundInstance` 
						(`round_id`, `station_instance_id`, `program_id`, `starttime`)
					VALUES 
						(:round_id, :station_instance_id, :program_id, :starttime);";
				$args = array(
					'round_id' => $round_key, 
					'station_instance_id' => $station_instance_id, 
					'program_id' => $program_id, 
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
	
	$temp_key = -1;
	if ($game->current_round_id == 1)
		$temp_key = "NULL";
	else if ($game->current_round_id != "")
	{
		foreach ($rounds as $key => $value)
		{
			if ($game->current_round_id == $key)
				break;
			$temp_key = $key;
		}
	}
	
	if ($temp_key == "NULL")
	{
		$query = "
			UPDATE `game` 
			SET `current_round_id` = NULL
			WHERE `id` = :game_id;";
		$args = array('game_id' => $game->id);
		$db->query($query, $args);
	}
	else if ($temp_key > -1)
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
	$db = Database::getDatabase();
	$rounds = RoundInfo::GetRounds();
	$game = new Game($vars[1]);

	$temp_key = -1;
	if ($game->current_round_id == "")
		$temp_key = 1;
	else
	{
		foreach ($rounds as $key => $value)
		{
			// dirty code starts here
			if ($temp_key > -1)
			{
				$temp_key = $key;
				break;
			}
			if ($game->current_round_id == $key)
				$temp_key = $key;
		}
	}
	
	if ($temp_key > -1)
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