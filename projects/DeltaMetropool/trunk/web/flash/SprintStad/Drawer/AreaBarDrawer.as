package SprintStad.Drawer 
{
	import flash.display.MovieClip;
	import flash.display.Sprite;
	import SprintStad.Data.Program.Program;
	import SprintStad.Data.Round.Round;
	import SprintStad.Data.Station.Station;
	import SprintStad.Data.Station.StationInstance;
	import SprintStad.Data.Data;
	import SprintStad.Data.Types.Type;
	import SprintStad.Data.Types.Types;
	import SprintStad.Debug.Debug;
	
	public class AreaBarDrawer
	{
		private var bar:MovieClip = null;
		private var colors:Array = new Array(
			new ColorHome(), new ColorWork(), new ColorLeisure(), 
			new ColorUrban(), new ColorRural(), new ColorEmpty());
		private var types:Types = new Types();
		private var startX:Number = 0;
		
		public function AreaBarDrawer(bar:MovieClip) 
		{
			types = Data.Get().GetTypes()
			this.bar = bar;
			for (var i:int = colors.length - 1; i >= 0; i--)
				bar.addChild(colors[i]);
			DrawBar(0, 0, 0, 0, 0, 0);
		}
		
		public function DrawBar(home:int, work:int, leisure:int, urban:int, rural:int, empty:int)
		{
			bar.visible = true;
			clearBar();
			
			var areas:Array = new Array(
				home, work, leisure, urban, rural, empty);
			var total_area:int = home + work + leisure + urban + rural + empty;
			
			
			for (var i:int = 0; i < colors.length; i++)
			{
				appendClipByMovieClip(colors[i], total_area, areas[i]);
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
			clearBar();
			
			var stationInstance:StationInstance = StationInstance.CreateInitial(station);
			if (currentRound == null)
			{
				DrawBar(stationInstance.area_cultivated_home,
						stationInstance.area_cultivated_work,
						stationInstance.area_cultivated_mixed,
						stationInstance.area_undeveloped_urban,
						stationInstance.area_undeveloped_rural,
						0);
				return;
			}	
			var allocated:Array = new Array();
			for (var k:int = 0; k < types.GetTypeCount(); k++)
			{
				allocated[k] = 0;
			}
			
			var specialHome:int = 0;
			var specialWork:int = 0;
			var specialLeisure:int = 0;
			
			//For each round up until the current, decide the following:
			//Get the allocated value belonging to the type and increase the value.
			for (var i:int = 0; i < currentRound.round_info_id; i++)
			{
				var round:Round = station.GetRoundById(i);
				
				if (round != null)
				{
						if (round.exec_program.area_home > 0)
						{
							var index:int = types.getIndex(round.exec_program.type_home);
							allocated[index] += round.exec_program.area_home;
							if (round.exec_program.type_home.type != "average_home")
								specialHome += round.exec_program.area_home;
						}
						if ( round.exec_program.area_work > 0)
						{
							index = types.getIndex(round.exec_program.type_work);
							allocated[index] += round.exec_program.area_work;
							if (round.exec_program.type_work.type != "average_work")
								specialWork += round.exec_program.area_work;
						}
						if ( round.exec_program.area_leisure > 0)
						{
							index = types.getIndex(round.exec_program.type_leisure);
							allocated[index] += round.exec_program.area_leisure;
							if (round.exec_program.type_leisure.type != "average_leisure")
								specialLeisure += round.exec_program.area_leisure;
						}
						if(round.round_info_id + 1 < currentRound.round_info_id)
							stationInstance.ApplyRound(round);
				}
			}
			
			bar.visible = true;
			
			var total_area:int = station.area_cultivated_home + station.area_cultivated_mixed + station.area_cultivated_work + station.area_undeveloped_rural + station.area_undeveloped_urban;
			
			//Go through all the categories in the right order
			var categories:Array = new Array("average_home", "home", "average_work", "work", "average_leisure", "leisure");
			
			
			for each(var cat:String in categories)
			{				
				var cattypes:Array = types.GetTypesOfCategory(cat);
				for each(var type:Type in cattypes)
				{
					if (type.type == "average_home")
						allocated[type.id - 1] += stationInstance.area_cultivated_home - specialHome;
					else if (type.type == "average_work")
						allocated[type.id - 1] += stationInstance.area_cultivated_work - specialWork;
					else if (type.type == "average_leisure")
						allocated[type.id - 1] += stationInstance.area_cultivated_mixed - specialLeisure;
					
					Debug.out("Appending clip of type " + type.name + " " + allocated[type.id - 1] + " with colour " + type.color);
					appendClip(type, total_area, allocated[type.id - 1]);
				}
			}
		
			//TODO: Netter maker dan hardcoded Urban en Rural toevoegen.
			appendClipByMovieClip(new ColorUrban(), total_area, stationInstance.area_undeveloped_urban);
			appendClipByMovieClip(new ColorRural(), total_area, stationInstance.area_undeveloped_rural);
			
		}

		public function appendClip(type:Type, totalArea:int, clipArea:int)
		{
			var barWidth:Number = clipArea / totalArea * 100;
			var newColorClip:MovieClip = new MovieClip();
			
			newColorClip.graphics.clear();
			newColorClip.graphics.beginFill(parseInt("0x" + type.color, 16));
			newColorClip.graphics.drawRect(0, 0, 100, 100);
			newColorClip.graphics.endFill();
			
			newColorClip.x = this.startX;
			newColorClip.y = 0;
			newColorClip.width = barWidth;
			newColorClip.height = 100;
			newColorClip.visible = true;
			this.startX += barWidth;
			bar.addChild(newColorClip);
		}
		
		public function appendClipByMovieClip(newColorClip:MovieClip, totalArea:int, clipArea:int)
		{
			var barWidth:Number = clipArea / totalArea * 100;
			newColorClip.x = this.startX;
			newColorClip.y = 0;
			newColorClip.width = barWidth;
			newColorClip.height = 100;
			newColorClip.visible = true;
			this.startX += barWidth;
			bar.addChild(newColorClip);
		}
		
		public function drawPeriodBar(station:Station, program:Program)
		{
			clearBar();
			var totalArea:Number = station.GetTotalTransformArea();
			appendClip(program.type_home, totalArea, program.area_home);
			appendClip(program.type_work, totalArea, program.area_work);
			appendClip(program.type_leisure, totalArea, program.area_leisure);
			appendClipByMovieClip(new ColorEmpty(), totalArea, totalArea - program.area_home - program.area_leisure - program.area_work);
		}
		
		public function clearBar()
		{
			this.startX = 0;
			while (bar.numChildren > 0)
				bar.removeChildAt(0);
		}
		
		public function GetClip():MovieClip
		{
			return bar;
		}
	}
}