package SprintStad.Drawer 
{
	import flash.display.MovieClip;
	import flash.display.Sprite;
	import SprintStad.Data.Round.Round;
	import SprintStad.Data.Station.Station;
	import SprintStad.Data.Station.StationInstance;
	import SprintStad.Debug.Debug;
	public class AreaBarDrawer
	{
		private var bar:MovieClip = null;
		private var colors:Array = new Array(
			new ColorHome(), new ColorWork(), new ColorLeisure(), 
			new ColorUrban(), new ColorRural());
		
		public function AreaBarDrawer(bar:MovieClip) 
		{
			this.bar = bar;
			for (var i:int = colors.length - 1; i >= 0; i--)
				bar.addChild(colors[i]);
			DrawBar(0, 0, 0, 0, 0);
		}
		
		public function DrawBar(home:int, work:int, leisure:int, urban:int, rural:int)
		{
			bar.visible = true;
			var areas:Array = new Array(
				home, work, leisure, urban, rural);
			var total_area:int = home + work + leisure + urban + rural;
			var startX:Number = 0;
			var barWidth:Number = 0;
			
			for (var i:int = 0; i < colors.length; i++)
			{
				barWidth = areas[i] / total_area * 100;
				colors[i].x = 0;
				colors[i].y = 0;
				colors[i].width = startX + barWidth;
				colors[i].height = 100;
				startX += barWidth;
			}
		}
		
		/*	function drawStationInstanceBar
		*	@param: station:Station - The station for which the bar needs to be drawn
		* 	@param currentRound:int - The ID of the current round
		* 	Pre: The station exists and the round exists
		* 	Post: Has drawn the bar for this StationInstance
		*/
		public function drawStationCurrentBar(station:Station, currentRound:Round)
		{
			var stationInstance:StationInstance = StationInstance.CreateInitial(station);
			var colours:Array = new Array();
			var allocated:Array = new Array();
			
			
			//For each round up until the current, decide the following:
			//Does the colour belonging to the home, work or leisure type already exist on the bar?
			//If not: add it to the colourpool
			//Increase the value allocated to that colour
			for (var i:int = 0; i < currentRound.round_info_id; i++)
			{
				if (stationInstance.round.exec_program.area_home > 0)
				{
					if(colours.indexOf(stationInstance.round.exec_program.type_home.color) == -1)
						colours.push(stationInstance.round.exec_program.type_home.color);
					var index:int = colours.indexOf(stationInstance.round.exec_program.type_home.color);
					allocated[index] += stationInstance.round.exec_program.area_home;
				}
				if (stationInstance.round.exec_program.area_work > 0)
				{
					if(colours.indexOf(stationInstance.round.exec_program.type_work.color) == -1)
						colours.push(stationInstance.round.exec_program.type_work.color);
					var index:int = colours.indexOf(stationInstance.round.exec_program.type_work.color);
					allocated[index] += stationInstance.round.exec_program.area_work;
				}
				if (stationInstance.round.exec_program.area_leisure > 0)
				{
					if (colours.indexOf(stationInstance.round.exec_program.type_leisure.color) == -1)
						colours.push(stationInstance.round.exec_program.type_leisure.color);
					var index:int = colours.indexOf(stationInstance.round.exec_program.type_leisure.color);
					allocated[index] += stationInstance.round.exec_program.area_leisure;
				}
				stationInstance.ApplyRound(currentRound);
			}
			
			/*//////TODO OPEN
			bar.visible = true;
			var areas:Array = new Array(station.area_cultivated_home, station.area_cultivated_work,
										station.area_cultivated_mixed, station.area_undeveloped_urban,
										station.area_undeveloped_rural);
			var total_area:int = home + work + leisure + urban + rural;
			var startX:Number = 0;
			var barWidth:Number = 0;
			
			for (var i:int = 0; i < colors.length; i++)
			{
				barWidth = areas[i] / total_area * 100;
				colors[i].x = 0;
				colors[i].y = 0;
				colors[i].width = startX + barWidth;
				colors[i].height = 100;
				startX += barWidth;
			}
			bar.visible = true;
			//var 
			////////TODO CLOSE*/
		}

		
		public function GetClip():MovieClip
		{
			return bar;
		}
	}
}