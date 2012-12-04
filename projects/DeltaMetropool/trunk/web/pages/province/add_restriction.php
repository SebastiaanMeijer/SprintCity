<?php
require_once '../../includes/master.inc.php';

$game_id = Game::getGameIdOfSession(session_id());
if (User::isProvince())
{
	$station = $_REQUEST['stationId'];
	$type = $_REQUEST['typeId'];
	// if not exists
	if (!TypeRestriction::isActive($game_id, $station, $type))
	{
		TypeRestriction::addRestriction($game_id, $station, $type);
	}
}

// output full list of restrictions
include('get_restrictions.php');
?>