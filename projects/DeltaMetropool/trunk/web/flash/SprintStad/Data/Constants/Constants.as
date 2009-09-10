package SprintStad.Data.Constants 
{
	import SprintStad.Debug.Debug;
	public class Constants
	{
		public var average_citizens_per_home:Number = 0.0;
		public var average_workers_per_bvo:Number = 0.0;
		
		public function Constants() 
		{
			
		}
		
		public function ParseXML(xmlList:XMLList):void
		{
			var xml:XML = null;
			
			for each (xml in xmlList) 
			{
				var tag:String = xml.name();
				if (tag == "average_citizens_per_home")
					average_citizens_per_home = Number(xml);
				else if (tag == "average_workers_per_bvo")
					average_workers_per_bvo = Number(xml);
			}
		}		
	}
}