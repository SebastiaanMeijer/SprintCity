<?php
	require_once '../includes/master.inc.php';

	if (isset($_REQUEST['session']) &&
		$_REQUEST['session'] == session_id() &&
		ClientSession::hasSession($_REQUEST['session']))
	{
		printTeams();
	}
	
	function printTeams()
	{
		$team_fields = array('id', 'name', 'description', 'color', 'cpu', 'created', 'is_player');
		
		$db = Database::getDatabase();
		
		$team_result = getTeams(session_id());
		
		echo '<teams>';
		while ($team_row = mysql_fetch_array($team_result))
		{
			echo '<team>';
			foreach ($team_fields as $team_field)
			{
				echo '<' . $team_field . '>' . $team_row[$team_field] . '</' . $team_field . '>';
			}			
			echo '</team>';
		}
		
		echo '</teams>';
	}
	
	function getTeams($session_id)
	{
		$db = Database::getDatabase();
		$query = "
			SELECT Team.*, ClientSession.id = :id AS is_player
			FROM Team
			INNER JOIN TeamInstance 
			ON TeamInstance.team_id = Team.id 
			INNER JOIN Game 
			ON Game.id = TeamInstance.game_id 
			LEFT JOIN ClientSession 
			ON ClientSession.team_instance_id = TeamInstance.id";
		$args = array('id' => $session_id);
		return $db->query($query, $args);
	}
?>