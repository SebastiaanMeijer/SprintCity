package SprintStad.Calculators.Result 
{
	public class StationTravelersStats
	{
		public var travelersIn:int = 0;
		public var travelersOut:int = 0;
		
		public function StationTravelersStats(travelersIn:int, travelersOut:int) 
		{
			this.travelersIn = travelersIn;
			this.travelersOut = travelersOut;
		}
		
		public function GetTotal():int
		{
			return travelersIn + travelersOut;
		}		
	}

}