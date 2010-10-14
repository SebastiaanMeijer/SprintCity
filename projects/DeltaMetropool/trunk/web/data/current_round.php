<?php
	require_once '../includes/master.inc.php';

	if (ClientSession::hasSession(session_id()))
	{
		printCurrentRound();
	}
	
	function printCurrentRound()
	{
		echo '<id>' . RoundInfo::getCurrentRoundIdBySessionId(session_id()). '</id>';
	}
?>
