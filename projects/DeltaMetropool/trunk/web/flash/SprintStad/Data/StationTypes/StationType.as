package SprintStad.Data.StationTypes 
{
	import flash.display.Loader;
	import flash.display.Sprite;
	import flash.events.Event;
	import flash.events.IOErrorEvent;
	import flash.net.URLRequest;
	import SprintStad.Debug.ErrorDisplay;
	public class StationType
	{
		public var id:int = 0;
		public var name:String = "";
		public var description:String = "";
		public var image:String = "";
		public var imageData:Sprite = new Sprite();
		public var POVN:Number = 0;
		public var PWN:Number = 0;
		public var IWD:Number = 0;
		public var MNG:Number = 0;
		
		private var loader:Loader = null;
		
		public function StationType() 
		{
			
		}
		
		public function PostConstruct():void
		{
			try
			{
				this.loader = new Loader();
				this.loader.load(new URLRequest(image));
				this.loader.contentLoaderInfo.addEventListener(Event.COMPLETE, OnLoadComplete);
				this.loader.contentLoaderInfo.addEventListener(IOErrorEvent.IO_ERROR , OnLoadError);
			}
			catch (e:Error)
			{
				ErrorDisplay.Get().DisplayError("error loading: " + image);
			}
 		}
		
		public function OnLoadComplete(event:Event):void 
		{
			imageData.addChild(this.loader);
		}

		function OnLoadError(e:IOErrorEvent):void 
		{
			ErrorDisplay.Get().DisplayError("error loading: " + image);
		}
	}

}