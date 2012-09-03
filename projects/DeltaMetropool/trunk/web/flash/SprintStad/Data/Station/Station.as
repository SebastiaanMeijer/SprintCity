package SprintStad.Data.Station 
{
	import flash.display.Bitmap;
	import flash.display.Loader;
	import flash.display.MovieClip;
	import flash.display.Sprite;
	import flash.events.Event;
	import flash.events.IOErrorEvent;
	import flash.net.URLRequest;
	import flash.utils.Proxy;
	import SprintStad.Data.Data;
	import SprintStad.Data.Program.Program;
	import SprintStad.Data.Team.Team;
	import SprintStad.Data.Round.Round;
	import SprintStad.Data.Team.Team;
	import SprintStad.Debug.Debug;
	import SprintStad.Debug.ErrorDisplay;
	import SprintStad.Drawer.AreaBarDrawer;
	public class Station
	{	
		public static var image:String = "/SprintStad/images/stations/";
		
		public var id:int = 0;
		public var team_id:int = 0;
		public var code:String = "";
		public var name:String = "";
		public var description_facts:String = "";
		public var description_background:String = "";
		public var description_future:String = "";
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
		public var transform_area_undeveloped_rural:int = 0;
		public var count_home_total:int = 0;
		public var count_home_transform:int = 0;
		public var count_work_total:int = 0;
		public var count_work_transform:int = 0;
		public var count_worker_total:int = 0;
		public var count_worker_transform:int = 0;
		public var currentIU:int = 0;
		public var capUnder:int = 0;
		public var capOver:int = 0;
		
		public var rounds:Array = new Array();
		
		private var loader:Loader = null;
		
		// post data
		public var imageData:Sprite = new Sprite();
		public var areaBar:AreaBarDrawer;
		public var owner:Team;
		
		// game data
		public var program:Program = new Program();
		
		public function Station() 
		{
			areaBar = new AreaBarDrawer(new Placeholder());
		}
		
		public function RefreshAreaBar():void
		{
			var allocated:Number = 1;
			if (Data.Get().current_round_id > 2)
			{
				var round:Round = GetRoundById(Data.Get().current_round_id - 1);
				if (round.plan_program.TotalArea() > 0)
					allocated = round.exec_program.TotalArea() / round.plan_program.TotalArea();
			}
			areaBar.drawStationCircle(allocated);
		}
		
		public function RefreshMobilityBar():void
		{
			areaBar.drawMobilityCircle(currentIU, capUnder, capOver);
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
			{
				if (round.round_info_id == id)
					return round;
			}
			return null;
		}
		
		public function GetCurrentRound():Round
		{
			return GetRoundById(Data.Get().current_round_id);
		}
		
		public function GetLatestRound():Round
		{
			var result:Round = rounds[0];
			for (var i:int = 0; i < rounds.length; i++)
			{
				if (rounds[i].number > result.number)
					result = rounds[i];
			}
			return result;
		}
		
		public function GetRoundCount():int
		{
			return rounds.length;
		}
		
		public function IsLastRound(round_id:int):Boolean
		{
			return round_id - 1 == rounds[rounds.length - 1].round_info_id;
		}
		
		public function GetTotalTransformArea():int
		{
			return transform_area_cultivated_home + 
				transform_area_cultivated_mixed +
				transform_area_cultivated_work +
				transform_area_undeveloped_rural + 
				transform_area_undeveloped_urban;
		}
		

		public function PostConstruct():void
		{
			try
			{
				this.owner = Data.Get().GetTeams().GetTeamById(team_id);
				this.loader = new Loader();
				this.loader.load(new URLRequest(image + this.code + ".png"));
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
			imageData.addChild(this.loader);
			imageData.width = SprintStad.WIDTH;
			imageData.height = SprintStad.HEIGHT;
		}

		function OnLoadError(e:IOErrorEvent):void 
		{
			ErrorDisplay.Get().DisplayError("error loading: " + image + this.code +".png");
		}
		
		public function ParseXML(xmlData:XML):void
		{
			var round:Round = new Round();
			var roundXml:XML = null;
			var index:int = 0;

			roundXml = xmlData.round[index];
			while (roundXml != null)
			{
				for each (var xml:XML in roundXml.children())
				{
					if (xml.name() == "plan_program")
					{
						round.plan_program = new Program();
						round.plan_program.ParseXML(xml);
					}
					else if (xml.name() == "exec_program")
					{
						round.exec_program = new Program();
						round.exec_program.ParseXML(xml);
					}
					else
					{
						round[xml.name()] = xml;
					}
				}
				AddRound(round);
				round = new Round();
				index++;
				roundXml = xmlData.round[index];
			}
		}
		
		public function PrintRounds():void
		{
			Debug.out("----" + name + "----");
			for each (var round:Round in rounds)
			{
				Debug.out(round.round_info_id + " " + round.name);
				Debug.out("   " + round.plan_program);
				Debug.out("   " + round.exec_program);
			}
		}
		
		public function ToString():String
		{
			var result:String = ""
			result += "------------------------\n";
			result += name + " of team " + team_id + "\n";
			result += "POVN:" + POVN + " PWN:" + PWN + " IWD:" + IWD + " MNG:" + MNG + "\n";
			result += "area: (h:" + area_cultivated_home + " w:" + area_cultivated_work + " l:" + area_cultivated_mixed + " u:" + area_undeveloped_urban + " r:" + area_undeveloped_rural + ")\n";
			result += "transform: (h:" + transform_area_cultivated_home + " w:" + transform_area_cultivated_work + " l:" + transform_area_cultivated_mixed + " u:" + transform_area_undeveloped_urban + " r:" + transform_area_undeveloped_rural + ")\n";
			result += "houses: (total:" + count_home_total + " tranform:" + count_home_transform + ")\n";
			result += "bvo work: (total:" + count_work_total + " transform:" + count_work_transform + ")\n";
			result += "workers: (total:" + count_worker_total + " transform:" + count_worker_transform + ")";
			return result;
		}
	}
}