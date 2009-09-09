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
	import SprintStad.Calculators.Result.StationTypeEntry;
	import SprintStad.Calculators.StationTypeCalculator;
	import SprintStad.Data.Data;
	import SprintStad.Data.Round.Round;
	import SprintStad.Data.Station.Station;
	import SprintStad.Data.StationTypes.StationType;
	import SprintStad.Debug.Debug;
	import SprintStad.Debug.ErrorDisplay;
	import SprintStad.Drawer.AreaBarDrawer;
	import SprintStad.State.IState;

	public class StationInfoState implements IState
	{
		private var parent:SprintStad = null;
		private var currentStation:int = 0;
		
		public function StationInfoState(parent:SprintStad) 
		{
			this.parent = parent;
		}
		
		public function NextStation():void
		{
			currentStation = (currentStation + 1) % Data.Get().GetStations().GetStationCount();
			drawUI(currentStation);
		}
		
		public function PreviousStation():void
		{
			currentStation = Math.abs(currentStation - 1) % Data.Get().GetStations().GetStationCount();
			drawUI(currentStation);
		}
		
		public function NextStationEvent(e:Event):void
		{
			NextStation();
		}
		
		public function PreviousStationEvent(e:Event):void
		{
			PreviousStation();
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
			Data.Get().GetStations().PostConstruct();
			loadStationTypes();	
		}
		
		private function stationTypesLoaded(event:Event):void 
		{
			var view:MovieClip = parent.station_info_movie;
			var xmlData:XML = new XML(event.target.data);
			parseStationTypesData(xmlData);
			Data.Get().GetStationTypes().PostConstruct();
			drawUI(currentStation);			
			view.previous_station_button.addEventListener(MouseEvent.CLICK, PreviousStationEvent);
			view.next_station_button.addEventListener(MouseEvent.CLICK, NextStationEvent);
			//remove loading screen
			parent.removeChild(SprintStad.LOADER);
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
					Data.Get().GetStations().AddStation(station);
					station = new Station();
				}
				
				if (firstTag == "")
					firstTag = xml.name();
					
				if (xml.name() == "rounds")
					parseRounds(xml.rounds.children(), station);
				else
					station[xml.name()] = xml;
			}
			Data.Get().GetStations().AddStation(station);
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
			
			xmlList = xmlData.station_type.children();
			for each (xml in xmlList) 
			{
				var tag:String = xml.name();
				
				if (xml.name() == firstTag)
				{
					Data.Get().GetStationTypes().AddStationType(stationType);
					stationType = new StationType();
				}
				
				if (firstTag == "")
					firstTag = xml.name();
					
				stationType[xml.name()] = xml;
			}
			Data.Get().GetStationTypes().AddStationType(stationType);
		}
		
		private function drawUI(index:int):void
		{
			//draw stuff
			var view:MovieClip = parent.station_info_movie;
			var station:Station = Data.Get().GetStations().GetStation(index); 
			
			// station sign
			view.name_field.text = station.name;
			view.region_field.text = station.region;
			view.town_field.text = station.town;
			
			// right info
			view.description_facts.editable = false;
			view.description_facts.text = station.description_facts;
			
			view.description_background.editable = false;
			view.description_background.text = station.description_background;
			
			view.description_future.editable = false;
			view.description_future.text = station.description_future;
			
			// background
			view.sheet.addChild(station.imageData);

			//left info
			var top:Array = StationTypeCalculator.Get().GetStationTypeTop(station);
			
			view.station_type_1_percent.text = top[0].similarity + "%";
			top[0].stationType.imageData.width = 100;
			top[0].stationType.imageData.height = 100;
			view.station_type_1_image.addChild(top[0].stationType.imageData);
			
			view.station_type_2_percent.text = top[1].similarity + "%";
			top[1].stationType.imageData.width = 100;
			top[1].stationType.imageData.height = 100;
			view.station_type_2_image.addChild(top[1].stationType.imageData);
			
			view.station_type_3_percent.text = top[2].similarity + "%";
			top[2].stationType.imageData.width = 100;
			top[2].stationType.imageData.height = 100;
			view.station_type_3_image.addChild(top[2].stationType.imageData);
			
			AreaBarDrawer.Get().DrawBar(view.area_bar,
				station.area_cultivated_home,
				station.area_cultivated_work,
				station.area_cultivated_mixed, 
				station.area_undeveloped_urban,
				station.area_undeveloped_rural);
			view.area.text = "(" + (
				station.area_cultivated_home +
				station.area_cultivated_work +
				station.area_cultivated_mixed + 
				station.area_undeveloped_urban + 
				station.area_undeveloped_rural) + " ha.)";
			AreaBarDrawer.Get().DrawBar(view.transform_area_bar, 
				station.transform_area_cultivated_home, 
				station.transform_area_cultivated_work, 
				station.transform_area_cultivated_mixed, 
				station.transform_area_undeveloped_urban,
				station.transform_area_undeveloped_mixed);
			view.transform_area.text = "(" + ( 
				station.transform_area_cultivated_home + 
				station.transform_area_cultivated_work + 
				station.transform_area_cultivated_mixed +  
				station.transform_area_undeveloped_urban +
				station.transform_area_undeveloped_mixed) + " ha.)";
			view.ha_home.text = station.area_cultivated_home;
			view.bvo_work.text = station.area_cultivated_work;
			view.bvo_leisure.text = station.area_cultivated_mixed;
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