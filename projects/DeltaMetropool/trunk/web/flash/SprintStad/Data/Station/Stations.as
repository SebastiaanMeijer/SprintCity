package SprintStad.Data.Station 
{
	import SprintStad.Data.IDataCollection;
	import SprintStad.Data.Team.Team;
	public class Stations implements IDataCollection
	{	
		private var stations:Array = new Array();	
		
		public var MaxPOVN:Number = 0;
		public var MaxPWN:Number = 0;
		public var MaxIWD:Number = 0;
		public var MaxMNG:Number = 0;
		
		public function Stations() 
		{
			
		}
		
		public function PostConstruct():void
		{
			for each (var station:Station in stations)
			{
				station.PostConstruct();
			}
		}
		
		public function AddStation(station:Station):void
		{
			MaxPOVN = Math.max(MaxPOVN, station.POVN);
			MaxPWN = Math.max(MaxPWN, station.PWN);
			MaxIWD = Math.max(MaxIWD, station.IWD);
			MaxMNG = Math.max(MaxMNG, station.MNG);
			stations.push(station);
		}
		
		public function GetStation(index:int):Station
		{
			return stations[index];
		}
		
		public function GetStationById(id:int):Station
		{
			for each (var station:Station in stations)
				if (station.id == id)
					return station;
			return null;
		}
		
		public function GetStationByName(name:String):Station
		{
			for each (var station:Station in stations)
				if (station.name == name)
					return station;
			return null;
		}
		
		public function GetStationCount():int
		{
			return stations.length;
		}
		
		public function GetNextStationOfTeam(currentStation:Station, team:Team):Station
		{
			var index:int = stations.indexOf(currentStation);
			var station:Station = currentStation;
			do
			{
				index = (index + 1) % stations.length;
				station = stations[index];
				if (station.team_id == currentStation.team_id)
					return station;
			}
			while (station != currentStation)
			return currentStation;
		}
		
		public function GetPreviousStationOfTeam(currentStation:Station, team:Team):Station
		{
			var index:int = stations.indexOf(currentStation);
			var station:Station = currentStation;
			do
			{
				index = (stations.length + index - 1) % stations.length;
				station = stations[index];
				if (station.team_id == currentStation.team_id)
					return station;
			}
			while (station != currentStation)
			return currentStation;
		}
		
		public function GetNextStation(currentStation:Station):Station
		{
			var index:int = stations.indexOf(currentStation);
			return stations[(index + 1) % stations.length];
		}
		
		public function GetPreviousStation(currentStation:Station):Station
		{
			var index:int = stations.indexOf(currentStation);
			return stations[(stations.length + index - 1) % stations.length];
		}
		
		public function Clear():void
		{
			stations = new Array();
		}
		
		public function ParseXML(xmlData:XML):void
		{
			Clear();
			
			var xmlList:XMLList = xmlData.station.children();
			var station:Station = new Station();
			var xml:XML = null;
			var firstTag:String = "";

			for each (xml in xmlList) 
			{
				var tag:String = xml.name();
				if (xml.name() == firstTag)
				{
					AddStation(station);
					station = new Station();
				}
				
				if (firstTag == "")
					firstTag = xml.name();
					
				if (xml.name() == "rounds")
					station.ParseXML(xml.round.children());
				else
					station[xml.name()] = xml;
			}
			AddStation(station);
		}
	}
}