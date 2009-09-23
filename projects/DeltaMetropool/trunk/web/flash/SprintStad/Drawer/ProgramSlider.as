package SprintStad.Drawer 
{
	import fl.controls.Slider;
	import flash.display.MovieClip;
	public class ProgramSlider
	{
		public var clip:MovieClip = new Slider();
		public var size:Number;
		public var color:uint;
		
		public function ProgramSlider(color:uint = 0xffffff, size:Number = 0.1) 
		{
			this.size = size;
			this.color = color;
		}		
	}
}