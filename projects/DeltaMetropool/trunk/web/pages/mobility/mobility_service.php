<?php
require_once (__DIR__ . '/../../includes/master.inc.php');

// Get post variables that load.js gives
if (isset($_REQUEST['get'])) {
	if ($_REQUEST['get'] == 'stations') {
		$stations = getMobilityDataStations();
		echo json_encode($stations);
	} elseif ($_REQUEST['get'] == 'trains') {
		$trains = getMobilityDataTrains();
		echo json_encode($trains);
	}
}

if (isset($_REQUEST['trainId']) && isset($_REQUEST['stationStops'])) {
	$game_id = Game::getGameIdOfSession(session_id());
	$round_info_instance_id = RoundInfoInstance::getCurrentRoundInfoInstanceIdBySessionId(session_id());

	$train_table_id = 1;

	$trainId = $_REQUEST['trainId'];
	$stationStops = $_REQUEST['stationStops'];
	$stationIds = getCurrentStations($game_id, $train_table_id);

	$db = Database::getDatabase();
	foreach ($stationStops as $index => $frequency) {
		$query = "
			INSERT INTO TrainTableEntryInstance 
				(round_info_instance_id, train_id, station_id, frequency) 
			VALUES 
				(:round_info_instance_id, :train_id, :station_id, :frequency)
			ON DUPLICATE KEY UPDATE frequency = :frequency;";
		$args = array('round_info_instance_id' => $round_info_instance_id, 'train_id' => $trainId, 'station_id' => $stationIds[$index], 'frequency' => $frequency);
		$db -> query($query, $args);
	}
}

function getCurrentStations($game_id, $train_table_id) {
	$db = Database::getDatabase();
	$query = "
		SELECT TrainTableStation.id AS station_id
		FROM Station
		INNER JOIN StationInstance ON Station.id = StationInstance.station_id
		INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id
		INNER JOIN Game ON Game.id = TeamInstance.game_id
		INNER JOIN ScenarioStation ON ScenarioStation.scenario_id = Game.scenario_id AND ScenarioStation.station_id = Station.id
		INNER JOIN TrainTableStation ON TrainTableStation.code = Station.code
		WHERE TeamInstance.game_id = :game_id
		AND train_table_id = :train_table_id
		ORDER BY ScenarioStation.order;";
	$args = array('game_id' => $game_id, 'train_table_id' => $train_table_id);
	$result = $db -> query($query, $args);

	while ($row = mysql_fetch_array($result)) {
		$stations[] = $row['station_id'];
	}
	return $stations;
}

function getMobilityDataStations() {
	$db = Database::getDatabase();
	$db -> query("START TRANSACTION");

	$game_id = Game::getGameIdOfSession(session_id());
	createTempTables($game_id);

	$train_table_id = 1;

	$query = "
		SELECT Station.code, 
			   Station.name, 
			   tempNetworkValues.networkValue, 
			   IFNULL(A.currentTravelers, 0) AS currentTravelers, 
			   IFNULL(A.cap100, 0) AS cap100, 
			   IFNULL(A.capOver, 0) AS capOver, 
			   IFNULL(A.capUnder, 0) AS capUnder
		FROM Station
		INNER JOIN StationInstance ON Station.id = StationInstance.station_id
		INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id
		INNER JOIN Game ON Game.id = TeamInstance.game_id
		INNER JOIN ScenarioStation ON ScenarioStation.scenario_id = Game.scenario_id AND ScenarioStation.station_id = Station.id
		LEFT JOIN (
			SELECT Station.id AS station_id, 
				   SUM(travelersPerStop) AS currentTravelers, 
				   SUM(avg_travelers_per_stop) AS cap100, 
				   SUM(avg_travelers_per_stop) * 1.1 AS capOver, 
				   SUM(avg_travelers_per_stop) * 0.9 AS capUnder
			FROM Station 
			INNER JOIN TrainTableStation ON Station.code = TrainTableStation.code
			INNER JOIN tempTravelersPerStop ON TrainTableStation.id = tempTravelersPerStop.station_id
			INNER JOIN (SELECT train_id, COUNT(*) AS station_count
				FROM Station 
				INNER JOIN StationInstance ON Station.id = StationInstance.station_id
				INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id
				INNER JOIN TrainTableStation ON Station.code = TrainTableStation.code
				INNER JOIN traintableentry ON TrainTableStation.id = traintableentry.station_id
				WHERE TeamInstance.game_id = :game_id
				AND train_table_id = train_table_id
				GROUP BY train_id) AS StationCounts ON tempTravelersPerStop.train_id = StationCounts.train_id
			INNER JOIN (SELECT train_id, AVG(travelersPerStop) AS avg_travelers_per_stop
				FROM tempInitialTravelersPerStop
				GROUP BY train_id) AS AvgTravelersPerStop ON StationCounts.train_id = AvgTravelersPerStop.train_id
			WHERE station_count > 1
			AND train_table_id = :train_table_id
			GROUP BY Station.id
		) AS A ON A.station_id = Station.id
		LEFT JOIN TrainTableStation ON Station.code = TrainTableStation.code
		LEFT JOIN tempNetworkValues ON tempNetworkValues.station_id = TrainTableStation.id
		WHERE TeamInstance.game_id = :game_id
		AND train_table_id = :train_table_id
		ORDER BY ScenarioStation.order;";
	$args = array('game_id' => $game_id, 'train_table_id' => $train_table_id);
	$result = $db -> query($query, $args);
	$db -> query("COMMIT");

	while ($row = mysql_fetch_array($result)) {
		$stations[] = array("code" => $row['code'], "name" => $row['name'], "networkValue" => round($row['networkValue']), "prevIU" => 0, "currentIU" => round($row['currentTravelers']), "progIU" => 0, "cap100" => round($row['cap100']), "capOver" => round($row['capOver']), "capUnder" => round($row['capUnder']));
	}

	return $stations;
}

