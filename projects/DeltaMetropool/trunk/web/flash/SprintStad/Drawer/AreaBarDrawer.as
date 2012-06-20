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
		
		public function DrawBar(home:Number, work:Number, leisure:Number, urban:Number, rural:Number, empty:Number)
		{
			bar.visible = true;
			clearBar();
			
			var areas:Array = new Array(
				home, work, leisure, urban, rural, empty);
			var total_area:Number = home + work + leisure + urban + rural + empty;
			
			
			for (var i:int = 0; i < colors.length; i++)
			{
				appendClipByMovieClip(colors[i], total_area, areas[i]);
			}
		}
		
		public function drawStationCircle(allocated:Number)
		{
			this.clearBar();
			var newColorClip:MovieClip = new MovieClip();
			
			var R:uint = 0;
			var G:uint = 0;
			
			if (allocated > 0.5)
			{
				R = 255 * ((1 - allocated) / 0.5);
				G = 255;
			}
			else
			{
				R = 255;
				G = 255 * (allocated / 0.5);
			}

			var B:uint = 0;
			
			R = R << 16;
			G = G << 8;
			var RGB:uint = R + G;
			if(RGB == 0)
				RGB = (255 << 16);
			newColorClip.graphics.clear();
			newColorClip.graphics.beginFill(RGB);
			newColorClip.graphics.drawCircle(17.5, 17.5, 17.5);
			newColorClip.graphics.endFill();
			newColorClip.alpha = 0.4;
			this.appendClipByMovieClip(newColorClip, 1, 1);
		}
		
		
		/*	function drawStationInstanceBar
		*	@param: station:Station - The station for which the bar needs to be drawn
		* 	@param currentRound:int - The ID of the current round
		* 	Pre: The station exists and the round exists
		* 	Post: Has drawn the bar for this StationInstance
		*/
		public function drawStationCurrentBar(station:Station, currentRound:Round, futureProgram:Program = null)
		{
			clearBar();
			
			var stationInstance:StationInstance = StationInstance.CreateInitial(station);

			var allocated:Array = new Array();
			for (var k:int = 0; k < types.GetTypeCount(); k++)
			{
				allocated[k] = 0;
			}
			
			var total_area:int = stationInstance.GetTotalTransformArea();
			
			var round_id:int = 1;
			if(currentRound != null)
				round_id = currentRound.round_info_id;
			else if (Data.Get().current_round_id > 1)
			{
				round_id = station.GetRound(station.rounds.length - 1).round_info_id + 1;
			}
			//For every round up until now:
			//Get the allocated value belonging to the type and increase the value
			for (var i:int = 0; i < round_id; i++)
			{
				var round:Round = station.GetRoundById(i);
				var index:int;
				if (round != null)
				{
					if (round.exec_program.area_home > 0)
					{
						index = types.getIndex(round.exec_program.type_home);
						allocated[index] += round.exec_program.area_home;
					}
					if ( round.exec_program.area_work > 0)
					{
						index = types.getIndex(round.exec_program.type_work);
						allocated[index] += round.exec_program.area_work;
					}
					if ( round.exec_program.area_leisure > 0)
					{
						index = types.getIndex(round.exec_program.type_leisure);
						allocated[index] += round.exec_program.area_leisure;
					}
					if(round.round_info_id + 1 <= round_id)
						stationInstance.ApplyRound(round);
				}
			}
			
			
			//If this bar is to look into the future, calculate one more round of values depending on the future program.
			if (futureProgram != null)
			{
				if (futureProgram.area_home > 0)
				{
					index = types.getIndex(futureProgram.type_home);
					allocated[index] += futureProgram.area_home;
				}
				if ( futureProgram.area_work > 0)
				{
					index = types.getIndex(futureProgram.type_work);
					allocated[index] += futureProgram.area_work;
				}
				if ( futureProgram.area_leisure > 0)
				{
					index = types.getIndex(futureProgram.type_leisure);
					allocated[index] += futureProgram.area_leisure;
				}
				stationInstance.ApplyProgram(futureProgram);	
			}
			
			bar.visible = true;
			
			//Go through all the categories in the right order
			var categories:Array = new Array("average_home", "home", "average_work", "work", "average_leisure", "leisure");
			
			var sum:int = 0;
			
			for each(var cat:String in categories)
			{				
				var cattypes:Array = types.GetTypesOfCategory(cat);
				for each(var type:Type in cattypes)
				{
					if (type.type == "average_home")
					{
						allocated[type.id - 1] += stationInstance.transform_area_cultivated_home;
					}
					else if (type.type == "average_work")
					{
						allocated[type.id - 1] += stationInstance.transform_area_cultivated_work;
					}
					else if (type.type == "average_leisure")
					{
						allocated[type.id - 1] += stationInstance.transform_area_cultivated_mixed;
					}
					
					if (allocated[type.id - 1] > 0)
					{
						sum = sum + allocated[type.id - 1];
						appendClip(type, total_area, allocated[type.id - 1]);
					}
				}
			}
			
			var baseUrban:int = stationInstance.transform_area_undeveloped_urban;
			var baseRural:int = stationInstance.transform_area_undeveloped_rural;
			
			if(baseUrban > 0)
				appendClipByMovieClip(new ColorUrban(), total_area, baseUrban);
			if(baseRural > 0)
				appendClipByMovieClip(new ColorRural(), total_area, baseRural);
		}

		public function appendClip(type:Type, totalArea:Number, clipArea:Number)
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
		
		public function appendClipByMovieClip(newColorClip:MovieClip, totalArea:Number, clipArea:Number)
		{
			var barWidth:Number = 0
			if (totalArea != 0)
				barWidth = clipArea / totalArea * 100;
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