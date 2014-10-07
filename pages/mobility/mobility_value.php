<?php
require_once '../../includes/master.inc.php';

$game_id = Game::getGameIdOfSession(session_id());
$result = ValueInstance::getValuesByGameAndTeam($game_id, MOBILITY_TEAM_ID);
$hasValue = false;
while ($row = mysql_fetch_array($result)) {
    if ($row['checked'] == 1) {
        $value = json_encode($row['title']);
        echo $value;
        $hasValue = true;
    }
}
if (!$hasValue) {
    echo json_encode(false);
}
?>
