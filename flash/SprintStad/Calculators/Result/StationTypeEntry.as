package SprintStad.Calculators.Result 
{
	import SprintStad.Data.StationTypes.StationType;
	public class StationTypeEntry
	{
		public var similarity:int = 0;
		public var stationType:StationType = null;
		
		public function StationTypeEntry(similarity:int, stationType:StationType) 
		{
			this.similarity = similarity;
			this.stationType = stationType;
		}		
	}
}