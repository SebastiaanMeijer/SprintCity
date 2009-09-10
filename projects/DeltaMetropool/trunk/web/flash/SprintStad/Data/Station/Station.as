package SprintStad.Data.Station 
{
	import flash.display.Loader;
	import flash.display.MovieClip;
	import flash.display.Sprite;
	import flash.events.Event;
	import flash.events.IOErrorEvent;
	import flash.net.URLRequest;
	import flash.utils.Proxy;
	import SprintStad.Data.Round.Round;
	import SprintStad.Debug.ErrorDisplay;
	public class Station
	{	
		public var id:int = 0;
		public var code:String = "";
		public var name:String = "";
		public var description_facts:String = "";
		public var description_background:String = "";
		public var description_future:String = "";
		public var image:String = "";
		public var imageData:Sprite = new Sprite();
		public var town:String = "";
		public var region:String = "";
		public var POVN:Number = 0;
		public var PWN:Number = 0;
		public var IWD:Number = 0;
		public var MNG:Number = 0;
		public var area_cultivated_home:int = 0;
		public var area_cultivated_work:int = 0;
		public var area_cultivated_mixed:int = 0;
		public var area_undeveloped_urban:int = 0;
		public var area_undeveloped_rural:int = 0;
		public var transform_area_cultivated_home:int = 0;
		public var transform_area_cultivated_work:int = 0;
		public var transform_area_cultivated_mixed:int = 0;
		public var transform_area_undeveloped_urban:int = 0;
		public var transform_area_undeveloped_mixed:int = 0;
		public var count_home_total:int = 0;
		public var count_home_transform:int = 0;
		public var count_work_total:int = 0;
		public var count_work_transform:int = 0;
		
		private var rounds:Array = new Array();
		
		private var loader:Loader = null;
		
		public function Station() 
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
			imageData.width = SprintStad.WIDTH;
			imageData.height = SprintStad.HEIGHT;
		}

		function OnLoadError(e:IOErrorEvent):void 
		{
			ErrorDisplay.Get().DisplayError("error loading: " + image);
		}
		
		public function AddRound(round:Round):void
		{
			rounds.push(round);
		}
		
		public function GetRound(index:int):Round
		{
			return rounds[index];
		}
		
		public function GetRoundById(id:int):Round
		{
			for each (var round:Round in rounds)
				if (round.id == id)
					return round;
			return null;
		}
		
		public function GetRoundCount():int
		{
			return rounds.length;
		}
		
		public function ParseXML(xmlList:XMLList):void
		{
			var round:Round = new Round();
			var xml:XML = null;
			var firstTag:String = "";
			AddRound(round);
			
			for each (xml in xmlList) 
			{
				var tag:String = xml.name();
				
				if (xml.name() == firstTag)
				{
					AddRound(round);
					round = new Round();
				}
				
				if (firstTag == "")
					firstTag = xml.name();
					
				round[xml.name()] = xml;
			}
		}
	}
}