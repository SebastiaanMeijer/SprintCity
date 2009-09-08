package SprintStad.Debug 
{
	import flash.external.ExternalInterface;
	public class Debug
	{
		
		public function Debug() 
		{			
		}
		
		public static function out(message:String)
		{
			trace(message);
			ExternalInterface.call("console.log", message);
		}
		
	}

}