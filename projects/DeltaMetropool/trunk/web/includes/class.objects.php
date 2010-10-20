<?PHP
	// Stick your DBOjbect subclasses in here (to help keep things tidy).

	class User extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('users', array('id', 'username', 'password', 'level', 'email'), $id);
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
			return DBObject::glob("Team", "SELECT * FROM team WHERE id > 0 ORDER BY `created` DESC LIMIT " . $fromIndex . " , " . $numberOfRecords);
		}
		
		public static function getTeamsInGame($gameId)
		{
			return DBObject::glob("Team", "
				SELECT Team.id, Team.name, Team.description, Team.cpu, Team.created
				FROM Team
				INNER JOIN TeamInstance
				ON TeamInstance.team_id=Team.id
				WHERE TeamInstance.game_id=" . $gameId . ";");
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
	}
	
	class Station extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('Station', array(
				'id', 'code', 'name', 
				'description_facts', 'description_background', 'description_future', 
				'image', 'town', 'region', 
				'POVN', 'PWN', 'IWD', 'MNG', 
				'area_cultivated_home', 'area_cultivated_work', 'area_cultivated_mixed', 'area_undeveloped_urban', 'area_undeveloped_rural',
				'transform_area_cultivated_home', 'transform_area_cultivated_work', 'transform_area_cultivated_mixed', 'transform_area_undeveloped_urban', 'transform_area_undeveloped_rural', 
				'count_home_total', 'count_home_transform', 'count_work_total', 'count_work_transform'), 
				$id);
		}
		
		public static function getInitialCitizenCount($id)
		{
			if (isset($id))
			{
				$db = Database::getDatabase();
				$query = "
					SELECT ROUND(Station.count_home_total * Constants.average_citizens_per_home) AS InitialCitizenCount
					FROM Constants, Station
					WHERE Station.id = :station_id;";
				$args = array('station_id' => $id);
				$result = $db->query($query, $args);
				return $db->getValue($result);
			}
			return null;
		}
		
		public static function getInitialWorkerCount($id)
		{
			if (isset($id))
			{
				$db = Database::getDatabase();
				$query = "
					SELECT ROUND(Station.count_work_total * Constants.average_workers_per_bvo) AS InitialWorkersCount
					FROM Constants, Station
					WHERE Station.id = :station_id;";
				$args = array('station_id' => $id);
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
		
		public static function getStations($fromIndex, $numberOfRecords)
		{
			return DBObject::glob("Station", "SELECT * FROM  `station` ORDER BY `code` ASC LIMIT " . $fromIndex . " , " . $numberOfRecords);
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
			return DBObject::glob("Round", "SELECT * FROM `round` WHERE station_id = " . $station_id);
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
		
		public static function getRoundId($round_number)
		{
			$db = Database::getDatabase();
			return $db->getValue("SELECT `id` FROM `roundinfo` ORDER BY `number` ASC LIMIT " . $round_number . ", 1");
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
	}
	
	class StationInstance extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('StationInstance', array('id', 'station_id', 'team_instance_id'), $id);
		}
		
		public static function rowCountByGame($game_id)
		{
			$db = Database::getDatabase();
			return $db->getValue("
				SELECT COUNT(*) 
				FROM StationInstance
				INNER JOIN TeamInstance
				ON TeamInstance.id=StationInstance.team_instance_id
				WHERE TeamInstance.game_id = " . $game_id);
		}
		
		public static function getMaxId()
		{
			$db = Database::getDatabase();
			return $db->getValue("
				SELECT Max(id) 
				FROM StationInstance");
		}
	}
	
	class RoundInstance extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('RoundInstance', array('id', 'round_id', 'station_instance_id', 'plan_program_id', 'exec_program_id', 'starttime', 'POVN'), $id);
		}
		
		public static function getCommittedRounds($game_id, $round_id)
		{
			if ($round_id == "")
				$round_id_condition = " IS NULL";
			else
				$round_id_condition = "=" . $round_id;
			$db = Database::getDatabase();
			return $db->getValue("
				SELECT count(*)
				FROM RoundInstance
				INNER JOIN StationInstance
				ON StationInstance.id=RoundInstance.station_instance_id
				INNER JOIN TeamInstance
				ON TeamInstance.id=StationInstance.team_instance_id
				WHERE TeamInstance.game_id=" . $game_id . "
				AND RoundInstance.round_id" . $round_id_condition . "
				AND RoundInstance.plan_program_id IS NOT NULL");
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
	}
	
	class Program extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('Program', array('id', 'area_home', 'area_work', 'area_leisure', 'type_home', 'type_work', 'type_leisure'), $id);
		}
		
		public static function getMaxId()
		{
			$db = Database::getDatabase();
			return $db->getValue("
				SELECT Max(id) 
				FROM Program");
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
	}

