package SprintStad.State 
{
	import flash.display.MovieClip;
	import flash.events.MouseEvent;
	public class RoundState implements IState
	{		
		private var parent:SprintStad = null;
		
		public function RoundState(parent:SprintStad) 
		{
			this.parent = parent;
		}
		
		private function OnValuesButton(event:MouseEvent):void
		{
			parent.gotoAndPlay(SprintStad.FRAME_VALUES);
		}
		
		/* INTERFACE SprintStad.State.IState */
		
		public function Activate():void 
		{			
			var view:MovieClip = parent.round_movie;
			view.values_button.addEventListener(MouseEvent.CLICK, OnValuesButton);
		}
		
		public function Deactivate():void
		{
			//parent.intro_movie.removeEventListener(MouseEvent.CLICK, onContinueEvent);
		}
	}
}