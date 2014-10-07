package SprintStad.Data.Team
{
	import flash.display.Loader;
	import flash.events.Event;
	import SprintStad.Data.Data;
	import SprintStad.Data.Values.Value;
	import SprintStad.Data.Values.Values;
	
	public class Team
	{
		public var id:int = 0;
		public var name:String = "";
		public var description:String = "";
		public var color:String = "";
		public var cpu:Boolean = false;
		public var created:String = "";
		public var is_player:Boolean = false;
		
		public var value_description:String = "";
		public var values:Array = new Array();
		
		private var loader:Loader = null;
		
		public function Team()
		{
		}
		
		public function HasValue(valueId:int):Boolean
		{
			return values.indexOf(valueId) > -1;
		}
		
		public function AddValue(valueId:int):void
		{
			if (values.indexOf(valueId) == -1)
				values.push(valueId);
		}
		
		public function RemoveValue(valueId:int):void
		{
			var index:int = values.indexOf(valueId);
			if (index > -1)
				values.splice(index, 1);
		}
		
		public function ParseXML(xmlData:XML):void
		{
			values = new Array();
			var valueXml:XML = null;
			var index:int = 0;
			var id:int = 0;

			valueXml = xmlData.value[index];
			while (valueXml != null)
			{
				for each (var xml:XML in valueXml.children())
				{
					if (xml.name() == "id")
					{
						id = int(xml);
					}
					else if (xml.name() == "checked")
					{
						if (int(xml) == 1)
							values.push(id);
					}
				}
				index++;
				valueXml = xmlData.value[index];
			}
		}
		
		public function GetValuesXmlString():String
		{
			var xmlString:String = "";
			var values:Values = Data.Get().GetValues();
			
			xmlString += "<values>";
			for (var i:int = 0; i < values.GetValueCount(); i++)
			{
				var value:Value = values.GetValue(i);
				xmlString += "<value><id>" + value.id + "</id><checked>" + HasValue(value.id) + "</checked></value>";
			}			
			xmlString += "<description>" + value_description + "</description>";
			xmlString += "</values>";
			
			return xmlString;
		}
	}
}