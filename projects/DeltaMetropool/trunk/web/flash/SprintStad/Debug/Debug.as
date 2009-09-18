package SprintStad.Debug 
{
	import flash.external.ExternalInterface;
	public class Debug
	{
		
		public function Debug() 
		{			
		}
		
		public static function out(message:Object)
		{
			trace(String(message));
			ExternalInterface.call("console.log", String(message));
		}
		
	}

}