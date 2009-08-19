<?PHP
	// Stick your DBOjbect subclasses in here (to help keep things tidy).

	class User extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('users', array('username', 'password', 'level', 'email'), $id);
		}
	}
	
	class Team extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('Team', array('name', 'description', 'cpu', 'created'), $id);
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
				INNER JOIN StationInstance
				ON StationInstance.team_id=Team.id
				INNER JOIN Game
				ON StationInstance.game_id=Game.id
				WHERE StationInstance.game_id=" . $gameId . ";");
		}
	}
	
	class Station extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('Station', array(
				'code', 'name', 'description', 'image', 'town', 'region', 
				'POVN', 'PWN', 'IWD', 'MNG', 
				'area_cultivated_home', 'area_cultivated_work', 'area_cultivated_mixed', 'area_undeveloped_urban', 'area_undeveloped_rural',
				'transform_area_cultivated_home', 'transform_area_cultivated_work', 'transform_area_cultivated_mixed', 'transform_area_undeveloped_urban', 'transform_area_undeveloped_rural', 
				'count_home_total', 'count_home_transform', 'count_work_total', 'count_work_transform', 'network_value'), 
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
				array('station_id', 'round_info_id', 'description', 'new_transform_area', 'network_value', 'POVN', 'PWN'), $id);
		}
		
		public static function getRoundsByStation($station_id)
		{
			return DBObject::glob("Round", "SELECT * FROM `round` WHERE station_id = " . $station_id);
		}
	}
	
	
	class RoundInfo extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('RoundInfo', array('number', 'name'), $id);
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
			parent::__construct('StationInstance', array('station_id', 'team_id', 'game_id'), $id);
		}
		
		public static function rowCountByGame($game_id)
		{
			$db = Database::getDatabase();
			return $db->getValue("SELECT COUNT(*) FROM stationinstance WHERE game_id = " . $game_id);
		}
	}
	
	class RoundInstance extends DBObject
	{
		public function __construct($id = null)
		{
			parent::__construct('RoundInstance', array('round_id', 'station_instance_id', 'program_id', 'starttime'), $id);
		}
		
		public static function getCommittedRounds($game_id, $round_id)
		{
			$db = Database::getDatabase();
			return $db->getValue("
				SELECT count(*)
				FROM StationInstance
				INNER JOIN RoundInstance
				ON StationInstance.id=RoundInstance.station_instance_id
				WHERE StationInstance.game_id=" . $game_id . "
				AND RoundInstance.round_id=" . $round_id . "
				AND program_id IS NOT NULL");
		}
	}

