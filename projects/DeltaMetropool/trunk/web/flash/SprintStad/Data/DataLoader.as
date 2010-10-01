package SprintStad.Data 
{
	import flash.events.Event;
	import flash.events.IOErrorEvent;
	import flash.net.URLLoader;
	import flash.net.URLRequest;
	import flash.net.URLVariables;
	import SprintStad.Debug.Debug;
	import SprintStad.Debug.ErrorDisplay;
	import SprintStad;
	public class DataLoader
	{
		private static var instance:DataLoader = new DataLoader();
		
		private var root:SprintStad = null;
		
		public static const DATA_VALUES:int = 0;
		public static const DATA_STATIONS:int = 1;
		public static const DATA_STATION_TYPES:int = 2;
		public static const DATA_TYPES:int = 3;
		public static const DATA_CONSTANTS:int = 4;
		public static const DATA_TEAMS:int = 5;
		public static const DATA_CURRENT_ROUND:int = 6;
		
		private static const sources:Array = new Array(
			"data/values.php",
			"data/stations.php", 
			"data/station_types.php",
			"data/types.php",
			"data/constants.php",
			"data/teams.php",
			"data/current_round.php");
		
		private static const targets:Array = new Array(
			Data.Get().GetValues(),
			Data.Get().GetStations(),
			Data.Get().GetStationTypes(),
			Data.Get().GetTypes(),
			Data.Get().GetConstants(),
			Data.Get().GetTeams(),
			Data.Get());
			
		private var jobs:Array = new Array(new Array(), new Array(), new Array(), new Array(), new Array(), new Array(), new Array());
		private var jobsDone:Boolean = true;
		private var currentJob:int = 0;
			
		public function DataLoader() 
		{
		}
		
		public static function Get():DataLoader
		{
			return instance;
		}
		
		public function SetRoot(root:SprintStad):void
		{
			this.root = root;
		}
		
		public function AddJob(data:int, callback:Function)
		{
			jobs[data].push(callback);
			if (jobsDone)
			{
				jobsDone = false;
				currentJob = data;
				DoJob();
			}
		}
		
		private function DoJob():void
		{
			if (jobs[currentJob].length > 0)
			{
				StartLoading();
			}
			else
			{
				var startIndex = currentJob;
				NextJob();
				while (startIndex != currentJob)
				{
					if (jobs[currentJob].length > 0)
					{
						DoJob();
						return;
					}
					NextJob();
				}
				jobsDone = true;
				return;
			}
		}
		
		private function NextJob():void
		{
			currentJob = (currentJob + 1) % jobs.length;
		}
		
		private function StartLoading()
		{
			try
			{
				// prepare loader vars
				var vars:URLVariables = new URLVariables();
				vars.session = SprintStad.session;
				// load station data
				var loader:URLLoader = new URLLoader();
				var request:URLRequest = new URLRequest(SprintStad.DOMAIN + sources[currentJob]);
				request.data = vars;
				loader.addEventListener(Event.COMPLETE, LoadingDone);
				loader.addEventListener(IOErrorEvent.IO_ERROR , OnLoadError);
				loader.load(request);
			}
			catch (e:Error)
			{
				ErrorDisplay.Get().DisplayError("error loading: " + SprintStad.DOMAIN + sources[currentJob]);
			}
		}
		
		private function LoadingDone(event:Event):void 
		{
			try
			{
				var xmlData:XML = new XML(event.target.data);
				IDataCollection(targets[currentJob]).ParseXML(xmlData);
				IDataCollection(targets[currentJob]).PostConstruct();
			}
			catch (e:Error)
			{
				Debug.out("error in dataloader, LoadingDone() on Job:" + currentJob);
				Debug.out(event.target.data);
			}
			
			try
			{
				for each (var callback:Function in jobs[currentJob])
				{
					callback.call(this, currentJob);
				}
			}
			catch (e:Error)
			{
				Debug.out("error in dataloader callback functions, LoadingDone() on Job:" + currentJob);
			}			
			jobs[currentJob] = new Array();			
			NextJob();
			DoJob();
		}
		
		function OnLoadError(e:IOErrorEvent):void 
		{
			ErrorDisplay.Get().DisplayError("error loading: " + SprintStad.DOMAIN + sources[currentJob]);
		}		
	}
}