package SprintStad.Drawer 
{
	import fl.controls.Slider;
	import flash.display.MovieClip;
	import SprintStad.Data.Types.Type;
	import SprintStad.Debug.Debug;
	public class ProgramSlider
	{
		public static const SLIDER_SIZE:int = 12;
		public static const grabPositions:Array = new Array(-24.4, -11.4, 1.5);
		public static const clips:Array = new Array(new Slider03(), new Slider02(), new Slider01());
		public static const TYPE_HOME:uint = 0;
		public static const TYPE_WORK:uint = 1;
		public static const TYPE_LEISURE:uint = 2;
		
		private var sliderType:uint;
		private var type:Type;
		public var size:int;
		
		public var barClip:MovieClip = new Placeholder();
		
		public function ProgramSlider(type:Type, size:uint)
		{
			this.SetType(type);
			this.size = size;
		}
		
		public function SetType(type:Type):void
		{
			this.type = type;
			if (type.type == "home" || type.type == "average_home")
				sliderType = TYPE_HOME;
			else if (type.type == "work" || type.type == "average_work")
				sliderType = TYPE_WORK;
			else if (type.type == "leisure" || type.type == "average_leisure")
				sliderType = TYPE_LEISURE;
			DrawClips();
		}
		
		private function DrawClips():void
		{
			var sliderClip:MovieClip = clips[sliderType].type_color;
			sliderClip.graphics.clear();
			sliderClip.graphics.beginFill(parseInt("0x" + type.color, 16));
			sliderClip.graphics.drawRect(0, 0, 100, 100);
			sliderClip.graphics.endFill();
			
			barClip.graphics.clear();
			barClip.graphics.beginFill(parseInt("0x" + type.color, 16));
			barClip.graphics.drawRect(0, 0, 100, 100);
			barClip.graphics.endFill();
		}
		
		public function GetType():Type
		{
			return type;
		}
		
		public function GetSliderType():uint
		{
			return sliderType;
		}
		
		public function GetClip():MovieClip
		{
			return clips[sliderType];
		}
		
		public function GetGrabPosition():Number
		{
			return grabPositions[sliderType];
		}
	}
}