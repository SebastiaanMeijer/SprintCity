package SprintStad.Calculators 
{
	import SprintStad.Data.Constants.Constants;
	import SprintStad.Data.Data;
	import SprintStad.Data.Program.Program;
	import SprintStad.Data.Station.Station;
	import SprintStad.Data.Station.StationInstance;
	import SprintStad.Debug.Debug;
	
	public class StationStatsCalculator
	{
		public function StationStatsCalculator() 
		{
			
		}
		
		public static function GetStationAfterProgram(station:Station, program:Program):StationInstance
		{
			var stationInstance:StationInstance = StationInstance.Create(station);
			stationInstance.ApplyProgram(program);
			return stationInstance;
		}
		
		public static function GetTravelersStats(station:StationInstance):int
		{
			var constants:Constants = Data.Get().GetConstants();
			if (station.POVN >= 3000)
				return int(
					station.count_home_total * constants.average_citizens_per_home * (250 / 1000) + 
					station.count_work_total * constants.average_workers_per_bvo * (150 / 100));
			else if (station.POVN >= 500)
				return int(
					station.count_home_total * constants.average_citizens_per_home * (100 / 1000) + 
					station.count_work_total * constants.average_workers_per_bvo * (50 / 100));
			else
				return int(
					station.count_home_total * constants.average_citizens_per_home * (70 / 1000) + 
					station.count_work_total * constants.average_workers_per_bvo * (30 / 100));
		}
		
		public static function GetTransformArea(station:Station):Number
		{
			return Math.round( 
				station.transform_area_cultivated_home + 
				station.transform_area_cultivated_work + 
				station.transform_area_cultivated_mixed +  
				station.transform_area_undeveloped_urban +
				station.transform_area_undeveloped_mixed);
		}

		
	}

}