package SprintStad.Drawer 
{
	import flash.display.MovieClip;
	import SprintStad.Data.Types.Type;
	public class ProgramSlider
	{
		public var clip:MovieClip = new Slider();
		public var type:Type;
		public var size:Number;		
		
		public function ProgramSlider(type:Type, size:Number = 0.1) 
		{
			this.type = type;
			this.size = size;
		}		
	}
}