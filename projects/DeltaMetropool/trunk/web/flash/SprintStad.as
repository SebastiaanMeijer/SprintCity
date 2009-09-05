package{
	import flash.display.MovieClip;
	import SprintStad.Data.Values.Values;
	import SprintStad.State.*;
	public class SprintStad extends MovieClip {
		
		public static const DOMAIN:String = "http://localhost/Sprintstad/";
		public var session:String = "";
		
		public static const STATE_INTRO:int = 0;
		public static const STATE_VALUES:int = 1;
		public static const STATE_STATION_INFO:int = 2;
		
		public static const FRAME_INTRO:int = 1;
		public static const FRAME_VALUES:int = 11;
		public static const FRAME_STATION_INFO:int = 21;
		
		private var currentState:IState = null;
		private var states:Array = new Array();
		
		// data
		private var values:Values = new Values();
		
		public function SprintStad()
		{
			ResolveSessionHash();
			states[STATE_INTRO] = new IntroState(this);
			states[STATE_VALUES] = new ValuesState(this);
			states[STATE_STATION_INFO] = new StationInfoState(this);
		}
		
		private function ResolveSessionHash()
		{
			var args:Object = this.root.loaderInfo.parameters;
			
			for (var key:String in args) 
			{
				if (key == "session")
					session = String(args[key]);
			}
		}
		
		public function SetState(state:int):void
		{
			if (currentState)
				currentState.Deactivate();
			currentState = states[state];
			currentState.Activate();
		}
		
		public function GetValues():Values
		{
			return values;
		}
	}
}