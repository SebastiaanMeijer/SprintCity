package SprintStad.State 
{
	import fl.motion.Color;
	import fl.motion.MatrixTransformer;
	import flash.display.Bitmap;
	import flash.display.BitmapData;
	import flash.display.MovieClip;
	import flash.display.Sprite;
	import flash.display.Stage;
	import flash.events.Event;
	import flash.events.MouseEvent;
	import flash.geom.ColorTransform;
	import flash.geom.Matrix;
	import flash.geom.Point;
	import flash.text.TextField;
	import flash.text.TextFormat;
	import flash.text.TextFormatAlign;
	import flash.ui.Mouse;
	import flash.utils.ByteArray;
	import SprintStad.Data.Data;
	import SprintStad.Data.DataLoader;
	import SprintStad.Data.Facility.Facility;
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
		private var selection:StationSelection = new StationSelection();
		
		private var previousRoundId:int = -1;
		private var loadCount:int = 0;
		private var targetLoadCount:int = int.MAX_VALUE;
		
		private var initialMapScaleX:Number = Number.MIN_VALUE;
		private var initialMapScaleY:Number = Number.MIN_VALUE;
		
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
		
		private var currentMode:int = OverviewState.SPACE_MODE;
		
		public function OverviewState(parent:SprintStad) 
		{
			this.parent = parent;
		}
		
		private function Init():void
		{
			var stations:Stations = Data.Get().GetStations();

			// setup station circles
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
				var map:MovieClip = view.map;
				var station:Station;
				
				// set map position
				var stations:Stations = Data.Get().GetStations();
				
				parent.addChild(SprintStad.LOADER);
				
				Debug.out("Load data");
				DataLoader.Get().AddJob(DataLoader.DATA_CURRENT_ROUND, OnLoadingCurrentRoundDone);
				
				// refresh background map
				RefreshMap();
				
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
				
				// only initially load lineGraphDrawer, otherwise this is done in OnLoadingCurrentRoundDone()
				if (lineGraphDrawer == null)
					lineGraphDrawer = new LineGraphDrawer(Sprite(parent.overview_movie.lineGraphContainer));
				
				// station buttons
				for (var i:int = 0; i < stations.GetStationCount(); i++)
				{
					station = stations.GetStation(i);
					var movie:MovieClip = GetStationMovieClip(station);
					movie.buttonMode = true;
					movie.mouseChildren = false;
					movie.doubleClickEnabled = true;
					movie.addEventListener(MouseEvent.DOUBLE_CLICK, OnStationDoubleClick);
					movie.addEventListener(MouseEvent.CLICK, OnStationClick);
				}
				
				// initial selection
				parent.overview_movie.map.addChild(selection);
				selection.x = -500;
				selection.y = -500;
				selection.width = 8.5;
				selection.height = 8.5;
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
			var stationMovie:MovieClip = GetStationMovieClip(station);
			
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
			RefreshFacilities(station);
			RefreshTransformArea(station);
		}
		
		private function RefreshMap():void
		{
			var stations:Stations = Data.Get().GetStations();
			
			// take not of initial map scale if not already done so
			if (initialMapScaleX == Number.MIN_VALUE && initialMapScaleY == Number.MIN_VALUE)
			{
				initialMapScaleX = parent.overview_movie.map.scaleX;
				initialMapScaleY = parent.overview_movie.map.scaleY;
			}
			
			// set map position
			parent.overview_movie.map.x = stations.MapX;
			parent.overview_movie.map.y = stations.MapY;
			// Scale according to scenario
			var relPosX:Number = parent.overview_movie.map.x / parent.overview_movie.map.width;
			var relPosY:Number = parent.overview_movie.map.y / parent.overview_movie.map.height;
			parent.overview_movie.map.scaleX = initialMapScaleX * stations.ScaleFactor;
			parent.overview_movie.map.scaleY = initialMapScaleY * stations.ScaleFactor;
			parent.overview_movie.map.x = relPosX * parent.overview_movie.map.width + (.5 * parent.stage.stageWidth * (1-stations.ScaleFactor));
			parent.overview_movie.map.y = relPosY * parent.overview_movie.map.height + (.5 * parent.stage.stageHeight * (1-stations.ScaleFactor));
		}
		
		private function RefreshStationCircles():void
		{
			var stations:Stations = Data.Get().GetStations();
			FillStationCircles(stations);
		}
		
		private function FillStationCircles(stations:Stations):void
		{	
			for (var i:int = 0; i < stations.GetStationCount(); i++)
			{
				var station:Station = stations.GetStation(i);
				var movie:MovieClip = GetStationMovieClip(station);
				
				// set station id on overview station circle movieclip
				movie['id'] = station.id;
			
				// set outline color and filling of station circles
				var colorTransform:ColorTransform = new ColorTransform();
				colorTransform.color = parseInt("0x" + station.owner.color, 16);
				movie.outline.transform.colorTransform = colorTransform;
				if (currentMode == OverviewState.SPACE_MODE)
					station.RefreshAreaBar();
				else if (currentMode == OverviewState.MOBILITY_MODE)
					station.RefreshMobilityBar();
				movie.graph.addChild(station.areaBar.GetClip());
				movie.alpha = 1;
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
					if (Data.Get().current_round_id == 1)
					{
						TextField(view.getChildByName("type_" + type.id)).text = type.getTotalDemand() + " ha";
					}
					else
					{
						TextField(view.getChildByName("type_" + type.id)).text = type.GetDemandUntilNow() + " ha";
					}
				}
			}
		}
		
		private function SetDemandWindowsVisible(visible:Boolean):void
		{
			var parentMovie:MovieClip = parent.overview_movie;
			var types:Types = Data.Get().GetTypes();
			for (var i:int = 0; i < types.GetTypeCount(); i++)
			{
				var type:Type = types.GetType(i);
				if (type.id < 15)
				{
					TextField(parentMovie.getChildByName("type_" + type.id)).visible = visible;
				}
			}
			
			parentMovie.demand_title.visible = visible;
			parentMovie.demand_home.visible = visible;
			parentMovie.demand_work.visible = visible;
			parentMovie.demand_leisure.visible = visible;
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
				pastStationInstance.transform_area_undeveloped_rural,
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
			view.mobilityDevelopment.text = Data.Get().GetMobilityReport().getReport();
		}
		
		private function RefreshLineGraphs(station:Station):void
		{
			Debug.out("Refreshing Line Graphs, stationID:" + station.id);
			try {
				if (currentMode == OverviewState.SPACE_MODE)
					lineGraphDrawer.DrawSpaceGraph(station.id);
				else if (currentMode == OverviewState.MOBILITY_MODE)
					lineGraphDrawer.DrawMobilityGraph(station.id);
			}
			catch (e:Error)
			{
				Debug.out(e.getStackTrace());
			}
		}
		
		private function RefreshFacilities(station:Station):void
		{
			Debug.out("Refreshing Facilities, stationID:" + station.id);
			try {
				var container:MovieClip = parent.overview_movie.facilitiesContainer;
				while (container.numChildren > 0)
					container.removeChildAt(0);
				container.scaleX = 0.5;
				container.scaleY = 0.5;
				var x:Number = 0;
				for (var i:int; i < station.bonuses.length; i++)
				{
					var facility:Facility = Data.Get().GetFacilities().GetFacilityById(station.bonuses[i]);
					if (facility)
					{
						var bitmap:Bitmap = new Bitmap(facility.imageData);
						bitmap.x = x;
						container.addChild(bitmap);
						x += bitmap.width + 10;
					}
				}
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
			Debug.out("We're in round: "+ roundID);
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
			
			for each (var round:Round in Data.Get().GetStations().GetStation(parent.currentStationIndex).rounds)
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
			var parentMovie:MovieClip = parent.overview_movie;
			parentMovie.spacePanelElements.visible = false;
			parentMovie.mobilityPanelElements.visible = false;
			parentMovie.mobilityDevelopment.visible = false;
			
			parentMovie.space_button.visible = false;
			parentMovie.mobility_button.visible = false;
			
			parentMovie.spaceTitle.visible = false;
			parentMovie.mobilityTitle.visible = false;
			
			parentMovie.lineGraphContainer.visible = false;
			
			parentMovie.space_button.removeEventListener(MouseEvent.CLICK, OnSpaceButton); // test
			parentMovie.mobility_button.removeEventListener(MouseEvent.CLICK, OnMobilityButton);
			
			Debug.out("Hid mode elements");
		}
		
		private function SetSpaceMode():void
		{
			var parentMovie:MovieClip = parent.overview_movie;
			currentMode = SPACE_MODE;
			Debug.out("Now entering Space Mode.")
			try 
			{
				// set panel
				parentMovie.spacePanelElements.visible = true;
				
				// set button
				parentMovie.mobility_button.visible = true;
				
				// set eventlistener
				parentMovie.mobility_button.addEventListener(MouseEvent.CLICK, OnMobilityButton);
				
				// set title
				parentMovie.spaceTitle.visible = true;
				
				// graph
				parentMovie.lineGraphContainer.visible = true;
				RefreshLineGraphs(Data.Get().GetStations().GetStation(parent.currentStationIndex));
				
				// set panels
				SetDemandWindowsVisible(true);
				
				// refresh station circles
				RefreshStationCircles();
				
				Debug.out("...Entered Space Mode");
			}
			catch (e:Error)
			{
				Debug.out("Error in setting mode");
			}
		}

		private function SetMobilityMode():void
		{
			var parentMovie:MovieClip = parent.overview_movie;
			currentMode = MOBILITY_MODE;
			Debug.out("Now entering Mobility mode.")
			try 
			{
				// set panel
				parentMovie.mobilityPanelElements.visible = true;
				
				// set description
				parentMovie.mobilityDevelopment.visible = true;
				
				// set button
				parentMovie.space_button.visible = true;
				
				// set eventlistener
				parentMovie.space_button.addEventListener(MouseEvent.CLICK, OnSpaceButton);
				
				// set title
				parentMovie.mobilityTitle.visible = true;
				
				// graph
				parentMovie.lineGraphContainer.visible = true;
				
				// set panels
				SetDemandWindowsVisible(false);
				
				// refresh station circles
				RefreshStationCircles();
				
				RefreshLineGraphs(Data.Get().GetStations().GetStation(parent.currentStationIndex));
			}
			catch (e:Error)
			{
				Debug.out("Error in setting mode2");
			}
		}
		
		private function GetStationMovieClip(station:Station):MovieClip
		{
			var movie_name:String = station.code;
			return MovieClip(parent.overview_movie.map.getChildByName(movie_name));
		}
		
		private function GetStationByMovieClipObject(stationCircleClip:Object):Station
		{
			// find station id
			var station_id:Object;
			while (stationCircleClip != null && station_id == null)
			{
				if (stationCircleClip != null && stationCircleClip.id != null)
					station_id = stationCircleClip.id;
				stationCircleClip = stationCircleClip.parent;
			}
			return Data.Get().GetStations().GetStationById(int(station_id));
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
			SetMode(OverviewState.MOBILITY_MODE);
		}
		
		private function OnSpaceButton(event:MouseEvent):void
		{
			SetMode(OverviewState.SPACE_MODE);
		}
		
		private function OnStationClick(event:MouseEvent):void
		{
			try
			{
				var station:Station = GetStationByMovieClipObject(event.target);
				if (station != null)
					SelectStation(Data.Get().GetStations().GetStationIndex(station));
			}
			catch (e:Error) 
			{
				Debug.out(e.name);
				Debug.out(e.message);
			}
		}
		
		private function OnStationDoubleClick(event:MouseEvent):void
		{
			try
			{
				var station:Station = GetStationByMovieClipObject(event.target);
				if (station != null)
				{
					if (station.owner.is_player)
					{
						if (Data.Get().current_round_id < 7)
						{
							DataLoader.Get().AddJob(DataLoader.DATA_CURRENT_ROUND, OnCurrentRoundKnown);
							return;
						}
					}
					if (Data.Get().current_round_id == 7)
					{
						DataLoader.Get().AddJob(DataLoader.DATA_CURRENT_ROUND, OnCurrentRoundKnown);
					}
					
				}
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
		
		public function OnLoadingCurrentRoundDone(data:int):void
		{
			Debug.out(this + " I know " + data);
			
			// determine further loading
			targetLoadCount = 0;
			
			// only load if the round has changed
			if (previousRoundId < Data.Get().current_round_id)
			{
				DataLoader.Get().AddJob(DataLoader.DATA_STATIONS, OnLoadingDone);
				DataLoader.Get().AddJob(DataLoader.DATA_MOBILITY_REPORT, OnLoadingDone);
				lineGraphDrawer = new LineGraphDrawer(Sprite(parent.overview_movie.lineGraphContainer));
				targetLoadCount += 2;
			}
			// only load values if -in- or -just out of- masterplan phase
			if (Data.Get().current_round_id == 1 || previousRoundId == 1)
			{
				DataLoader.Get().AddJob(DataLoader.DATA_VALUES, OnLoadingDone);
				targetLoadCount += 1;
			}
			
			// set previous to current round
			previousRoundId = Data.Get().current_round_id;
			
			// if nothing needs to be loaded, just call OnLoadingDone with a dummy value to run the UI init code
			if (targetLoadCount == 0)
				OnLoadingDone( -1);
		}
		
		public function OnLoadingDone(data:int):void
		{
			Debug.out(this + " I know " + data);
			loadCount++;
			if (loadCount >= targetLoadCount)
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