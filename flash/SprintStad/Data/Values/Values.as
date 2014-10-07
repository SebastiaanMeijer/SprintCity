package SprintStad.Data.Values 
{
	import SprintStad.Data.IDataCollection;
	import SprintStad.Debug.Debug;
	
	public class Values implements IDataCollection
	{
		private var values:Array = new Array();
		
		public function Values() 
		{
		}
		
		public function PostConstruct():void
		{}
		
		public function GetValue(index:int):Value
		{
			return values[index];
		}
		
		public function GetValueById(id:int):Value
		{
			for each (var value:Value in values)
				if (value.id == id)
					return value;
			return null;
		}
		
		public function GetValueCount():int
		{
			return values.length;
		}
		
		public function Clear():void
		{
			values = new Array();
		}
		
		public function ParseXML(xmlData:XML):void
		{
			Clear();
			
			var valueList:XMLList = null;
			var value:Value = new Value();
			var valueInfo:XML = null;
			var firstTag:String = "";
			
			valueList = xmlData.value.children();
			for each (valueInfo in valueList) 
			{
				var tag:String = valueInfo.name();
				
				if (tag == firstTag)
				{
					values.push(value);
					value = new Value();
				}
				
				if (firstTag == "")
					firstTag = tag;
				
				if (tag == "id")
					value.id = int(valueInfo);
				else if (tag == "title")
					value.title = valueInfo;
				else if (tag == "description")
					value.description = valueInfo;
			}
			values.push(value);
		}
	}
}