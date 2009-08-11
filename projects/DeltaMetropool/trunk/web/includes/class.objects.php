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
			return DBObject::glob("Team", "SELECT * FROM  `team` ORDER BY  `created` DESC LIMIT " . $fromIndex . " , " . $numberOfRecords);
		}
    }

