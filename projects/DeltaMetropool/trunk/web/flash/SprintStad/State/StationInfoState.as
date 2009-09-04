package SprintStad.State 
{
	import flash.display.MovieClip;
	import SprintStad.State.IState;

	public class StationInfoState implements IState
	{
		private var parent:SprintStad = null;
		
		public function StationInfoState(parent:SprintStad) 
		{
			this.parent = parent;
		}	
		
		/* INTERFACE SprintStad.State.IState */
		
		public function Activate():void
		{
			
		}
		
		public function Deactivate():void
		{
			
		}
		
	}

}