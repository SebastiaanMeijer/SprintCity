<?php
require_once '../includes/master.inc.php';

	if (ClientSession::hasSession(session_id()))
	{
		printStationTypes();
	}
	
	function printStationTypes()
	{
		$station_type_fields = array(
			'id', 'name', 'description', 'image',
			'POVN', 'PWN', 'IWD', 'MNG'
		);
		
		$db = Database::getDatabase();
		
		$station_types_result = getStationTypes();
		
		echo '<station_types>';
		
		while ($station_type_row = mysql_fetch_array($station_types_result))
		{
			echo '<station_type>';
			foreach ($station_type_fields as $station_type_field)
			{
				echo '<' . $station_type_field . '>' . $station_type_row[$station_type_field] . '</' . $station_type_field . '>';
			}
			
			echo '</station_type>';
		}
		
		echo '</station_types>';
	}
	
	function getStationTypes()
	{
		$db = Database::getDatabase();
		$query = "
			SELECT * 
			FROM StationTypes";
		return $db->query($query);
	}