package SprintStad.State 
{
	import fl.motion.Color;
	import flash.display.MovieClip;
	import flash.display.Sprite;
	import flash.events.Event;
	import flash.events.MouseEvent;
	import flash.geom.ColorTransform;
	import flash.geom.Point;
	import flash.text.TextField;
	import flash.text.TextFormat;
	import flash.text.TextFormatAlign;
	import flash.ui.Mouse;
	import flash.utils.ByteArray;
	import SprintStad.Data.Data;
	import SprintStad.Data.DataLoader;
	import SprintStad.Data.Graph.LineGraph;
	import SprintStad.Data.Round.Round;
	import SprintStad.Data.Station.Station;
	import SprintStad.Data.Station.Stations;
	import SprintStad.Data.Station.StationInstance;
	import SprintStad.Data.Types.Type;
	import SprintStad.Data.Types.Types;
	import SprintStad.Debug.Debug;
	import SprintStad.Drawer.AreaBarDrawer;
	import SprintStad.Calculators.StationStatsCalculator;
	import SprintStad.Drawer.LineGraphDrawer;
	public class OverviewState  implements IState
	{
		private var parent:SprintStad = null;
		private var selection:StationSelection = new StationSelection(); // still used?
		
		private var selectedStation:Station = null;
		
		private var stationIndex:int = 0;
		private var loadCount:int = 0;
		
		private var barInitial:AreaBarDrawer;
		private var barMasterplan:AreaBarDrawer;
		private var barReality:AreaBarDrawer;
		private var barPlanned:AreaBarDrawer;
		private var barAllocated:AreaBarDrawer;
		
		private var lineGraphDrawer:LineGraphDrawer;
		
		// switch between space and mobility modes
		private static const SPACE_MODE:int = 0;
		private static const MOBILITY_MODE:int = 1;
		private static const NONE:int = -1;
		
		private var currentMode:int = OverviewState.NONE;
		
		public function OverviewState(parent:SprintStad) 
		{
			this.parent = parent;
		}
		
		private function Init():void
		{
			var stations:Stations = Data.Get().GetStations();
			
			FillStationCircles(stations);
			
			// fill in the demand windows
			FillDemandWindows();
			
			// select first station
			SelectStation(parent.currentStationIndex);
			
			// change the planned/assign bar titles
			ChangePlannedBarTitles();
			
			SetMode(OverviewState.SPACE_MODE);
		}
		
		/* INTERFACE SprintStad.State.IState */
		
		public function Activate():void 
		{
			try
			{
				Debug.out("Activate OverviewState");
				var view:MovieClip = parent.overview_movie;
				var station:Station;
				
				parent.addChild(SprintStad.LOADER);
				
				//LoadStations();
				Debug.out("Load the stations");
				DataLoader.Get().AddJob(DataLoader.DATA_CURRENT_ROUND, OnLoadingDone);
				DataLoader.Get().AddJob(DataLoader.DATA_STATIONS, OnLoadingDone);
				
				// buttons
				view.program_button.buttonMode = true;
				view.program_button.addEventListener(MouseEvent.CLICK, OnProgramButton);
				view.info_button.buttonMode = true;
				view.info_button.addEventListener(MouseEvent.CLICK, OnInfoButton);
				view.values_button.buttonMode = true;
				view.values_button.addEventListener(MouseEvent.CLICK, OnValuesButton);
				view.mobility_button.buttonMode = true;
				view.mobility_button.addEventListener(MouseEvent.CLICK, OnMobilityButton);
				view.space_button.buttonMode = true;
				view.space_button.addEventListener(MouseEvent.CLICK, OnSpaceButton);
				Debug.out("Activated buttons");
				
				// bar graphs
				var spacePanel:MovieClip = parent.overview_movie.spacePanelElements;
				barInitial = new AreaBarDrawer(spacePanel.graph_initial);
				barMasterplan = new AreaBarDrawer(spacePanel.graph_masterplan);
				barReality = new AreaBarDrawer(spacePanel.graph_reality);
				barPlanned = new AreaBarDrawer(spacePanel.graph_planned);
				barAllocated = new AreaBarDrawer(spacePanel.graph_allocated);
				
				lineGraphDrawer = new LineGraphDrawer(Sprite(parent.overview_movie.lineGraphContainer));
				
				// station buttons
				var stations:Stations = Data.Get().GetStations();
				for (var i:int = 0; i < stations.GetStationCount(); i++)
				{
					station = stations.GetStation(i);
					var movie:MovieClip = GetStationMovieClip(station);
					movie.buttonMode = true;
					movie.addEventListener(MouseEvent.CLICK, OnStationClick);
				}
				
				// initial selection
				parent.overview_movie.addChild(selection);
				selection.x = -500;
				selection.y = -500;
				selection.width = 42;
				selection.height = 42;
			}
			catch (e:Error)
			{
				Debug.out(e.name);
				Debug.out(e.message);
				Debug.out(e.getStackTrace());
			}
		}
			
		private function SelectStation(stationIndex:int):void
		{
			var view:MovieClip = parent.overview_movie;
			var station:Station = Data.Get().GetStations().GetStation(stationIndex);
			var stationMovie:MovieClip = MovieClip(view.getChildByName(station.name.replace(" ", "_")));
			
			parent.currentStationIndex = stationIndex;
			selection.x = stationMovie.x;
			selection.y = stationMovie.y;
			
			view.board.name_field.text = station.name;
			view.board.region_field.text = station.region;
			view.board.town_field.text = station.town;
			
			SetButtons(station);
			
			RefreshBars(station);
			RefreshMobility(station);
			RefreshLineGraphs(station);
			RefreshTransformArea(station);
			
		}
		
		private function FillStationCircles(stations:Stations):void
		{
			for (var i:int = 0; i < stations.GetStationCount(); i++)
			{
				var station:Station = stations.GetStation(i);
				var movie:MovieClip = GetStationMovieClip(station);
				
			
				var colorTransform:ColorTransform = new ColorTransform();
				colorTransform.color = parseInt("0x" + station.owner.color, 16);
				movie.outline.transform.colorTransform = colorTransform;				
				station.RefreshAreaBar();
				movie.graph.addChild(station.areaBar.GetClip());
			}
		}
		
		
		private function FillDemandWindows():void
		{
			
			var view:MovieClip = parent.overview_movie;
			var types:Types = Data.Get().GetTypes();
			
			for (var i:int = 0; i < types.GetTypeCount(); i++)
			{
				var type:Type = types.GetType(i);
				if (type.id < 15)
				{
					TextField(view.getChildByName("type_" + type.id)).text = type.GetDemandUntilNow() + " ha";
				}
			}
		}
		
		private function SetButtons(station:Station):void
		{
			
			var view:MovieClip = parent.overview_movie;
			// set program button
			if (station.owner.is_player)
			{
				view.program_button.visible = true;
				view.program_button.buttonMode = true;
				view.program_button.addEventListener(MouseEvent.CLICK, OnProgramButton);
			}
			else
			{
				view.program_button.visible = false;
				view.program_button.buttonMode = false;
				view.program_button.removeEventListener(MouseEvent.CLICK, OnProgramButton);
			}
			
			// set result button
			if (station.IsLastRound(Data.Get().current_round_id))
			{
				view.program_button.visible = false;
				view.result_button.visible = true;
				view.result_button.buttonMode = true;
				view.result_button.addEventListener(MouseEvent.CLICK, OnResultButton);
			}
			else
			{
				view.result_button.visible = false;
				view.result_button.buttonMode = false;
				view.result_button.removeEventListener(MouseEvent.CLICK, OnResultButton);
			}
		}
		
				// Should set the number in the textfield to the 
		// amount of the available transformable area in the current round
		private function RefreshTransformArea(station:Station)
		{
			try 
			{
			
				var spacePanel:MovieClip = parent.overview_movie.spacePanelElements;
			
			// amount Ha last
				if (station != null)
				{
					var instance:StationInstance = StationInstance.CreateInitial(station);
					
					var transformArea:int  = instance.GetTotalTransformArea();
					TextField(spacePanel.amountHa2TransformArea).text = transformArea + " ha";	
						
					if (Data.Get().current_round_id > 2 && GetPreviousRound().round_info_id > 1)
					{
						var plannedArea:int = GetPreviousRound().plan_program.TotalArea();
						TextField(spacePanel.amountHaPlanned).text = plannedArea + " ha";
						var allocatedArea:int = GetPreviousRound().exec_program.TotalArea();
						TextField(spacePanel.amountHaAllocated).text = allocatedArea + " ha";
					}
					else
					{
						TextField(spacePanel.amountHaPlanned).text = "";
						TextField(spacePanel.amountHaAllocated).text = "";
					}
				}	
			}
			catch (e:Error)
			{
				
				Debug.out(e.name);
				Debug.out(e.getStackTrace());
			}
		}
		
		private function RefreshBars(station:Station):void
		{
			var pastStationInstance:StationInstance = StationInstance.CreateInitial(station);
			barInitial.DrawBar(
				pastStationInstance.transform_area_cultivated_home, 
				pastStationInstance.transform_area_cultivated_work, 
				pastStationInstance.transform_area_cultivated_mixed, 
				pastStationInstance.transform_area_undeveloped_urban,
				pastStationInstance.transform_area_undeveloped_mixed,
				0);
			

			barMasterplan.DrawBar(
				station.program.area_home, 
				station.program.area_work, 
				station.program.area_leisure, 
				0,
				0,
				station.GetTotalTransformArea() - station.program.area_home - station.program.area_leisure - station.program.area_work);
			
			barReality.drawStationCurrentBar(station, this.GetCurrentRound());
			var roundID:int = Data.Get().current_round_id;
			if (roundID > 2)
			{
				var round:Round = station.GetRoundById(roundID - 1);
				
				
				//Draw the planned bar according to the given program
				if (round.plan_program != null)
				{
					barPlanned.drawPeriodBar(station, round.plan_program);
					barAllocated.drawPeriodBar(station, round.exec_program);
				}
				else
				{
					barPlanned.DrawBar(0,0,0,0,0,1);
					barAllocated.DrawBar(0,0,0,0,0,1);
				}
			}
			else
			{
				if(barPlanned != null)
					barPlanned.GetClip().visible = false;
				if(barAllocated != null)
					barAllocated.GetClip().visible = false;
			}
		}
		
		private function RefreshMobility(station:Station):void
		{
			var view:MovieClip = parent.overview_movie;
			
			var textAreaFormat:TextFormat = new TextFormat();
			textAreaFormat.align = TextFormatAlign.JUSTIFY;
			textAreaFormat.size = 9;
			
			view.mobilityDevelopment.editable = false;
			view.mobilityDevelopment.setStyle("textFormat", textAreaFormat);
			view.mobilityDevelopment.text = station.description_future;
		}
		
		private function RefreshLineGraphs(station:Station):void
		{
			Debug.out("Refreshing Line Graphs, stationID:" + station.id);
			try{
			lineGraphDrawer.DrawGraph(station.id);
			}
			catch (e:Error)
			{
				Debug.out(e.getStackTrace());
			}
		}
		
		private function ChangePlannedBarTitles():void
		{
			Debug.out("Changing Planned/Assigned Bar titles...");
			
			var view:MovieClip = parent.overview_movie.spacePanelElements;
			
			var period:String = "";
			var roundID:int = Data.Get().current_round_id;
			Debug.out("We're in round: "+ roundID +" ....!!!");
			if (roundID > 2)
			{
				view.plannedPeriod.visible = true;
				view.plannedText.visible = true;
				view.allocatedPeriod.visible = true;
				view.allocatedText.visible = true;
					
			}
			else
			{
				view.plannedPeriod.visible = false;
				view.plannedText.visible = false;
				view.allocatedPeriod.visible = false;
				view.allocatedText.visible = false;
			}
			
			//Get the round name belonging to the current round through one of the stations.
			var roundNameStart:String = "";
			var roundNameFinish:String = "";
			
			for each (var round:Round in Data.Get().GetStations().GetStation(stationIndex).rounds)
			{
				if (round.round_info_id == roundID)
					roundNameFinish = round.name;
				else if (round.round_info_id == roundID - 1)
					roundNameStart = round.name;
			}
			if (roundNameFinish == "")
			{
				roundNameFinish = "2030";
				//Uncomment the next line for a nicer way of giving the last rounds name, when the rounds are called different than years.
				//roundNameFinish = "einde";
				
			}
			view.plannedPeriod.text = roundNameStart + " - " + roundNameFinish;
			view.allocatedPeriod.text = roundNameStart + " - " + roundNameFinish;
		}
		

		// SetMode sets the two panels visible or invisible
		// Displays the right mode button and mode title
		private function SetMode(mode:int):void
		{
			var parent:MovieClip = parent.overview_movie;
			
			HideModeElements();
			
			Debug.out("setmode");
			if (mode == OverviewState.SPACE_MODE) // extra check
			{
				SetSpaceMode();
			}
			else if (mode == OverviewState.MOBILITY_MODE) // extra check
			{
				SetMobilityMode();
			}
		}
		
		private function HideModeElements():void
		{
			var parent:MovieClip = parent.overview_movie;
			parent.spacePanelElements.visible = false;
			parent.mobilityPanelElements.visible = false;
			parent.mobilityDevelopment.visible = false;
			
			parent.space_button.visible = false;
			parent.mobility_button.visible = false;
			
			parent.spaceTitle.visible = false;
			parent.mobilityTitle.visible = false;
			
			parent.lineGraphContainer.visible = false;
			
			parent.space_button.removeEventListener(MouseEvent.CLICK, OnSpaceButton); // test
			parent.mobility_button.removeEventListener(MouseEvent.CLICK, OnMobilityButton);
			
			Debug.out("Hid mode elements");
		}
		
		private function SetSpaceMode():void
		{
			var parent:MovieClip = parent.overview_movie;
			
			Debug.out("Now entering Space Mode.")
			try 
			{
				// set panel
				parent.spacePanelElements.visible = true;
				
				// set button
				parent.mobility_button.visible = true;
				
				// set eventlistener
				parent.mobility_button.addEventListener(MouseEvent.CLICK, OnMobilityButton);
				
				// set title
				parent.spaceTitle.visible = true;
				
				// graph
				parent.lineGraphContainer.visible = true;
				
				// set panels
				Debug.out("...Entered Space Mode");
			}
			catch (e:Error)
			{
				Debug.out("Error in setting mode");
			}
		}

		private function SetMobilityMode():void
		{
			var parent:MovieClip = parent.overview_movie;
			Debug.out("Now entering Mobility mode.")
			
			try 
			{
				// set panel
				parent.mobilityPanelElements.visible = true;
				
				// set description
				parent.mobilityDevelopment.visible = true;
				
				// set button
				parent.space_button.visible = true;
				
				// set eventlistener
				parent.space_button.addEventListener(MouseEvent.CLICK, OnSpaceButton);
				
				// set title
				parent.mobilityTitle.visible = true;
			}
			catch (e:Error)
			{
				Debug.out("Error in setting mode2");
			}
		}
		
		
		private function GetStationMovieClip(station:Station):MovieClip
		{
			var movie_name:String = station.name.replace(" ", "_");
			return MovieClip(parent.overview_movie.getChildByName(movie_name));
		}
		
		private function GetPreviousRound():Round
		{
			var roundID:int = Data.Get().current_round_id;
			
			if (roundID == 1)
				return null;
			else
				return parent.GetCurrentStation().GetRoundById(roundID-1);
		}
		
		private function GetCurrentRound():Round
		{
			return parent.GetCurrentStation().GetRoundById(Data.Get().current_round_id);
		}
		
		private function OnValuesButton(event:MouseEvent):void
		{
			parent.gotoAndPlay(SprintStad.FRAME_VALUES);
		}
		
		private function OnProgramButton(event:MouseEvent):void
		{
			DataLoader.Get().AddJob(DataLoader.DATA_CURRENT_ROUND, OnCurrentRoundKnown);
		}
		
		private function OnResultButton(event:MouseEvent):void
		{
			DataLoader.Get().AddJob(DataLoader.DATA_CURRENT_ROUND, OnCurrentRoundKnown);
		}
		
		private function OnInfoButton(event:MouseEvent):void
		{
			parent.gotoAndPlay(SprintStad.FRAME_STATION_INFO);
		}
		
		private function OnMobilityButton(event:MouseEvent):void
		{
			Debug.out("Clicked on mobility button!");
			SetMode(OverviewState.MOBILITY_MODE);
		}
		
		private function OnSpaceButton(event:MouseEvent):void
		{
			Debug.out("Clicked on space button!");
			SetMode(OverviewState.SPACE_MODE);
		}
		
		private function OnStationClick(event:MouseEvent):void
		{
			try{
				var object:Object = event.target;
				var station_name:String = object.name.replace("_" , " ");
				var station:Station = Data.Get().GetStations().GetStationByName(station_name);
				
				while (object != null && station == null)
				{
					object = object.parent;
					if (object != null && object.name != null)
					{
						station_name = object.name.replace("_" , " ");
						station = Data.Get().GetStations().GetStationByName(station_name);
					}
				}
				if (station != null)
					SelectStation(Data.Get().GetStations().GetStationIndex(station));			
			}
			catch (e:Error) 
			{
				Debug.out(e.name);
				Debug.out(e.message);
			}
		}
		
		public function OnCurrentRoundKnown(data:int):void
		{
			if (Data.Get().current_round_id == 1)
				parent.gotoAndPlay(SprintStad.FRAME_PROGRAM);
			else if (parent.GetCurrentStation().IsLastRound(Data.Get().current_round_id))
				parent.gotoAndPlay(SprintStad.FRAME_RESULT);
			else
				parent.gotoAndPlay(SprintStad.FRAME_ROUND);
		}
		
		public function OnLoadingDone(data:int):void
		{
			Debug.out(this + " I know " + data);
			loadCount++;
			if (loadCount >= 2)
			{
				loadCount = 0;
				Init();
				//remove loading screen
				parent.removeChild(SprintStad.LOADER);
				Debug.out("removed loadscreen removed" );
			}
		}
		public function Deactivate():void
		{
		
		}
	}
}