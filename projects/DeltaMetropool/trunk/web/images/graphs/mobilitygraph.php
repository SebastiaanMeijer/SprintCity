<?php
require_once('../../includes/master.inc.php');
require_once('linegraph.php');

// Data
if (ClientSession::hasSession(session_id()))
{
	$povnData = array(0);
	$travelerData = array(0);

	$width = isset($_REQUEST['width']) ? $_REQUEST['width'] : 480;
	$height = isset($_REQUEST['height']) ? $_REQUEST['height'] : 220;
	
	$povnData = LoadPOVNData($_REQUEST['session'], $_REQUEST['station']);
	$travelerData = LoadTravelerData($_REQUEST['session'], $_REQUEST['station']);
	
	if(isset($povnData))
		array_unshift($povnData, $initPovnCount);
	else
		$povnData = array($initPovnCount);
	
	if(isset($travelerData))
		array_unshift($travelerData, $initTravelerCount);
	else
		$travelerData = array($initTravelerCount);
	
	
	$gameId = Game::getGameIdOfSession(session_id());
	
	// Construct
	//$graph = new LineGraph(720,330);
	$graph = new LineGraph($width, $height);
	
	// Set input
	$graph->SetInputArray($povnData);
	$graph->SetInputArray($travelerData);
	$graph->SetToMobilityColors();
	
	// Get image
	$image = $graph->GetImage(); //must fail if there is no width, height or inputArray
	
	// Make .PHP -> .PNG-image
	header('Content-type:image/png');
	
	// Display on screen
	imagepng($image);
	
	// Destroy garbage
	imagedestroy($image);
}

function LoadPOVNData($session_id, $station_id)
{
	if (isset($game_id) && isset($station_id))
	{
		$db = Database::getDatabase();
		$query = ""
		$args = array('game_id' => $game_id, 'station_id' => $station_id);
		$result = $db->query($query, $args);
		if (mysql_num_rows($result) > 0)
		{
			$data = array();
			while ($row = mysql_fetch_array($result))
				$data[] = round($row['CitizenCount']);
			return $data;
		}
		else
			return NULL;return array(0);
}

function LoadTravelerData($session_id, $station_id)
{
	return array(0);
}
?>