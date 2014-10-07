package SprintStad.Data.Round 
{
	import SprintStad.Data.IDataCollection;
	import SprintStad.Debug.Debug;
	
	public class MobilityReport implements IDataCollection
	{
		private var report:String = "";
		
		public function MobilityReports() 
		{
			
		}
		
		public function PostConstruct():void
		{
			
		}
		
		public function getReport():String
		{
			return report;
		}
		
		public function Clear():void
		{
			report = "";
		}
		
		public function ParseXML(xmlData:XML):void
		{
			Clear();
			report = xmlData;
		}
	}

}