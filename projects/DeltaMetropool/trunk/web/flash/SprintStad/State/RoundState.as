package SprintStad.State 
{
	import flash.display.Bitmap;
	import flash.display.DisplayObject;
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
	import SprintStad.Data.Round.Round;
	import SprintStad.Data.Station.Station;
	import SprintStad.Data.Station.StationInstance;
	import SprintStad.Data.StationTypes.StationType;
	import SprintStad.Data.Types.Type;
	import SprintStad.Data.Types.Types;
	import SprintStad.Debug.Debug;
	import SprintStad.Drawer.AreaBarDrawer;
	import SprintStad.Drawer.ProgramEditor;
	import SprintStad.Drawer.ProgramSlider;
	import SprintStad;
	public class RoundState implements IState
	{		
		private var parent:SprintStad = null;
		private var loadCount:int = 0;
		private var editor:ProgramEditor;
		private var oldProgram:Program;
		private var stationInstance:StationInstance;
		
		private var barCenterTransformArea:AreaBarDrawer;
		private var barCurrentArea:AreaBarDrawer;
		private var barCurrentTransformArea:AreaBarDrawer;
		private var barFutureArea:AreaBarDrawer;
		private var barFutureTransformArea:AreaBarDrawer;
		
		private var popup:StationTypePopup = null;
		private var popupImage:Bitmap = new Bitmap();
		
		public function RoundState(parent:SprintStad) 
		{
			this.parent = parent;
		}
			
		private function DrawUI(station:Station):void
		{
			// draw stuff
			var view:MovieClip = parent.round_movie;
			
			// station sign
			view.board.name_field.text = station.name;
			view.board.region_field.text = station.region;
			view.board.town_field.text = station.town;
			
			// background
			view.sheet.addChild(station.imageData);
			
			// graph masterplan
			barCenterTransformArea.DrawBar(
				station.program.area_home, 
				station.program.area_work, 
				station.program.area_leisure, 
				station.GetTotalTransformArea() - station.program.area_home - station.program.area_work - station.program.area_leisure,
				0,
				0);
			
			// update editor
			editor.SetStation(station);
			
			// left info
			//station.PrintRounds();
			stationInstance = StationInstance.Create(station);
			stationInstance.ApplyProgram(Program.Default());
			DrawStationInfo(stationInstance, view.current_info, barCurrentArea, barCurrentTransformArea, "HUIDIG");
			
			// right info
			OnEditorChange();
		}
		
		private function DrawStationInfo(station:StationInstance, 
			clip:MovieClip, area_bar:AreaBarDrawer, transform_area_bar:AreaBarDrawer, 
			title:String)
		{
			var top:Array = StationTypeCalculator.GetStationTypeTop(station);
			var bitmap:Bitmap;
			
			clip.title.text = title;
			
			clip.station_type_1_percent.text = top[0].similarity + "%";
			clip.station_type_1_name.text = top[0].stationType.name;
			bitmap = new Bitmap(top[0].stationType.imageData);
			bitmap.width = 100;
			bitmap.height = 100;
			clip.station_type_1_image.addChild(bitmap);
			clip.station_type_1_image.stationType = top[0].stationType;
			clip.station_type_1_image.addEventListener(MouseEvent.MOUSE_OVER, MouseOverType);
			clip.station_type_1_image.addEventListener(MouseEvent.MOUSE_OUT, MouseOutType);
			
			clip.station_type_2_percent.text = top[1].similarity + "%";
			clip.station_type_2_name.text = top[1].stationType.name;
			bitmap = new Bitmap(top[1].stationType.imageData);
			bitmap.width = 100;
			bitmap.height = 100;
			clip.station_type_2_image.addChild(bitmap);
			clip.station_type_2_image.stationType = top[1].stationType;
			clip.station_type_2_image.addEventListener(MouseEvent.MOUSE_OVER, MouseOverType);
			clip.station_type_2_image.addEventListener(MouseEvent.MOUSE_OUT, MouseOutType);
			
			clip.station_type_3_percent.text = top[2].similarity + "%";
			clip.station_type_3_name.text = top[2].stationType.name;
			bitmap = new Bitmap(top[2].stationType.imageData);
			bitmap.width = 100;
			bitmap.height = 100;
			clip.station_type_3_image.addChild(bitmap);
			clip.station_type_3_image.stationType = top[2].stationType;
			clip.station_type_3_image.addEventListener(MouseEvent.MOUSE_OVER, MouseOverType);
			clip.station_type_3_image.addEventListener(MouseEvent.MOUSE_OUT, MouseOutType);
			
			area_bar.DrawBar(
				station.area_cultivated_home,
				station.area_cultivated_work,
				station.area_cultivated_mixed, 
				station.area_undeveloped_urban,
				station.area_undeveloped_rural,
				0);
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
				station.transform_area_undeveloped_mixed,
				0);
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
			vars.session = SprintStad.session;
			vars.data = GetCurrentRound().plan_program.GetXmlString();
			request.data = vars;
			loader.load(request);
		}
		
		private function InitWindows():void
		{
			var view:MovieClip = parent.round_movie;
			var types:Types = Data.Get().GetTypes();
			
			var cat_types:Array;
			var i:int = 0;
			var clip:MovieClip;
			var bitmap:Bitmap;
			
			//Clear all the demand ha strings so places that won't be used won't be showing.
			for (i = 0; i < 6; i++)
			{
				view.home_window.getChildByName("type_" + (i + 1)).type_area.text = "";
				view.work_window.getChildByName("type_" + (i + 1)).type_area.text = "";
				view.leisure_window.getChildByName("type_" + (i + 1)).type_area.text = "";
			}
			
			
			cat_types = types.GetTypesOfCategory("home");
			for (i = 0; i < cat_types.length; i++)
			{
				clip = view.home_window.getChildByName("type_" + (i + 1));
				bitmap = new Bitmap(cat_types[i].imageData);
				bitmap.width = 100;
				bitmap.height = 100;
				clip.type_image.addChild(bitmap);
				clip.type_name.text = cat_types[i].name;
				clip.type_id = cat_types[i].id;
				clip.buttonMode = true;
				clip.type_area.text = cat_types[i].GetDemandUntilNow() + " ha"
				clip.type_area.background = true;
				clip.addEventListener(MouseEvent.CLICK, OnTypeButtonClicked);
			}
			
			cat_types = types.GetTypesOfCategory("work");
			for (i = 0; i < cat_types.length; i++)
			{
				clip = view.work_window.getChildByName("type_" + (i + 1));
				bitmap = new Bitmap(cat_types[i].imageData);
				bitmap.width = 100;
				bitmap.height = 100;
				clip.type_image.addChild(bitmap);
				clip.type_name.text = cat_types[i].name;
				clip.type_id = cat_types[i].id;
				clip.buttonMode = true;
				clip.type_area.text = cat_types[i].GetDemandUntilNow() + " ha"
				clip.type_area.background = true;
				clip.addEventListener(MouseEvent.CLICK, OnTypeButtonClicked);
			}
			
			cat_types = types.GetTypesOfCategory("leisure");
			for (i = 0; i < cat_types.length; i++)
			{
				clip = view.leisure_window.getChildByName("type_" + (i + 1));
				bitmap = new Bitmap(cat_types[i].imageData);
				bitmap.width = 100;
				bitmap.height = 100;
				clip.type_image.addChild(bitmap);
				clip.type_name.text = cat_types[i].name;
				clip.type_id = cat_types[i].id;
				clip.buttonMode = true;
				clip.type_area.text = cat_types[i].GetDemandUntilNow() + " ha"
				clip.type_area.background = true;
				clip.addEventListener(MouseEvent.CLICK, OnTypeButtonClicked);
			}
		}
		
		private function GetCurrentRound():Round
		{
			return parent.GetCurrentStation().GetRoundById(Data.Get().current_round_id);
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
		
		private function OnTypeButtonClicked(e:Event):void
		{
			var clip:DisplayObject = DisplayObject(e.target);
			while (clip != null && !(clip is TypeCard))
				clip = clip.parent;
			
			if (clip != null)
			{
				var type:Type = Data.Get().GetTypes().GetTypeById(clip["type_id"]);
				GetCurrentRound().plan_program.SetType(type);
				editor.ChangeSliderType(type);
			}
			OnEditorChange();
		}
		
		private function OnEditorChange():void
		{
			var program:Program = CreateProgram();
			var tempStationInstance:StationInstance = stationInstance.Copy();
			tempStationInstance.ApplyStaticRoundInfo(parent.GetCurrentStation().GetRoundById(Data.Get().current_round_id));
			tempStationInstance.ApplyProgram(program);
			DrawStationInfo(tempStationInstance, parent.round_movie.future_info, barFutureArea, barFutureTransformArea, "TOEKOMST");
		}
		
		private function CreateProgram():Program
		{
			var program:Program = GetCurrentRound().plan_program;
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
			GetCurrentRound().plan_program = CreateProgram();
			UploadXML();
			parent.gotoAndPlay(SprintStad.FRAME_OVERVIEW);
		}
		
		private function OnCancelButton(event:MouseEvent):void
		{
			GetCurrentRound().plan_program = oldProgram;
			parent.gotoAndPlay(SprintStad.FRAME_OVERVIEW);
		}
		
		private function OnHomeButton(event:MouseEvent):void
		{
			var view:MovieClip = parent.round_movie;
			view.home_window.visible = true;
			view.work_window.visible = false;
			view.leisure_window.visible = false;
		}
		
		private function OnWorkButton(event:MouseEvent):void
		{
			var view:MovieClip = parent.round_movie;
			view.home_window.visible = false;
			view.work_window.visible = true;
			view.leisure_window.visible = false;
		}
		
		private function OnLeisureButton(event:MouseEvent):void
		{
			var view:MovieClip = parent.round_movie;
			view.home_window.visible = false;
			view.work_window.visible = false;
			view.leisure_window.visible = true;
		}
		
		public function OnLoadingDone(data:int):void
		{
			loadCount++;
			if (loadCount >= 2)
			{
				loadCount = 0;
				DrawUI(parent.GetCurrentStation());				
				//remove loading screen
				parent.removeChild(SprintStad.LOADER);
			}
		}
		
		/* INTERFACE SprintStad.State.IState */
		
		public function Activate():void 
		{
			var view:MovieClip = parent.round_movie;
			parent.addChild(SprintStad.LOADER);
			view.ok_button.buttonMode = true;
			view.ok_button.addEventListener(MouseEvent.CLICK, OnOkButton);
			view.cancel_button.buttonMode = true;
			view.cancel_button.addEventListener(MouseEvent.CLICK, OnCancelButton);
			
			// init station type poput menu
			popup = new StationTypePopup();
			popup.image.addChild(popupImage);
			popup.x = 287;
			popup.y = 120;
			
			view.home_button.buttonMode = true;
			view.home_button.addEventListener(MouseEvent.CLICK, OnHomeButton);
			view.work_button.buttonMode = true;
			view.work_button.addEventListener(MouseEvent.CLICK, OnWorkButton);
			view.leisure_button.buttonMode = true;
			view.leisure_button.addEventListener(MouseEvent.CLICK, OnLeisureButton);
			
			view.home_window.visible = false;
			view.work_window.visible = false;
			view.leisure_window.visible = false;
			InitWindows();
			
			// init bar graphs
			barCenterTransformArea = new AreaBarDrawer(view.transform_graph);
			barCurrentArea = new AreaBarDrawer(view.current_info.area_bar);
			barCurrentTransformArea = new AreaBarDrawer(view.current_info.transform_area_bar);
			barFutureArea = new AreaBarDrawer(view.future_info.area_bar);
			barFutureTransformArea = new AreaBarDrawer(view.future_info.transform_area_bar);
			
			// draw editor
			var types:Types = Data.Get().GetTypes();
			editor = new ProgramEditor(view.program_graph, OnEditorChange);
			
			// create a copy of the current program
			oldProgram = GetCurrentRound().plan_program.Copy();
			
			// display loading screen
			DataLoader.Get().AddJob(DataLoader.DATA_STATIONS, OnLoadingDone);
			DataLoader.Get().AddJob(DataLoader.DATA_CURRENT_ROUND, OnLoadingDone);
		}
		
		public function Deactivate():void
		{
			//parent.intro_movie.removeEventListener(MouseEvent.CLICK, onContinueEvent);
		}
	}
}