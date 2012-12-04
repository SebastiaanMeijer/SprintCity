<?php

require_once dirname(__DIR__) . '../../includes/master.inc.php';

$game_id = Game::getGameIdOfSession(session_id());
$restrictions = TypeRestriction::getActiveRestrictionsInGame($game_id);
$restriction = mysql_fetch_array($restrictions);
$current_station = -1;
if ($restriction)
	{
		echo '<b>' . $restriction['Station'] . ':</b> ';
		$current_station = $restriction['StationId'];
	}
while ($restriction)
{
	echo '<a href="" onclick="removeRestriction(' . $restriction['StationId'] . ', ' . $restriction['TypeId'] . '); return false;">' . $restriction['Type'] . '</a>';
	$restriction = mysql_fetch_array($restrictions);
	if ($restriction && $restriction['StationId'] != $current_station)
	{
		$current_station = $restriction['StationId'];
		echo '<br/><b>' . $restriction['Station'] . ':</b> ';
	}
	else if ($restriction)
	{
		echo ', ';
	}
}
?>