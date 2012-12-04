package SprintStad.Data.Facility 
{
	import SprintStad.Data.Facility.Facility;
	import SprintStad.Data.IDataCollection;
	import SprintStad.Debug.Debug;
	public class Facilities implements IDataCollection
	{
		private var facilities:Array = new Array();
		
		public function Facilities() 
		{
			
		}
		
		public function PostConstruct():void
		{
			for each (var facility:Facility in facilities)
			{
				facility.PostConstruct();
			}
		}
		
		public function AddFacility(facility:Facility):void
		{
			facilities.push(facility);
		}
		
		public function GetFacilityById(id:int):Facility
		{
			for each (var facility:Facility in facilities)
			{
				if (facility.id == id)
					return facility;
			}
			return null;
		}
		
		public function GetFacilityCount():int
		{
			return facilities.length;
		}
		
		public function Clear():void
		{
			facilities = new Array();
		}
		
		public function ParseXML(xmlData:XML):void
		{
			Clear();
			
			var xmlList:XMLList = xmlData.facility.children();
			var facility:Facility = new Facility();
			var xml:XML = null;
			var firstTag:String = "";
			
			for each (xml in xmlList) 
			{
				var tag:String = xml.name();
				
				if (xml.name() == firstTag)
				{
					AddFacility(facility);
					facility = new Facility();
				}
				
				if (firstTag == "")
					firstTag = xml.name();
					
				facility[xml.name()] = xml;
			}
			AddFacility(facility);
		}
	}
}