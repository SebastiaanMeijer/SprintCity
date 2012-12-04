<?PHP
	// Stick your DBOjbect subclasses in here (to help keep things tidy).

	class User extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('users', array('id', 'username', 'password', 'level', 'email'), $id);
		}
		
		public static function isProvince()
		{
			$db = Database::getDatabase();
			$result = $db->query("
				SELECT * 
				FROM TeamInstance 
				INNER JOIN ClientSession ON TeamInstance.id = ClientSession.team_instance_id 
				WHERE ClientSession.id = :session_id AND TeamInstance.team_id = :province_team_id",
				array('session_id' => session_id(), 'province_team_id' => PROVINCE_TEAM_ID));
			return $db->numRows($result) > 0;
		}
	}
	
	class ClientSession extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('ClientSession', array('id', 'team_instance_id', 'created'), $id);
		}
		
		public static function hasSession($id)
		{
			$db = Database::getDatabase();
			$result = $db->query("
					SELECT COUNT(*) 
					FROM ClientSession
					WHERE id = :id;",
					array('id' => $id));
			return $db->getValue($result) > 0;
		}
		
		public static function isMobilityTeam($id)
		{
			$db = Database::getDatabase();
			$result = $db->query("
					SELECT team_id 
					FROM TeamInstance
					INNER JOIN ClientSession ON TeamInstance.id = ClientSession.team_instance_id
					WHERE ClientSession.id = :id;",
					array('id' => $id));
			return $db->getValue($result) == MOBILITY_TEAM_ID;
		}
		
		public static function isProvinceTeam($id)
		{
			$db = Database::getDatabase();
			$result = $db->query("
					SELECT team_id 
					FROM TeamInstance
					INNER JOIN ClientSession ON TeamInstance.id = ClientSession.team_instance_id
					WHERE ClientSession.id = :id;",
					array('id' => $id));
			return $db->getValue($result) == PROVINCE_TEAM_ID;
		}
	}
	
	class Team extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('Team', array('id', 'name', 'description', 'color', 'cpu', 'created'), $id);
		}
		
		public static function rowCount()
		{
			$db = Database::getDatabase();
			return $db->getValue("SELECT COUNT(*) FROM team");
		}
		
		public static function getTeams($fromIndex, $numberOfRecords)
		{
			return DBObject::glob("Team", "SELECT * FROM team WHERE id > 1 ORDER BY `created` DESC LIMIT " . $fromIndex . " , " . $numberOfRecords);
		}
		
		public static function getTeamsInGame($gameId)
		{
			$db = Database::getDatabase();
			$query = "
				SELECT Team.id, Team.name, Team.description, Team.cpu, Team.created
				FROM Team
				INNER JOIN TeamInstance ON TeamInstance.team_id = Team.id
				WHERE TeamInstance.game_id = :game_id;";
			$args = array('game_id' => $gameId);
			$result = $db->query($query, $args);
			return DBObject::glob("Team", $result);
		}
		
		public static function getCurrentTeamColor()
		{
			$db = Database::getDatabase();
			$query = "
				SELECT Team.color
				FROM Team
				INNER JOIN TeamInstance ON Team.id = TeamInstance.team_id
				INNER JOIN ClientSession ON TeamInstance.id = ClientSession.team_instance_id
				WHERE ClientSession.id = :session_id";
			$args = array('session_id' => session_id());
			$result = $db->query($query, $args);
			return $db->getValue($result);
		}
	}
	
	class TeamInstance extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('TeamInstance', array('id', 'game_id', 'team_id', 'value_description'), $id);
		}
		
		public static function getTeamInstanceIdByGameAndTeam($gameId, $teamId)
		{
			$db = Database::getDatabase();
			$result = $db->query("
					SELECT `id` 
					FROM `TeamInstance` 
					WHERE game_id = :gameId 
					AND team_id = :teamId 
					LIMIT 0, 1",
					array('gameId' => $gameId, 'teamId' => $teamId));
			return $db->getValue($result);
		}
		
		public static function getValueDescription($gameId, $teamId)
		{
			$db = Database::getDatabase();
			$result = $db->query("
					SELECT value_description
					FROM TeamInstance
					WHERE game_id = :gameId 
					AND team_id = :teamId",
					array('gameId' => $gameId, 'teamId' => $teamId));
			return $db->getValue($result);
		}
	}
	
	class Station extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('Station', array(
				'id', 'code', 'name', 'variant', 
				'description_facts', 'description_background', 'description_future', 'town', 'region', 'POVN', 'PWN', 'IWD', 'MNG', 
				'area_cultivated_home', 'area_cultivated_work', 'area_cultivated_mixed', 'area_undeveloped_urban', 'area_undeveloped_rural',
				'transform_area_cultivated_home', 'transform_area_cultivated_work', 'transform_area_cultivated_mixed', 'transform_area_undeveloped_urban', 'transform_area_undeveloped_rural', 
				'count_home_total', 'count_home_transform', 'count_work_total', 'count_work_transform', 'count_worker_total', 'count_worker_transform'), 
				$id);
		}
		
		public static function getDefaultStation()
		{
			$station = new Station();
			$station->POVN = 0;
			$station->PWN = 0;
			$station->IWD = 0;
			$station->MNG = 0;
			$station->area_cultivated_home = 0;
			$station->area_cultivated_work = 0;
			$station->area_cultivated_mixed = 0;
			$station->area_undeveloped_urban = 0;
			$station->area_undeveloped_rural = 0;
			$station->transform_area_cultivated_home = 0;
			$station->transform_area_cultivated_work = 0;
			$station->transform_area_cultivated_mixed = 0;
			$station->transform_area_undeveloped_urban = 0;
			$station->transform_area_undeveloped_rural = 0;
			$station->count_home_total = 0;
			$station->count_home_transform = 0;
			$station->count_work_total = 0;
			$station->count_work_transform = 0;
			$station->count_worker_total = 0;
			$station->count_worker_transform = 0;
			return $station;
		}
		
		public static function getInitialCitizenCount($station_id)
		{
			if (isset($station_id))
			{
				$db = Database::getDatabase();
				$query = "
					SELECT ROUND(Station.count_home_total * Constants.average_citizens_per_home) AS InitialCitizenCount
					FROM Constants, Station
					WHERE Station.id = :station_id;";
				$args = array('station_id' => $station_id);
				$result = $db->query($query, $args);
				return $db->getValue($result);
			}
			return null;
		}
		
		public static function getInitialWorkerCount($station_id)
		{
			if (isset($station_id))
			{
				$db = Database::getDatabase();
				$query = "
					SELECT ROUND(Station.count_work_total * Constants.average_workers_per_bvo) AS InitialWorkersCount
					FROM Constants, Station
					WHERE Station.id = :station_id;";
				$args = array('station_id' => $station_id);
				$result = $db->query($query, $args);
				return $db->getValue($result);
			}
			return null;
		}
		
		public static function GetInitialTravelerCount($station_id)
		{

			if (isset($station_id))
			{
				$db = Database::getDatabase();
				$query = "
					SELECT
					ROUND
					(
						Station.area_cultivated_mixed * Constants.average_travelers_per_ha_leisure 
						+
						Station.count_home_total * Constants.average_citizens_per_home * Constants.average_travelers_per_citizen
						+
						Station.count_work_total * Constants.average_workers_per_bvo * Constants.average_travelers_per_worker
					) AS TravelerCount
					FROM Constants, Station
					WHERE Station.id = :station_id;";
				$args = array('station_id' => $station_id);
				$result = $db->query($query, $args);
				return $db->getValue($result);
			}
			return null;
		}
		
		public static function getInitialPOVNByStationInstanceId($stationInstanceId)
		{
			if (isset($stationInstanceId))
			{
				$db = Database::getDatabase();
				$query = "
					SELECT POVN
					FROM Station
					INNER JOIN StationInstance ON Station.id = StationInstance.station_id
					WHERE StationInstance.id = :station_instance_id;";
				$args = array('station_instance_id' => $stationInstanceId);
				$result = $db->query($query, $args);
				return $db->getValue($result);
			}
			return null;
		}
		
		public static function rowCount()
		{
			$db = Database::getDatabase();
			return $db->getValue("SELECT COUNT(*) FROM station");
		}
		
		public static function isStationCodeUnique($code, $id)
		{
			$db = Database::getDatabase();
			$query = "
				SELECT COUNT(*)
				FROM Station
				WHERE name = :code AND id != :id";
			$args = array('code' => $code, 'id' => $id);
			$result = $db->query($query, $args);
			return $db->getValue($result) == 0;
		}
		
		public static function isStationNameUnique($name, $id)
		{
			$db = Database::getDatabase();
			$query = "
				SELECT COUNT(*)
				FROM Station
				WHERE name = :name AND id != :id";
			$args = array('name' => $name, 'id' => $id);
			$result = $db->query($query, $args);
			return $db->getValue($result) == 0;
		}
		
		public static function getAllStations()
		{
			return DBObject::glob("Station", "SELECT * FROM  `station` ORDER BY `name`, `variant` ASC");
		}
		
		public static function getStations($fromIndex, $numberOfRecords)
		{
			$db = Database::getDatabase();
			$query = "
				SELECT *
				FROM Station
				ORDER BY code ASC LIMIT :from_index, :number_of_records;";
			$args = array('from_index' => $fromIndex, 'number_of_records' => $numberOfRecords);
			$result = $db->query($query, $args);
			return DBObject::glob("Station", $result);
		}
		
		public static function getStationById($id)
		{
			$db = Database::getDatabase();
			$query = "
				SELECT *
				FROM Station
				WHERE Station.id = :station_id;";
			$args = array('station_id' => $id);
			$result = $db->query($query, $args);
			return DBObject::glob("Station", $result);
		}
		
		public static function getStationByCode($code)
		{
			$db = Database::getDatabase();
			$query = "
				SELECT *
				FROM Station
				WHERE Station.code = :station_code
				LIMIT 0, 1;";
			$args = array('station_code' => $code);
			$result = $db->query($query, $args);
			return DBObject::glob("Station", $result);
		}
		
		public static function getStationsOfScenario($scenarioId)
		{
			$db = Database::getDatabase();
			$query = "
				SELECT Station.* 
				FROM Station 
				INNER JOIN ScenarioStation ON Station.id = ScenarioStation.station_id
				WHERE ScenarioStation.scenario_id = :scenario_id
				ORDER BY ScenarioStation.order;";
			$args = array('scenario_id' => $scenarioId);
			$result = $db->query($query, $args);
			return DBObject::glob("Station", $result);
		}
		
		public static function getStationsNotOfScenario($scenarioId)
		{
			$db = Database::getDatabase();
			$query = "
				SELECT Station.*
				FROM Station
				WHERE Station.id NOT IN (SELECT station_id FROM
												  ScenarioStation
												  WHERE scenario_id = :scenario_id)";
			$args = array('scenario_id' => $scenarioId);
			$result = $db->query($query, $args);
			return DBObject::glob("Station", $result);
		}
		
		public static function getStationsUsedInGame($gameId)
		{
			if (isset($gameId))
			{
				$db = Database::getDatabase();
				$query = "
					SELECT Station.* 
					FROM Station 
					INNER JOIN StationInstance ON Station.id = StationInstance.station_id
					INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id
					WHERE TeamInstance.game_id = :game_id;";
				$args = array('game_id' => $gameId);
				$result = $db->query($query, $args);
				return DBObject::glob("Station", $result);
			}
			return null;
		}
		
		public static function getStationNamesByTeam($teamId)
		{
			$db = Database::getDatabase();
			$query =  "
				SELECT Station.*
				FROM Station 
				INNER JOIN StationInstance ON Station.id = StationInstance.station_id
				INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id
				WHERE TeamInstance.id = :team_id;";
			$args = array('team_id' => $teamId);
			$result = $db->query($query, $args);
			return DBObject::glob("Station", $result);
		}
		
		public static function getStationInstanceId($stationId)
		{
			$db = Database::getDatabase();
			$query = "
				SELECT StationInstance.id
				FROM StationInstance
				INNER JOIN Station ON StationInstance.station_id = Station.id
				WHERE Station.id = :station_id";
			$args = array('station_id' => $stationId);
			$result = $db->query($query, $args);
			return $db->getValue($result);
		}

		
		public static function getStationsAndPOVNUsedInGame($gameId)
		{
			if (isset($gameId))
			{
				$db = Database::getDatabase();
				$query = "
					SELECT Station.id, Station.name, RoundInstance.POVN
					FROM Station 
					INNER JOIN StationInstance ON Station.id = StationInstance.station_id
					INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id
					INNER JOIN RoundInstance ON StationInstance.id = RoundInstance.station_instance_id
					INNER JOIN Round ON RoundInstance.round_id = Round.id
					INNER JOIN RoundInfo ON Round.round_info_id = RoundInfo.id
					INNER JOIN Game ON RoundInfo.id = Game.current_round_id AND TeamInstance.game_id = Game.id
					WHERE Game.id = :game_id
					ORDER BY Station.code ASC;";
				$args = array('game_id' => $gameId);
				$result = $db->query($query, $args);
				return $result;
			}
			return null;
		}
		
		public static function isStationInUse($station_id)
		{
			$db = Database::getDatabase();
			$result = $db->query("
				SELECT COUNT(*)
				FROM Station
				LEFT OUTER JOIN ScenarioStation ON Station.id = ScenarioStation.station_id
				LEFT OUTER JOIN Scenario ON ScenarioStation.scenario_id = Scenario.id
				RIGHT OUTER JOIN Game ON Game.scenario_id = Scenario.id
				WHERE Station.id = :station_id",
				array('station_id' => $station_id));
			return ($db->getValue($result) != 0);
		}
		
		public static function getGamesByStation($station_id)
		{
			$db = Database::getDatabase();
			$result = $db->query("
				SELECT Game.id, Game.name
				FROM Station
				LEFT OUTER JOIN ScenarioStation ON Station.id = ScenarioStation.station_id
				LEFT OUTER JOIN Scenario ON ScenarioStation.scenario_id = Scenario.id
				RIGHT OUTER JOIN Game ON Game.scenario_id = Scenario.id
				WHERE Station.id = :station_id",
				array('station_id' => $station_id));
			return $result;
		}
	}
	
	class Scenario extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('Scenario', array('id', 'train_table_id', 'name', 'description', 'init_map_position_x', 'init_map_position_y', 'init_map_scale'), $id);
		}
		
		public static function getScenarioById($id)
		{
			$db = Database::getDatabase();
			$query = "
				SELECT *
				FROM Scenario
				WHERE Scenario.id = :scenario_id;";
			$args = array('scenario_id' => $id);
			$result = $db->query($query, $args);
			return DBObject::glob("Scenario", $result);
		}
		
		public static function rowCount()
		{
			$db = Database::getDatabase();
			return $db->getValue("SELECT COUNT(*) FROM scenario");
		}
		
		public static function getScenarios($fromIndex, $numberOfRecords)
		{
			return DBObject::glob("Scenario", "SELECT * FROM `scenario` LIMIT " . $fromIndex . " , " . $numberOfRecords);
		}
		
		public static function getAllScenarios()
		{
			return DBObject::glob("Scenario", "SELECT * FROM `scenario`");
		}
		
		public static function getCurrentScenario()
		{
			$db = Database::getDatabase();
			$result = $db ->query("
				SELECT Scenario.* 
				FROM Scenario
				INNER JOIN Game
				ON Scenario.id = Game.scenario_id
				INNER JOIN TeamInstance
				ON Game.id = TeamInstance.game_id
				INNER JOIN ClientSession
				ON ClientSession.team_instance_id = TeamInstance.id
				WHERE ClientSession.id = :session_id", 
				array('session_id' => session_id()));
			return DBObject::glob("Scenario", $result);
		}
		
		public static function getScenarioOfGame($game_id)
		{
			$db = Database::getDatabase();
			$result = $db ->query("
				SELECT Scenario.* 
				FROM Scenario
				INNER JOIN Game
				ON Scenario.id = Game.scenario_id
				WHERE Game.id = :game_id", 
				array('game_id' => $game_id));
			return DBObject::glob("Scenario", $result);
		}
		public static function isScenarioInUse($scenario_id)
		{
			$db = Database::getDatabase();
			$result = $db -> query("
				SELECT COUNT(*)
				FROM GAME
				LEFT OUTER JOIN Scenario
				ON Game.scenario_id = Scenario.id
				WHERE Game.scenario_id = :scenario_id",
				array('scenario_id' => $scenario_id));
			return $db->getValue($result) != 0;
		}
		
		public static function isScenarioNameUnique($name, $id)
		{
			$db = Database::getDatabase();
			$query = "
				SELECT COUNT(*)
				FROM Scenario
				WHERE name = :name AND id != :id";
			$args = array('name' => $name, 'id' => $id);
			$result = $db->query($query, $args);
			return $db->getValue($result) == 0;
		}
	}
	
	class ScenarioStation extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('ScenarioStation', array('id', 'order', 'scenario_id', 'station_id'), $id);
		}
		
		public static function setStationsForScenario($scenario_id, $stations)
		{
			$db = Database::getDatabase();
			//Remove old station order
			$delete = $db->query(" 
			DELETE 
			FROM ScenarioStation
			WHERE scenario_id = :scenario_id",
			array('scenario_id' => $scenario_id));
			
			$i = 1;
			foreach($stations as $station)
			{
				$insert = $db->query("
				INSERT INTO ScenarioStation (`order`, `scenario_id`, `station_id`)
				VALUES (:i, :scenario_id, :station_id)",
				array('i' => $i, 'scenario_id' => $scenario_id, 'station_id' => $station->id));
				$i++;
			}
		}
	}
	
	class Game extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('Game', array('id', 'name', 'notes', 'starttime', 'current_round_id', 'active'), $id);
		}
		
		public static function rowCount()
		{
			$db = Database::getDatabase();
			return $db->getValue("SELECT COUNT(*) FROM game");
		}
		
		public static function getGames($fromIndex, $numberOfRecords)
		{
			return DBObject::glob("Game", "SELECT * FROM `game` ORDER BY `active` DESC, `starttime` DESC LIMIT " . $fromIndex . " , " . $numberOfRecords);
		}
		
		public static function getActiveGames()
		{
			return DBObject::glob("Game", "SELECT * FROM `game` WHERE `active` = 1 ORDER BY `name` ASC;");
		}
		
		public static function getGameIdOfSession($session_id)
		{
			$db = Database::getDatabase();
			$result = $db ->query("
				SELECT game_id 
				FROM TeamInstance 
				INNER JOIN ClientSession
				ON ClientSession.team_instance_id = TeamInstance.id
				WHERE ClientSession.id = :session_id", 
				array('session_id' => $session_id));
			return $db->getValue($result);
		}
		
		public static function deleteGameById($id)
		{
			$db = Database::getDatabase();
			
			$db->query("DELETE Program
			FROM Program, RoundInstance
			INNER JOIN StationInstance ON RoundInstance.station_instance_id = StationInstance.id
			INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id
			WHERE TeamInstance.game_id = :id AND 
				(Program.id = RoundInstance.plan_program_id OR 
				Program.id = RoundInstance.exec_program_id OR
				Program.id = StationInstance.program_id)",
			array('id' => $id));
			
			$db->query("DELETE TeamInstance
			FROM TeamInstance
			WHERE TeamInstance.game_id = :id",
			array('id' => $id));
			
			$db->query("DELETE
			FROM RoundInfoInstance
			WHERE RoundInfoInstance.game_id = :id",
			array('id' => $id));
			
			$db->query("
			DELETE 
			FROM Game
			WHERE Game.id = :id",
			array('id' => $id));
		}
	}
	
	class Round extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('Round', 
				array('id', 'station_id', 'round_info_id', 'description', 'new_transform_area', 'POVN', 'PWN'), $id);
		}
		
		public static function getRoundsByStation($station_id)
		{
			$db = Database::getDatabase();
			$query = "
				SELECT *
				FROM Round
				WHERE station_id = :stationId;";
			$args = array('stationId' => $station_id);
			$result = $db->query($query, $args);
			return DBObject::glob("Round", $result);
		}
		
		public static function getUsedRoundInfos()
		{
			$db = Database::getDatabase();
			$query = "
				SELECT RoundInfo.*
				FROM RoundInfo
				INNER JOIN Round ON RoundInfo.id = Round.round_info_id
				GROUP BY RoundInfo.id";
			return DBObject::glob("RoundInfo", $query);
		}
	}
	
	class Demand extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('Demand', 
				array('id', 'scenario_id', 'round_info_id', 'type_id', 'amount'), $id);
		}
		
		public static function getDemandForScenario($scenario_id)
		{
			$db = Database::getDatabase();
			$query = "
				SELECT *
				FROM demand
				WHERE scenario_id = :scenario_id
				";
			$args = array('scenario_id' => $scenario_id);
			$result = $db->query($query, $args);
			return DBObject::glob("Demand", $result);
		}
		
		public static function getDemandDescriptionForScenario($scenario_id)
		{
			$db = Database::getDatabase();
			$query = "
				SELECT Types.id, Types.name, Types.color, 
					GROUP_CONCAT(Demand.round_info_id ORDER BY Demand.round_info_id), 
					GROUP_CONCAT(Demand.amount ORDER BY Demand.round_info_id)
				FROM Demand 
				INNER JOIN Types ON Demand.type_id = Types.id
				INNER JOIN RoundInfo ON Demand.round_info_id = RoundInfo.id
				WHERE Demand.scenario_id = :scenarioId
				GROUP BY Types.id
				ORDER BY RoundInfo.number;";
			
			$args = array('scenarioId' => $scenario_id);
			$result = $db->query($query, $args);
			return $result;
		}
	}
	
	class RoundInfoInstance extends DBObject
	{
		public function __construct($id = null)
		{
			parent:: __construct('RoundInfoInstance',
				array('id', 'game_id', 'round_info_id', 'mobility_report'), $id);
		}
		
		public static function getMobilityReport($gameId)
		{
			$db = Database::getDatabase();
			$query = "
				SELECT RoundInfoInstance.mobility_report
				FROM RoundInfoInstance
				INNER JOIN Game ON RoundInfoInstance.game_id = Game.id
					AND RoundInfoInstance.round_info_id = Game.current_round_id
				WHERE Game.id = :game_id";
			$args = array('game_id' => $gameId);
			$result = $db->query($query, $args);
			return $db->getValue($result);
		}
		
		public static function getCurrentRoundInfoInstanceIdBySessionId($session_id)
		{
			$db = Database::getDatabase();
			$result = $db ->query("
				SELECT RoundInfoInstance.id 
				FROM RoundInfoInstance
				INNER JOIN Game 
				ON Game.current_round_id = RoundInfoInstance.round_info_id
				AND Game.id = RoundInfoInstance.game_id
				INNER JOIN TeamInstance 
				ON TeamInstance.game_id = Game.id 
				INNER JOIN ClientSession 
				ON ClientSession.team_instance_id = TeamInstance.id 
				WHERE ClientSession.id = :session_id", 
				array('session_id' => $session_id));
			if ($db->getValue($result) == "")
				return 0;
			return $db->getValue($result);
		}
		
		public static function getFromGameIdAndRoundInfoId($game_id, $round_info_id)
		{
			$db = Database::getDatabase();
			$result = $db ->query("
				SELECT RoundInfoInstance.id 
				FROM RoundInfoInstance
				WHERE game_id = :game_id
				AND round_info_id = :round_info_id",
				array('game_id' => $game_id, 'round_info_id' => $round_info_id));
			if ($db->getValue($result) == "")
				return 0;
			return $db->getValue($result);
		}
	}
	
	class Value extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('Value', array('id', 'title', 'description', 'type'), $id);
		}
		
		public static function getAreaValues()
		{
			return DBObject::glob("Value", "SELECT * FROM value WHERE type = 'area'");
		}
		
		public static function getMobilityValues()
		{
			return DBObject::glob("Value", "SELECT * FROM value WHERE type = 'mobility'");
		}
		
		public static function getValueDescription($valueId)
		{
			$db = Database::getDatabase();
			$query = "SELECT title FROM value WHERE id = :value_id;";
			$args = array('value_id' => $valueId);
			$result = $db->query($query, $args);
			return $db->getValue($result);
		}
	}
	
	class ValueInstance extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('ValueInstance', array('id', 'value_id', 'team_instance_id', 'checked'), $id);
		}
		
		public static function getValueInstances()
		{
			return DBObject::glob("ValueInstance", "SELECT * FROM ValueInstance");
		}
		
		public static function getValuesByGameAndTeam($gameId, $teamId)
		{
			$db = Database::getDatabase();
			$query = "
				SELECT ValueInstance.id, Value.title, ValueInstance.checked
				FROM ValueInstance
				INNER JOIN Value ON ValueInstance.value_id = Value.id
				INNER JOIN TeamInstance ON ValueInstance.team_instance_id = TeamInstance.id
				WHERE TeamInstance.game_id = :game_id
					AND TeamInstance.team_id = :team_id";
			$args = array(
				'game_id' => $gameId,
				'team_id' => $teamId);
			$result = $db->query($query, $args);
			return $result;
		}
		
		public static function getCheckedValuesByTeam($teamInstanceId)
		{
			$db = Database::getDatabase();
			$query = "
				SELECT ValueInstance.* 
				FROM ValueInstance 
				WHERE checked = 1 
					AND team_instance_id = :team_instance_id";
			$args = array('team_instance_id' => $teamInstanceId);
			$result = $db->query($query, $args);
			return DBObject::glob("ValueInstance", $result);
		}
	
	}
	
	class RoundInfo extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('RoundInfo', array('id', 'number', 'name', 'description'), $id);
		}
		
		public static function rowCount()
		{
			$db = Database::getDatabase();
			return $db->getValue("SELECT COUNT(*) FROM roundinfo");
		}
		
		public static function getRounds()
		{
			return DBObject::glob("RoundInfo", "SELECT * FROM  `roundinfo` ORDER BY `number` ASC");
		}
		
		public static function getRoundId($roundNumber)
		{
			$db = Database::getDatabase();
			$query = "SELECT id FROM RoundInfo ORDER BY number ASC LIMIT :round_number, 1";
			$args = array('round_number' => $roundNumber);
			$result = $db->query($query, $args);
			return $db->getValue($result);
		}
		
		public static function getCurrentRoundIdByGameId($game_id)
		{
			$db = Database::getDatabase();
			$result = $db ->query("
				SELECT RoundInfo.id 
				FROM RoundInfo 
				INNER JOIN Game 
				ON Game.current_round_id = RoundInfo.id 
				WHERE Game.id = :game_id", 
				array('game_id' => $game_id));
			return $db->getValue($result);
		}
		
		public static function getCurrentRoundIdBySessionId($session_id)
		{
			$db = Database::getDatabase();
			$result = $db ->query("
				SELECT RoundInfo.id 
				FROM RoundInfo 
				INNER JOIN Game 
				ON Game.current_round_id = RoundInfo.id 
				INNER JOIN TeamInstance 
				ON TeamInstance.game_id = Game.id 
				INNER JOIN ClientSession 
				ON ClientSession.team_instance_id = TeamInstance.id 
				WHERE ClientSession.id = :session_id", 
				array('session_id' => $session_id));
			if ($db->getValue($result) == "")
				return 0;
			return $db->getValue($result);
		}
		
		public static function getCurrentRoundNameBySessionId($session_id)
		{
			$db = Database::getDatabase();
			$result = $db ->query("
				SELECT RoundInfo.name 
				FROM RoundInfo 
				INNER JOIN Game 
				ON Game.current_round_id = RoundInfo.id 
				INNER JOIN TeamInstance 
				ON TeamInstance.game_id = Game.id 
				INNER JOIN ClientSession 
				ON ClientSession.team_instance_id = TeamInstance.id 
				WHERE ClientSession.id = :session_id", 
				array('session_id' => $session_id));
			if ($db->getValue($result) == "")
				return 0;
			return $db->getValue($result);
		}
		
		public static function getRoundInfoIdAfter($roundInfoId)
		{
			$db = Database::getDatabase();
			$query = "
				SELECT RoundInfo.id
				FROM RoundInfo
				INNER JOIN RoundInfo AS RoundInfo2 ON RoundInfo2.id = :round_info_id
				WHERE RoundInfo.number > RoundInfo2.number
				ORDER BY RoundInfo.number ASC
				LIMIT 0, 1";
			$args = array('round_info_id' => $roundInfoId);
			$result = $db->query($query, $args);
			return $db->getValue($result);
		}
		
		public static function getRoundInfoIdBefore($roundInfoId)
		{
			$db = Database::getDatabase();
			$query = "
				SELECT RoundInfo.id
				FROM RoundInfo
				INNER JOIN RoundInfo AS RoundInfo2 ON RoundInfo2.id = :round_info_id
				WHERE RoundInfo.number < RoundInfo2.number
				ORDER BY RoundInfo.number DESC
				LIMIT 0, 1";
			$args = array('round_info_id' => $roundInfoId);
			$result = $db->query($query, $args);
			return $db->getValue($result);
		}
	}
	
	class StationInstance extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('StationInstance', array('id', 'station_id', 'team_instance_id', 'initial_POVN'), $id);
		}
		
		public static function rowCountByGame($gameId)
		{
			$db = Database::getDatabase();
			$query = "
				SELECT COUNT(*) 
				FROM StationInstance
				INNER JOIN TeamInstance
				ON TeamInstance.id=StationInstance.team_instance_id
				WHERE TeamInstance.game_id = :game_id";
			$args = array('game_id' => $gameId);
			$result = $db->query($query, $args);
			return $db->getValue($result);
		}
	}
	
	class RoundInstance extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('RoundInstance', array('id', 'round_id', 'station_instance_id', 'plan_program_id', 'exec_program_id', 'starttime', 'POVN'), $id);
		}
		
		public static function getCommittedRounds($gameId, $roundId)
		{
			if ($roundId == "")
				$round_id_condition = "IS NULL";
			else
				$round_id_condition = "= " . $roundId;
			$db = Database::getDatabase();
			$query = "
				SELECT count(*)
				FROM RoundInstance
				INNER JOIN StationInstance ON StationInstance.id = RoundInstance.station_instance_id
				INNER JOIN TeamInstance ON TeamInstance.id = StationInstance.team_instance_id
				WHERE TeamInstance.game_id = :game_id
					AND RoundInstance.round_id " . $round_id_condition . " 
					AND RoundInstance.plan_program_id IS NOT NULL";
			$args = array('game_id' => $gameId);
			$result = $db->query($query, $args);
			return $db->getValue($result);
		}
		
		public static function getCurrentRoundInstances($game_id)
		{
			$db = Database::getDatabase();
			$query = "
				SELECT RoundInstance.*
				FROM RoundInstance
				INNER JOIN Round ON RoundInstance.round_id = Round.id
				INNER JOIN RoundInfo ON Round.round_info_id = RoundInfo.id
				INNER JOIN StationInstance ON RoundInstance.station_instance_id = StationInstance.id
				INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id
				INNER JOIN Game ON RoundInfo.id = Game.current_round_id AND TeamInstance.game_id = Game.id
				INNER JOIN Station ON StationInstance.station_id = Station.id
				WHERE Game.id = :game_id;";
			$args = array('game_id' => $game_id);
			$result = $db->query($query, $args);
			return DBObject::glob("RoundInstance", $result);
		}
		
		public static function getRoundInstances($game_id, $round_info_id)
		{
			$db = Database::getDatabase();
			$query = "
				SELECT RoundInstance.*
				FROM RoundInstance
				INNER JOIN Round ON RoundInstance.round_id = Round.id
				INNER JOIN StationInstance ON RoundInstance.station_instance_id = StationInstance.id
				INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id
				WHERE TeamInstance.game_id = :game_id
					AND Round.round_info_id = :round_info_id;";
			$args = array(
				'game_id' => $game_id,
				'round_info_id' => $round_info_id);
			$result = $db->query($query, $args);
			return DBObject::glob("RoundInstance", $result);
		}
		
		public static function getStationAppliedPrograms($stationid, $roundId)
		{
			
		}
	}
	
	class Facility extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('Facility', array('id', 'name', 'description', 'image', 'citizens', 'workers', 'travelers'), $id);
		}
		
		public static function getAllFacilities()
		{
			return DBObject::glob("Facility", "SELECT * FROM `facility`;");
		}
		
		public static function getFacilitiesInGame($game_id)
		{
			$db = Database::getDatabase();
			$query = "
				SELECT RoundInfo.name AS Round, Station.name AS Station, Facility.name AS Facility
				FROM Game
				INNER JOIN TeamInstance ON Game.id = TeamInstance.game_id
				INNER JOIN StationInstance ON TeamInstance.id = StationInstance.team_instance_id
				INNER JOIN RoundInstance ON StationInstance.id = RoundInstance.station_instance_id
				INNER JOIN Round ON RoundInstance.round_id = Round.id
				INNER JOIN RoundInfo ON Round.round_info_id = RoundInfo.id
				INNER JOIN Station ON StationInstance.station_id = Station.id
				INNER JOIN FacilityInstance ON RoundInstance.id = FacilityInstance.round_instance_id
				INNER JOIN Facility ON FacilityInstance.facility_id = Facility.id
				WHERE Game.id = :game_id AND RoundInfo.id <= Game.current_round_id;";
			$args = array('game_id' => $game_id);
			return $db->query($query, $args);
		}
		
		public static function getFacilityById($id)
		{
			$db = Database::getDatabase();
			$query = "
				SELECT *
				FROM Facility
				WHERE Facility.id = :facility_id;";
			$args = array('facility_id' => $id);
			$result = $db->query($query, $args);
			return DBObject::glob("Facility", $result);
		}
		
		public static function addFacilityToStation($game_id, $facility_id, $station_id)
		{
			$db = Database::getDatabase();
			$query = "
				INSERT INTO FacilityInstance (round_instance_id, facility_id)
				SELECT RoundInstance.id, :facility_id
				FROM RoundInstance
				INNER JOIN StationInstance ON RoundInstance.station_instance_id = StationInstance.id
				INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id
				INNER JOIN Game ON TeamInstance.game_id = Game.id
				INNER JOIN RoundInfo ON Game.current_round_id = RoundInfo.id
				INNER JOIN Round ON RoundInfo.id = Round.round_info_id AND RoundInstance.round_id = Round.id
				WHERE 
					StationInstance.station_id = :station_id AND
					Game.id = :game_id;";
			$args = array(
				'game_id' => $game_id,
				'facility_id' => $facility_id,
				'station_id' => $station_id);
			$db->query($query, $args);
		}
	}
	
	class TypeRestriction extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('TypeRestriction', array('id', 'station_instance_id', 'from_round_info_id', 'to_round_info_id', 'type_id'), $id);
		}
		
		public static function getActiveRestrictionsInGame($game_id)
		{
			$db = Database::getDatabase();
			$query = "
				SELECT Station.name AS Station, Station.id AS StationId, Types.name AS Type, Types.id AS TypeId
				FROM TypeRestriction
				INNER JOIN StationInstance ON TypeRestriction.station_instance_id = StationInstance.id
				INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id
				INNER JOIN Game ON TeamInstance.game_id = Game.id
				INNER JOIN Station ON StationInstance.station_id = Station.id
				INNER JOIN Types ON TypeRestriction.type_id = Types.id
				WHERE 
					Game.id = :game_id AND
					TypeRestriction.from_round_info_id <= Game.current_round_id AND
					(ISNULL(TypeRestriction.to_round_info_id) OR TypeRestriction.to_round_info_id > Game.current_round_id)
				ORDER BY Station.name, Types.id";
			$args = array('game_id' => $game_id);
			return $db->query($query, $args);
		}
		
		public static function isActive($game_id, $station_id, $type_id)
		{
			$db = Database::getDatabase();
			$query = "
				SELECT TypeRestriction.id
				FROM TypeRestriction
				INNER JOIN StationInstance ON TypeRestriction.station_instance_id = StationInstance.id
				INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id
				INNER JOIN Game ON TeamInstance.game_id = Game.id
				INNER JOIN Station ON StationInstance.station_id = Station.id
				INNER JOIN Types ON TypeRestriction.type_id = Types.id
				WHERE 
					Game.id = :game_id AND 
					StationInstance.station_id = :station_id AND 
					TypeRestriction.type_id = :type_id AND 
					TypeRestriction.from_round_info_id <= Game.current_round_id AND 
					(ISNULL(TypeRestriction.to_round_info_id) OR TypeRestriction.to_round_info_id > Game.current_round_id)";
			$args = array(
				'game_id' => $game_id,
				'station_id' => $station_id,
				'type_id' => $type_id);
			$result = $db->query($query, $args);
			return mysql_num_rows($result) > 0;
		}
		
		public static function addRestriction($game_id, $station_id, $type_id)
		{
			$db = Database::getDatabase();
			$query = "
				INSERT INTO TypeRestriction (station_instance_id, from_round_info_id, type_id)
				SELECT StationInstance.id, Game.current_round_id, :type_id
				FROM StationInstance
				INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id
				INNER JOIN Game ON TeamInstance.game_id = Game.id
				WHERE
					Game.id = :game_id AND  
					StationInstance.station_id = :station_id;";
			$args = array(
				'game_id' => $game_id,
				'station_id' => $station_id,
				'type_id' => $type_id);
			$db->query($query, $args);
		}
		
		public static function removeRestriction($game_id, $station_id, $type_id)
		{
			$db = Database::getDatabase();
			$query = "
				UPDATE TypeRestriction
				INNER JOIN StationInstance ON TypeRestriction.station_instance_id = StationInstance.id
				INNER JOIN TeamInstance ON StationInstance.team_instance_id = TeamInstance.id
				INNER JOIN Game ON TeamInstance.game_id = Game.id
				SET to_round_info_id = Game.current_round_id
				WHERE
					Game.id = :game_id AND  
					StationInstance.station_id = :station_id AND
					TypeRestriction.type_id = :type_id;";
			$args = array(
				'game_id' => $game_id,
				'station_id' => $station_id,
				'type_id' => $type_id);
			$db->query($query, $args);
		}
	}
	
	class Type extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('Type', array('id', 'name', 'description', 'color', 'image', 'area_density', 'people_density', 'povn'), $id);
		}
		
		public static function getTypes()
		{
			return DBObject::glob("Type", "SELECT * FROM types");
		}
		
		public static function getSpecificTypes()
		{
			return DBObject::glob("Type", "SELECT * FROM types WHERE type = 'home' OR type = 'work' OR type = 'leisure'");
		}
		
		public static function getAverageTypes()
		{
			return DBObject::glob("Type", "SELECT * FROM types WHERE type = 'average_home' OR type = 'average_work' OR type = 'average_leisure'");
		}
		
		public static function getTypeById($id)
		{
			$db = Database::getDatabase();
			$query = "
				SELECT *
				FROM Type
				WHERE Type.id = :type_id;";
			$args = array('type_id' => $id);
			$result = $db->query($query, $args);
			return DBObject::glob("Type", $result);
		}
	}
	
	class StationTypes extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('StationTypes', array('id', 'name', 'description', 'image', 'POVN', 'PWN', 'IWD', 'MNG'), $id);
		}
		
		public static function getAllStationTypes()
		{
			return DBObject::glob("StationTypes", "SELECT * FROM stationtypes");
		}
	}
	
	class Program extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('Program', array('id', 'area_home', 'area_work', 'area_leisure', 'type_home', 'type_work', 'type_leisure'), $id);
		}
		
		public static function isOwnedBySession($id, $session_id)
		{
			$db = Database::getDatabase();
			
			// first attempt: stations
			$result = $db->query("
					SELECT COUNT(*) 
					FROM ClientSession 
					INNER JOIN TeamInstance 
					ON ClientSession.team_instance_id = TeamInstance.id 
					INNER JOIN StationInstance 
					ON TeamInstance.id = StationInstance.team_instance_id 
					INNER JOIN Program 
					ON StationInstance.program_id = Program.id 
					WHERE ClientSession.id = :session_id AND 
					Program.id = :id;", 
					array('session_id' => $session_id, 'id' => $id));
			if ($db->getValue($result) > 0)
				return true;
			
			// second attempt: rounds
			$result = $db->query("
					SELECT COUNT(*) 
					FROM ClientSession 
					INNER JOIN TeamInstance 
					ON ClientSession.team_instance_id = TeamInstance.id 
					INNER JOIN StationInstance 
					ON TeamInstance.id = StationInstance.team_instance_id 
					INNER JOIN RoundInstance
					ON StationInstance.id = RoundInstance.station_instance_id
					INNER JOIN Program 
					ON RoundInstance.plan_program_id = Program.id 
					WHERE ClientSession.id = :session_id AND 
					Program.id = :id;", 
					array('session_id' => $session_id, 'id' => $id));
			return $db->getValue($result) > 0;
		}
		
		public static function getMasterplan($stationId)
		{
			$db = Database::getDatabase();
			$query = "
				SELECT *
				FROM Program
				INNER JOIN StationInstance ON Program.id = StationInstance.program_id
				WHERE StationInstance.id = :station_id";
			$args = array('station_id' => $stationId);
			$result = $db->query($query, $args);
			return DBObject::glob("Program", $result);

		}
		
		public static function getStationAppliedPrograms($stationId, $roundId)
		{
			$db = Database::getDatabase();
			$query = "
				SELECT Program.*
				FROM Program
				INNER JOIN RoundInstance ON Program.id = RoundInstance.exec_program_id
				INNER JOIN Round ON RoundInstance.round_id = Round.id
				INNER JOIN StationInstance ON RoundInstance.station_instance_id = StationInstance.id
				WHERE Round.round_info_id < :round_id 
					AND StationInstance.id = :station_id;";
			$args = array(
				'round_id' => $roundId,
				'station_id' => $stationId);
			$result = $db->query($query, $args);
			return DBObject::glob("Program", $result);
		}
		
		public static function areFutureProgramsFilled($programId)
		{
			$db = Database::getDatabase();
			$query = "
				SELECT *
				FROM Program AS Program1
				INNER JOIN RoundInstance AS RoundInstance1 ON Program1.id = RoundInstance1.plan_program_id OR Program1.id = RoundInstance1.exec_program_id
				INNER JOIN Round AS Round1 ON RoundInstance1.round_id = Round1.id
				INNER JOIN RoundInfo AS RoundInfo1 ON Round1.round_info_id = RoundInfo1.id
				INNER JOIN RoundInfo AS RoundInfo2 ON RoundInfo2.number > RoundInfo1.number
				INNER JOIN Round AS Round2 ON RoundInfo2.id = Round2.round_info_id AND Round2.station_id = Round1.station_id
				INNER JOIN RoundInstance AS RoundInstance2 ON Round2.id = RoundInstance2.round_id AND RoundInstance2.station_instance_id = RoundInstance1.station_instance_id
				INNER JOIN Program AS Program2 ON RoundInstance2.plan_program_id = Program2.id
				WHERE Program1.id = :program_id AND (Program2.area_home != 0 OR Program2.area_work != 0 OR Program2.area_leisure != 0);";
			$args = array('program_id' => $programId);
			$result = $db->query($query, $args);
			return mysql_num_rows($result) > 0;
		}
	}
	
	class TrainTable extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('TrainTable', array('id', 'filename', 'import_timestamp'), $id);
		}
		
		public function SetData($filename)
		{
			$this->filename = $filename;
			
			$this->save();
		}
		
		public function SetImportTimestamp()
		{
			$db = Database::getDatabase();
			$db->query("
				UPDATE TrainTable SET import_timestamp = NOW() WHERE id = :id",
				array('id' => $this->id));
		}
		
		public static function GetAllTrainTables()
		{
			return DBObject::glob("TrainTable", "SELECT * FROM TrainTable ORDER BY import_timestamp DESC");
		}
		
		public static function GetTrainTableIdOfGame($game_id)
		{
			$db = Database::getDatabase();
			$result = $db ->query("
				SELECT train_table_id 
				FROM Scenario
				INNER JOIN Game ON Game.scenario_id = Scenario.id
				WHERE Game.id = :game_id",
				array('game_id' => $game_id));
			if ($db->getValue($result) == "")
				return 0;
			return $db->getValue($result);
		}
	}
	
	class TrainTableTrain extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('TrainTableTrain', array('id', 'train_table_id', 'name', 'type'), $id);
		}
		
		public function SetData($train_table_id, $name, $type)
		{
			$this->train_table_id = $train_table_id;
			$this->name = $name;
			$this->type = $type;
			
			$this->save();
		}
	}
		
	class TrainTableStation extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('TrainTableStation', array('id', 'train_table_id', 'code', 'name', 'chain', 'travelers'), $id);
		}
		
		public function SetData($train_table_id, $code, $name)
		{
			$this->train_table_id = $train_table_id;
			$this->code = $code;
			$this->name = $name;
			
			$this->save();
		}
		
		public static function getStationByCode($train_table_id, $code)
		{
			$db = Database::getDatabase();
			$query = "
				SELECT *
				FROM TrainTableStation
				WHERE TrainTableStation.code = :station_code
				AND TrainTableStation.train_table_id = :train_table_id
				LIMIT 0, 1;";
			$args = array('station_code' => $code,
						  'train_table_id' => $train_table_id);
			$result = $db->query($query, $args);
			return DBObject::glob("TrainTableStation", $result);
		}

		public static function getStationByName($train_table_id, $name)
		{
			$db = Database::getDatabase();
			$query = "
				SELECT *
				FROM TrainTableStation
				WHERE TrainTableStation.name = :station_name
				AND TrainTableStation.train_table_id = :train_table_id
				LIMIT 0, 1;";
			$args = array('station_name' => $name,
						  'train_table_id' => $train_table_id);
			$result = $db->query($query, $args);
			return DBObject::glob("TrainTableStation", $result);
		}
		
		public function calculateNetworkValue()
		{
			
		}
	}
		
	class TrainTableEntry extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('TrainTableEntry', array('id', 'train_id', 'station_id', 'frequency'), $id);
		}
		
		public function SetData($train_id, $station_id, $frequency)
		{
			$db = Database::getDatabase();
			$db->query("
				SELECT * 
				FROM `TrainTableEntry` 
				WHERE train_id = :train_id 
				AND station_id = :station_id
				LIMIT 1",
				array('train_id' => $train_id, 'station_id' => $station_id));
			if($db->hasRows())
			{
				$row = $db->getRow();
				$this->load($row);
			}
			else
			{
				$this->train_id = $train_id;
				$this->station_id = $station_id;
			}
			$this->frequency = $frequency;
			
			$this->save();
		}
	}
	