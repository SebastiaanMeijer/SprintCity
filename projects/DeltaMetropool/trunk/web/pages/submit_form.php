<?php
require_once '../includes/master.inc.php';

// TODO: Add admin check

switch( $_REQUEST['Action'] )
{
	case "new_game":
		NewGame();
		break;
	case "new_team":
		NewTeam();
		break;
	case "edit_constants":
		EditConstants();
		break;
	default:
}

function NewGame()
{
	$db = Database::getDatabase();
	$firstRoundId = RoundInfo::GetRoundId(0);
	
	$query = "
		INSERT INTO `Game` 
			(`name` , `notes` , `starttime`, `current_round_id`)
		VALUES 
			(:name, :notes, :starttime, :firstround);";
	$args = array(
		'name' => $_REQUEST['name'], 
		'notes' => $_REQUEST['notes'], 
		'starttime' => date( 'Y-m-d H:i:s'), 
		'firstround' => $firstRoundId);
	$db->query($query, $args);
	
	$game_id = mysql_insert_id($db->db);
	$stations = Station::getStations(0, Station::rowCount());
	foreach ($stations as $station_key => $station_value) 
	{
		if (isset($_REQUEST['team_' . $station_key]) &&
			$_REQUEST['team_'. $station_key] != "null")
		{
			$query = "
				INSERT INTO `StationInstance` 
					(`station_id` , `team_id` , `game_id`)
				VALUES 
					(:station_id, :team_id, :game_id);";
			$args = array(
				'station_id' => $station_key, 
				'team_id' => $_REQUEST['team_' . $station_key], 
				'game_id' => $game_id);
			$db->query($query, $args);
			
			$station_instance_id = mysql_insert_id($db->db);
			
			$query = "
				INSERT INTO `RoundInstance` 
					(`round_id` , `starttime`)
				VALUES 
					(:round_id, :starttime);";
			$args = array(
				'round_id' => $firstRoundId, 
				'starttime' =>date( 'Y-m-d H:i:s'));
			$db->query($query, $args);
		}
	}
	header("Location: ../admin.php?view=games");
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
		'created' => date( 'Y-m-d H:i:s'));
	$db->query($query, $args);
	
	header("Location: ../admin.php?view=teams&page=" . (isset($_REQUEST['page']) ? $_REQUEST['page'] : 1));
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
	
	header("Location: ../admin.php?view=constants");
}
?>