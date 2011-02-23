package SprintStad.Data.Graph 
{
	import flash.display.Bitmap;
	import flash.display.Loader;
	import flash.display.Sprite;
	import flash.events.Event;
	import flash.net.URLRequest;
	import SprintStad;
	import SprintStad.Debug.Debug;
	
	public class LineGraph extends Sprite
	{
		private var stationID:int;
		private var graphWidth:int = 480;
		private var graphHeight:int = 220;
		private var graphType:String = GRAPH_SPACE;
		
		private var loader:Loader;
		
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
			this.loader = new Loader();
			var url:URLRequest = new URLRequest(SprintStad.DOMAIN + 
				"images/graphs/" + graphType + ".php?station=" + this.stationID + "&width=" + 
				this.graphWidth + "&height=" + this.graphHeight)
			Debug.out("Load image: " + url.url);
			this.loader.load(url);
			this.loader.contentLoaderInfo.addEventListener(Event.COMPLETE, OnLoadComplete);
		}
		
		public function OnLoadComplete(event:Event):void 
		{
			Bitmap(this.loader.content).smoothing = true;
			this.addChild(loader);
		}
	}
}