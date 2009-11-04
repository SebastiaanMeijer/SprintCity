package SprintStad.Drawer 
{
	import flash.display.MovieClip;
	import flash.events.MouseEvent;
	import SprintStad.Data.Data;
	import SprintStad.Data.Round.Round;
	import SprintStad.Data.Station.Station;
	import SprintStad.Debug.Debug;
	public class ProgramEditor
	{
		private const SLIDER_WIDTH:int = 5;
		
		public var changeCallback:Function = null;
		public var clip:MovieClip = null;
		public var canvasX:Number = 0;
		public var canvasY:Number = 0;
		public var canvasWidth:Number = 100;
		public var canvasHeight:Number = 100;
		public var sliders:Array = new Array();	
		public var totalArea:int = 0;
		public var availableTransformArea:int = 0;
		
		private var activeSlider:ProgramSlider = null;
		private var startMouseX:Number = 0;
		private var startSliderSize:Number = 0;
		
		private var station:Station = null;
		
		public function ProgramEditor(clip:MovieClip, changeCallback:Function) 
		{
			this.changeCallback = changeCallback;
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
		
		public function SetArea(area:int)
		{
			this.totalArea = area;
			for each (var slider:ProgramSlider in sliders)
				slider.size -= slider.size % (1 / totalArea);
		}
		
		public function GetSliderArea(slider:ProgramSlider):int
		{
			return Math.round(slider.size * totalArea);
		}
		
		public function SetStation(station:Station)
		{
			this.station = station;
			SetArea(station.GetTotalTransformArea());
			availableTransformArea = GetAvailableTransformArea();
		}
		
		public function Draw():void
		{
			var x:Number = 0;

			clip.graph.graphics.clear();
			
			if (station != null)
			{
				for (var i:int = 0; i < Data.Get().current_round_id - 1; i++)
				{
					var round:Round = station.GetRound(i);
					if (round.program != null)
					{
						Debug.out("program: " + round.program);
						var size:Number;
						size = round.program.area_home / totalArea;
						if (size > 0)
							DrawBlock(x, size, round.program.type_home.color);
						x += size;
						Debug.out(" size: " + size);
						size = round.program.area_work / totalArea;
						if (size > 0)
							DrawBlock(x, size, round.program.type_work.color);
						x += size;
						Debug.out(" size: " + size);
						size = round.program.area_leisure / totalArea;
						if (size > 0)
							DrawBlock(x, size, round.program.type_leisure.color);
						x += size;
						Debug.out(" size: " + size);
					}
				}
			}
			
			for each (var slider:ProgramSlider in sliders)
			{
				DrawBlock(x, slider.size, slider.type.color);
				x += slider.size * 100;
				clip.parent.addChild(slider.clip);
				slider.clip.x = canvasX + (x / 100) * canvasWidth;
				slider.clip.y = canvasY - 3;
				slider.clip.area.text = GetSliderArea(slider);
			}
		}
		
		private function GetAvailableTransformArea():int
		{
			var result:int = 0;
			for (var i:int = 0; i < Data.Get().current_round_id; i++)
			{
				var round:Round = station.GetRound(i);
				result += round.new_transform_area;
				if (round.program != null)
					result -= round.program.area_home + round.program.area_work + round.program.area_leisure;
			}
			return result;
		}
		
		private function DrawBlock(x:Number, size:Number, color:String)
		{
			clip.graph.graphics.beginFill(parseInt("0x" + color, 16));
			clip.graph.graphics.drawRect(x, 0, size * 100, 100);
			clip.graph.graphics.endFill();
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
				var startSize:Number = activeSlider.size;
				var dirty:Boolean = false;
				activeSlider.size = startSliderSize + mouseDelta / canvasWidth;
				activeSlider.size -= activeSlider.size % (1 / totalArea);
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
						dirty = true;
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
					dirty = true;
				}
				if (dirty || activeSlider.size != startSize)
					changeCallback.call(this);
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