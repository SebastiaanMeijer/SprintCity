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
				'id', 'code', 'name', 
				'description_facts', 'description_background', 'description_future', 
				'image', 'town', 'region', 
				'POVN', 'PWN', 'IWD', 'MNG', 
				'area_cultivated_home', 'area_cultivated_work', 'area_cultivated_mixed', 'area_undeveloped_urban', 'area_undeveloped_rural',
				'transform_area_cultivated_home', 'transform_area_cultivated_work', 'transform_area_cultivated_mixed', 'transform_area_undeveloped_urban', 'transform_area_undeveloped_mixed', 
				'count_home_total', 'count_home_transform', 'count_work_total', 'count_work_transform'), 
				$id);
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
		
		function GetInitialTravelerCount($station_id)
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
			parent::__construct('StationInstance', array('id', 'station_id', 'team_instance_id'), $id);
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
	
	class Type extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('Type', array('id', 'name', 'description', 'color', 'image', 'density', 'povn'), $id);
		}
		
		public static function getTypes()
		{
			return DBObject::glob("Type", "SELECT * FROM types");
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
	}

