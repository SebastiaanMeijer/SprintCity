package SprintStad.Data.Station 
{
	public class Stations
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
		
		public function GetStationCount():int
		{
			return stations.length;
		}
		
		public function Clear():void
		{
			stations = new Array();
		}
		
		public function ParseXML(xmlList:XMLList):void
		{
			Clear();
			
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
					station.ParseXML(xml.rounds.children());
				else
					station[xml.name()] = xml;
			}
			AddStation(station);
		}
	}
}