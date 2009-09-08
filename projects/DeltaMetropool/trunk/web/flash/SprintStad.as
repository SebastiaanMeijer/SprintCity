package{
	import flash.display.MovieClip;
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
		
		public static const FRAME_INTRO:int = 1;
		public static const FRAME_VALUES:int = 11;
		public static const FRAME_STATION_INFO:int = 21;
		
		private var currentState:IState = null;
		private var states:Array = new Array();
		
		// data
		private var values:Values = new Values();
		private var stations:Stations = new Stations();
		private var stationTypes:StationTypes = new StationTypes();
		
		public function SprintStad()
		{
			ResolveSessionHash();
			states[STATE_INTRO] = new IntroState(this);
			states[STATE_VALUES] = new ValuesState(this);
			states[STATE_STATION_INFO] = new StationInfoState(this);
			ErrorDisplay.Get().SetRoot(this);
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
		
		public function GetStations():Stations
		{
			return stations;
		}
		
		public function GetStationTypes():StationTypes
		{
			return stationTypes
		}
	}
}