function getMobilityDataTrains() {
	$db = Database::getDatabase();
	$db -> query("START TRANSACTION");

	$game_id = Game::getGameIdOfSession(session_id());
	createTempTables($game_id);

	$train_table_id = 1;

	$query = "
		SELECT A.train_id, 
			   TrainTableTrain.name, 
			   TrainTableTrain.type, 
			   A.station_order, 
			   IFNULL(frequency,0) AS frequency, 
			   initialAvgTravelersPerStop.avgTravelers AS initialAvgTravelers, 
			   avgTravelersPerStop.avgTravelers AS currentAvgTravelers 
		FROM (
			SELECT * 
			FROM (
				SELECT train_id
				FROM Station 
				INNER JOIN StationInstance ON Station.id = StationInstance.station_id
				INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id
				INNER JOIN TrainTableStation ON Station.code = TrainTableStation.code
				INNER JOIN traintableentry ON TrainTableStation.id = traintableentry.station_id
				WHERE TeamInstance.game_id = :game_id
				AND train_table_id = :train_table_id
				GROUP BY train_id
				HAVING COUNT(*) > 1
			) AS A,
			(
				SELECT ScenarioStation.order AS station_order, 
					   TrainTableStation.id AS station_id
				FROM Station 
				INNER JOIN StationInstance ON Station.id = StationInstance.station_id
				INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id
				INNER JOIN TrainTableStation ON Station.code = TrainTableStation.code
				INNER JOIN Game ON Game.id = TeamInstance.game_id
				INNER JOIN ScenarioStation ON ScenarioStation.station_id = Station.id AND ScenarioStation.scenario_id = Game.scenario_id
				WHERE TeamInstance.game_id = :game_id
				AND train_table_id = :train_table_id
			) AS B
		) AS A 
		LEFT JOIN tempEntries ON A.train_id = tempEntries.train_id AND A.station_id = tempEntries.station_id
		INNER JOIN (
			SELECT train_id, 
				   AVG(travelersPerStop) AS avgTravelers
			FROM tempInitialTravelersPerStop
			GROUP BY train_id
		) AS initialAvgTravelersPerStop ON initialAvgTravelersPerStop.train_id = A.train_id
		INNER JOIN (
			SELECT train_id, AVG(travelersPerStop) AS avgTravelers
			FROM tempTravelersPerStop
			GROUP BY train_id
		) AS avgTravelersPerStop ON avgTravelersPerStop.train_id = A.train_id
		INNER JOIN TrainTableTrain ON A.train_id = TrainTableTrain.id
		ORDER BY train_id, station_order";

	$args = array('game_id' => $game_id, 'train_table_id' => $train_table_id);
	$result = $db -> query($query, $args);
	$db -> query("COMMIT");
	
	$trainId = -1;
	while ($row = mysql_fetch_array($result)) {
		if ($row['train_id'] != $trainId && $trainId != -1) {
			$train['stationStops'] = $stationStops;
			$trains[] = $train;
			$stationStops = array();
		}

		$train = array("id" => $row['train_id'], "name" => $row['type'], "beginStation" => $row['name'], "endStation" => "", "currentAvgIU" => round($row['currentAvgTravelers']), "minAvgIU" => round($row['initialAvgTravelers'] * 0.9), "maxAvgIU" => round($row['initialAvgTravelers'] * 1.1));
		$stationStops[] = $row['frequency'];
		$trainId = $train['id'];
	}
	$train['stationStops'] = $stationStops;
	$trains[] = $train;

	return $trains;
}

