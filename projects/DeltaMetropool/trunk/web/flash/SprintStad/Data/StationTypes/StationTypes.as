package SprintStad.Data.StationTypes 
{
	public class StationTypes
	{
		private var stationTypes:Array = new Array();
		
		public function StationTypes() 
		{
			
		}
		public function AddStationType(stationType:StationType):void
		{
			stationTypes.push(stationType);
		}
		
		public function GetStationType(index:int):StationType
		{
			return stationTypes[index];
		}
		
		public function GetStationTypeById(id:int):StationType
		{
			for each (var stationType:StationType in stationTypes)
				if (stationType.id == id)
					return stationType;
			return null;
		}
		
		public function GetStationTypeCount():int
		{
			return stationType.length;
		}		
	}
}