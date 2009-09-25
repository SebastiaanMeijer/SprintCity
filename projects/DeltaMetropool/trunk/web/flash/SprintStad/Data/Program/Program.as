package SprintStad.Data.Program 
{
	import SprintStad.Data.Types.Type;
	public class Program
	{
		public var home_type:Type = null;
		public var work_type:Type = null;
		public var leisure_type:Type = null;
		
		public var home_area:int = 0;
		public var work_area:int = 0;
		public var leisure_area:int = 0;
		
		public function Program() 
		{
			
		}
		
		public function TotalArea():int
		{
			return home_area + work_area + leisure_area;
		}
	}
}