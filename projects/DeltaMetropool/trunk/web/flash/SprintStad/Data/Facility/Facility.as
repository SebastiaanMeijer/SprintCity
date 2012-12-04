package SprintStad.Data.Facility 
{
	import flash.display.Bitmap;
	import flash.display.BitmapData;
	import flash.display.Loader;
	import flash.display.MovieClip;
	import flash.display.Sprite;
	import flash.events.Event;
	import flash.events.IOErrorEvent;
	import flash.net.URLRequest;
	import SprintStad.Debug.ErrorDisplay;
	public class Facility
	{
		public var id:int = 0;
		public var name:String = "";
		public var description:String = "";
		public var image:String = "";
		public var imageData:BitmapData;
		public var citizens:int = 0;
		public var workers:int = 0;
		public var travelers:int = 0;
		
		private var loader:Loader = null;
		
		public function Facility() 
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
			Bitmap(this.loader.content).smoothing = true;
			imageData = new BitmapData(this.loader.width, this.loader.height, true, 0x00ffffff);
			imageData.draw(this.loader);
		}

		function OnLoadError(e:IOErrorEvent):void 
		{
			ErrorDisplay.Get().DisplayError("error loading: " + image);
		}
	}

}