function createTempTables($game_id) {
	$db = Database::getDatabase();
	$round_info_instance_id = RoundInfoInstance::getCurrentRoundInfoInstanceIdBySessionId(session_id());

	$train_table_id = 1;

	$queries = array("DROP TABLE IF EXISTS tempInitialEntries;", "DROP TABLE IF EXISTS tempInitialNetworkValues;", "DROP TABLE IF EXISTS tempInitialTravelers;", "DROP TABLE IF EXISTS tempInitialTravelersPerStop;", "DROP TABLE IF EXISTS tempEntries;", "DROP TABLE IF EXISTS tempNetworkValues;", "DROP TABLE IF EXISTS tempTravelers;", "DROP TABLE IF EXISTS tempTravelersPerStop;", "CREATE TABLE tempInitialEntries (train_id INT, station_id INT, frequency INT);", "CREATE TABLE tempInitialNetworkValues (station_id INT, networkValue DOUBLE);", "CREATE TABLE tempInitialTravelers (station_id INT, travelers INT);", "CREATE TABLE tempInitialTravelersPerStop (train_id INT, station_id INT, travelersPerStop INT);", "CREATE TABLE tempEntries (train_id INT, station_id INT, frequency INT);", "CREATE TABLE tempNetworkValues (station_id INT, networkValue DOUBLE);", "CREATE TABLE tempTravelers (station_id INT, travelers INT);", "CREATE TABLE tempTravelersPerStop (train_id INT, station_id INT, travelersPerStop INT);");
	foreach ($queries as $query) {
		$db -> query($query, array());
	}

	createInitialEntriesTable("tempInitialEntries", $train_table_id);
	createNetworkValueTable("tempInitialNetworkValues", "tempInitialEntries", $train_table_id);
	createInitialTravelersTable("tempInitialTravelers", $game_id, $train_table_id);
	createTravelersPerStopTable("tempInitialTravelersPerStop", "tempInitialEntries", "tempInitialNetworkValues", "tempInitialTravelers");

	createCurrentEntriesTable("tempEntries", $train_table_id, $round_info_instance_id);
	createNetworkValueTable("tempNetworkValues", "tempEntries", $train_table_id);
	createCurrentTravelersTable("tempTravelers", "tempInitialNetworkValues", "tempNetworkValues", $game_id, $train_table_id);
	createTravelersPerStopTable("tempTravelersPerStop", "tempEntries", "tempNetworkValues", "tempTravelers");
}

function createInitialEntriesTable($table_name, $train_table_id) {
	$db = Database::getDatabase();
	$query = "
	INSERT INTO " . $table_name . "
	SELECT train_id, station_id, frequency 
	FROM traintableentry
	INNER JOIN traintabletrain ON train_id = traintabletrain.id 
	WHERE train_table_id = :train_table_id;";
	$args = array('train_table_id' => $train_table_id);
	$db -> query($query, $args);
}

function createCurrentEntriesTable($table_name, $train_table_id, $round_info_instance_id) {
	$db = Database::getDatabase();
	$query = "
	INSERT INTO " . $table_name . "
	SELECT train_id, station_id, frequency
	FROM (
		SELECT * 
		FROM (
			SELECT * 
			FROM (
				SELECT train_id, station_id, frequency
				FROM traintableentryinstance 
				WHERE round_info_instance_id = :round_info_instance_id 
				UNION
				SELECT train_id, station_id, frequency 
				FROM traintableentry
			) AS A 
			GROUP BY train_id, station_id
		) AS A 
		WHERE frequency != 0
	) AS A
	INNER JOIN traintabletrain ON train_id = traintabletrain.id 
	WHERE train_table_id = :train_table_id;";
	$args = array('train_table_id' => $train_table_id, 'round_info_instance_id' => $round_info_instance_id);
	$db -> query($query, $args);
}

