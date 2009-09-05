package SprintStad.State 
{
	import flash.display.MovieClip;
	import flash.events.MouseEvent;
	public class IntroState implements IState
	{
		private var parent:SprintStad = null;
		
		public function IntroState(parent:SprintStad) 
		{
			this.parent = parent;
		}	
		
		private function onContinueEvent(event:MouseEvent):void
		{
			parent.gotoAndPlay(SprintStad.FRAME_VALUES);
		}
		
		/* INTERFACE SprintStad.State.IState */
		
		public function Activate():void 
		{			
			parent.intro_movie.addEventListener(MouseEvent.CLICK, onContinueEvent);
		}
		
		public function Deactivate():void
		{
			//parent.intro_movie.removeEventListener(MouseEvent.CLICK, onContinueEvent);
		}
	}
}