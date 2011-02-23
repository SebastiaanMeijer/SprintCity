<?phprequire_once('../../includes/master.inc.php');require_once('linegraph.php');// commented security to allow public reports//if (ClientSession::hasSession(session_id()))//{	// Data	$citizenData = array(0);	$workerData = array(0);	$width = isset($_REQUEST['width']) ? $_REQUEST['width'] : 480;	$height = isset($_REQUEST['height']) ? $_REQUEST['height'] : 220;		$gameId = isset($_REQUEST['game']) ? $_REQUEST['game'] : Game::getGameIdOfSession(session_id());		$citizenData = LoadCitizenData($gameId, $_REQUEST['station']);	$workerData = LoadWorkerData($gameId, $_REQUEST['station']);	$initCitizenCount = Station::getInitialCitizenCount($_REQUEST['station']);	$initWorkerCount = Station::getInitialWorkerCount($_REQUEST['station']);		if(isset($citizenData))		array_unshift($citizenData, $initCitizenCount);	else		$citizenData = array($initCitizenCount);		if(isset($workerData))		array_unshift($workerData, $initWorkerCount);	else		$workerData = array($initWorkerCount);		// Get min/max values	$minMaxCitizenCount = array_merge(LoadInitCitizenDataMinMax(), LoadCitizenDataMinMax($gameId));	$minCitizenCount = min($minMaxCitizenCount) - 10;	$maxCitizenCount = max($minMaxCitizenCount);	$minMaxWorkerCount = array_merge(LoadInitWorkerDataMinMax(), LoadWorkerDataMinMax($gameId));	$minWorkerCount = min($minMaxWorkerCount) - 10;	$maxWorkerCount = max($minMaxWorkerCount);		// Construct	// $graph = new LineGraph(480,220);	$graph = new LineGraph($width, $height);		// Set input	$graph->SetInputArray($citizenData, $minCitizenCount, $maxCitizenCount);	$graph->SetInputArray($workerData, $minWorkerCount, $maxWorkerCount);		//var_dump( $citizenData);		// Get image	$image = $graph->GetImage(); //must fail if there is no width, height or inputArray		// Make .PHP -> .PNG-image	header('Content-type:image/png');		// Display on screen	imagepng($image);		// Destroy garbage	imagedestroy($image);//}function LoadInitCitizenDataMinMax(){	$db = Database::getDatabase();	$query = "		SELECT 			MIN(InitialCitizenCount) AS MinInitCitizenCount,			MAX(InitialCitizenCount) AS MaxInitCitizenCount		FROM 		(			SELECT ROUND(Station.count_home_total * Constants.average_citizens_per_home) AS InitialCitizenCount			FROM Constants, Station		) AS t1 LIMIT 0,1;";	$result = $db->query($query, array());	if (mysql_num_rows($result) > 0)	{		$data = array();		$row = mysql_fetch_array($result);		$data[] = round($row['MinInitCitizenCount']);		$data[] = round($row['MaxInitCitizenCount']);		return $data;	}	return array();}function LoadInitWorkerDataMinMax(){	$db = Database::getDatabase();	$query = "		SELECT 			MIN(InitialWorkerCount) AS MinInitWorkerCount,			MAX(InitialWorkerCount) AS MaxInitWorkerCount		FROM 		(			SELECT ROUND(Station.count_work_total * Constants.average_workers_per_bvo) AS InitialWorkerCount			FROM Constants, Station		) AS t1 LIMIT 0,1;";	$result = $db->query($query, array());	if (mysql_num_rows($result) > 0)	{		$data = array();		$row = mysql_fetch_array($result);		$data[] = round($row['MinInitWorkerCount']);		$data[] = round($row['MaxInitWorkerCount']);		return $data;	}	return array();}function LoadCitizenDataMinMax($game_id){	if(isset($game_id))	{		$db = Database::getDatabase();		$query = "			SELECT 				MIN(CitizenCount) AS MinCitizenCount,				MAX(CitizenCount) AS MaxCitizenCount			FROM 			(				SELECT					(						(							(								(									Station.area_cultivated_home - 									(										(SUM(Program.area_home) + SUM(Program.area_work) + SUM(Program.area_leisure)) 										* 										(transform_area_cultivated_home / (transform_area_cultivated_home + transform_area_cultivated_work + transform_area_cultivated_mixed + transform_area_undeveloped_urban + transform_area_undeveloped_rural))									)								)								* 								(count_home_total / area_cultivated_home)							) 							+ 							SUM(Program.area_home * Types.area_density)						) 						* Constants.average_citizens_per_home					) AS CitizenCount					FROM Constants, Station					INNER JOIN StationInstance ON Station.id = StationInstance.station_id 					INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id					INNER JOIN RoundInstance ON StationInstance.id = RoundInstance.station_instance_id					INNER JOIN Program ON RoundInstance.exec_program_id = Program.id					INNER JOIN Types ON Program.type_home = Types.id					INNER JOIN Round ON RoundInstance.round_id = Round.id AND Station.id = Round.station_id					INNER JOIN RoundInfo ON Round.round_info_id = RoundInfo.id					INNER JOIN RoundInfo AS RoundInfo2 ON RoundInfo.id < RoundInfo2.id					INNER JOIN Game ON TeamInstance.game_id = Game.id AND RoundInfo2.id <= Game.current_round_id					WHERE Game.id = :game_id					GROUP BY Station.id, RoundInfo2.id					ORDER BY RoundInfo2.id			) AS t1 LIMIT 0,1;";		$result = $db->query($query, array('game_id' => $game_id));		if (mysql_num_rows($result) > 0)		{			$data = array();			$row = mysql_fetch_array($result);			if ($row['MinCitizenCount'] != NULL) $data[] = round($row['MinCitizenCount']);			if ($row['MaxCitizenCount'] != NULL) $data[] = round($row['MaxCitizenCount']);			return $data;		}	}	return array();}function LoadWorkerDataMinMax($game_id){	if (isset($game_id))	{		$db = Database::getDatabase();		$query = "			SELECT				MIN(WorkerCount) AS MinWorkerCount,				MAX(WorkerCount) AS MaxWorkerCount			FROM			(				SELECT				(					(						(							(								Station.area_cultivated_work - 								(									(SUM(Program.area_home) + SUM(Program.area_work) + SUM(Program.area_leisure)) 									* 									(transform_area_cultivated_work / (transform_area_cultivated_home + transform_area_cultivated_work + transform_area_cultivated_mixed + transform_area_undeveloped_urban + transform_area_undeveloped_rural))								)							)							* 							(count_worker_total / (area_cultivated_work + area_cultivated_mixed))						) 						+ 						SUM(Program.area_work * WorkerTypes.people_density)					)					+					(						(							(								Station.area_cultivated_mixed - 								(									(SUM(Program.area_home) + SUM(Program.area_work) + SUM(Program.area_leisure)) 									* 									(transform_area_cultivated_mixed / (transform_area_cultivated_home + transform_area_cultivated_work + transform_area_cultivated_mixed + transform_area_undeveloped_urban + transform_area_undeveloped_rural))								)							)							* 							(count_worker_total / (area_cultivated_work + area_cultivated_mixed))						) 						+ 						SUM(Program.area_leisure * LeisureTypes.people_density)					) 				) AS WorkerCount				FROM Constants, Station				INNER JOIN StationInstance ON Station.id = StationInstance.station_id 				INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id				INNER JOIN RoundInstance ON StationInstance.id = RoundInstance.station_instance_id				INNER JOIN Program ON RoundInstance.exec_program_id = Program.id				INNER JOIN Types AS WorkerTypes ON Program.type_work = WorkerTypes.id				INNER JOIN Types AS LeisureTypes ON Program.type_leisure = LeisureTypes.id				INNER JOIN Round ON RoundInstance.round_id = Round.id AND Station.id = Round.station_id				INNER JOIN RoundInfo ON Round.round_info_id = RoundInfo.id				INNER JOIN RoundInfo AS RoundInfo2 ON RoundInfo.id < RoundInfo2.id				INNER JOIN Game ON TeamInstance.game_id = Game.id AND RoundInfo2.id <= Game.current_round_id				WHERE Game.id = :game_id				GROUP BY Station.id, RoundInfo2.id				ORDER BY RoundInfo2.id			) AS t1 LIMIT 0,1;";		$result = $db->query($query, array('game_id' => $game_id));		if (mysql_num_rows($result) > 0)		{			$data = array();			$row = mysql_fetch_array($result);			if ($row['MinWorkerCount'] != NULL) $data[] = round($row['MinWorkerCount']);			if ($row['MaxWorkerCount'] != NULL) $data[] = round($row['MaxWorkerCount']);			return $data;		}	}	return array();}function LoadCitizenData($game_id, $station_id){	if (isset($game_id) && isset($station_id))	{		$db = Database::getDatabase();		$query = "			SELECT				(					(						(							(								Station.area_cultivated_home - 								(									(SUM(Program.area_home) + SUM(Program.area_work) + SUM(Program.area_leisure)) 									* 									(transform_area_cultivated_home / (transform_area_cultivated_home + transform_area_cultivated_work + transform_area_cultivated_mixed + transform_area_undeveloped_urban + transform_area_undeveloped_rural))								)							)							* 							(count_home_total / area_cultivated_home)						) 						+ 						SUM(Program.area_home * Types.area_density)					) 					* Constants.average_citizens_per_home				) AS CitizenCount			FROM Constants, Station			INNER JOIN StationInstance ON Station.id = StationInstance.station_id 			INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id			INNER JOIN RoundInstance ON StationInstance.id = RoundInstance.station_instance_id			INNER JOIN Program ON RoundInstance.exec_program_id = Program.id			INNER JOIN Types ON Program.type_home = Types.id			INNER JOIN Round ON RoundInstance.round_id = Round.id AND Station.id = Round.station_id			INNER JOIN RoundInfo ON Round.round_info_id = RoundInfo.id			INNER JOIN RoundInfo AS RoundInfo2 ON RoundInfo.id < RoundInfo2.id			INNER JOIN Game ON TeamInstance.game_id = Game.id AND RoundInfo2.id <= Game.current_round_id			WHERE Game.id = :game_id AND Station.id = :station_id			GROUP BY Station.id, RoundInfo2.id			ORDER BY RoundInfo2.id;";		$args = array('game_id' => $game_id, 'station_id' => $station_id);		$result = $db->query($query, $args);		if (mysql_num_rows($result) > 0)		{			$data = array();			while ($row = mysql_fetch_array($result))				$data[] = round($row['CitizenCount']);			return $data;		}		else			return NULL;	}}function LoadWorkerData($game_id, $station_id){	if (isset($game_id) && isset($station_id))	{		$db = Database::getDatabase();		$query = "			SELECT				(					(						(							(								Station.area_cultivated_work - 								(									(SUM(Program.area_home) + SUM(Program.area_work) + SUM(Program.area_leisure)) 									* 									(transform_area_cultivated_work / (transform_area_cultivated_home + transform_area_cultivated_work + transform_area_cultivated_mixed + transform_area_undeveloped_urban + transform_area_undeveloped_rural))								)							)							* 							(count_worker_total / (area_cultivated_work + area_cultivated_mixed))						) 						+ 						SUM(Program.area_work * WorkTypes.people_density)					) 					+					(						(							(								Station.area_cultivated_mixed - 								(									(SUM(Program.area_home) + SUM(Program.area_work) + SUM(Program.area_leisure)) 									* 									(transform_area_cultivated_mixed / (transform_area_cultivated_home + transform_area_cultivated_work + transform_area_cultivated_mixed + transform_area_undeveloped_urban + transform_area_undeveloped_rural))								)							)							* 							(count_worker_total / (area_cultivated_work + area_cultivated_mixed))						) 						+ 						SUM(Program.area_leisure * LeisureTypes.people_density)					) 				) AS WorkerCount			FROM Constants, Station			INNER JOIN StationInstance ON Station.id = StationInstance.station_id 			INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id			INNER JOIN RoundInstance ON StationInstance.id = RoundInstance.station_instance_id			INNER JOIN Program ON RoundInstance.exec_program_id = Program.id			INNER JOIN Types AS WorkTypes ON Program.type_work = WorkTypes.id			INNER JOIN Types AS LeisureTypes ON Program.type_leisure = LeisureTypes.id			INNER JOIN Round ON RoundInstance.round_id = Round.id AND Station.id = Round.station_id			INNER JOIN RoundInfo ON Round.round_info_id = RoundInfo.id			INNER JOIN RoundInfo AS RoundInfo2 ON RoundInfo.id < RoundInfo2.id			INNER JOIN Game ON TeamInstance.game_id = Game.id AND RoundInfo2.id <= Game.current_round_id			WHERE Game.id = :game_id AND Station.id = :station_id			GROUP BY Station.id, RoundInfo2.id			ORDER BY RoundInfo2.id;";		$args = array('game_id' => $game_id, 'station_id' => $station_id);		$result = $db->query($query, $args);		if (mysql_num_rows($result) > 0)		{			$data = array();			while ($row = mysql_fetch_array($result))				$data[] = round($row['WorkerCount']);			return $data;		}		else			return NULL;	}}?>