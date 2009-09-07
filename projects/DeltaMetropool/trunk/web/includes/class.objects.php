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
	}
	
	class Team extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('Team', array('id', 'name', 'description', 'cpu', 'created'), $id);
		}
		
		public static function rowCount()
		{
			$db = Database::getDatabase();
			return $db->getValue("SELECT COUNT(*) FROM team");
		}
		
		public static function getTeams($fromIndex, $numberOfRecords)
		{
			return DBObject::glob("Team", "SELECT * FROM  `team` ORDER BY `created` DESC LIMIT " . $fromIndex . " , " . $numberOfRecords);
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
		
		public static function rowCount()
		{
			$db = Database::getDatabase();
			return $db->getValue("SELECT COUNT(*) FROM station");
		}
		
		public static function getStations($fromIndex, $numberOfRecords)
		{
			return DBObject::glob("Station", "SELECT * FROM  `station` ORDER BY `code` ASC LIMIT " . $fromIndex . " , " . $numberOfRecords);
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
			parent::__construct('Value', array('id', 'title', 'description'), $id);
		}
		
		public static function getValues()
		{
			return DBObject::glob("Value", "SELECT * FROM `value`");
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
	}
	
	class RoundInstance extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('RoundInstance', array('id', 'round_id', 'station_instance_id', 'program_id', 'starttime'), $id);
		}
		
		public static function getCommittedRounds($game_id, $round_id)
		{
			$db = Database::getDatabase();
			return $db->getValue("
				SELECT count(*)
				FROM RoundInstance
				INNER JOIN StationInstance
				ON StationInstance.id=RoundInstance.station_instance_id
				INNER JOIN TeamInstance
				ON TeamInstance.id=StationInstance.team_instance_id
				WHERE TeamInstance.game_id=" . $game_id . "
				AND RoundInstance.round_id=" . $round_id . "
				AND program_id IS NOT NULL");
		}
	}

