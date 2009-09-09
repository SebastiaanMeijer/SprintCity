package SprintStad.Drawer 
{
	import flash.display.MovieClip;
	import SprintStad.Debug.Debug;
	public class AreaBarDrawer
	{
		private static var instance:AreaBarDrawer = new AreaBarDrawer();
		
		public function AreaBarDrawer() 
		{
			
		}
		
		public static function Get():AreaBarDrawer
		{
			return instance;
		}
		
		public function DrawBar(bar:MovieClip, home:int, work:int, leisure:int, urban:int, rural:int)
		{
			var colors:Array = new Array(
				new ColorHome(), new ColorWork(), new ColorLeisure(), 
				new ColorUrban(), new ColorRural());
			var areas:Array = new Array(
				home, work, leisure, urban, rural);			
			var total_area:int = home + work + leisure + urban + rural;
			var startX:Number = 0;
			var barWidth:Number = 0;
			
			for (var i:int = 0; i < 5; i++)
			{
				barWidth = areas[i] / total_area * 100;		
				colors[i].x = startX;
				colors[i].y = 0;
				colors[i].width = barWidth;
				colors[i].height = 100;				
				bar.addChild(colors[i]);
				startX += barWidth;
			}
		}
		
	}

}