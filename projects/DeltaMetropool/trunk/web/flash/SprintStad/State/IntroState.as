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
		
		public function IntroState(parent:SprintStad) 
		{
			this.parent = parent;
		}	
		
		private function OnContinueEvent(event:MouseEvent):void
		{
			parent.gotoAndPlay(SprintStad.FRAME_VALUES);
		}
		
		public function OnLoadingDone(data:int)
		{
			Debug.out(this + " I know " + data);
		}
		
		/* INTERFACE SprintStad.State.IState */
		
		public function Activate():void 
		{
			DataLoader.Get().AddJob(DataLoader.DATA_TEAMS, OnLoadingDone);
			DataLoader.Get().AddJob(DataLoader.DATA_VALUES, OnLoadingDone);
			DataLoader.Get().AddJob(DataLoader.DATA_TYPES, OnLoadingDone);
			DataLoader.Get().AddJob(DataLoader.DATA_STATION_TYPES, OnLoadingDone);
			DataLoader.Get().AddJob(DataLoader.DATA_CONSTANTS, OnLoadingDone);
			DataLoader.Get().AddJob(DataLoader.DATA_STATIONS, OnLoadingDone);
			parent.intro_movie.buttonMode = true;
			parent.intro_movie.addEventListener(MouseEvent.CLICK, OnContinueEvent);
		}
		
		public function Deactivate():void
		{
			//parent.intro_movie.removeEventListener(MouseEvent.CLICK, onContinueEvent);
		}
	}
}