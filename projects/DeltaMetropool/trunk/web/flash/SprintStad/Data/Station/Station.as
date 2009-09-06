package SprintStad.Data.Station 
{
	import flash.utils.Proxy;
	import SprintStad.Data.Round.Round;
	public class Station
	{	
		public var id:int = 0;
		public var code:String = "";
		public var name:String = "";
		public var description:String = "";
		public var image:String = "";
		public var town:String = "";
		public var region:String = "";
		public var POVN:Number = 0;
		public var PWN:Number = 0;
		public var IWD:Number = 0;
		public var MNG:Number = 0;
		public var area_cultivated_home:int = 0;
		public var area_cultivated_work:int = 0;
		public var area_cultivated_mixed:int = 0;
		public var area_undeveloped_urban:int = 0;
		public var area_undeveloped_rural:int = 0;
		public var transform_area_cultivated_home:int = 0;
		public var transform_area_cultivated_work:int = 0;
		public var transform_area_cultivated_mixed:int = 0;
		public var transform_area_undeveloped_urban:int = 0;
		public var transform_area_undeveloped_mixed:int = 0;
		public var count_home_total:int = 0;
		public var count_home_transform:int = 0;
		public var count_work_total:int = 0;
		public var count_work_transform:int = 0;
		
		private var rounds:Array = new Array();
		
		public function Station() 
		{			
		}
		
		public function AddRound(round:Round):void
		{
			rounds.push(round);
		}
		
		public function GetRound(index:int):Round
		{
			return rounds[index];
		}
		
		public function GetRoundById(id:int):Round
		{
			for each (var round:Round in rounds)
				if (round.id == id)
					return round;
			return null;
		}
		
		public function GetRoundCount():int
		{
			return rounds.length;
		}
	}
}