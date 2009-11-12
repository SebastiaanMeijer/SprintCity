package SprintStad.Data.Types 
{
	import flash.display.Loader;
	import flash.display.MovieClip;
	import flash.display.Sprite;
	import flash.events.Event;
	import flash.events.IOErrorEvent;
	import flash.net.URLRequest;
	import SprintStad.Debug.Debug;
	import SprintStad.Debug.ErrorDisplay;
	public class Type
	{
		public var id:int = 0;
		public var name:String = "";
		public var type:String = "";
		public var description:String = "";
		public var color:String = "0x000000";
		public var image:String = "";
		public var imageData:Sprite = new Sprite();
		public var density:Number = 0;
		public var colorClip:MovieClip = new Placeholder();
		
		private var loader:Loader = null;
		
		public function Type() 
		{
		}
		
		public function PostConstruct():void
		{
			try
			{
				if (image != "")
				{
					this.loader = new Loader();
					this.loader.load(new URLRequest(image));
					this.loader.contentLoaderInfo.addEventListener(Event.COMPLETE, OnLoadComplete);
					this.loader.contentLoaderInfo.addEventListener(IOErrorEvent.IO_ERROR , OnLoadError);
				}
			}
			catch (e:Error)
			{
				ErrorDisplay.Get().DisplayError("error loading Type.image: " + image);
			}
			colorClip.graphics.clear();
			colorClip.graphics.beginFill(parseInt("0x" + color, 16));
			colorClip.graphics.drawRect(0, 0, 100, 100);
			colorClip.graphics.endFill();
 		}
		
		public function OnLoadComplete(event:Event):void 
		{
			imageData.addChild(this.loader);
		}

		function OnLoadError(e:IOErrorEvent):void 
		{
			ErrorDisplay.Get().DisplayError("error loading Type.image: " + image);
		}
	}

}