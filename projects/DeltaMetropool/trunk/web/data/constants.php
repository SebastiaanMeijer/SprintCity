<?php
	require_once '../includes/master.inc.php';

	if (ClientSession::hasSession(session_id()))
	{
		printConstants();
	}
	
	function printConstants()
	{
		$constants_fields = array(
			'average_citizens_per_home', 'average_workers_per_bvo', 
			'average_travelers_per_ha_leisure', 'average_travelers_per_citizen', 'average_travelers_per_worker'
		);
		
		$db = Database::getDatabase();
		
		$constants_result = getConstants();
		
		echo '<constants>';
		$constants_row = mysql_fetch_array($constants_result);
		foreach ($constants_fields as $constants_field)
		{
			echo '<' . $constants_field . '>' . $constants_row[$constants_field] . '</' . $constants_field . '>';
		}
		echo '</constants>';
	}
	
	function getConstants()
	{
		$db = Database::getDatabase();
		$query = "
			SELECT * 
			FROM Constants 
			LIMIT 0, 1";
		return $db->query($query);
	}
?>