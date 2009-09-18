package SprintStad.State 
{
	import flash.display.MovieClip;
	import flash.events.MouseEvent;
	import SprintStad.Data.Data;
	import SprintStad.Data.Station.Station;
	import SprintStad.Debug.Debug;
	public class ProgramState implements IState
	{		
		private var parent:SprintStad = null;
		
		public function ProgramState(parent:SprintStad) 
		{
			this.parent = parent;
		}
		
		private function OnOkButton(event:MouseEvent):void
		{
			parent.gotoAndPlay(SprintStad.FRAME_ROUND);
		}
		
		private function OnValuesButton(event:MouseEvent):void
		{
			parent.gotoAndPlay(SprintStad.FRAME_VALUES);
		}
		
		private function DrawUI(index:int):void
		{
			//draw stuff
			var view:MovieClip = parent.program_movie;
			var station:Station = Data.Get().GetStations().GetStation(index); 
			
			view.sheet.addChild(station.imageData);
		}
		
		/* INTERFACE SprintStad.State.IState */
		
		public function Activate():void 
		{
			var view:MovieClip = parent.program_movie;
			view.ok_button.buttonMode = true;
			view.ok_button.addEventListener(MouseEvent.CLICK, OnOkButton);
			view.values_button.buttonMode = true;
			view.values_button.addEventListener(MouseEvent.CLICK, OnValuesButton);
		}
		
		public function Deactivate():void
		{
			//parent.intro_movie.removeEventListener(MouseEvent.CLICK, onContinueEvent);
		}
	}
}