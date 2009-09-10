package SprintStad.Data.Types 
{
	public class Types
	{
		private var types:Array = new Array();
		
		public function Types() 
		{
			
		}
		
		public function PostConstruct():void
		{
			for each (var type:Type in types)
			{
				type.PostConstruct();
			}
		}
		
		public function AddType(type:Type):void
		{
			types.push(type);
		}
		
		public function GetType(index:int):Type
		{
			return types[index];
		}
		
		public function GetTypeById(id:int):Type
		{
			for each (var type:Type in types)
				if (type.id == id)
					return type;
			return null;
		}
		
		public function GetTypeCount():int
		{
			return types.length;
		}
		
		public function ParseXML(xmlList:XMLList):void
		{
			var type:Type = new Type();
			var xml:XML = null;
			var firstTag:String = "";
			
			for each (xml in xmlList) 
			{
				var tag:String = xml.name();
				
				if (xml.name() == firstTag)
				{
					AddType(type);
					type = new Type();
				}
				
				if (firstTag == "")
					firstTag = xml.name();
					
				type[xml.name()] = xml;
			}
			AddType(type);
		}
	}
}