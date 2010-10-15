<?php
	require_once '../includes/master.inc.php';

	if (ClientSession::hasSession(session_id()))
	{
		printValues();
	}
	
	function printValues()
	{
		$values = Value::getAreaValues();
		
		echo '<values>';
		foreach ($values as $value_key => $value_value) 
	{
			echo '<value>';
			echo '<id>' . $value_key . '</id>';
			echo '<title>' . $value_value->title . '</title>';
			echo '<description>' . $value_value->description . '</description>';
			echo '</value>';
		}
		echo '</values>';
	}
?>