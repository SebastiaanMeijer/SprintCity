package SprintStad.Calculators 
{
	import SprintStad.Data.Constants.Constants;
	import SprintStad.Data.Data;
	import SprintStad.Data.Program.Program;
	import SprintStad.Data.Round.Round;
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
		
		
		// Returns new allocatable area + unallocated area of previous round
		public static function GetTransformArea(station:Station):Number
		{	
			var newAllocatableArea:int; // there is always new allocatable area
			var previousNewAllocatableArea:int = 0; //default if there is no previous
			var unAllocatedArea:int = 0; //default if there is no previous
			
			var previousRound:Round;
			var roundID:int = Data.Get().current_round_id;
			var currentStationRound:Round = station.GetRound(roundID);
			
			var transformArea:Number = new Number( -1.0);
			
			// get all the information now
			
			newAllocatableArea = currentStationRound.new_transform_area;
					
			if (roundID > 2)
			{
				previousRound = station.GetRound(roundID - 1);
				previousNewAllocatableArea = previousRound.new_transform_area;
				unAllocatedArea = previousNewAllocatableArea - previousRound.exec_program.TotalArea(); 
			}
			
			transformArea = Number(newAllocatableArea + unAllocatedArea); //makes sense right?
			
			return transformArea;
		}

		
	}

}