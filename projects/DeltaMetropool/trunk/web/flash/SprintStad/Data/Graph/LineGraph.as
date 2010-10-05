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
				"&station=" + this.stationID));
			this.addChild(loader);
			this.width = 240;
			this.height = 110;
		}
	}

}