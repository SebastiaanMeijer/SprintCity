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
			
			var sessionID:String = FindSession();
			var stations:Stations = Data.Get().GetStations();
			
			LoadGraphs(sessionID, stations);
		}
		
		private function FindSession():String
		{
			return SprintStad.session;
		}
		
		private function LoadGraphs(sessionID:String, stations:Stations):void
		{
			var stationCount:int = stations.GetStationCount();
			
			Debug.out("before for each");
			
			
			
			for (var i:int = 0; i < stationCount; i++)
			{
				var station:Station = stations.stations[i];
				
				
				lineGraphs[station.id] = new SprintStad.Data.Graph.LineGraph(sessionID, station.id);
				Debug.out("In foreachloopieee: StationID:" + station.id);
			}
			
			//for (var i:int = 0; i < stationCount; i++)
				//lineGraphs[i] = new LineGraph(sessionID, i);
			Debug.out("Loaded graphs, sessionID:" + sessionID + " and stationcount:" + stationCount);
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