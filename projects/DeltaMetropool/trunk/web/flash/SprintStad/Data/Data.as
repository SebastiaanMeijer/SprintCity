package SprintStad.Data 
{
	import SprintStad.Data.Constants.Constants;
	import SprintStad.Data.Facility.Facilities;
	import SprintStad.Data.Round.MobilityReport;
	import SprintStad.Data.Station.Stations;
	import SprintStad.Data.StationTypes.StationTypes;
	import SprintStad.Data.Team.Teams;
	import SprintStad.Data.Types.Types;
	import SprintStad.Data.Values.Values;
	import SprintStad.Debug.Debug;
	
	public class Data implements IDataCollection
	{
		private static var instance:Data = new Data();
		
		public var current_round_id = 0;
		
		private var teams:Teams = new Teams();
		private var values:Values = new Values();
		private var stations:Stations = new Stations();
		private var stationTypes:StationTypes = new StationTypes();
		private var types:Types = new Types();
		private var constants:Constants = new Constants();
		private var mobilityReport:MobilityReport = new MobilityReport();
		private var facilities:Facilities = new Facilities();
		
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
		
		public function GetMobilityReport():MobilityReport
		{
			return mobilityReport;
		}
		
		public function GetFacilities():Facilities
		{
			return facilities;
		}
		
		public function PostConstruct():void
		{
		}
		
		public function Clear():void
		{
		}
		
		public function ParseXML(xmlData:XML):void
		{
			Clear();
			var xmlList:XMLList = xmlData.children();
			this.current_round_id = xmlList;
		}
	}
}