package{
	import flash.display.MovieClip;
	import SprintStad.Data.DataLoader;
	import SprintStad.Data.Station.Station;
	import SprintStad.Data.Station.Stations;
	import SprintStad.Data.StationTypes.StationTypes;
	import SprintStad.Data.Values.Values;
	import SprintStad.Debug.ErrorDisplay;
	import SprintStad.State.*;
	public class SprintStad extends MovieClip 
	{
		public static const WIDTH:int = 1024;
		public static const HEIGHT:int = 768;
		public static const DOMAIN:String = "/Sprintstad/";
		public var session:String = "";
		public static const LOADER:MovieClip = new LoadingScreen();
		
		public static const STATE_INTRO:int = 0;
		public static const STATE_VALUES:int = 1;
		public static const STATE_STATION_INFO:int = 2;
		public static const STATE_PROGRAM:int = 3;
		public static const STATE_ROUND:int = 4;
		public static const STATE_OVERVIEW:int = 5;
		
		public static const FRAME_INTRO:int = 1;
		public static const FRAME_VALUES:int = 11;
		public static const FRAME_STATION_INFO:int = 21;
		public static const FRAME_PROGRAM:int = 31;
		public static const FRAME_ROUND:int = 41;
		public static const FRAME_OVERVIEW:int = 51;
		
		private var currentState:IState = null;
		public var currentStation:Station = null;
		private var states:Array = new Array();
		
		public function SprintStad()
		{
			ResolveSessionHash();
			ErrorDisplay.Get().SetRoot(this);
			DataLoader.Get().SetRoot(this);
			states[STATE_INTRO] = new IntroState(this);
			states[STATE_VALUES] = new ValuesState(this);
			states[STATE_STATION_INFO] = new StationInfoState(this);
			states[STATE_PROGRAM] = new ProgramState(this);
			states[STATE_ROUND] = new RoundState(this);
			states[STATE_OVERVIEW] = new OverviewState(this);
		}
		
		private function ResolveSessionHash():void
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
		
		public function GetState(state:int):IState
		{
			return states[state];
		}
	}
}