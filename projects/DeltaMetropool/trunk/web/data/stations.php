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
		'id', 'code', 'name', 'description', 'image', 'town', 'region', 
		'POVN', 'PWN', 'IWD', 'MNG',
		'area_cultivated_home', 'area_cultivated_work', 'area_cultivated_mixed', 
		'area_undeveloped_urban', 'area_undeveloped_rural',
		'transform_area_cultivated_home', 'transform_area_cultivated_work', 'transform_area_cultivated_mixed',
		'transform_area_undeveloped_urban', 'transform_area_undeveloped_mixed',
		'count_home_total', 'count_home_transform',
		'count_work_total', 'count_work_transform'
		);
		
		$round_fields = array(
			'id', 'number', 'name', 'description', 'new_transform_area', 'POVN', 'PWN'
		);
		
		$db = Database::getDatabase();
		
		$station_result = getStationsOfClient(session_id());
		
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
	
	function getStationsOfClient($session_id)
	{
		$db = Database::getDatabase();
		$query = "
			SELECT Station.* 
			FROM Station 
			INNER JOIN StationInstance 
			ON StationInstance.station_id = Station.id 
			INNER JOIN TeamInstance 
			ON TeamInstance.id = StationInstance.team_instance_id 
			INNER JOIN ClientSession 
			ON ClientSession.team_instance_id = TeamInstance.id 
			WHERE ClientSession.id = :id";
		$args = array('id' => $session_id);
		return $db->query($query, $args);
	}
	
	function getRoundsOfStation($id)
	{
		$db = Database::getDatabase();
		$query = "
			SELECT Round.*, RoundInfo.number, RoundInfo.name 
			FROM Round 
			INNER JOIN RoundInfo 
			ON RoundInfo.id = Round.round_info_id 
			WHERE Round.station_id = :id
			ORDER BY RoundInfo.number";
		$args = array('id' => $id);
		return $db->query($query, $args);
	}
?>