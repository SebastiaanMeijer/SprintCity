<?php
require_once('../../includes/master.inc.php');
require_once('linegraph.php');

// Data
$povnData = array(0);
$travelerData = array(0);

if (isset($_REQUEST['session']) &&
	$_REQUEST['session'] == session_id() &&
	ClientSession::hasSession($_REQUEST['session']))
{
	$citizenData = LoadPOVNData($_REQUEST['session'], $_REQUEST['station']);
	$workerData = LoadTravelerData($_REQUEST['session'], $_REQUEST['station']);
}

function LoadPOVNData($session_id, $station_id)
{
	return array(0);
}

function LoadTravelerData($session_id, $station_id)
{
	return array(0);
}

// Construct
$graph = new LineGraph(240,110);

// Set input
$graph->SetInputArray($povnData);
$graph->SetInputArray($travelerData);

// Get image
$image = $graph->GetImage(); //must fail if there is no width, height or inputArray

// Make .PHP -> .PNG-image
header('Content-type:image/png');

// Display on screen
imagepng($image);

// Destroy garbage
imagedestroy($image);
?>