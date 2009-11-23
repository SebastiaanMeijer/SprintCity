package SprintStad.Drawer 
{
	import flash.display.MovieClip;
	import flash.events.MouseEvent;
	import SprintStad.Data.Data;
	import SprintStad.Data.Program.Program;
	import SprintStad.Data.Round.Round;
	import SprintStad.Data.Station.Station;
	import SprintStad.Data.Types.Type;
	import SprintStad.Data.Types.Types;
	import SprintStad.Debug.Debug;
	public class ProgramEditor
	{		
		public var changeCallback:Function = null;
		public var clip:MovieClip = null;
		public var canvasX:Number = 0;
		public var canvasY:Number = 0;
		public var canvasWidth:Number = 100;
		public var canvasHeight:Number = 100;
		
		public var totalArea:int = 0;
		public var availableArea:int = 0;
		public var editAreaStart:int = 0;
		
		public var sliders:Array = new Array();
		private var activeSlider:ProgramSlider = null;
		private var startMouseX:Number = 0;
		private var startSliderSize:Number = 0;
		private var unavailabeArea:MovieClip = new UnavailableArea();
		
		private var station:Station;
		private var currentRound:int = 0;		
		private var programHistory:Array;
		
		private var lastTime:Number = 0;
		
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
		
		public function SetTotalArea(area:int):void
		{
			this.totalArea = area;
		}
		
		public function SetStation(station:Station):void
		{
			this.station = station;
			Init();
		}
		
		public function ChangeSliderType(type:Type):void
		{
			var slider:ProgramSlider = null;
			
			if (type.type == "home" || type.type == "average_home")
				slider = sliders[ProgramSlider.TYPE_HOME];
			else if (type.type == "work" || type.type == "average_work")
				slider = sliders[ProgramSlider.TYPE_WORK];
			else if (type.type == "leisure" || type.type == "average_leisure")
				slider = sliders[ProgramSlider.TYPE_LEISURE];
			
			slider.SetType(type);
		}
		
		private function Init():void
		{
			var types:Types = Data.Get().GetTypes();
			var i:int = 0;
			
			currentRound = Data.Get().current_round_id;
			sliders = new Array();
			
			// create a history to be drawn in front of the slider edit area
			programHistory = new Array();
			for (i = 0; i < types.GetTypeCount(); i++)
				programHistory.push(0);
			// total area is always the same
			SetTotalArea(station.GetTotalTransformArea());
			if (currentRound == 1)
			{
				// if at masterplan phase all the transform area is available
				availableArea = station.GetTotalTransformArea();
				// set sliders
				sliders.push(new ProgramSlider(station.program.type_home, station.program.area_home));
				sliders.push(new ProgramSlider(station.program.type_work, station.program.area_work));
				sliders.push(new ProgramSlider(station.program.type_leisure, station.program.area_leisure));
			}
			else
			{
				// if in a game round
				// - find out what transform are was left out in previous rounds
				// - calc the starting point of the new transform area
				var round:Round = station.GetRoundById(1);	// needed when the for loop is skipped
				for (i = 2; i < this.currentRound; i++)
				{
					round = station.GetRoundById(i);
					if (round != null)
					{
						availableArea += round.new_transform_area;
						if (round.exec_program != null)
						{
							var filledArea = round.exec_program.area_home + round.exec_program.area_work + round.exec_program.area_leisure;
							availableArea -= filledArea;
							editAreaStart += filledArea;
							programHistory[round.exec_program.type_home.id - 1] += round.exec_program.area_home;
							programHistory[round.exec_program.type_work.id - 1] += round.exec_program.area_work;
							programHistory[round.exec_program.type_leisure.id - 1] += round.exec_program.area_leisure;
						}
					}
				}
				round = station.GetRoundById(this.currentRound);
				
				if (round != null)
				{
					availableArea += round.new_transform_area;
					// set sliders
					sliders.push(new ProgramSlider(round.plan_program.type_home, round.plan_program.area_home));
					sliders.push(new ProgramSlider(round.plan_program.type_work, round.plan_program.area_work));
					sliders.push(new ProgramSlider(round.plan_program.type_leisure, round.plan_program.area_leisure));
				}
			}
			
			// add type bars
			for (i = 0; i < types.GetTypeCount(); i++)
				clip.addChild(types.GetType(i).colorClip);
			
			// add unavailabe area bar
			clip.addChild(unavailabeArea);			
			
			if (sliders.length == 3)
			{
				// add area bars
				clip.addChild(sliders[ProgramSlider.TYPE_HOME].barClip);
				clip.addChild(sliders[ProgramSlider.TYPE_WORK].barClip);
				clip.addChild(sliders[ProgramSlider.TYPE_LEISURE].barClip);
							
				// add slider clips
				clip.parent.addChild(sliders[ProgramSlider.TYPE_HOME].GetClip());
				clip.parent.addChild(sliders[ProgramSlider.TYPE_WORK].GetClip());
				clip.parent.addChild(sliders[ProgramSlider.TYPE_LEISURE].GetClip());
			}
		}
		
		public function Draw():void
		{
			var types:Types = Data.Get().GetTypes();
			var x:int = 0;
			
			for (var i:int = 0; i < types.GetTypeCount(); i++)
			{
				var colorClip:MovieClip = types.GetType(i).colorClip;
				colorClip.x = (x / totalArea) * 100;
				colorClip.y = 0;
				colorClip.width = (programHistory[i] / totalArea) * 100;
				colorClip.height = 100;
				x += programHistory[i];
			}
			
			unavailabeArea.x = ((editAreaStart + availableArea) / totalArea) * 100;
			unavailabeArea.y = 0;
			unavailabeArea.width = Math.max(0, 100.0 - unavailabeArea.x);
			unavailabeArea.height = 100;
			
			for each (var slider:ProgramSlider in sliders)
			{
				slider.barClip.x = (x / totalArea) * 100;
				slider.barClip.y = 0;
				slider.barClip.width = (slider.size / totalArea) * 100;
				slider.barClip.height = 100;
				
				x += slider.size;
				
				var sliderClip:MovieClip = slider.GetClip();
				sliderClip.x = canvasX + (x / totalArea) * canvasWidth;
				sliderClip.y = canvasY - 3;
				sliderClip.area.text = slider.size;
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
			//Debug.out("PreDraw actions: " + (new Date().getTime() - lastTime) + " ms");
			lastTime = new Date().getTime();
			if (activeSlider != null)
			{
				var mouseDelta:Number = e.stageX - startMouseX;
				var startSize:int = activeSlider.size;
				activeSlider.size = startSliderSize + Math.round((mouseDelta / canvasWidth) * totalArea);
				//activeSlider.size -= activeSlider.size % (1 / totalArea);
				
				// min bound, size value should be 0 or bigger
				activeSlider.size = Math.max(activeSlider.size, 0);
				
				// max bound
				if (TotalSliderSize() > availableArea)
					activeSlider.size -= TotalSliderSize() - availableArea;
				
				if (activeSlider.size != startSize)
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
				if (mouseY > GetSliderTop(slider) && mouseY < GetSliderBottom(slider))
				{
					var sliderX:Number = GetSliderX(slider);
					if (mouseX > sliderX - ProgramSlider.SLIDER_SIZE / 2&& mouseX < sliderX + ProgramSlider.SLIDER_SIZE / 2)
					{
						return slider;
					}					
				}
			}
			return null;
			
		}
		
		private function GetSliderX(slider:ProgramSlider):Number
		{
			var position:int = 0;
			var i:int = -1;
			do {
				i++;
				position += sliders[i].size;
			} while (sliders[i] != slider && i < sliders.length)
			return canvasX  + canvasWidth * ((editAreaStart + position) / totalArea);
		}

		private function GetSliderTop(slider:ProgramSlider):Number
		{
			return canvasY + slider.GetGrabPosition() - ProgramSlider.SLIDER_SIZE;
		}
		
		private function GetSliderBottom(slider:ProgramSlider):Number
		{
			return canvasY + canvasHeight;
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