function createNetworkValueTable($table_name, $entries_table, $train_table_id) {
	$db = Database::getDatabase();
	$query = "
	INSERT INTO " . $table_name . "
	SELECT traintablestation.id, IFNULL(SUM(trainvalue * frequency),0) + chain AS networkvalue
	FROM traintablestation 
	LEFT JOIN " . $entries_table . " ON traintablestation.id = " . $entries_table . ".station_id
	LEFT JOIN (
		SELECT train_id, SUM(stopvalue) / COUNT(stopvalue) AS trainvalue 
		FROM (
			SELECT station_id, SUM(frequency) AS stopvalue FROM " . $entries_table . "
			GROUP BY station_id
		) AS stopvalues
		INNER JOIN " . $entries_table . " ON stopvalues.station_id = " . $entries_table . ".station_id
		GROUP BY train_id
	) AS trainvalues ON trainvalues.train_id = " . $entries_table . ".train_id
	WHERE train_table_id = :train_table_id
	GROUP BY traintablestation.id;";
	$args = array('train_table_id' => $train_table_id);
	$db -> query($query, $args);
}

function createInitialTravelersTable($table_name, $game_id, $train_table_id) {
	$db = Database::getDatabase();
	$query = "
	INSERT INTO " . $table_name . "
	SELECT id AS station_id, A.travelers 
	FROM (
		SELECT * 
		FROM (
			SELECT Station.code, ROUND
			(
				Station.area_cultivated_mixed * Constants.average_travelers_per_ha_leisure 
				+
				Station.count_home_total * Constants.average_citizens_per_home * Constants.average_travelers_per_citizen
				+
				Station.count_work_total * Constants.average_workers_per_bvo * Constants.average_travelers_per_worker
			) AS travelers
			FROM Constants, Station		
		) AS A 
		UNION
		SELECT code, travelers
		FROM TrainTableStation 
		WHERE train_table_id = :train_table_id
	) AS A 
	INNER JOIN TrainTableStation ON A.code = TrainTableStation.code
	WHERE train_table_id = :train_table_id
	GROUP BY A.code;";
	$args = array('train_table_id' => $train_table_id);
	$db -> query($query, $args);
}

