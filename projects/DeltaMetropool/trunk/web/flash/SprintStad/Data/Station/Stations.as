package SprintStad.Data.Station 
{
	import SprintStad.Data.Data;
	import SprintStad.Data.IDataCollection;
	import SprintStad.Data.Team.Team;
	import SprintStad.Debug.Debug;
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
		
		public function GetStationIndex(station:Station):int
		{
			return stations.indexOf(station);
		}
		
		public function GetStationCount():int
		{
			return stations.length;
		}
		
		public function GetNextStationOfTeam(currentStationIndex:int, team:Team):int
		{
			var currentStation:Station = GetStation(currentStationIndex);
			var index:int = currentStationIndex;
			do
			{
				index = (index + 1) % stations.length;
				if (stations[index].team_id == currentStation.team_id)
					return index;
			}
			while (index != currentStationIndex)
			return currentStationIndex;
		}
		
		public function GetPreviousStationOfTeam(currentStationIndex:int, team:Team):int
		{
			var currentStation:Station = GetStation(currentStationIndex);
			var index:int = currentStationIndex;
			do
			{
				index = (stations.length + index - 1) % stations.length;
				if (stations[index].team_id == currentStation.team_id)
					return index;
			}
			while (index != currentStationIndex)
			return currentStationIndex;
		}
		
		public function GetNextStation(currentStationIndex:int):int
		{
			return (currentStationIndex + 1) % stations.length;
		}
		
		public function GetPreviousStation(currentStationIndex:int):int
		{
			return (stations.length + currentStationIndex - 1) % stations.length;
		}
		
		public function Clear():void
		{
			stations = new Array();
		}
		
		public function ParseXML(xmlData:XML):void
		{
			Clear();
			
			var station:Station = new Station();
			var stationXml:XML = null;
			var index:int = 0;
			stationXml = xmlData.station[index];
			while (stationXml != null)
			{
				for each (var xml:XML in stationXml.children())
				{
					if (xml.name() == "program")
					{
						station.program.ParseXML(stationXml.program[0]);
					}
					else if (xml.name() == "rounds")
					{
						station.ParseXML(xml);
					}
					else
					{
						station[xml.name()] = xml;
					}
				}
				AddStation(station);
				station = new Station();
				index++;
				stationXml = xmlData.station[index];
			}
		}
	}
}