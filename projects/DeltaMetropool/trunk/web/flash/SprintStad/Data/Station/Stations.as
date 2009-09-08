package SprintStad.Data.Station 
{
	public class Stations
	{	
		private var stations:Array = new Array();	
		
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
	}
}