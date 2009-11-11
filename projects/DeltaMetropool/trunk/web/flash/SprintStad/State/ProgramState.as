package SprintStad.State 
{
	import flash.display.MovieClip;
	import flash.events.Event;
	import flash.events.MouseEvent;
	import flash.net.URLLoader;
	import flash.net.URLRequest;
	import flash.net.URLVariables;
	import SprintStad.Calculators.StationStatsCalculator;
	import SprintStad.Calculators.StationTypeCalculator;
	import SprintStad.Data.Data;
	import SprintStad.Data.DataLoader;
	import SprintStad.Data.Program.Program;
	import SprintStad.Data.Station.Station;
	import SprintStad.Data.Station.StationInstance;
	import SprintStad.Data.Types.Types;
	import SprintStad.Debug.Debug;
	import SprintStad.Drawer.AreaBarDrawer;
	import SprintStad.Drawer.ProgramEditor;
	import SprintStad.Drawer.ProgramSlider;
	public class ProgramState implements IState
	{		
		private var parent:SprintStad = null;
		private var editor:ProgramEditor;
		private var currentProgram:Program;
		
		private var barCenterTransformArea:AreaBarDrawer;
		private var barCurrentArea:AreaBarDrawer;
		private var barCurrentTransformArea:AreaBarDrawer;
		private var barFutureArea:AreaBarDrawer;
		private var barFutureTransformArea:AreaBarDrawer;
		
		public function ProgramState(parent:SprintStad) 
		{
			this.parent = parent;
		}
			
		private function DrawUI(station:Station):void
		{
			// draw stuff
			var view:MovieClip = parent.program_movie;
			
			// station sign
			view.board.name_field.text = station.name;
			view.board.region_field.text = station.region;
			view.board.town_field.text = station.town;
			
			// background
			view.sheet.addChild(station.imageData);
			
			// graphs
			barCenterTransformArea.DrawBar(
				station.transform_area_cultivated_home, 
				station.transform_area_cultivated_work, 
				station.transform_area_cultivated_mixed, 
				station.transform_area_undeveloped_urban,
				station.transform_area_undeveloped_mixed);
			
			// left info
			DrawStationInfo(StationInstance.Create(station), view.current_info, barCurrentArea, barCurrentTransformArea, "HUIDIG");
			
			// update editor
			editor.SetStation(station);
		}
		
		private function DrawStationInfo(station:StationInstance, 
			clip:MovieClip, area_bar:AreaBarDrawer, transform_area_bar:AreaBarDrawer, 
			title:String)
		{
			var top:Array = StationTypeCalculator.GetStationTypeTop(station);
			
			clip.title.text = title;
			
			clip.station_type_1_percent.text = top[0].similarity + "%";
			clip.station_type_1_name.text = top[0].stationType.name;
			top[0].stationType.imageData.width = 100;
			top[0].stationType.imageData.height = 100;
			clip.station_type_1_image.addChild(top[0].stationType.imageData);
			
			clip.station_type_2_percent.text = top[1].similarity + "%";
			clip.station_type_2_name.text = top[1].stationType.name;
			top[1].stationType.imageData.width = 100;
			top[1].stationType.imageData.height = 100;
			clip.station_type_2_image.addChild(top[1].stationType.imageData);
			
			clip.station_type_3_percent.text = top[2].similarity + "%";
			clip.station_type_3_name.text = top[2].stationType.name;
			top[2].stationType.imageData.width = 100;
			top[2].stationType.imageData.height = 100;
			clip.station_type_3_image.addChild(top[2].stationType.imageData);
			
			area_bar.DrawBar(
				station.area_cultivated_home,
				station.area_cultivated_work,
				station.area_cultivated_mixed, 
				station.area_undeveloped_urban,
				station.area_undeveloped_rural);
			clip.area.text = "(" + Math.round(
				station.area_cultivated_home +
				station.area_cultivated_work +
				station.area_cultivated_mixed + 
				station.area_undeveloped_urban + 
				station.area_undeveloped_rural) + " ha.)";
			transform_area_bar.DrawBar(
				station.transform_area_cultivated_home, 
				station.transform_area_cultivated_work, 
				station.transform_area_cultivated_mixed, 
				station.transform_area_undeveloped_urban,
				station.transform_area_undeveloped_mixed);
			clip.transform_area.text = "(" + Math.round( 
				station.transform_area_cultivated_home + 
				station.transform_area_cultivated_work + 
				station.transform_area_cultivated_mixed +  
				station.transform_area_undeveloped_urban +
				station.transform_area_undeveloped_mixed) + " ha.)";
				
			clip.amount_travelers.text = StationStatsCalculator.GetTravelersStats(station);
			clip.amount_citizens.text = int(station.count_home_total * Data.Get().GetConstants().average_citizens_per_home);
			clip.amount_workers.text = int(station.count_work_total * Data.Get().GetConstants().average_workers_per_bvo);
			clip.amount_houses.text = Math.round(station.count_home_total);
			clip.bvo_work.text = Math.round(station.count_work_total);
		}
		
		private function UploadXML():void 
		{
			var loader:URLLoader = new URLLoader();
			var request:URLRequest = new URLRequest(SprintStad.DOMAIN + "data/program.php");
			var vars:URLVariables = new URLVariables();
			vars.session = parent.session;
			vars.data = parent.currentStation.program.GetXmlString();
			request.data = vars;
			loader.load(request);
		}
		
		private function OnEditorChange():void
		{
			CreateProgram();
			var stationInstance:StationInstance = 
				StationStatsCalculator.GetStationAfterProgram(parent.currentStation, parent.currentStation.program);
			DrawStationInfo(stationInstance, parent.program_movie.future_info, barFutureArea, barFutureTransformArea, "TOEKOMST");
		}
		
		private function CreateProgram():Program
		{
			var program:Program = parent.currentStation.program;
			for each (var slider:ProgramSlider in editor.sliders)
			{
				switch (slider.GetSliderType())
				{
					case ProgramSlider.TYPE_HOME:
						program.type_home = slider.GetType();
						program.area_home = Math.round(slider.size);
						break;
					case ProgramSlider.TYPE_WORK:
						program.type_work = slider.GetType();
						program.area_work = Math.round(slider.size);
						break;
					case ProgramSlider.TYPE_LEISURE:
						program.type_leisure = slider.GetType();
						program.area_leisure = Math.round(slider.size);
						break;
				}
			}
			return program;
		}
		
		private function OnOkButton(event:MouseEvent):void
		{
			parent.currentStation.program = CreateProgram();
			UploadXML();
			parent.gotoAndPlay(SprintStad.FRAME_OVERVIEW);
		}
		
		private function OnCancelButton(event:MouseEvent):void
		{
			parent.gotoAndPlay(SprintStad.FRAME_OVERVIEW);
		}
		
		public function OnLoadingDone(data:int):void
		{
			var view:MovieClip = parent.program_movie;
			DrawUI(parent.currentStation);
			
			//remove loading screen
			parent.removeChild(SprintStad.LOADER);
		}
		
		/* INTERFACE SprintStad.State.IState */
		
		public function Activate():void 
		{
			var view:MovieClip = parent.program_movie;
			parent.addChild(SprintStad.LOADER);
			
			view.ok_button.buttonMode = true;
			view.ok_button.addEventListener(MouseEvent.CLICK, OnOkButton);
			view.cancel_button.buttonMode = true;
			view.cancel_button.addEventListener(MouseEvent.CLICK, OnCancelButton);
			
			// init bar graphs
			barCenterTransformArea = new AreaBarDrawer(view.transform_graph);
			barCurrentArea = new AreaBarDrawer(view.current_info.area_bar);
			barCurrentTransformArea = new AreaBarDrawer(view.current_info.transform_area_bar);
			barFutureArea = new AreaBarDrawer(view.future_info.area_bar);
			barFutureTransformArea = new AreaBarDrawer(view.future_info.transform_area_bar);
			
			// draw editor
			var types:Types = Data.Get().GetTypes();
			editor = new ProgramEditor(view.program_graph, OnEditorChange);
			
			DataLoader.Get().AddJob(DataLoader.DATA_STATIONS, OnLoadingDone);
		}
		
		public function Deactivate():void
		{
			//parent.intro_movie.removeEventListener(MouseEvent.CLICK, onContinueEvent);
		}
	}
}