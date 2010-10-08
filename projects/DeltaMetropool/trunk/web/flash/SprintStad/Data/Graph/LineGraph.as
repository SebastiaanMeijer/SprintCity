package SprintStad.Data.Graph 
{
	import flash.display.Loader;
	import flash.display.Sprite;
	import flash.net.URLRequest;
	import SprintStad;
	
	public class LineGraph extends Sprite
	{
		private var sessionID:String;
		private var stationID:int;
		private var graphWidth:int = 240;
		private var graphHeight:int = 110;
		
		public function LineGraph(sessionID:String, stationID:int) 
		{
			this.sessionID = sessionID;
			this.stationID = stationID;
			
			LoadImage();
		}
		
		private function LoadImage():void
		{
			var loader:Loader = new Loader(); 
			loader.load(new URLRequest(SprintStad.DOMAIN + 
				"images/graphs/spacegraph.php?session=" + this.sessionID + 
				"&station=" + this.stationID + "&width=" + 
				this.graphWidth + "&height=" + this.graphHeight));

			this.addChild(loader);
			
		}
	}

}