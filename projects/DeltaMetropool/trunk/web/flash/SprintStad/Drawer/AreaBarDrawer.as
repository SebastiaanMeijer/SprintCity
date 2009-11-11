package SprintStad.Drawer 
{
	import flash.display.MovieClip;
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
		
		public function GetClip():MovieClip
		{
			return bar;
		}
	}
}