package SprintStad.State 
{
	import flash.display.MovieClip;
	import flash.events.Event;
	import flash.events.MouseEvent;
	import SprintStad.Calculators.StationStatsCalculator;
	import SprintStad.Calculators.StationTypeCalculator;
	import SprintStad.Data.Data;
	import SprintStad.Data.DataLoader;
	import SprintStad.Data.Station.Station;
	import SprintStad.Debug.Debug;
	import SprintStad.Drawer.AreaBarDrawer;
	import SprintStad.Drawer.ProgramEditor;
	import SprintStad.Drawer.ProgramSlider;
	public class ProgramState implements IState
	{		
		private var parent:SprintStad = null;
		private var editor:ProgramEditor;
		
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
			AreaBarDrawer.DrawBar(view.transform_graph,
				station.transform_area_cultivated_home, 
				station.transform_area_cultivated_work, 
				station.transform_area_cultivated_mixed, 
				station.transform_area_undeveloped_urban,
				station.transform_area_undeveloped_mixed);
			
			// left info
			DrawStationInfo(station, view.current_info, "HUIDIG");
		}
		
		private function DrawStationInfo(station:Station, clip:MovieClip, title:String)
		{
			var top:Array = StationTypeCalculator.GetStationTypeTop(station);
			
			clip.title.text = title;
			
			clip.station_type_1_percent.text = top[0].similarity + "%";
			top[0].stationType.imageData.width = 100;
			top[0].stationType.imageData.height = 100;
			clip.station_type_1_image.addChild(top[0].stationType.imageData);
			
			clip.station_type_2_percent.text = top[1].similarity + "%";
			top[1].stationType.imageData.width = 100;
			top[1].stationType.imageData.height = 100;
			clip.station_type_2_image.addChild(top[1].stationType.imageData);
			
			clip.station_type_3_percent.text = top[2].similarity + "%";
			top[2].stationType.imageData.width = 100;
			top[2].stationType.imageData.height = 100;
			clip.station_type_3_image.addChild(top[2].stationType.imageData);
			
			AreaBarDrawer.DrawBar(clip.area_bar,
				station.area_cultivated_home,
				station.area_cultivated_work,
				station.area_cultivated_mixed, 
				station.area_undeveloped_urban,
				station.area_undeveloped_rural);
			clip.area.text = "(" + (
				station.area_cultivated_home +
				station.area_cultivated_work +
				station.area_cultivated_mixed + 
				station.area_undeveloped_urban + 
				station.area_undeveloped_rural) + " ha.)";
			AreaBarDrawer.DrawBar(clip.transform_area_bar, 
				station.transform_area_cultivated_home, 
				station.transform_area_cultivated_work, 
				station.transform_area_cultivated_mixed, 
				station.transform_area_undeveloped_urban,
				station.transform_area_undeveloped_mixed);
			clip.transform_area.text = "(" + ( 
				station.transform_area_cultivated_home + 
				station.transform_area_cultivated_work + 
				station.transform_area_cultivated_mixed +  
				station.transform_area_undeveloped_urban +
				station.transform_area_undeveloped_mixed) + " ha.)";
				
			clip.amount_travelers.text = StationStatsCalculator.GetTravelersStats(station);
			clip.amount_citizens.text = int(station.count_home_total * Data.Get().GetConstants().average_citizens_per_home);
			clip.amount_workers.text = int(station.count_work_total * Data.Get().GetConstants().average_workers_per_bvo);
			clip.amount_houses.text = station.count_home_total;
			clip.bvo_work.text = station.count_work_total;
		}
		
		private function OnOkButton(event:MouseEvent):void
		{
			parent.gotoAndPlay(SprintStad.FRAME_ROUND);
		}
		
		private function OnValuesButton(event:MouseEvent):void
		{
			parent.gotoAndPlay(SprintStad.FRAME_VALUES);
		}
		
		public function NextStationEvent(e:Event):void
		{
			parent.currentStation = Data.Get().GetStations().GetNextStationOfTeam(
				parent.currentStation, Data.Get().GetTeams().GetOwnTeam());
			DrawUI(parent.currentStation);
		}
		
		public function PreviousStationEvent(e:Event):void
		{
			parent.currentStation = Data.Get().GetStations().GetPreviousStationOfTeam(
				parent.currentStation, Data.Get().GetTeams().GetOwnTeam());
			DrawUI(parent.currentStation);
		}
		
		public function OnLoadingDone(data:int):void
		{
			var view:MovieClip = parent.program_movie;
			DrawUI(parent.currentStation);
			view.previous_station_button.addEventListener(MouseEvent.CLICK, PreviousStationEvent);
			view.next_station_button.addEventListener(MouseEvent.CLICK, NextStationEvent);
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
			view.values_button.buttonMode = true;
			view.values_button.addEventListener(MouseEvent.CLICK, OnValuesButton);
			
			// draw editor
			editor = new ProgramEditor(view.program_graph);
			editor.AddSlider(new ProgramSlider(0xD85D5D));
			editor.AddSlider(new ProgramSlider(0xBB88B1));
			editor.AddSlider(new ProgramSlider(0xFCF38D));
			
			DataLoader.Get().AddJob(DataLoader.DATA_STATIONS, OnLoadingDone);
		}
		
		public function Deactivate():void
		{
			//parent.intro_movie.removeEventListener(MouseEvent.CLICK, onContinueEvent);
		}
	}
}