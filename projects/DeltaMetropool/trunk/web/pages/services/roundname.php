<?php
require_once '../../includes/master.inc.php';

$roundname =  RoundInfo::getCurrentRoundNameBySessionId(session_id());

echo json_encode($roundname);
?>
