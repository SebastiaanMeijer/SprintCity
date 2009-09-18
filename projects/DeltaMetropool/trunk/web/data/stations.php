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
		
		$db = Database::getDatabase();
		
		$station_result = getStations(session_id());
		
		echo '<stations>';
		
		while ($station_row = mysql_fetch_array($station_result))
		{
			echo '<station>';
			foreach ($station_fields as $station_field)
			{
				echo '<' . $station_field . '>' . $station_row[$station_field] . '</' . $station_field . '>';
			}
			
			echo '<rounds>';
			$round_result = getRoundsOfStation($station_row['id']);
			while ($round_row = mysql_fetch_array($round_result))
			{
				echo '<round>';
				foreach ($round_fields as $round_field)
				{
					echo '<' . $round_field . '>' . $round_row[$round_field] . '</' . $round_field . '>';
				}
				echo '</round>';
			}			
			echo '</rounds>';
			
			echo '</station>';
		}
		
		echo '</stations>';
	}
	
	function getStations($session_id)
	{
		$db = Database::getDatabase();
		$game_id = Game::getGameIdOfSession($session_id);
		$query = "
			SELECT Station.*, TeamInstance.team_id AS team_id
			FROM Station 
			INNER JOIN StationInstance 
			ON StationInstance.station_id = Station.id 
			INNER JOIN TeamInstance 
			ON TeamInstance.id = StationInstance.team_instance_id 
			WHERE TeamInstance.game_id = :game_id";
		$args = array('game_id' => $game_id);
		return $db->query($query, $args);
	}
	
	function getRoundsOfStation($id)
	{
		$db = Database::getDatabase();
		$query = "
			SELECT Round.id, Round.new_transform_area, Round.POVN, Round.PWN, RoundInfo.number, RoundInfo.name, RoundInfo.description
			FROM Round 
			INNER JOIN RoundInfo 
			ON RoundInfo.id = Round.round_info_id 
			WHERE Round.station_id = :id
			ORDER BY RoundInfo.number";
		$args = array('id' => $id);
		return $db->query($query, $args);
	}
?>