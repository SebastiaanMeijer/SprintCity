package SprintStad.Data.Round 
{
	import SprintStad.Data.Program.Program;
	import SprintStad.Debug.Debug;
	public class Round
	{		
		public var id:int = 0;
		public var round_info_id:int = 0;
		public var number:int = 0;
		public var name:String = "";
		public var description:String = "";
		public var new_transform_area:int = 0;
		public var POVN:Number = 0;
		public var PWN:Number = 0;
		
		// bonuses
		public var citizen_bonus:int = 0;
		public var worker_bonus:int = 0;
		public var traveler_bonus:int = 0;
		public var bonuses:String = "";
		
		// game data
		public var plan_program:Program;
		public var exec_program:Program;
		
		public function Round() 
		{
			plan_program = Program.Default();
			exec_program = Program.Default();
		}
	}
}