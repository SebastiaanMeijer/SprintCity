package SprintStad.State 
{
	import flash.display.MovieClip;
	import flash.events.Event;
	import flash.events.IOErrorEvent;
	import flash.events.MouseEvent;
	import flash.net.URLLoader;
	import flash.net.URLRequest;
	import flash.net.URLVariables;
	import SprintStad.Data.Data;
	import SprintStad.Data.DataLoader;
	import SprintStad.Debug.Debug;
	import SprintStad.Debug.ErrorDisplay;
	public class IntroState implements IState
	{
		private var parent:SprintStad = null;
		private var loadCount:int = 0;
		
		public function IntroState(parent:SprintStad) 
		{
			this.parent = parent;
		}	
		
		private function OnContinueEvent(event:MouseEvent):void
		{
			parent.gotoAndPlay(SprintStad.FRAME_OVERVIEW);
		}
		
		public function OnStageOneLoadingDone(data:int)
		{
			Debug.out(this + " I know " + data);
			loadCount++;
			// stage two loading
			if (loadCount >= 7)
				DataLoader.Get().AddJob(DataLoader.DATA_STATIONS, OnLoadingDone);
		}
		
		public function OnLoadingDone(data:int)
		{
			Debug.out(this + " I know " + data);
			parent.currentStationIndex = Data.Get().GetStations().GetStationCount() - 1;
			parent.currentStationIndex = 
				Data.Get().GetStations().GetNextStationOfTeam(
					parent.currentStationIndex, 
					Data.Get().GetTeams().GetOwnTeam());
			parent.intro_movie.buttonMode = true;
			parent.intro_movie.addEventListener(MouseEvent.CLICK, OnContinueEvent);
		}
		
		/* INTERFACE SprintStad.State.IState */
		
		public function Activate():void 
		{
			// stage one loading
			DataLoader.Get().AddJob(DataLoader.DATA_VALUES, OnStageOneLoadingDone);
			DataLoader.Get().AddJob(DataLoader.DATA_TEAMS, OnStageOneLoadingDone);
			DataLoader.Get().AddJob(DataLoader.DATA_TYPES, OnStageOneLoadingDone);
			DataLoader.Get().AddJob(DataLoader.DATA_STATION_TYPES, OnStageOneLoadingDone);
			DataLoader.Get().AddJob(DataLoader.DATA_FACILITES, OnStageOneLoadingDone);
			DataLoader.Get().AddJob(DataLoader.DATA_CONSTANTS, OnStageOneLoadingDone);
			DataLoader.Get().AddJob(DataLoader.DATA_CURRENT_ROUND, OnStageOneLoadingDone);
		}
		
		public function Deactivate():void
		{
			//parent.intro_movie.removeEventListener(MouseEvent.CLICK, onContinueEvent);
		}
	}
}