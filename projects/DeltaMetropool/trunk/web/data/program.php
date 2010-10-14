<?php
	require_once '../includes/master.inc.php';
	
	if (ClientSession::hasSession(session_id()))
	{
		if (isset($_REQUEST['data']))
			submitProgram(session_id(), $_REQUEST['data']);
	}
	
	function submitProgram($session_id, $xml)
	{
		$db = Database::getDatabase();
		$xml_array = xml2array($xml);
		
		// check if this user is allowed to change the program
		if (Program::isOwnedBySession($xml_array['program']['program_id'], $session_id))
		{
			$query = "
				UPDATE `Program` 
				SET `type_home` = :type_home, 
					`type_work` = :type_work, 
					`type_leisure` = :type_leisure, 
					`area_home` = :area_home, 
					`area_work` = :area_work, 
					`area_leisure` = :area_leisure 
				WHERE `id` = :program_id";
			$args = array(
				'type_home' => $xml_array['program']['type_home'], 
				'type_work' => $xml_array['program']['type_work'], 
				'type_leisure' => $xml_array['program']['type_leisure'], 
				'area_home' => $xml_array['program']['area_home'], 
				'area_work' => $xml_array['program']['area_work'], 
				'area_leisure' => $xml_array['program']['area_leisure'], 
				'program_id' => $xml_array['program']['program_id']);
			$db->query($query, $args);
		}
	}
?>
