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
	import SprintStad.Data.Station.Station;
	
	// for now just a container that may contain graphs
	public class LineGraphDrawer 
	{
		private var parent:Sprite;
		private var lineGraphs:Array = new Array();
		private var currentGraph:LineGraph;
		
		public function LineGraphDrawer(parent:Sprite) 
		{
			this.parent = parent;
			
			var stations:Stations = Data.Get().GetStations();
			
			LoadGraphs(stations);
		}
		
		private function LoadGraphs(stations:Stations):void
		{
			var stationCount:int = stations.GetStationCount();
			
			for (var i:int = 0; i < stationCount; i++)
			{
				var station:Station = stations.stations[i];
				lineGraphs[station.id] = new LineGraph(station.id);
			}
		}
		
		public function DrawGraph(stationID: int):void
		{
			var lineGraph:LineGraph = lineGraphs[stationID];
			
			if (currentGraph != null)
			{
				parent.removeChild(currentGraph);
				currentGraph = lineGraph;
			}
			
			parent.addChild(lineGraph);
		}
	}
}