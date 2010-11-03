package SprintStad.Data.Graph 
{
	import flash.display.Loader;
	import flash.display.Sprite;
	import flash.net.URLRequest;
	import SprintStad;
	import SprintStad.Debug.Debug;
	
	public class LineGraph extends Sprite
	{
		private var stationID:int;
		private var graphWidth:int = 480;
		private var graphHeight:int = 220;
		private var graphType:String = GRAPH_SPACE;
		
		public static const GRAPH_SPACE = "spacegraph";
		public static const GRAPH_MOBILITY = "mobilitygraph";
		
		public function LineGraph(stationID:int, graphType:String) 
		{
			this.stationID = stationID;
			this.graphType = graphType;
			LoadImage();
		}
		
		private function LoadImage():void
		{
			var loader:Loader = new Loader();
			var url:URLRequest = new URLRequest(SprintStad.DOMAIN + 
				"images/graphs/" + graphType + ".php?station=" + this.stationID + "&width=" + 
				this.graphWidth + "&height=" + this.graphHeight)
			Debug.out("Load image: " + url.url);
			loader.load(url);
			this.addChild(loader);
		}
	}
}