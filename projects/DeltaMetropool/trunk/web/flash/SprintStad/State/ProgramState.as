package SprintStad.State 
{
	import flash.display.MovieClip;
	import flash.events.Event;
	import flash.events.MouseEvent;
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
			DrawStationInfo(StationInstance.Create(station), view.current_info, "HUIDIG");
			
			// update editor
			editor.SetArea(station.GetTotalTransformArea());
		}
		
		private function DrawStationInfo(station:StationInstance, clip:MovieClip, title:String)
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
			
			AreaBarDrawer.DrawBar(clip.area_bar,
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
			AreaBarDrawer.DrawBar(clip.transform_area_bar, 
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
		
		private function OnEditorChange():void
		{
			var program:Program = CreateProgram();
			var stationInstance:StationInstance = 
				StationStatsCalculator.GetStationAfterProgram(parent.currentStation, program);
			DrawStationInfo(stationInstance, parent.program_movie.future_info, "TOEKOMST");
		}
		
		private function CreateProgram():Program
		{
			var program:Program = new Program();
			for each (var slider:ProgramSlider in editor.sliders)
			{
				switch (slider.type.type)
				{
					case "home":
					case "average_home":
						program.home_type = slider.type;
						program.home_area = Math.round(slider.size * editor.totalArea);
						break;
					case "work":
					case "average_work":
						program.work_type = slider.type;
						program.work_area = Math.round(slider.size * editor.totalArea);
						break;
					case "leisure":
					case "average_leisure":
						program.leisure_type = slider.type;
						program.leisure_area = Math.round(slider.size * editor.totalArea);
						break;
				}
			}
			return program;
		}
		
		private function OnOkButton(event:MouseEvent):void
		{
			parent.currentStation.program = CreateProgram();
			parent.gotoAndPlay(SprintStad.FRAME_OVERVIEW);
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
			var types:Types = Data.Get().GetTypes();
			editor = new ProgramEditor(view.program_graph, OnEditorChange);
			editor.AddSlider(new ProgramSlider(types.GetTypesOfCategory("average_home")[0]));
			editor.AddSlider(new ProgramSlider(types.GetTypesOfCategory("average_work")[0]));
			editor.AddSlider(new ProgramSlider(types.GetTypesOfCategory("average_leisure")[0]));
			
			DataLoader.Get().AddJob(DataLoader.DATA_STATIONS, OnLoadingDone);
		}
		
		public function Deactivate():void
		{
			//parent.intro_movie.removeEventListener(MouseEvent.CLICK, onContinueEvent);
		}
	}
}