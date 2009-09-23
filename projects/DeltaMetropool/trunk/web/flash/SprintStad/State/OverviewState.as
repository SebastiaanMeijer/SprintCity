package SprintStad.State 
{
	import fl.motion.Color;
	import flash.display.MovieClip;
	import flash.events.Event;
	import flash.events.MouseEvent;
	import flash.geom.ColorTransform;
	import flash.geom.Point;
	import SprintStad.Data.Data;
	import SprintStad.Data.DataLoader;
	import SprintStad.Data.Station.Station;
	import SprintStad.Data.Station.Stations;
	import SprintStad.Debug.Debug;
	import SprintStad.Drawer.AreaBarDrawer;
	public class OverviewState  implements IState
	{
		private var parent:SprintStad = null;
		private var selection:StationSelection = new StationSelection();
		private var stationIndex:int = 0;
		
		public function OverviewState(parent:SprintStad) 
		{
			this.parent = parent;
		}
		
		private function SelectStation(station:Station):void
		{
			var view:MovieClip = parent.overview_movie;
			var stationMovie:MovieClip = MovieClip(view.getChildByName(station.name.replace(" ", "_")));
			parent.currentStation = station;
			selection.x = stationMovie.x;
			selection.y = stationMovie.y;
			view.board.name_field.text = station.name;
			view.board.region_field.text = station.region;
			view.board.town_field.text = station.town;
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
		}
		
		private function GetStationMovieClip(station:Station):MovieClip
		{
			var movie_name:String = station.name.replace(" ", "_");
			return MovieClip(parent.overview_movie.getChildByName(movie_name));
		}
		
		private function OnProgramButton(event:MouseEvent):void
		{
			//StationInfoState(parent.GetState(SprintStad.STATE_STATION_INFO)).SetCurrentStation(1);
			parent.gotoAndPlay(SprintStad.FRAME_PROGRAM);
		}
		
		private function OnInfoButton(event:MouseEvent):void
		{
			parent.gotoAndPlay(SprintStad.FRAME_STATION_INFO);
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
					SelectStation(station);			
			}
			catch (e:Error) 
			{
				Debug.out(e.name);
				Debug.out(e.message);
			}
		}
		
		public function OnLoadingDone(data:int)
		{
			Debug.out(this + " I know " + data);
			var view:MovieClip = parent.overview_movie;
			var stations:Stations = Data.Get().GetStations();
			for (var i:int = 0; i < stations.GetStationCount(); i++)
			{
				var station:Station = stations.GetStation(i);
				var movie:MovieClip = GetStationMovieClip(station);
				
				var colorTransform:ColorTransform = new ColorTransform();
				colorTransform.color = parseInt("0x" + station.owner.color, 16);
				movie.outline.transform.colorTransform = colorTransform;				
				
				AreaBarDrawer.DrawBar(movie.graph,
					station.area_cultivated_home,
					station.area_cultivated_work,
					station.area_cultivated_mixed, 
					station.area_undeveloped_urban,
					station.area_undeveloped_rural);
			}
		}
			
		/* INTERFACE SprintStad.State.IState */
		
		public function Activate():void 
		{
			try
			{
				//var station:Station = Data.Get().GetStations().GetStation(1);
				var view:MovieClip = parent.overview_movie;
				var station:Station;
				
				//LoadStations();
				DataLoader.Get().AddJob(DataLoader.DATA_STATIONS, OnLoadingDone);
				
				// buttons
				view.program_button.buttonMode = true;
				view.program_button.addEventListener(MouseEvent.CLICK, OnProgramButton);
				view.info_button.buttonMode = true;
				view.info_button.addEventListener(MouseEvent.CLICK, OnInfoButton);
				
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
				selection.x = 500;
				selection.y = 500;
				selection.width = 42;
				selection.height = 42;
				SelectStation(parent.currentStation);
			}
			catch (e:Error)
			{
				Debug.out(e.name);
				Debug.out(e.message);
			}
		}
		
		public function Deactivate():void
		{

		}
	}
}