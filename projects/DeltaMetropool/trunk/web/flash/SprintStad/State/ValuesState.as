package SprintStad.State 
{
	import fl.controls.CheckBox;
	import fl.controls.RadioButton;
	import fl.controls.TextArea;
	import flash.display.Loader;
	import flash.display.MovieClip;
	import flash.events.Event;
	import flash.events.IOErrorEvent;
	import flash.events.MouseEvent;
	import flash.filters.GlowFilter;
	import flash.net.URLLoader;
	import flash.net.URLRequest;
	import flash.net.URLRequestMethod;
	import flash.net.URLVariables;
	import SprintStad.Data.Data;
	import SprintStad.Data.DataLoader;
	import SprintStad.Data.Values.Value;
	import SprintStad.Debug.Debug;
	import SprintStad.Debug.ErrorDisplay;
	import SprintStad.State.IState;
	import SprintStad;
	import SprintStad.Data.Values.Values;
	import SprintStad.Data.Team.Teams;
	import SprintStad.Data.Team.Team;
	
	public class ValuesState implements IState
	{
		private var parent:SprintStad = null;
		
		private var amountSelected:int = 0;
		private var disabled:Boolean = false;
		
		var filter:GlowFilter = new GlowFilter(0xffffff, 1, 6, 6, 1, 1);
		
		private static const CHECKBOX_HORIZONTAL_MARGIN:int = 10;
		
		public function ValuesState(parent:SprintStad) 
		{
			this.parent = parent;
		}	

		private function drawUI():void
		{
			var data:Data = Data.Get();
			var team:Team = parent.GetCurrentStation().owner;
			var values:Values = data.GetValues();
			
			// set description field text
			parent.values_movie.description_field.text = team.value_description;
			parent.values_movie.description_field.addEventListener(Event.CHANGE, descriptionChanged);
			parent.values_movie.valuesTeamName.text = team.name;
			
			// build checkboxes field
			var field:MovieClip = parent.values_movie.checkbox_field;
			var entrySpace:Number = field.height / (data.GetValues().GetValueCount() + 1);
			var y:Number = entrySpace / 2;
			var width:Number = field.width - CHECKBOX_HORIZONTAL_MARGIN * 2;
			
			amountSelected = 0;
			disabled = false;
			
			for (var i:int = 0; i < values.GetValueCount(); i++)
			{
				var value:Value = values.GetValue(i);
				var checkBox:CheckBox = new CheckBox();
				
				checkBox.name = String(value.id);
				checkBox.label = value.title;
				checkBox.labelPlacement = "right";
				checkBox.selected = team.HasValue(value.id);
				if (checkBox.selected)
					amountSelected++;
				
				checkBox.x = CHECKBOX_HORIZONTAL_MARGIN;
				checkBox.y = y;
				checkBox.width = width;
				checkBox.addEventListener(Event.CHANGE, checkBoxChanged);
				field.addChild(checkBox);
				y += entrySpace;
			}
			if (amountSelected >= 3)
				disabled = true;
		}
		
		private function uploadXML():void 
		{
			var loader:URLLoader = new URLLoader();
			var request:URLRequest = new URLRequest(SprintStad.DOMAIN + "data/teams.php");
			var vars:URLVariables = new URLVariables();
			var teams:Teams = Data.Get().GetTeams();
			vars.data = teams.GetTeamById(teams.GetOwnTeam().id).GetValuesXmlString();
			request.data = vars;
			loader.load(request);
		}		
		
		private function onContinueEvent(event:MouseEvent):void
		{
			parent.gotoAndPlay(SprintStad.FRAME_OVERVIEW);
		}
		
		private function onMouseOverEvent(event:MouseEvent):void
		{
			filter.strength = 1.5;
			parent.values_movie.continue_button.filters = [filter];
		}
		
		private function onMouseOutEvent(event:MouseEvent):void
		{
			filter.strength = 1;
			if (parent.values_movie != null && parent.values_movie.continue_button != null)
				parent.values_movie.continue_button.filters = [filter];
		}
		
		private function checkBoxChanged(event:Event):void
		{
			var value:Value = Data.Get().GetValues().GetValueById(int(event.target.name));
			
			if (value != null)
			{
				var team:Team = Data.Get().GetTeams().GetOwnTeam();
				
				// if not in the masterplan round or if the current values displayed are not yours, set the checkbox to it's original state and return
				if (Data.Get().current_round_id != 1 || 
					team.id != Data.Get().GetStations().GetStation(parent.currentStationIndex).owner.id)
				{
					event.target.selected = (!event.target.selected);
					return;
				}
				
				if(disabled && event.target.selected == false)
				{
					team.RemoveValue(value.id);
					amountSelected--;
				}
				else if(!disabled && event.target.selected == true)
				{
					team.AddValue(value.id);
					amountSelected++;
				}
				else if (!disabled && event.target.selected == false)
				{
					team.RemoveValue(value.id);
					amountSelected--;
				}
				else
				{
					event.target.selected = (!event.target.selected);
					return;
				}
			}
			
			if (amountSelected >= 3)
				disabled = true;
			else
				disabled = false;
		}
		
		private function descriptionChanged(event:Event):void
		{
			Data.Get().GetTeams().GetOwnTeam().value_description = parent.values_movie.description_field.text;
		}
		
		public function OnLoadingDone(data:int)
		{
			Debug.out(this + " I know " + data);
			
			drawUI();
			parent.removeChild(SprintStad.LOADER);
		}

		/* INTERFACE SprintStad.State.IState */
		
		public function Activate():void
		{
			parent.addChild(SprintStad.LOADER);
			// prepare continue button
			parent.values_movie.continue_button.buttonMode = true;
			parent.values_movie.continue_button.addEventListener(MouseEvent.CLICK, onContinueEvent);
			parent.values_movie.continue_button.addEventListener(MouseEvent.MOUSE_OVER, onMouseOverEvent);
			parent.values_movie.continue_button.addEventListener(MouseEvent.MOUSE_OUT, onMouseOutEvent);
			
			DataLoader.Get().AddJob(DataLoader.DATA_VALUES, OnLoadingDone);
		}
		
		public function Deactivate():void
		{
			uploadXML();
		}		
	}
}