function createCurrentTravelersTable($table_name, $nwval_table_initial, $nwval_table_current, $game_id, $train_table_id) {
	$db = Database::getDatabase();
	$query = "
	INSERT INTO " . $table_name . "
	SELECT id AS station_id, A.travelers 
	FROM (
		SELECT * 
		FROM (
			SELECT A.code, A.travelers * 
				IFNULL(
					(current.networkValue - initial.networkValue) 
					/ 
					initial.networkValue 
					/
					IF((current.networkValue - initial.networkValue) / initial.networkValue > 5, 20, IF((current.networkValue - initial.networkValue) / initial.networkValue > 1, 15, 10))
					+ 1
					, 1
				) AS travelers 
			FROM (
				SELECT Station.code, ROUND
				(
					(
						(
							(
								(
									(
										Station.area_cultivated_home - 
										(
											SUM(Round.new_transform_area) 
											* 
											(transform_area_cultivated_home / (transform_area_cultivated_home + transform_area_cultivated_work + 
											transform_area_cultivated_mixed + transform_area_undeveloped_urban + transform_area_undeveloped_rural))
										)
									)
									* 
									(count_home_total / area_cultivated_home)
								) 
								+ 
								SUM(Program.area_home * TypesHome.area_density)
							) 
							* 
							Constants.average_citizens_per_home * Constants.average_travelers_per_citizen
						) 
						+
						(
							(
								(
									(
										Station.area_cultivated_work - 
										(
											SUM(Round.new_transform_area) 
											* 
											(transform_area_cultivated_work / (transform_area_cultivated_home + transform_area_cultivated_work + 
											transform_area_cultivated_mixed + transform_area_undeveloped_urban + transform_area_undeveloped_rural))
										)
									)
									* 
									(count_worker_total / (area_cultivated_work + area_cultivated_mixed))
								) 
								+ 
								SUM(Program.area_work * TypesWork.people_density)
							)
							*
							Constants.average_travelers_per_worker
						)
						+
						(
							(
								(
									(
										Station.area_cultivated_mixed - 
										(
											SUM(Round.new_transform_area) 
											* 
											(transform_area_cultivated_mixed / (transform_area_cultivated_home + transform_area_cultivated_work + 
											transform_area_cultivated_mixed + transform_area_undeveloped_urban + transform_area_undeveloped_rural))
										)
									)
									* 
									(count_worker_total / (area_cultivated_work + area_cultivated_mixed))
								) 
								+ 
								SUM(Program.area_leisure * TypesLeisure.people_density)
							) 
							*
							Constants.average_travelers_per_worker
						)
					)
				) AS travelers
				FROM Constants, Station
				INNER JOIN StationInstance ON Station.id = StationInstance.station_id 
				INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id
				INNER JOIN RoundInstance ON StationInstance.id = RoundInstance.station_instance_id
				INNER JOIN Program ON RoundInstance.exec_program_id = Program.id
				INNER JOIN Types AS TypesHome ON Program.type_home = TypesHome.id
				INNER JOIN Types AS TypesWork ON Program.type_work = TypesWork.id
				INNER JOIN Types AS TypesLeisure ON Program.type_leisure = TypesLeisure.id
				INNER JOIN Round ON RoundInstance.round_id = Round.id AND Station.id = Round.station_id
				INNER JOIN RoundInfo ON Round.round_info_id = RoundInfo.id
				INNER JOIN RoundInfo AS RoundInfo2 ON RoundInfo.id <= RoundInfo2.id
				INNER JOIN Round AS Round2 ON RoundInfo2.id = Round2.round_info_id AND Station.id = Round2.station_id
				INNER JOIN RoundInstance AS RoundInstance2 ON Round2.id = RoundInstance2.round_id AND StationInstance.id = RoundInstance2.station_instance_id
				INNER JOIN Game ON TeamInstance.game_id = Game.id AND RoundInfo2.id < current_round_id
				WHERE Game.id = :game_id
				GROUP BY Station.id, RoundInfo2.id
				ORDER BY RoundInfo2.id
			) AS A
			INNER JOIN traintablestation ON A.code = traintablestation.code
			INNER JOIN " . $nwval_table_initial . " AS initial ON initial.station_id = traintablestation.id
			INNER JOIN " . $nwval_table_current . " AS current ON current.station_id = traintablestation.id
			WHERE train_table_id = :train_table_id
		) AS A 
		UNION
		SELECT code, travelers
		FROM TrainTableStation 
		WHERE train_table_id = :train_table_id
	) AS A 
	INNER JOIN TrainTableStation ON A.code = TrainTableStation.code
	WHERE train_table_id = :train_table_id
	GROUP BY A.code;";
	$args = array('game_id' => $game_id, 'train_table_id' => $train_table_id);
	$db -> query($query, $args);
}

function createTravelersPerStopTable($table_name, $entries_table, $nwval_table, $travelers_table) {
	$db = Database::getDatabase();
	$query = "
	INSERT INTO " . $table_name . "
	SELECT " . $entries_table . ".train_id, " . $entries_table . ".station_id, (trainvalue * frequency) / networkValue * " . $travelers_table . ".travelers / frequency AS travelersPerStop
	FROM (
		SELECT train_id, SUM(stopvalue) / COUNT(stopvalue) AS trainvalue 
		FROM (
			SELECT station_id, SUM(frequency) AS stopvalue 
			FROM " . $entries_table . "
	        GROUP BY station_id
		) AS stopvalues
	    INNER JOIN " . $entries_table . " ON stopvalues.station_id = " . $entries_table . ".station_id
	    GROUP BY train_id
	) AS trainvalues
	INNER JOIN " . $entries_table . " ON trainvalues.train_id = " . $entries_table . ".train_id
	INNER JOIN traintablestation ON traintablestation.id = " . $entries_table . ".station_id
	INNER JOIN " . $nwval_table . " ON traintablestation.id = " . $nwval_table . ".station_id
	INNER JOIN " . $travelers_table . " ON traintablestation.id = " . $travelers_table . ".station_id;";
	$db -> query($query, array());
}
?>
