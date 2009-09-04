<?php
	require_once '../includes/master.inc.php';
	
	$fh = fopen("test.log", 'w') or die("can't open file");
	//fwrite($fh, "|1:test");
	//if (isset($_GET['session']) &&
	//	$_GET['session'] == session_id() &&
	//	ClientSession::hasSession($_GET['session']))
	//{
		if (isset($HTTP_RAW_POST_DATA))
		{
			submitValues($HTTP_RAW_POST_DATA);
		}
		else
		{
			printValues();
		}
	//}
	
	fclose($fh);
	
	function submitValues($xml)
	{
		$db = Database::getDatabase();
		$xml_array = xml2array($xml);
		
		$query = "
			SELECT team_instance_id
			FROM ClientSession 
			WHERE ClientSession.id = :id";
		$args = array('id' => session_id());
		$result = $db->query($query, $args);
		
		$team_instance_id = $db->getValue($result);
		
		foreach ($xml_array as $key => $value)
		{
			if ($key == 'value')
			{
				$query = "
					UPDATE `ValueInstance` 
					SET `checked` = :checked 
					WHERE value_id = :value_id 
					AND team_instance_id = :team_instance_id";
				$args = array(
					'checked' => $value['checked'], 
					'value_id' => $value['id'], 
					'team_instance_id' => $team_instance_id);
				$db->query($query, $args);
			}
			else if ($key == 'description')
			{
				$query = "
					UPDATE `TeamInstance` 
					SET `value_description` = :description 
					WHERE id = :id";
				$args = array(
					'description' => $value,
					'id' => $team_instance_id);
				$db->query($query, $args);
			}
		}
		
		$session = new ClientSession($_GET['session']);
		print_r($session);
	}
	
	function printValues()
	{
		$db = Database::getDatabase();
		
		echo '<values>';
		
		$query = "
			SELECT Value.id, Value.title, Value.description, ValueInstance.checked
			FROM Value 
			INNER JOIN ValueInstance 
			ON ValueInstance.value_id = Value.id 
			INNER JOIN TeamInstance 
			ON TeamInstance.id = ValueInstance.team_instance_id 
			INNER JOIN ClientSession 
			ON ClientSession.team_instance_id = TeamInstance.id 
			WHERE ClientSession.id = :id";
		$args = array('id' => session_id());
		$result = $db->query($query, $args);
		
		while ($row = mysql_fetch_array($result))
		{
			echo '<value>';
			echo '<id>' . $row['id'] . '</id>';
			echo '<title>' . $row['title'] . '</title>';
			echo '<description>' . $row['description'] . '</description>';
			echo '<checked>' . $row['checked'] . '</checked>';
			echo '</value>';
		}
		
		$query = "
			SELECT TeamInstance.value_description
			FROM TeamInstance 
			INNER JOIN ClientSession 
			ON ClientSession.team_instance_id = TeamInstance.id 
			WHERE ClientSession.id = :id";
		$args = array('id' => session_id());
		$result = $db->query($query, $args);
		
		//echo '<description>' . $db->getValue($result) . '</description>';
		echo '<description>' . session_id() . '</description>';
		
		echo '</values>';
	}
?>