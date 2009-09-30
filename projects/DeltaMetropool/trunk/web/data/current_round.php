<?php
	require_once '../includes/master.inc.php';

	if (isset($_REQUEST['session']) &&
		$_REQUEST['session'] == session_id() &&
		ClientSession::hasSession($_REQUEST['session']))
	{
		printCurrentRound();
	}
	
	function printCurrentRound()
	{
		echo '<id>' . RoundInfo::getCurrentRoundIdBySessionId(session_id()). '</id>';
	}
?>
