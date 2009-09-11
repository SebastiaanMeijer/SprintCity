package SprintStad.State 
{
	import flash.display.MovieClip;
	import flash.events.MouseEvent;
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
		
		/* INTERFACE SprintStad.State.IState */
		
		public function Activate():void 
		{
			Debug.out("Activate ProgramState");
			var view:MovieClip = parent.program_movie;
			view.ok_button.addEventListener(MouseEvent.CLICK, OnOkButton);
			view.values_button.addEventListener(MouseEvent.CLICK, OnValuesButton);
		}
		
		public function Deactivate():void
		{
			//parent.intro_movie.removeEventListener(MouseEvent.CLICK, onContinueEvent);
		}
	}
}