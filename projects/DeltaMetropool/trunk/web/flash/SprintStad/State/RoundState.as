package SprintStad.State 
{
	import flash.display.MovieClip;
	import flash.events.MouseEvent;
	import SprintStad.Debug.Debug;
	public class RoundState implements IState
	{		
		private var parent:SprintStad = null;
		
		public function RoundState(parent:SprintStad) 
		{
			this.parent = parent;
		}
		
		private function OnOkButton(event:MouseEvent):void
		{
			parent.gotoAndPlay(SprintStad.FRAME_OVERVIEW);
		}
		
		private function OnValuesButton(event:MouseEvent):void
		{
			parent.gotoAndPlay(SprintStad.FRAME_VALUES);
		}
		
		/* INTERFACE SprintStad.State.IState */
		
		public function Activate():void 
		{			
			var view:MovieClip = parent.round_movie;
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