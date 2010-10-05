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
		private var lineGraphs:Array = new Array();
		
		public function LineGraphDrawer(parent:Sprite) 
		{
			var sessionID:String = FindSession();
			var stations:Stations = Data.Get().GetStations();
			
			LoadGraphs(sessionID, stations);
			
			var lineGraph:LineGraph = lineGraphs[2];
			parent.addChild(lineGraph);
		}
		
		private function FindSession():String
		{
			return SprintStad.session;
		}
		
		private function LoadGraphs(sessionID:String, stations:Stations):void
		{
			for (var i:int = 0; i < stations.GetStationCount(); i++)
				lineGraphs[i] = new LineGraph(sessionID, i);
		}
		
		public function GetGraph(stationID: int)
		{
			return lineGraphs[stationID];
		}
	}

}