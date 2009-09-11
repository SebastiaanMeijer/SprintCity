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
			Debug.out("Activate RoundState");
			try
			{
			var view:MovieClip = parent.round_movie;
			Debug.out("1");
			view.ok_button.addEventListener(MouseEvent.CLICK, OnOkButton);
			Debug.out("2");
			view.values_button.addEventListener(MouseEvent.CLICK, OnValuesButton);
			Debug.out("3");
			}
			catch (e:Error)
			{
				Debug.out("Awww crap " + e.name + " " + e.message);
			}
		}
		
		public function Deactivate():void
		{
			//parent.intro_movie.removeEventListener(MouseEvent.CLICK, onContinueEvent);
		}
	}
}