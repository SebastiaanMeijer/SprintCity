<?php
require_once '../../includes/master.inc.php';

if (User::isProvince())
{
	$station = $_REQUEST['stationId'];
	$type = $_REQUEST['typeId'];
	$game_id = Game::getGameIdOfSession(session_id());
	// if not exists
	if (TypeRestriction::isActive($game_id, $station, $type))
	{
		TypeRestriction::removeRestriction($game_id, $station, $type);
	}
}

// output full list of restrictions
include('get_restrictions.php');
?>