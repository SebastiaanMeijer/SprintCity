<?php
require_once '../../includes/master.inc.php';

// Get post variables that load.js gives

if (isset($_REQUEST['get'])) {

    if ($_REQUEST['get'] == 'stations') {
		$game_id = Game::getGameIdOfSession(session_id());
		createTempTables($game_id);
        $stations = array(
            array("name" => "Den Haag CS", "networkValue" => rand(20, 200), "prevIU" => rand(20, 100), "currentIU" => rand(20, 200), "progIU" => rand(20, 200), "cap100" => rand(20, 200), "capOver" => -1, "capUnder" => -1),
            array("name" => "Den Haag HS", "networkValue" => rand(20, 200), "prevIU" => rand(20, 100), "currentIU" => rand(20, 200), "progIU" => rand(20, 200), "cap100" => rand(20, 200), "capOver" => -1, "capUnder" => -1),
            array("name" => "Den Haag Moerwijk", "networkValue" => rand(20, 200), "prevIU" => rand(20, 100), "currentIU" => rand(20, 200), "progIU" => rand(20, 200), "cap100" => rand(20, 200), "capOver" => -1, "capUnder" => -1),
            array("name" => "Rijswijk", "networkValue" => rand(20, 200), "prevIU" => rand(20, 100), "currentIU" => rand(20, 200), "progIU" => rand(20, 200), "cap100" => rand(20, 200), "capOver" => -1, "capUnder" => -1),
            array("name" => "Delft", "networkValue" => rand(20, 200), "prevIU" => rand(20, 100), "currentIU" => rand(20, 200), "progIU" => rand(20, 200), "cap100" => rand(20, 200), "capOver" => -1, "capUnder" => -1),
            array("name" => "Delft Zuid", "networkValue" => rand(20, 200), "prevIU" => rand(20, 100), "currentIU" => rand(20, 200), "progIU" => rand(20, 200), "cap100" => rand(20, 200), "capOver" => -1, "capUnder" => -1),
            array("name" => "Schiedam Kethel", "networkValue" => rand(20, 200), "prevIU" => rand(20, 100), "currentIU" => rand(20, 200), "progIU" => rand(20, 200), "cap100" => rand(20, 200), "capOver" => -1, "capUnder" => -1),
            array("name" => "Schiedam Centraal", "networkValue" => rand(20, 200), "prevIU" => rand(20, 100), "currentIU" => rand(20, 200), "progIU" => rand(20, 200), "cap100" => rand(20, 200), "capOver" => -1, "capUnder" => -1),
            array("name" => "Rotterdam Centraal", "networkValue" => rand(20, 200), "prevIU" => rand(20, 100), "currentIU" => rand(20, 200), "progIU" => rand(20, 200), "cap100" => rand(20, 200), "capOver" => -1, "capUnder" => -1)
        );
        echo json_encode($stations);
    }
    
    elseif ($_REQUEST['get'] == 'trains') {
        $trains = array(
            array("id" => "1", "name" => "Sneltrein", "beginStation" => "Amsterdam", "endStation" => "Breda", 
                "stationStops" => array(0,2,0,2,2,0,0,0,2), "avgIU" => "1436"),
            array("id" => "545", "name" => "Int.", "beginStation" => "Amsterdam", "endStation" => "Brussel", 
                "stationStops" => array(0,2,0,0,0,0,0,0,2), "avgIU" => "1226")
        );
        
        echo json_encode($trains);
    }
}

