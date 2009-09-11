package SprintStad.Calculators 
{
	import SprintStad.Data.Constants.Constants;
	import SprintStad.Data.Data;
	import SprintStad.Data.Station.Station;
	public class StationStatsCalculator
	{
		public function StationStatsCalculator() 
		{
			
		}
		
		public static function GetTravelersStats(station:Station):int
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

		
	}

}