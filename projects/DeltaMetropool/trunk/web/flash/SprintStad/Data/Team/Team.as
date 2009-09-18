package SprintStad.Data.Team
{
	import flash.display.Loader;
	import flash.events.Event;
	public class Team
	{
		public var id:int = 0;
		public var name:String = "";
		public var description:String = "";
		public var cpu:Boolean = false;
		public var created:String = "";
		public var is_player:Boolean = false;
		
		private var loader:Loader = null;
		
		public function Team() 		
		{			
		}
	}
}