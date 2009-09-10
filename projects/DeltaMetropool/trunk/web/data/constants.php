<?php
	require_once '../includes/master.inc.php';

	if (isset($_REQUEST['session']) &&
		$_REQUEST['session'] == session_id() &&
		ClientSession::hasSession($_REQUEST['session']))
	{
		printConstants();
	}
	
	function printConstants()
	{
		$constants_fields = array(
			'average_citizens_per_home', 'average_workers_per_bvo'
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