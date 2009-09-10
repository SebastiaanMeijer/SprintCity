<?php
	require_once '../includes/master.inc.php';

	if (isset($_REQUEST['session']) &&
		$_REQUEST['session'] == session_id() &&
		ClientSession::hasSession($_REQUEST['session']))
	{
		printTypes();
	}
	
	function printTypes()
	{
		$type_fields = array(
			'id', 'name', 'type', 'description', 'image', 'density'
		);
		
		$db = Database::getDatabase();
		
		$types_result = getTypes();
		
		echo '<types>';
		
		while ($type_row = mysql_fetch_array($types_result))
		{
			echo '<type>';
			foreach ($type_fields as $type_field)
			{
				echo '<' . $type_field . '>' . $type_row[$type_field] . '</' . $type_field . '>';
			}
			
			echo '</type>';
		}
		
		echo '</types>';
	}
	
	function getTypes()
	{
		$db = Database::getDatabase();
		$query = "
			SELECT * 
			FROM Types";
		return $db->query($query);
	}
?>
