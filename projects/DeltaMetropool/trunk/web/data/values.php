<?php
	require_once '../includes/master.inc.php';

	if (ClientSession::hasSession(session_id()))
	{
		printValues();
	}
	
	function printValues()
	{
		$result = getValues();
		
		echo '<values>';
		while ($row = mysql_fetch_array($result))
		{
			echo '<value>';
			echo '<id>' . $row['id'] . '</id>';
			echo '<title>' . $row['title'] . '</title>';
			echo '<description>' . $row['description'] . '</description>';
			echo '</value>';
		}
		echo '</values>';
	}
	
	function getValues()
	{
		$db = Database::getDatabase();
		$query = "SELECT * FROM Value";
		return $db->query($query);
	}
?>