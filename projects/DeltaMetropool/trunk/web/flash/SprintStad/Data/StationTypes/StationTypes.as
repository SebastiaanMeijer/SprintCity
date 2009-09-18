package SprintStad.Data.StationTypes 
{
	import SprintStad.Data.IDataCollection;
	public class StationTypes implements IDataCollection
	{
		private var stationTypes:Array = new Array();
		
		public function StationTypes() 
		{
			
		}
		
		public function PostConstruct():void
		{
			for each (var stationType:StationType in stationTypes)
			{
				stationType.PostConstruct();
			}
		}
		
		public function AddStationType(stationType:StationType):void
		{
			stationTypes.push(stationType);
		}
		
		public function GetStationType(index:int):StationType
		{
			return stationTypes[index];
		}
		
		public function GetStationTypeById(id:int):StationType
		{
			for each (var stationType:StationType in stationTypes)
				if (stationType.id == id)
					return stationType;
			return null;
		}
		
		public function GetStationTypeCount():int
		{
			return stationTypes.length;
		}
		
		public function Clear():void
		{
			stationTypes = new Array();
		}
		
		public function ParseXML(xmlData:XML):void
		{
			Clear();
			
			var xmlList:XMLList = xmlData.station_type.children();
			var stationType:StationType = new StationType();
			var xml:XML = null;
			var firstTag:String = "";
			
			for each (xml in xmlList) 
			{
				var tag:String = xml.name();
				
				if (xml.name() == firstTag)
				{
					AddStationType(stationType);
					stationType = new StationType();
				}
				
				if (firstTag == "")
					firstTag = xml.name();
					
				stationType[xml.name()] = xml;
			}
			AddStationType(stationType);
		}
	}
}