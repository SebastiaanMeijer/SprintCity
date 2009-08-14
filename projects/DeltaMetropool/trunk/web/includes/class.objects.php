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
			parent::__construct('Game', array('name', 'notes', 'starttime', 'current_round_id'), $id);
		}
		
		public static function rowCount()
		{
			$db = Database::getDatabase();
			return $db->getValue("SELECT COUNT(*) FROM game");
		}
		
		public static function getGames($fromIndex, $numberOfRecords)
		{
			return DBObject::glob("Game", "SELECT * FROM  `game` ORDER BY `starttime` DESC LIMIT " . $fromIndex . " , " . $numberOfRecords);
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
			parent::__construct('RoundInstance', array('round_id', 'program_id', 'starttime'), $id);
		}
		
		public static function getCommittedRounds($round_id)
		{
			$db = Database::getDatabase();
			return $db->getValue("
				SELECT count(*)
				FROM Round
				INNER JOIN RoundInstance
				ON Round.id=RoundInstance.round_id
				WHERE Round.round_info_id = " . $round_id . "
				AND RoundInstance.program_id IS NOT NULL");
		}
	}

