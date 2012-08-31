<?php

require_once '../../includes/master.inc.php';


$db = Database::getDatabase();
$game_id = Game::getGameIdOfSession(session_id());
// uncheck all ambitions for the mobility player
$query = "
			UPDATE ValueInstance
			INNER JOIN TeamInstance ON ValueInstance.team_instance_id = TeamInstance.id
			INNER JOIN Team ON TeamInstance.team_id = Team.id
			SET ValueInstance.checked = false
			WHERE TeamInstance.game_id = :game_id
				AND Team.id = :team_id";
$args = array(
    'game_id' => $game_id,
    'team_id' => MOBILITY_TEAM_ID);
$db->query($query, $args);

// check selected ambitions
$valueInstanceId = $_REQUEST['valueInstanceId'];
$query = "
				UPDATE ValueInstance
				SET ValueInstance.checked = true
				WHERE ValueInstance.id = :value_instance_id";
$args = array('value_instance_id' => $valueInstanceId);
$db->query($query, $args);

$motivation = $_REQUEST['motivation'];
    // fill ambition motivation
    $query = "
			UPDATE TeamInstance
			SET TeamInstance.value_description = :motivation
			WHERE TeamInstance.game_id = :game_id
				AND TeamInstance.team_id = :team_id";
    $args = array(
        'motivation' => $motivation,
        'game_id' => $game_id,
        'team_id' => MOBILITY_TEAM_ID);
    $db->query($query, $args);
?>
