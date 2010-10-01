package SprintStad.Drawer 
{
	import flash.display.Graphics;
	import flash.display.MovieClip;
	import SprintStad.Data.Graph.LineGraph;
	import flash.display.Sprite;
	import SprintStad.Data.Station.Stations;
	import SprintStad.Data.Data;
	import SprintStad.Debug.Debug;
	import SprintStad;
	
	// for now just a container that may contain graphs
	public class LineGraphDrawer 
	{
		private var lineGraph:Array = new Array();
		
		public function LineGraphDrawer(parent:Sprite) 
		{
			var sessionID:String = FindSession();
			var stations:Stations = Data.Get().GetStations();
		

			LoadGraphs(sessionID, stations);
			
			parent.addChild(lineGraph[2]);
			
		}
		
		// Todo: Implement find session
		private function FindSession():String
		{
			return SprintStad.session;
		}
		
		private function LoadGraphs(sessionID:String, stations:Stations):void
		{
			for (var i:int = 0; i < stations.GetStationCount(); i++)
				lineGraph[i] = new LineGraph(sessionID, i);
		}
		
		public function GetGraph(stationID: int)
		{
			return lineGraph[stationID];
		}
	}

}