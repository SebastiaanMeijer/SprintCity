package SprintStad.Data.Types 
{
	import flash.display.Bitmap;
	import flash.display.BitmapData;
	import flash.display.Loader;
	import flash.display.MovieClip;
	import flash.display.Sprite;
	import flash.events.Event;
	import flash.events.IOErrorEvent;
	import flash.net.URLRequest;
	import SprintStad.Data.Data;
	import SprintStad.Data.Demand.Demand;
	import SprintStad.Data.Round.Round;
	import SprintStad.Data.Station.Station;
	import SprintStad.Debug.Debug;
	import SprintStad.Debug.ErrorDisplay;
	public class Type
	{
		public static const TYPE_HOME = "home";
		public static const TYPE_WORK = "work";
		public static const TYPE_LEISURE = "leisure";
		public static const TYPE_AVERAGE_HOME = "average_home";
		public static const TYPE_AVERAGE_WORK = "average_work";
		public static const TYPE_AVERAGE_LEISURE = "average_leisure";
		
		public var id:int = 0;
		public var name:String = "";
		public var type:String = "";
		public var description:String = "";
		public var color:String = "0x000000";
		public var image:String = "";
		public var imageData:BitmapData;
		public var area_density:Number = 0;
		public var people_density:Number = 0;
		public var colorClip:MovieClip = new Placeholder();
		
		private var demands:Array = new Array();
		
		private var loader:Loader = null;
		
		public function Type() 
		{
		}
		
		public function AddDemand(demand:Demand):void
		{
			demands.push(demand);
		}
		
		public function GetTotalDemand():Number
		{
			var result:Number = 0;
			for each (var demand:Demand in demands)
			{
				result += demand.amount
			}
			return result;
		}
		
		public function GetDemandUntilNow():Number
		{
			var currentRound:int = Data.Get().current_round_id;
			var result:Number = 0;
			
			for each (var demand:Demand in demands)
			{
				if (demand.round_info_id <= currentRound)
					result += demand.amount
			}
			
			for each (var station:Station in Data.Get().GetStations().stations)
			{
				for each (var round:Round in station.rounds)
				{
					if (round.round_info_id < currentRound &&
						round.exec_program != null)
					{
						if (round.exec_program.type_home == this)
							result -= round.exec_program.area_home;
						else if (round.exec_program.type_work == this)
							result -= round.exec_program.area_work;
						else if (round.exec_program.type_leisure == this)
							result -= round.exec_program.area_leisure;
					}
				}
			}
			return result;
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
			Bitmap(this.loader.content).smoothing = true;
			imageData = new BitmapData(this.loader.width, this.loader.height);
			imageData.draw(this.loader);
		}

		function OnLoadError(e:IOErrorEvent):void 
		{
			ErrorDisplay.Get().DisplayError("error loading Type.image: " + image);
		}
		
		public function ParseXML(xmlData:XML):void
		{
			var demand:Demand = new Demand();
			var demandXml:XML = null;
			var index:int = 0;

			demandXml = xmlData.demand[index];
			while (demandXml != null)
			{
				for each (var xml:XML in demandXml.children())
				{
					demand[xml.name()] = xml;
				}
				AddDemand(demand);
				demand = new Demand();
				index++;
				demandXml = xmlData.demand[index];
			}
		}
	}

}