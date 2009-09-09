package SprintStad.Data 
{
	import SprintStad.Data.Station.Stations;
	import SprintStad.Data.StationTypes.StationTypes;
	import SprintStad.Data.Values.Values;
	public class Data
	{
		private static var instance:Data = new Data();
		
		private var values:Values = new Values();
		private var stations:Stations = new Stations();
		private var stationTypes:StationTypes = new StationTypes();
		
		public function Data() 
		{
			
		}
		
		public static function Get():Data
		{
			return instance;
		}
		
		public function GetValues():Values
		{
			return values;
		}
		
		public function GetStations():Stations
		{
			return stations;
		}
		
		public function GetStationTypes():StationTypes
		{
			return stationTypes
		}		
	}
}