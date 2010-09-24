package SprintStad.State 
{
	import fl.controls.TextArea;
	import flash.display.Bitmap;
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
	import SprintStad.Data.Station.StationInstance;
	import SprintStad.Data.StationTypes.StationType;
	import SprintStad.Debug.Debug;
	import SprintStad.Debug.ErrorDisplay;
	import SprintStad.Drawer.AreaBarDrawer;
	import SprintStad.State.IState;

	public class StationInfoState implements IState
	{
		private var parent:SprintStad = null;
		private var barTotalArea:AreaBarDrawer;
		private var barTransformArea:AreaBarDrawer;
		
		private var popup:StationTypePopup = null;
		private var popupImage:Bitmap = new Bitmap();
		
		public function StationInfoState(parent:SprintStad) 
		{
			this.parent = parent;
		}
		
		public function NextStationEvent(e:Event):void
		{
			parent.currentStationIndex = Data.Get().GetStations().GetNextStation(parent.currentStationIndex);
			DrawUI(parent.GetCurrentStation());
		}
		
		public function PreviousStationEvent(e:Event):void
		{
			parent.currentStationIndex = Data.Get().GetStations().GetPreviousStation(parent.currentStationIndex);
			DrawUI(parent.GetCurrentStation());
		}
		
		private function DrawUI(station:Station):void
		{
			//draw stuff
			var view:MovieClip = parent.station_info_movie;
			var stationInstance:StationInstance = StationInstance.Create(station);
			// station sign
			view.board.name_field.text = station.name;
			view.board.region_field.text = station.region;
			view.board.town_field.text = station.town;
			
			var textAreaFormat:TextFormat = new TextFormat();
			textAreaFormat.align = TextFormatAlign.JUSTIFY;
			textAreaFormat.size = 9;
			
			// right info
			view.description_background.editable = false;
			view.description_background.setStyle("textFormat", textAreaFormat);
			view.description_background.text = station.description_background;
			
			view.description_future.editable = false;
			view.description_future.setStyle("textFormat", textAreaFormat);
			view.description_future.text = station.description_future;
			
			// background
			view.sheet.addChild(station.imageData);
			
			//left info
			view.current_info.title.text = "";
			var top:Array = StationTypeCalculator.GetStationTypeTop(stationInstance);
			var bitmap:Bitmap;
			
			view.current_info.station_type_1_percent.text = top[0].similarity + "%";
			view.current_info.station_type_1_name.text = top[0].stationType.name;
			bitmap = new Bitmap(top[0].stationType.imageData);
			bitmap.width = 100;
			bitmap.height = 100;
			view.current_info.station_type_1_image.addChild(bitmap);
			view.current_info.station_type_1_image.stationType = top[0].stationType;
			view.current_info.station_type_1_image.addEventListener(MouseEvent.MOUSE_OVER, MouseOverType);
			view.current_info.station_type_1_image.addEventListener(MouseEvent.MOUSE_OUT, MouseOutType);
			
			view.current_info.station_type_2_percent.text = top[1].similarity + "%";
			view.current_info.station_type_2_name.text = top[1].stationType.name;
			bitmap = new Bitmap(top[1].stationType.imageData);
			bitmap.width = 100;
			bitmap.height = 100;
			view.current_info.station_type_2_image.addChild(bitmap);
			view.current_info.station_type_2_image.stationType = top[1].stationType;
			view.current_info.station_type_2_image.addEventListener(MouseEvent.MOUSE_OVER, MouseOverType);
			view.current_info.station_type_2_image.addEventListener(MouseEvent.MOUSE_OUT, MouseOutType);
			
			view.current_info.station_type_3_percent.text = top[2].similarity + "%";
			view.current_info.station_type_3_name.text = top[2].stationType.name;
			bitmap = new Bitmap(top[2].stationType.imageData);
			bitmap.width = 100;
			bitmap.height = 100;
			view.current_info.station_type_3_image.addChild(bitmap);
			view.current_info.station_type_3_image.stationType = top[2].stationType;
			view.current_info.station_type_3_image.addEventListener(MouseEvent.MOUSE_OVER, MouseOverType);
			view.current_info.station_type_3_image.addEventListener(MouseEvent.MOUSE_OUT, MouseOutType);
			
			barTotalArea.DrawBar(
				station.area_cultivated_home,
				station.area_cultivated_work,
				station.area_cultivated_mixed, 
				station.area_undeveloped_urban,
				station.area_undeveloped_rural,
				0);			
			view.current_info.area.text = "(" + (
				station.area_cultivated_home +
				station.area_cultivated_work +
				station.area_cultivated_mixed + 
				station.area_undeveloped_urban + 
				station.area_undeveloped_rural) + " ha.)";			
			barTransformArea.DrawBar(
				station.transform_area_cultivated_home, 
				station.transform_area_cultivated_work, 
				station.transform_area_cultivated_mixed, 
				station.transform_area_undeveloped_urban,
				station.transform_area_undeveloped_mixed,
				0);			
			view.current_info.transform_area.text = "(" + ( 
				station.transform_area_cultivated_home + 
				station.transform_area_cultivated_work + 
				station.transform_area_cultivated_mixed +  
				station.transform_area_undeveloped_urban +
				station.transform_area_undeveloped_mixed) + " ha.)";
			
			view.current_info.amount_travelers.text = StationStatsCalculator.GetTravelersStats(stationInstance);
			view.current_info.amount_citizens.text = int(station.count_home_total * Data.Get().GetConstants().average_citizens_per_home);
			view.current_info.amount_workers.text = int(station.count_work_total * Data.Get().GetConstants().average_workers_per_bvo);
			view.current_info.amount_houses.text = station.count_home_total;
			view.current_info.bvo_work.text = station.count_work_total;
		}
		
		private function MouseOverType(event:MouseEvent):void
		{
			ShowPopup(event.target.stationType);
		}
		
		private function MouseOutType(event:MouseEvent):void
		{
			HidePopup();
		}
		
		private function ShowPopup(type:StationType):void
		{
			popup.title.text = type.name;
			popup.description.text = type.description;
			popupImage = new Bitmap(type.imageData);
			popupImage.width = 100;
			popupImage.height = 100;
			popup.image.addChild(popupImage);
			parent.addChild(popup);
		}
		
		private function HidePopup()
		{
			popup.image.removeChild(popupImage);
			parent.removeChild(popup);
		}
		
		private function OnCancelButton(event:MouseEvent):void
		{
			parent.gotoAndPlay(SprintStad.FRAME_OVERVIEW);
		}
		
		public function OnLoadingDone(data:int)
		{
			var view:MovieClip = parent.station_info_movie;
			DrawUI(parent.GetCurrentStation());
			
			view.previous_station_button.buttonMode = true;
			view.previous_station_button.addEventListener(MouseEvent.CLICK, PreviousStationEvent);
			view.next_station_button.buttonMode = true;
			view.next_station_button.addEventListener(MouseEvent.CLICK, NextStationEvent);
			
			//remove loading screen
			parent.removeChild(SprintStad.LOADER);
		}
		
		/* INTERFACE SprintStad.State.IState */
		
		public function Activate():void
		{
			var view:MovieClip = parent.station_info_movie;
			parent.addChild(SprintStad.LOADER);
			
			view.cancel_button.buttonMode = true;
			view.cancel_button.addEventListener(MouseEvent.CLICK, OnCancelButton);
			
			// init station type poput menu
			popup = new StationTypePopup();
			popup.image.addChild(popupImage);
			popup.x = 287;
			popup.y = 120;
			
			// init bar graphs
			barTotalArea = new AreaBarDrawer(view.current_info.area_bar);
			barTransformArea = new AreaBarDrawer(view.current_info.transform_area_bar);
			
			DataLoader.Get().AddJob(DataLoader.DATA_STATIONS, OnLoadingDone);
		}
		
		public function Deactivate():void
		{
			
		}
		
	}

}