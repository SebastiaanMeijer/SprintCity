package SprintStad.Data.Team
{
	import SprintStad.Data.IDataCollection;
	import SprintStad.Debug.Debug;
	
	public class Teams implements IDataCollection
	{
		private var teams:Array = new Array();	
		
		public function Teams() 
		{
		}
		
		public function AddTeam(team:Team):void
		{
			teams.push(team);
		}
		
		public function GetTeam(index:int):Team
		{
			return teams[index];
		}
		
		public function GetTeamById(id:int):Team
		{
			for each (var team:Team in teams)
				if (team.id == id)
					return team;
			return null;
		}
		
		public function GetOwnTeam():Team
		{
			for each (var team:Team in teams)
				if (team.is_player)
					return team;
			return null;
		}
		
		public function GetTeamCount():int
		{
			return teams.length;
		}
		
		/* INTERFACE SprintStad.Data.IDataCollection */
		
		public function PostConstruct():void
		{			
		}
		
		public function Clear():void
		{
			teams = new Array();
		}
		
		public function ParseXML(xmlData:XML):void
		{
			Clear();
			
			var xmlList:XMLList = xmlData.team.children();
			var team:Team = new Team();
			var xml:XML = null;
			var firstTag:String = "";
			
			for each (xml in xmlList) 
			{
				var tag:String = xml.name();
				
				if (xml.name() == firstTag)
				{
					AddTeam(team);
					team = new Team();
				}
				
				if (firstTag == "")
					firstTag = xml.name();
				
				if (tag == "is_player")
				{
					team[tag] = (int(xml) == 0 ? false : true);
				}
				else if (xml.name() == "values")
				{
					team.ParseXML(xml);
				}
				else
				{
					team[tag] = xml;
				}
			}
			AddTeam(team);
		}		
	}
}