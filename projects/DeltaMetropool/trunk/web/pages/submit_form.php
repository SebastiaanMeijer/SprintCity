<?php
require_once '../includes/master.inc.php';

if(!$Auth->loggedIn()) redirect('login.php');


$action = isset($_REQUEST['Action']) ? $_REQUEST['Action'] : "";
$vars = split(",", $action);

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
	$firstRoundId = RoundInfo::GetRoundId(0);
	
	// add new game record to the Game table
	$query = "
		INSERT INTO `Game` 
			(`name`, `notes`, `starttime`, `current_round_id`, `active`)
		VALUES 
			(:name, :notes, :starttime, :firstround, :active);";
	$args = array(
		'name' => $_REQUEST['name'], 
		'notes' => $_REQUEST['notes'], 
		'starttime' => date( 'Y-m-d H:i:s'), 
		'firstround' => $firstRoundId,
		'active' => 1);
	$db->query($query, $args);
	
	// create a game tree containing the team_instances which in turn contain the staion_instances
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
		
		foreach ($station_collection as $station_id)
		{
			$query = "
				INSERT INTO `StationInstance` 
					(`station_id`, `team_instance_id`) 
				VALUES 
					(:station_id, :team_instance_id);";
			$args = array(
				'station_id' => $station_id,
				'team_instance_id' => $team_instance_id);
			$db->query($query, $args);
		}
		
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
	foreach ($rounds as $key => $value)
	{
		if ($game->current_round_id == $key)
			break;
		$temp_key = $key;
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

function NextStepGame($vars)
{
	$db = Database::getDatabase();
	$rounds = RoundInfo::GetRounds();
	$game = new Game($vars[1]);

	$temp_key = -1;
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
			(`name` , `description` , `cpu`, `created`)
		VALUES 
			(:name, :description, :cpu, :created);";
	$args = array(
		'name' => $_REQUEST['name'], 
		'description' => $_REQUEST['description'], 
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