package SprintStad.Data.Program 
{
	import SprintStad.Data.Data;
	import SprintStad.Data.Types.Type;
	import SprintStad.Data.Types.Types;
	import SprintStad.Debug.Debug;
	public class Program
	{
		public var id:uint = 0;
		
		public var type_home:Type = null;
		public var type_work:Type = null;
		public var type_leisure:Type = null;
		
		public var area_home:int = 0;
		public var area_work:int = 0;
		public var area_leisure:int = 0;
		
		public function Program() 
		{
			
		}
		
		public function TotalArea():int
		{
			return area_home + area_work + area_leisure;
		}
		
		public function SetType(type:Type):void
		{
			if (type.type == "home" || type.type == "average_home")
				type_home = type;
			else if (type.type == "work" || type.type == "average_work")
				type_work = type;
			else if (type.type == "leisure" || type.type == "average_leisure")
				type_leisure = type;
		}
		
		public function ParseXML(xmlData:XML):void
		{
			var types:Types = Data.Get().GetTypes();
			id = xmlData.program_id;
			type_home = types.GetTypeById(xmlData.type_home);
			type_work = types.GetTypeById(xmlData.type_work);
			type_leisure = types.GetTypeById(xmlData.type_leisure);
			area_home = xmlData.area_home;
			area_work = xmlData.area_work;
			area_leisure = xmlData.area_leisure;
		}
		
		public function GetXmlString():String
		{
			var xmlString:String = "";
			
			xmlString += "<program>";
			xmlString += "<program_id>" + id + "</program_id>";
			xmlString += "<type_home>" + type_home.id + "</type_home>";
			xmlString += "<type_work>" + type_work.id + "</type_work>";
			xmlString += "<type_leisure>" + type_leisure.id + "</type_leisure>";
			xmlString += "<area_home>" + area_home + "</area_home>";
			xmlString += "<area_work>" + area_work + "</area_work>";
			xmlString += "<area_leisure>" + area_leisure + "</area_leisure>";
			xmlString += "</program>";
			
			return xmlString;
		}
		
		public function Copy():Program
		{
			var copy:Program = new Program();
			copy.id = this.id;
			copy.type_home = this.type_home;
			copy.type_work = this.type_work;
			copy.type_leisure = this.type_leisure;
			copy.area_home = this.area_home;
			copy.area_work = this.area_work;
			copy.area_leisure = this.area_leisure;
			return copy;
		}
	}
}