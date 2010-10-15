<?php
	require_once '../includes/master.inc.php';

	if (ClientSession::hasSession(session_id()))
	{
		if (isset($_REQUEST['data']))
			submitValues($_REQUEST['data']);
		else
			printTeams();
	}
	
	function submitValues($xml)
	{
		$db = Database::getDatabase();
		$xml_array = xml2array($xml);
		
		$gameId = Game::getGameIdOfSession(session_id());
		
		foreach ($xml_array['values']['value'] as $value)
		{
			$query = "
				UPDATE ValueInstance
				INNER JOIN TeamInstance ON ValueInstance.team_instance_id = TeamInstance.id
				INNER JOIN ClientSession ON TeamInstance.id = ClientSession.team_instance_id
				SET ValueInstance.checked=:checked 
				WHERE ValueInstance.value_id=:value_id AND ClientSession.id=:session_id";
			$args = array(
				'checked' => $value['checked'] == 'true' ? '1' : '0', 
				'value_id' => $value['id'], 
				'session_id' => session_id());
			$db->query($query, $args);
		}
		
		$query = "
			UPDATE TeamInstance 
			INNER JOIN ClientSession ON TeamInstance.id = ClientSession.team_instance_id
			SET TeamInstance.value_description = :description 
			WHERE ClientSession.id = :id";
		$args = array(
			'description' => $xml_array['values']['description'],
			'id' => session_id());
		$db->query($query, $args);
		
		$session = new ClientSession(session_id());
	}
	
	function printTeams()
	{
		$team_fields = array('id', 'name', 'description', 'color', 'cpu', 'created', 'is_player', 'value_description');
		
		$db = Database::getDatabase();
		
		$team_result = getTeams(session_id());
		
		echo '<teams>';
		while ($team_row = mysql_fetch_array($team_result))
		{
			echo '<team>';
			foreach ($team_fields as $team_field)
			{
				if ($team_field == 'is_player')
					$team_row[$team_field] = $team_row[$team_field] == 1 ? 1 : 0;
				echo '<' . $team_field . '>' . $team_row[$team_field] . '</' . $team_field . '>';
			}
			$value_result = getValuesOfTeamInstance($team_row['team_instance_id']);
			echo '<values>';
			while ($value_row = mysql_fetch_array($value_result))
			{
				echo '<value>';
				echo '<id>' . $value_row['Value'] . '</id>';
				echo '<checked>' . $value_row['Checked'] . '</checked>';
				echo '</value>';
			}
			echo '</values>';
			echo '</team>';
		}
		
		echo '</teams>';
	}
	
	function getTeams($session_id)
	{
		$db = Database::getDatabase();
		$game_id = Game::getGameIdOfSession($session_id);
		$query = "
			SELECT 
				Team.*, 
				TeamInstance.id AS team_instance_id, 
				TeamInstance.value_description AS value_description, 
				TeamInstance.game_id, 
				MAX(ClientSession.id = :id) AS is_player
			FROM Team
			INNER JOIN TeamInstance 
			ON TeamInstance.team_id = Team.id 
			INNER JOIN Game 
			ON Game.id = TeamInstance.game_id 
			LEFT JOIN ClientSession 
			ON ClientSession.team_instance_id = TeamInstance.id 
			GROUP BY TeamInstance.id 
			HAVING TeamInstance.game_id = :game_id";
		$args = array(
			'id' => $session_id,
			'game_id' => $game_id);
		$result = $db->query($query, $args);
		return $result;
	}
	
	function getValuesOfTeamInstance($team_instance_id)
	{
		$db = Database::getDatabase();
		$query = "
			SELECT ValueInstance.value_id AS Value, ValueInstance.checked AS Checked
			FROM ValueInstance
			INNER JOIN TeamInstance
			ON TeamInstance.id = ValueInstance.team_instance_id
			WHERE TeamInstance.id = :team_id";
		$args = array(
			'team_id' => $team_instance_id);
		$results = $db->query($query, $args);
		return $results;
	}
?>