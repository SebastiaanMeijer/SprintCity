<?php
	require_once '../includes/master.inc.php';

	if (ClientSession::hasSession(session_id()))
	{
		printFacilities();
	}
	
	function printFacilities()
	{
		$facility_fields = array(
			'id', 'name', 'description', 'image', 'citizens', 'workers', 'travelers'
		);
		
		$facilities_result = getFacilities();
		
		echo '<facilities>' . "\n";
		
		while ($facility_row = mysql_fetch_array($facilities_result))
		{
			echo "\t" . '<facility>' . "\n";
			foreach ($facility_fields as $facility_field)
			{
				echo "\t\t" . '<' . $facility_field . '>' . $facility_row[$facility_field] . '</' . $facility_field . '>' . "\n";
			}
			echo "\t" . '</facility>' . "\n";
		}
		
		echo '</facilities>';
	}
	
	function getFacilities()
	{
		$db = Database::getDatabase();
		$query = "
			SELECT * 
			FROM Facility";
		return $db->query($query);
	}
?>