function createTempTables($game_id) {
	$db = Database::getDatabase();
	
	$train_table_id = 1;
	$round_instance_id = 1;
	
	$queries = array(
		"DROP TABLE IF EXISTS tempEntries;",
		"DROP TABLE IF EXISTS tempInitialNetworkValues;",
		"DROP TABLE IF EXISTS tempNetworkValues;",
		"DROP TABLE IF EXISTS tempTravelers;",
		"DROP TABLE IF EXISTS tempTravelersPerStop;",
		
		"CREATE TABLE tempEntries (train_id INT, station_id INT, frequency INT);",
		"CREATE TABLE tempInitialNetworkValues (station_id INT, networkValue DOUBLE);",
		"CREATE TABLE tempNetworkValues (station_id INT, networkValue DOUBLE);",
		"CREATE TABLE tempTravelers (station_id INT, travelers INT);",
		"CREATE TABLE tempTravelersPerStop (train_id INT, station_id INT, travelersPerStop INT);");
	foreach ($queries as $query) {
		$db->query($query, array());
	}
	
	// tempEntries serves as the traintable input for the calculation of the other temporary tables;
	// initially it is set to the unmodified train table
	createInitialEntriesTable("tempEntries", $train_table_id);
	
	createNetworkValueTable("tempInitialNetworkValues", $train_table_id);
	
	// clear tempEntries, then fill it with the train table for the current round
	$db->query("TRUNCATE tempEntries;", array());
	createCurrentEntriesTable("tempEntries", $train_table_id, $round_instance_id);
	
	createNetworkValueTable("tempNetworkValues", $train_table_id);
	createTravelersTable("tempTravelers", $game_id, $train_table_id);
	createTravelersPerStopTable("tempTravelersPerStop");
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
	$db->query($query, $args);
}

function createCurrentEntriesTable($table_name, $train_table_id, $round_instance_id) {
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
				WHERE round_instance_id = :round_instance_id 
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
	$args = array('train_table_id' => $train_table_id,
				  'round_instance_id' => $round_instance_id);
	$db->query($query, $args);
}

function createNetworkValueTable($table_name, $train_table_id) {
	$db = Database::getDatabase();
	$query = "
	INSERT INTO " . $table_name . "
	SELECT traintablestation.id AS station_id, IFNULL(networkvalue,0) AS networkvalue 
	FROM (
		SELECT station_id, SUM(trainvalue * frequency) + chain AS networkvalue
		FROM (
			SELECT train_id, SUM(stopvalue) / COUNT(stopvalue) AS trainvalue 
			FROM (
		    	SELECT station_id, SUM(frequency) AS stopvalue FROM
		            tempEntries
		        GROUP BY station_id
			) AS stopvalues
		    INNER JOIN tempEntries ON stopvalues.station_id = tempEntries.station_id
		    GROUP BY train_id
		) AS trainvalues
		INNER JOIN tempEntries ON trainvalues.train_id = tempEntries.train_id
		INNER JOIN `traintablestation` ON `traintablestation`.id = tempEntries.station_id
		GROUP BY station_id
	) AS A
	RIGHT JOIN traintablestation ON A.station_id = traintablestation.id
	WHERE train_table_id = :train_table_id;";
	$args = array('train_table_id' => $train_table_id);
	$db->query($query, $args);
}

function createTravelersTable($table_name, $game_id, $train_table_id) {
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
			INNER JOIN tempInitialNetworkValues AS initial ON initial.station_id = traintablestation.id
			INNER JOIN tempNetworkValues AS current ON current.station_id = traintablestation.id
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
	$args = array('game_id' => $game_id,
				  'train_table_id' => $train_table_id);
	$db->query($query, $args);
}

function createTravelersPerStopTable($table_name) {
	$db = Database::getDatabase();
	$query = "
	INSERT INTO " . $table_name . "
	SELECT tempEntries.train_id, tempEntries.station_id, (trainvalue * frequency) / networkValue * tempTravelers.travelers / frequency AS travelersPerStop
	FROM (
		SELECT train_id, SUM(stopvalue) / COUNT(stopvalue) AS trainvalue 
		FROM (
			SELECT station_id, SUM(frequency) AS stopvalue 
			FROM tempEntries
	        GROUP BY station_id
		) AS stopvalues
	    INNER JOIN tempEntries ON stopvalues.station_id = tempEntries.station_id
	    GROUP BY train_id
	) AS trainvalues
	INNER JOIN tempEntries ON trainvalues.train_id = tempEntries.train_id
	INNER JOIN traintablestation ON traintablestation.id = tempEntries.station_id
	INNER JOIN tempNetworkValues ON traintablestation.id = tempNetworkValues.station_id
	INNER JOIN tempTravelers ON traintablestation.id = tempTravelers.station_id;";
	$db->query($query, array());
}


?>
