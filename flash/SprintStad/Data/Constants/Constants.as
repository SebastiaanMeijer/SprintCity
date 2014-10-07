package SprintStad.Data.Constants 
{
	import SprintStad.Data.IDataCollection;
	import SprintStad.Debug.Debug;
	public class Constants implements IDataCollection
	{
		public var average_citizens_per_home:Number = 0.0;
		public var average_workers_per_bvo:Number = 0.0;
		public var average_travelers_per_ha_leisure:Number = 0.0;
		public var average_travelers_per_citizen:Number = 0.0;
		public var average_travelers_per_worker:Number = 0.0;
		
		public function Constants() 
		{
			
		}
		
		public function PostConstruct():void
		{}
		
		public function Clear():void
		{}
		
		public function ParseXML(xmlData:XML):void
		{
			var xmlList:XMLList = xmlData.children();
			var xml:XML = null;

			for each (xml in xmlList) 
			{
				var tag:String = xml.name();
				if (tag == "average_citizens_per_home")
					average_citizens_per_home = Number(xml);
				else if (tag == "average_workers_per_bvo")
					average_workers_per_bvo = Number(xml);
				else if (tag == "average_travelers_per_ha_leisure")
					average_travelers_per_ha_leisure = Number(xml);
				else if (tag == "average_travelers_per_citizen")
					average_travelers_per_citizen = Number(xml);
				else if (tag == "average_travelers_per_worker")
					average_travelers_per_worker = Number(xml);
			}
		}		
	}
}