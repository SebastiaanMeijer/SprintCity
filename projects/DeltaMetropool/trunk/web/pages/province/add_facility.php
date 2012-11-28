<?php
require_once '../../includes/master.inc.php';

if (User::isProvince())
{
	$station = Station::getStationById($_REQUEST['stationId']);
	$facility = Facility::getFacilityById($_REQUEST['facilityId']);
	$game_id = Game::getGameIdOfSession(session_id());
	Facility::addFacilityToStation($game_id, $_REQUEST['facilityId'], $_REQUEST['stationId']);
	$round_name =  RoundInfo::getCurrentRoundNameBySessionId(session_id());
	$station_name = $station[$_REQUEST['stationId']]->name;
	$facility_name = $facility[$_REQUEST['facilityId']]->name;
	echo json_encode($round_name . ' - ' . $station_name . ' - ' . $facility_name . '<br/>');
}
else
{
	echo json_encode("");
}
?>