package SprintStad.State 
{
	import flash.display.MovieClip;
	import flash.events.MouseEvent;
	import SprintStad.Debug.Debug;
	public class OverviewState  implements IState
	{
		private var parent:SprintStad = null;
		
		public function OverviewState(parent:SprintStad) 
		{
			this.parent = parent;
		}
		
		private function OnStationLeidenButton(event:MouseEvent):void
		{
			StationInfoState(parent.GetState(SprintStad.STATE_STATION_INFO)).SetCurrentStation(0);
			parent.gotoAndPlay(SprintStad.FRAME_STATION_INFO);
		}
		
		private function OnStationSassenheimButton(event:MouseEvent):void
		{
			StationInfoState(parent.GetState(SprintStad.STATE_STATION_INFO)).SetCurrentStation(1);
			parent.gotoAndPlay(SprintStad.FRAME_STATION_INFO);
		}
		
		/* INTERFACE SprintStad.State.IState */
		
		public function Activate():void 
		{
			Debug.out("Activate Overview");
			var view:MovieClip = parent.overview_movie;
			view.Station_Leiden.addEventListener(MouseEvent.CLICK, OnStationLeidenButton);
			view.Station_Sassenheim.addEventListener(MouseEvent.CLICK, OnStationSassenheimButton);
		}
		
		public function Deactivate():void
		{

		}
	}
}