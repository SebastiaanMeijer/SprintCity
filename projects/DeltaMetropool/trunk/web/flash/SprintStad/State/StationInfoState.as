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
	import flash.text.TextFormat;
	import flash.text.TextFormatAlign;
	import SprintStad.Calculators.Result.StationTypeEntry;
	import SprintStad.Calculators.StationStatsCalculator;
	import SprintStad.Calculators.StationTypeCalculator;
	import SprintStad.Data.Data;
	import SprintStad.Data.DataLoader;
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
		
		public function SetCurrentStation(index:int):void
		{
			currentStation = index % Data.Get().GetStations().GetStationCount();
		}
		
		public function NextStation():void
		{
			currentStation = (currentStation + 1) % Data.Get().GetStations().GetStationCount();
			DrawUI(currentStation);
		}
		
		public function PreviousStation():void
		{
			currentStation = Math.abs(currentStation - 1) % Data.Get().GetStations().GetStationCount();
			DrawUI(currentStation);
		}
		
		public function NextStationEvent(e:Event):void
		{
			NextStation();
		}
		
		public function PreviousStationEvent(e:Event):void
		{
			PreviousStation();
		}
		
		private function DrawUI(index:int):void
		{
			//draw stuff
			var view:MovieClip = parent.station_info_movie;
			var station:Station = Data.Get().GetStations().GetStation(index); 
			
			// station sign
			view.board.name_field.text = station.name;
			view.board.region_field.text = station.region;
			view.board.town_field.text = station.town;
			
			var textAreaFormat:TextFormat = new TextFormat();
			textAreaFormat.align = TextFormatAlign.JUSTIFY;
			textAreaFormat.size = 9;
			
			// right info
			view.description_facts.editable = false;
			view.description_facts.setStyle("textFormat", textAreaFormat);
			view.description_facts.text = station.description_facts;
			
			view.description_background.editable = false;
			view.description_background.setStyle("textFormat", textAreaFormat);
			view.description_background.text = station.description_background;
			
			view.description_future.editable = false;
			view.description_future.setStyle("textFormat", textAreaFormat);
			view.description_future.text = station.description_future;
			
			// background
			view.sheet.addChild(station.imageData);

			//left info
			var top:Array = StationTypeCalculator.GetStationTypeTop(station);
			
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
			
			AreaBarDrawer.DrawBar(view.area_bar,
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
			AreaBarDrawer.DrawBar(view.transform_area_bar, 
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
				
			view.amount_travelers.text = StationStatsCalculator.GetTravelersStats(station);
			view.amount_citizens.text = int(station.count_home_total * Data.Get().GetConstants().average_citizens_per_home);
			view.amount_workers.text = int(station.count_work_total * Data.Get().GetConstants().average_workers_per_bvo);
			view.amount_houses.text = station.count_home_total;
			view.bvo_work.text = station.count_work_total;
		}
		
		function OnStationLoadError(e:IOErrorEvent):void 
		{
			ErrorDisplay.Get().DisplayError("error loading: stations; " + SprintStad.DOMAIN + "data/stations.php");
		}
		
		private function OnNextButton(event:MouseEvent):void
		{
			parent.gotoAndPlay(SprintStad.FRAME_OVERVIEW);
		}
		
		private function OnValuesButton(event:MouseEvent):void
		{
			parent.gotoAndPlay(SprintStad.FRAME_VALUES);
		}
		
		public function OnLoadingDone(data:int)
		{
			var view:MovieClip = parent.station_info_movie;
			DrawUI(currentStation);
			view.previous_station_button.addEventListener(MouseEvent.CLICK, PreviousStationEvent);
			view.next_station_button.addEventListener(MouseEvent.CLICK, NextStationEvent);
			//remove loading screen
			parent.removeChild(SprintStad.LOADER);
		}
		
		/* INTERFACE SprintStad.State.IState */
		
		public function Activate():void
		{
			var view:MovieClip = parent.station_info_movie;
			parent.addChild(SprintStad.LOADER);
			view.next_button.buttonMode = true;
			view.next_button.addEventListener(MouseEvent.CLICK, OnNextButton);
			view.values_button.buttonMode = true;
			view.values_button.addEventListener(MouseEvent.CLICK, OnValuesButton);
			DataLoader.Get().AddJob(DataLoader.DATA_STATIONS, OnLoadingDone);
		}
		
		public function Deactivate():void
		{
			
		}
		
	}

}