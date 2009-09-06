package SprintStad.State 
{
	import flash.display.MovieClip;
	import flash.events.Event;
	import flash.events.MouseEvent;
	import flash.net.URLLoader;
	import flash.net.URLRequest;
	import flash.net.URLVariables;
	import SprintStad.Data.Station.Station;
	import SprintStad.State.IState;

	public class StationInfoState implements IState
	{
		private var parent:SprintStad = null;
		
		private var loaded:Boolean = false;
		
		public function StationInfoState(parent:SprintStad) 
		{
			this.parent = parent;
		}
		
		private function stationsLoaded(event:Event):void 
		{
			var xmlData:XML = new XML(event.target.data);
			parseStationData(xmlData);
			drawUI();
			loaded = true;
		}
		
		private function stationTypesLoaded(event:Event):void 
		{
			var xmlData:XML = new XML(event.target.data);
			parseStationTypesData(xmlData);
			drawUI();
			loaded = true;
		}
		
		private function parseStationData(xmlData:XML):void
		{
			var xmlList:XMLList = null;
			var station:Station = new Station();
			var xml:XML = null;
			var firstTag:String = "";
			
			xmlList = xmlData.station.children();
			for each (xml in xmlList) 
			{
				var tag:String = xml.name();
				
				if (xml.name() == firstTag)
				{
					parent.GetStations().AddStation(station);
					station = new Station();
				}
				
				if (firstTag == "")
					firstTag = xml.name();
					
				station[xml.name()] = xml;
			}
		}
		
		private function parseStationTypesData(xmlData:XML):void
		{
			
		}
		
		private function drawUI():void
		{
			if (loaded)
			{
				//draw stuff				
				//remove loading screen
				parent.removeChild(SprintStad.LOADER);
			}		
		}
		
		/* INTERFACE SprintStad.State.IState */
		
		public function Activate():void
		{
			parent.addChild(SprintStad.LOADER);
			// prepare loader vars
			var vars:URLVariables = new URLVariables();
			vars.session = parent.session;
			// load station data
			var stationLoader:URLLoader = new URLLoader();
			var stationRequest:URLRequest = new URLRequest(SprintStad.DOMAIN + "data/stations.php");
			stationRequest.data = vars;
			stationLoader.addEventListener(Event.COMPLETE, stationsLoaded);
			stationLoader.load(stationRequest);
			// load station data
			var typesLoader:URLLoader = new URLLoader();
			var typesRequest:URLRequest = new URLRequest(SprintStad.DOMAIN + "data/station_types.php");
			typesRequest.data = vars;
			typesLoader.addEventListener(Event.COMPLETE, stationTypesLoaded);
			typesLoader.load(typesRequest);
		}
		
		public function Deactivate():void
		{
			
		}
		
	}

}