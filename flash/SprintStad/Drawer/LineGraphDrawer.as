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
		private var spaceGraphs:Array = new Array();
		private var mobilityGraphs:Array = new Array();
		
		public function LineGraphDrawer() 
		{
			var stations:Stations = Data.Get().GetStations();
			
			LoadGraphs(stations);
		}
		
		private function LoadGraphs(stations:Stations):void
		{
			var stationCount:int = stations.GetStationCount();
			
			for (var i:int = 0; i < stationCount; i++)
			{
				var station:Station = stations.stations[i];
				spaceGraphs[station.id] = new LineGraph(station.id, LineGraph.GRAPH_SPACE);
				mobilityGraphs[station.id] = new LineGraph(station.id, LineGraph.GRAPH_MOBILITY);
			}
		}
		
		private function RemoveAllChildren(parent:MovieClip):void
		{
			while (parent.numChildren > 0)
				parent.removeChildAt(0);
		}
		
		public function DrawSpaceGraph(parent:MovieClip, stationID:int):void
		{
			RemoveAllChildren(parent);
			parent.addChild(spaceGraphs[stationID]);
		}
		
		public function DrawMobilityGraph(parent:MovieClip, stationID:int):void
		{
			RemoveAllChildren(parent);
			parent.addChild(mobilityGraphs[stationID]);
		}
	}
}