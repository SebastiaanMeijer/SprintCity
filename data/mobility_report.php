<?php
	require_once '../includes/master.inc.php';

	if (ClientSession::hasSession(session_id()))
	{
		printMobilityReport();
	}
	
	function printMobilityReport()
	{
		$report = RoundInfoInstance::getMobilityReport(Game::getGameIdOfSession(session_id()));
		
		echo '<mobility_report>';
		echo $report;
		echo '</mobility_report>';
	}
?>