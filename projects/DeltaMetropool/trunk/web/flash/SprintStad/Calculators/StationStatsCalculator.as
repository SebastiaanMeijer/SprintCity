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
		
		public static function GetTravelersStats(stationInstance:StationInstance):int
		{
			var station:Station = stationInstance.station;
			var povn_growth:Number = 0;
			if (station.GetCurrentRound() != null)
				povn_growth = (station.GetCurrentRound().POVN - station.POVN) / station.POVN;
			var traveler_growth:Number = 0;
			var travelers:Number = GetInitialTravelersStats(stationInstance);
			if (povn_growth > 5)
				traveler_growth = (povn_growth / 20);
			else if (povn_growth > 1)
				traveler_growth = (povn_growth / 15);
			else
				traveler_growth = (povn_growth / 10);
			return int(Math.round(travelers * (1 + traveler_growth)));
		}
		
		public static function GetInitialTravelersStats(station:StationInstance):Number
		{
			var constants:Constants = Data.Get().GetConstants();
			var result:Number = station.count_home_total * constants.average_citizens_per_home * constants.average_travelers_per_citizen + 
				station.count_worker_total * constants.average_travelers_per_worker + 
				station.area_cultivated_mixed * constants.average_travelers_per_ha_leisure;
			return result;
		}
		
		// Returns new allocatable area + unallocated area of previous round
		public static function GetTransformArea(station:Station):int
		{	
			var newAllocatableArea:int; // there is always new allocatable area
			var previousNewAllocatableArea:int = 0; //default if there is no previous
			var totalArea:int = 0;
			var unAllocatedArea:int = 0; //default if there is no previous
			
			var previousRound:Round;
			var roundID:int = Data.Get().current_round_id;
			var currentStationRound:Round = station.GetRound(roundID);
			
			var transformArea:Number = new Number( -1.0);
			
			// get all the information now
			newAllocatableArea = 0;
			if (currentStationRound != null)
				newAllocatableArea = currentStationRound.new_transform_area;
			
			if (roundID > 2)
			{
				previousRound = station.GetRound(roundID - 1);
				previousNewAllocatableArea = 0;
				totalArea = 0;
				if (previousRound != null)
				{
					previousNewAllocatableArea = previousRound.new_transform_area;
					if (previousRound.exec_program != null)
						totalArea = previousRound.exec_program.TotalArea();
				}
				unAllocatedArea = previousNewAllocatableArea - totalArea; 
			}
			
			transformArea = newAllocatableArea + unAllocatedArea; //makes sense right?
			
			return transformArea;
		}
	}
}