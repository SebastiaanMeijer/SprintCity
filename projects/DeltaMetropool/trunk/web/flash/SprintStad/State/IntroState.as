package SprintStad.State 
{
	import flash.display.MovieClip;
	import flash.events.Event;
	import flash.events.IOErrorEvent;
	import flash.events.MouseEvent;
	import flash.net.URLLoader;
	import flash.net.URLRequest;
	import flash.net.URLVariables;
	import SprintStad.Data.Data;
	import SprintStad.Debug.ErrorDisplay;
	public class IntroState implements IState
	{
		private var parent:SprintStad = null;
		
		public function IntroState(parent:SprintStad) 
		{
			this.parent = parent;
		}	
		
		private function LoadStationTypes()
		{
			try
			{
				// prepare loader vars
				var vars:URLVariables = new URLVariables();
				vars.session = parent.session;
				// load station data
				var typesLoader:URLLoader = new URLLoader();
				var typesRequest:URLRequest = new URLRequest(SprintStad.DOMAIN + "data/station_types.php");
				typesRequest.data = vars;
				typesLoader.addEventListener(Event.COMPLETE, StationTypesLoaded);
				typesLoader.addEventListener(IOErrorEvent.IO_ERROR , OnStationTypesLoadError);
				typesLoader.load(typesRequest);
			}
			catch (e:Error)
			{
				ErrorDisplay.Get().DisplayError("error loading: station types; " + SprintStad.DOMAIN + "data/station_types.php");
			}
		}
		
		private function LoadTypes()
		{
			try
			{
				// prepare loader vars
				var vars:URLVariables = new URLVariables();
				vars.session = parent.session;
				// load station data
				var typesLoader:URLLoader = new URLLoader();
				var typesRequest:URLRequest = new URLRequest(SprintStad.DOMAIN + "data/types.php");
				typesRequest.data = vars;
				typesLoader.addEventListener(Event.COMPLETE, TypesLoaded);
				typesLoader.addEventListener(IOErrorEvent.IO_ERROR , OnTypesLoadError);
				typesLoader.load(typesRequest);
			}
			catch (e:Error)
			{
				ErrorDisplay.Get().DisplayError("error loading: types; " + SprintStad.DOMAIN + "data/types.php");
			}
		}
		
		private function LoadConstants()
		{
			try
			{
				// prepare loader vars
				var vars:URLVariables = new URLVariables();
				vars.session = parent.session;
				// load station data
				var constantsLoader:URLLoader = new URLLoader();
				var constantsRequest:URLRequest = new URLRequest(SprintStad.DOMAIN + "data/constants.php");
				constantsRequest.data = vars;
				constantsLoader.addEventListener(Event.COMPLETE, ConstantsLoaded);
				constantsLoader.addEventListener(IOErrorEvent.IO_ERROR , OnConstantsLoadError);
				constantsLoader.load(constantsRequest);
			}
			catch (e:Error)
			{
				ErrorDisplay.Get().DisplayError("error loading: constants; " + SprintStad.DOMAIN + "data/constants.php");
			}
		}
		
		private function StationTypesLoaded(event:Event):void 
		{
			var xmlData:XML = new XML(event.target.data);
			Data.Get().GetStationTypes().ParseXML(xmlData.station_type.children());
			Data.Get().GetStationTypes().PostConstruct();
			LoadTypes();
		}
		
		private function TypesLoaded(event:Event):void 
		{
			var xmlData:XML = new XML(event.target.data);
			Data.Get().GetTypes().ParseXML(xmlData.type.children());
			Data.Get().GetTypes().PostConstruct();
			LoadConstants();
		}
		
		private function ConstantsLoaded(event:Event):void 
		{
			var xmlData:XML = new XML(event.target.data);
			Data.Get().GetConstants().ParseXML(xmlData.children());
		}
		
		private function onContinueEvent(event:MouseEvent):void
		{
			parent.gotoAndPlay(SprintStad.FRAME_VALUES);
		}
		
		function OnStationTypesLoadError(e:IOErrorEvent):void 
		{
			ErrorDisplay.Get().DisplayError("error loading: station types; " + SprintStad.DOMAIN + "data/station_types.php");
		}
		
		function OnTypesLoadError(e:IOErrorEvent):void 
		{
			ErrorDisplay.Get().DisplayError("error loading: types; " + SprintStad.DOMAIN + "data/types.php");
		}
		
		function OnConstantsLoadError(e:IOErrorEvent):void 
		{
			ErrorDisplay.Get().DisplayError("error loading: constants; " + SprintStad.DOMAIN + "data/constants.php");
		}
		
		/* INTERFACE SprintStad.State.IState */
		
		public function Activate():void 
		{			
			LoadStationTypes();
			parent.intro_movie.addEventListener(MouseEvent.CLICK, onContinueEvent);
		}
		
		public function Deactivate():void
		{
			//parent.intro_movie.removeEventListener(MouseEvent.CLICK, onContinueEvent);
		}
	}
}