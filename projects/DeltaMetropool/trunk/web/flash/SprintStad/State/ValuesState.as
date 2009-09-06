package SprintStad.State 
{
	import fl.controls.CheckBox;
	import fl.controls.TextArea;
	import flash.display.Loader;
	import flash.display.MovieClip;
	import flash.events.Event;
	import flash.events.MouseEvent;
	import flash.net.URLLoader;
	import flash.net.URLRequest;
	import flash.net.URLRequestMethod;
	import flash.net.URLVariables;
	import SprintStad.Data.Values.Value;
	import SprintStad.State.IState;
	
	public class ValuesState implements IState
	{
		private var parent:SprintStad = null;
		
		private static const CHECKBOX_HORIZONTAL_MARGIN:int = 10;
		
		public function ValuesState(parent:SprintStad) 
		{
			this.parent = parent;
		}	
		
		private function valuesLoaded(event:Event):void 
		{
			var xmlData:XML;
			
			if (event.target.data != "")
				xmlData = new XML(event.target.data);
			else
				xmlData = new XML("<values><value><id>1</id><title>Test waarde 1</title><description></description><checked>0</checked></value><value><id>2</id><title>Test waarde 2</title><description></description><checked>0</checked></value><value><id>3</id><title>Test waarde 3</title><description></description><checked>0</checked></value><value><id>4</id><title>Test waarde 4</title><description></description><checked>0</checked></value><value><id>5</id><title>Test waarde 5</title><description></description><checked>0</checked></value><description></description></values>");
			
			parseXmlData(xmlData);
			drawUI();
			
			//remove loading screen
			parent.removeChild(SprintStad.LOADER);
		}
		
		private function parseXmlData(xmlData:XML):void
		{
			var valueList:XMLList = null;
			var value:Value = new Value();
			var valueInfo:XML = null;
			
			valueList = xmlData.children();		
			for each (valueInfo in valueList) 
			{
				if (valueInfo.name() == "description")
					parent.GetValues().description = valueInfo;
			}
			
			valueList = xmlData.value.children();
			for each (valueInfo in valueList) 
			{
				var tag:String = valueInfo.name();
				
				if (tag == "id")
					value.id = int(valueInfo);
				else if (tag == "title")
					value.title = valueInfo;
				else if (tag == "description")
					value.description = valueInfo;
				else if (tag == "checked")
				{
					value.checked = Boolean(int(valueInfo));
					parent.GetValues().AddValue(value);
					value = new Value();
				}
			}
		}
		
		private function drawUI():void
		{
			// set description field text
			parent.values_movie.description_field.text = parent.GetValues().description;
			parent.values_movie.description_field.addEventListener(Event.CHANGE, descriptionChanged);
			
			// build checkboxes field
			var field:MovieClip = parent.values_movie.checkbox_field;
			var entrySpace:Number = field.height / (parent.GetValues().GetValueCount() + 1);
			var y:Number = entrySpace / 2;
			var width:Number = field.width - CHECKBOX_HORIZONTAL_MARGIN * 2;
			
			for (var i:int = 0; i < parent.GetValues().GetValueCount(); i++)
			{
				var value:Value = parent.GetValues().GetValue(i);
				var checkBox:CheckBox = new CheckBox();
				
				checkBox.name = String(value.id);
				checkBox.label = value.title;
				checkBox.labelPlacement = "right";
				checkBox.selected = value.checked;
				checkBox.x = CHECKBOX_HORIZONTAL_MARGIN;
				checkBox.y = y;
				checkBox.width = width;
				checkBox.addEventListener(Event.CHANGE, checkBoxChanged);
				field.addChild(checkBox);
				
				y += entrySpace;
			}			
		}
		
		private function uploadXML():void 
		{
			var loader:URLLoader = new URLLoader();
			var request:URLRequest = new URLRequest(SprintStad.DOMAIN + "data/values.php");
			var vars:URLVariables = new URLVariables();
			vars.session = parent.session;
			vars.data = parent.GetValues().GetXmlString();
			request.data = vars;
			loader.load(request);
		}
		
		private function onContinueEvent(event:MouseEvent):void
		{
			parent.gotoAndPlay(SprintStad.FRAME_STATION_INFO);
		}
		
		private function checkBoxChanged(event:Event):void
		{
			var value:Value = parent.GetValues().GetValueById(int(event.target.name));
			if (value != null)
				value.checked = event.target.selected;
		}
		
		private function descriptionChanged(event:Event):void
		{
			parent.GetValues().description = parent.values_movie.description_field.text;
		}
		
		/* INTERFACE SprintStad.State.IState */
		
		public function Activate():void
		{
			parent.addChild(SprintStad.LOADER);
			// prepare continue button
			parent.values_movie.continue_button.addEventListener(MouseEvent.CLICK, onContinueEvent);
			// load data
			var loader:URLLoader = new URLLoader();
			var request:URLRequest = new URLRequest(SprintStad.DOMAIN + "data/values.php");
			var vars:URLVariables = new URLVariables();
			vars.session = parent.session;
			request.data = vars;
			loader.addEventListener(Event.COMPLETE, valuesLoaded);
			loader.load(request);
		}
		
		public function Deactivate():void
		{
			uploadXML();
		}		
	}
}