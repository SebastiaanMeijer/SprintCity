package SprintStad.Drawer 
{
	import flash.display.MovieClip;
	import flash.events.MouseEvent;
	import SprintStad.Debug.Debug;
	public class ProgramEditor
	{
		private const SLIDER_WIDTH:int = 5;
		
		public var clip:MovieClip = null;
		public var canvasX:Number = 0;
		public var canvasY:Number = 0;
		public var canvasWidth:Number = 100;
		public var canvasHeight:Number = 100;
		public var sliders:Array = new Array();	
		
		private var activeSlider:ProgramSlider = null;
		private var startMouseX:Number = 0;
		private var startSliderSize:Number = 0;
		
		public function ProgramEditor(clip:MovieClip) 
		{
			this.clip = clip;
			this.canvasX = clip.x + clip.graph.x;
			this.canvasY = clip.y + clip.graph.y;
			this.canvasWidth = clip.width * (clip.graph.width / 100);
			this.canvasHeight = clip.height * (clip.graph.height / 100);
			
			clip.parent.addEventListener(MouseEvent.MOUSE_DOWN, OnMouseDown);
			clip.parent.addEventListener(MouseEvent.MOUSE_UP, OnMouseUp);
			clip.parent.addEventListener(MouseEvent.MOUSE_MOVE, OnMouseMove);
		}
		
		public function AddSlider(slider:ProgramSlider):void
		{
			sliders.push(slider);
		}
		
		public function Draw():void
		{
			var x:Number = 0;

			clip.graph.graphics.clear();
			for each (var slider:ProgramSlider in sliders)
			{
				clip.graph.graphics.beginFill(slider.color);
				clip.graph.graphics.drawRect(x, 0, slider.size * 100, 100);
				clip.graph.graphics.endFill();
				x += slider.size * 100;
				clip.parent.addChild(slider.clip);
				slider.clip.x = canvasX + (x / 100) * canvasWidth;
				slider.clip.y = canvasY - 3;
			}
		}
		
		private function OnMouseDown(e:MouseEvent):void
		{
			activeSlider = OverSlider(e.stageX, e.stageY);
			startMouseX = e.stageX;
			if (activeSlider != null)
				startSliderSize = activeSlider.size;
		}
		
		private function OnMouseUp(e:MouseEvent):void
		{
			activeSlider = null;
		}
		
		private function OnMouseMove(e:MouseEvent):void
		{
			if (activeSlider != null)
			{
				var mouseDelta:Number = e.stageX - startMouseX;
				activeSlider.size = startSliderSize + mouseDelta / canvasWidth;
				// min bound
				var i:int = sliders.indexOf(activeSlider) - 1;
				while (i >= 0 && activeSlider.size < 0)
				{
					if (sliders[i].size > Math.abs(activeSlider.size))
					{
						sliders[i].size += activeSlider.size;
						activeSlider.size = 0;
						startSliderSize = 0;
						startMouseX = e.stageX;
					}
					else
					{
						activeSlider.size += sliders[i].size;
						sliders[i].size = 0;
					}
					i--;
				}
				activeSlider.size = Math.max(activeSlider.size, 0);
				// max bound
				i = sliders.length - 1;
				while (TotalSliderSize() > 1.0)
				{
					sliders[i].size -= Math.min(sliders[i].size, TotalSliderSize() - 1.0);
					i--;
				}				
			}
			Draw();
		}
		
		private function OverSlider(mouseX:int, mouseY:int):ProgramSlider
		{
			var result:ProgramSlider = null;
			for (var i:int = sliders.length - 1; i >= 0; i--)
			{
				var slider:ProgramSlider = sliders[i];
				var sliderY:Number = GetSliderY(slider);
				if (mouseY > sliderY && mouseY < sliderY + canvasHeight)
				{
					var sliderX:Number = GetSliderX(slider);
					if (mouseX > sliderX - SLIDER_WIDTH && mouseX < sliderX + SLIDER_WIDTH)
					{
						if (result == null ||
							slider.size < result.size)
							result = slider;
					}
				}
			}
			return result;
		}
		
		private function GetSliderX(slider:ProgramSlider):Number
		{
			var position:Number = 0.0;
			var i:int = -1;
			do {
				i++;
				position += sliders[i].size;
			} while (sliders[i] != slider && i < sliders.length)
			return canvasX + position * canvasWidth;
		}
		
		private function GetSliderY(slider:ProgramSlider):Number
		{
			return canvasY;
		}
		
		private function TotalSliderSize():Number
		{
			var size:Number = 0.0;
			for each (var slider:ProgramSlider in sliders)
			{
				size += slider.size;
			}
			return size;
		}
	}
}