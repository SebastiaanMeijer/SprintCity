package SprintStad.Debug 
{
	import flash.display.MovieClip;
	public class ErrorDisplay
	{
		private static var instance:ErrorDisplay = new ErrorDisplay();
		private var root:MovieClip = null;
		
		public function ErrorDisplay() 
		{
			
		}
		
		public static function Get():ErrorDisplay
		{
			return instance;
		}
		
		public function SetRoot(movieClip:MovieClip):void
		{
			this.root = movieClip;
		}
		
		public function DisplayError(message:String):void
		{
			var errorScreen:ErrorScreen = new ErrorScreen();
			errorScreen.error_message.text = message;
			root.addChild(errorScreen);
		}
	}
}