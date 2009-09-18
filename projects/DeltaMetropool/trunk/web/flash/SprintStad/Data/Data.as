package SprintStad.Data 
{
	import SprintStad.Data.Constants.Constants;
	import SprintStad.Data.Station.Stations;
	import SprintStad.Data.StationTypes.StationTypes;
	import SprintStad.Data.Team.Teams;
	import SprintStad.Data.Types.Types;
	import SprintStad.Data.Values.Values;
	public class Data
	{
		private static var instance:Data = new Data();
		
		private var teams:Teams = new Teams();
		private var values:Values = new Values();
		private var stations:Stations = new Stations();
		private var stationTypes:StationTypes = new StationTypes();
		private var types:Types = new Types();
		private var constants:Constants = new Constants();
		
		public function Data() 
		{
			
		}
		
		public static function Get():Data
		{
			return instance;
		}
		
		public function GetTeams():Teams
		{
			return teams;
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
			return stationTypes;
		}
		
		public function GetTypes():Types
		{
			return types;
		}
		
		public function GetConstants():Constants
		{
			return constants;
		}
	}
}