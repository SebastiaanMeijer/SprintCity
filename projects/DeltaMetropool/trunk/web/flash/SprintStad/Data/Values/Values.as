package SprintStad.Data.Values 
{
	import SprintStad.Data.IDataCollection;
	public class Values implements IDataCollection
	{
		private var values:Array = new Array();
		public var description:String = "";
		
		public function Values() 
		{			
		}
		
		public function PostConstruct():void
		{}
		
		public function AddValue(value:Value):void
		{
			values.push(value);
		}
		
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
			
			valueList = xmlData.children();		
			for each (valueInfo in valueList) 
			{
				if (valueInfo.name() == "description")
					this.description = valueInfo;
			}
			
			valueList = xmlData.value.children();
			for each (valueInfo in valueList) 
			{
				var tag:String = valueInfo.name();
				
				if (tag == "id")
					value.id = int(valueInfo);
				else if (tag == "title")
					value.title = valueInfo;
				else if (tag == "description")
					value.description = valueInfo;
				else if (tag == "checked")
				{
					value.checked = Boolean(int(valueInfo));
					this.AddValue(value);
					value = new Value();
				}
			}
		}
		
		
		
		public function GetXmlString():String
		{
			var xmlString:String = "";
			
			xmlString += "<values>";
			
			for each (var value:Value in values)
			{
				xmlString += value.GetXmlString();
			}
			
			xmlString += "<description>";
			xmlString += description;
			xmlString += "</description>";
			xmlString += "</values>";
			
			return xmlString;
		}
	}
}