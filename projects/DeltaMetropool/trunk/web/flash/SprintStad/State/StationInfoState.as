package SprintStad.State 
{
	import fl.controls.TextArea;
	import flash.display.MovieClip;
	import flash.events.Event;
	import flash.events.IOErrorEvent;
	import flash.events.MouseEvent;
	import flash.net.URLLoader;
	import flash.net.URLRequest;
	import flash.net.URLVariables;
	import SprintStad.Data.Round.Round;
	import SprintStad.Data.Station.Station;
	import SprintStad.Data.StationTypes.StationType;
	import SprintStad.Debug.Debug;
	import SprintStad.Debug.ErrorDisplay;
	import SprintStad.State.IState;

	public class StationInfoState implements IState
	{
		private var parent:SprintStad = null;
		
		public function StationInfoState(parent:SprintStad) 
		{
			this.parent = parent;
		}
		
		private function loadStations()
		{
			try
			{
				// prepare loader vars
				var vars:URLVariables = new URLVariables();
				vars.session = parent.session;
				// load station data
				var stationLoader:URLLoader = new URLLoader();
				var stationRequest:URLRequest = new URLRequest(SprintStad.DOMAIN + "data/stations.php");
				stationRequest.data = vars;
				stationLoader.addEventListener(Event.COMPLETE, stationsLoaded);
				stationLoader.addEventListener(IOErrorEvent.IO_ERROR , OnStationLoadError);
				stationLoader.load(stationRequest);
			}
			catch (e:Error)
			{
				ErrorDisplay.Get().DisplayError("error loading: stations; " + SprintStad.DOMAIN + "data/stations.php");
			}
		}
		
		private function loadStationTypes()
		{
			try
			{
				// prepare loader vars
				var vars:URLVariables = new URLVariables();
				vars.session = parent.session;
				// load station data
				var typesLoader:URLLoader = new URLLoader();
				var typesRequest:URLRequest = new URLRequest(SprintStad.DOMAIN + "data/station_types.php");
				typesRequest.data = vars;
				typesLoader.addEventListener(Event.COMPLETE, stationTypesLoaded);
				typesLoader.addEventListener(IOErrorEvent.IO_ERROR , OnStationTypesLoadError);
				typesLoader.load(typesRequest);
			}
			catch (e:Error)
			{
				ErrorDisplay.Get().DisplayError("error loading: station types; " + SprintStad.DOMAIN + "data/station_types.php");
			}
		}
		
		private function stationsLoaded(event:Event):void 
		{
			var xmlData:XML = new XML(event.target.data);
			parseStationData(xmlData);
			loadStationTypes();	
		}
		
		private function stationTypesLoaded(event:Event):void 
		{
			var xmlData:XML = new XML(event.target.data);
			parseStationTypesData(xmlData);
			parent.GetStations().PostConstruct();
			drawUI();
		}
		
		private function parseStationData(xmlData:XML):void
		{
			var xmlList:XMLList = null;
			var station:Station = new Station();
			var xml:XML = null;
			var firstTag:String = "";
			parent.GetStations().AddStation(station);

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
					
				if (xml.name() == "rounds")
					parseRounds(xml.rounds.children(), station);
				else
					station[xml.name()] = xml;
			}
		}
		
		private function parseRounds(xmlList:XMLList, station:Station):void
		{
			var round:Round = new Round();
			var xml:XML = null;
			var firstTag:String = "";
			station.AddRound(round);
			
			for each (xml in xmlList) 
			{
				var tag:String = xml.name();
				
				if (xml.name() == firstTag)
				{
					station.AddRound(round);
					round = new Round();
				}
				
				if (firstTag == "")
					firstTag = xml.name();
					
				round[xml.name()] = xml;
			}
		}
		
		private function parseStationTypesData(xmlData:XML):void
		{
			var xmlList:XMLList = null;
			var stationType:StationType = new StationType();
			var xml:XML = null;
			var firstTag:String = "";
			parent.GetStationTypes().AddStationType(stationType);
			
			xmlList = xmlData.station.children();
			for each (xml in xmlList) 
			{
				var tag:String = xml.name();
				
				if (xml.name() == firstTag)
				{
					parent.GetStationTypes().AddStationType(stationType);
					stationType = new StationType();
				}
				
				if (firstTag == "")
					firstTag = xml.name();
					
				stationType[xml.name()] = xml;
			}
		}
		
		private function drawUI():void
		{
			//draw stuff
			var view:MovieClip = parent.station_info_movie;
			var station:Station = parent.GetStations().GetStation(0); 
			view.name_field.text = station.name;
			view.region_field.text = station.region;
			view.town_field.text = station.town;
			view.description_facts.editable = false;
			view.description_facts.text = station.description_facts;
			view.description_background.editable = false;
			view.description_background.text = station.description_background;
			view.description_future.editable = false;
			view.description_future.text = station.description_future;
			view.sheet.addChild(station.imageData);
			
			
			//remove loading screen
			parent.removeChild(SprintStad.LOADER);
		}
		
		function OnStationLoadError(e:IOErrorEvent):void 
		{
			ErrorDisplay.Get().DisplayError("error loading: stations; " + SprintStad.DOMAIN + "data/stations.php");
		}
		
		function OnStationTypesLoadError(e:IOErrorEvent):void 
		{
			ErrorDisplay.Get().DisplayError("error loading: station types; " + SprintStad.DOMAIN + "data/station_types.php");
		}
		
		/* INTERFACE SprintStad.State.IState */
		
		public function Activate():void
		{
			parent.addChild(SprintStad.LOADER);
			loadStations();
		}
		
		public function Deactivate():void
		{
			
		}
		
	}

}