package SprintStad.Data.Program 
{
	import SprintStad.Data.Data;
	import SprintStad.Data.Types.Type;
	import SprintStad.Data.Types.Types;
	import SprintStad.Debug.Debug;
	public class Program
	{
		public var program_id:uint = 0;
		
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
		
		public function ParseXML(xmlData:XML):void
		{
			var types:Types = Data.Get().GetTypes();
			program_id = xmlData.program_id;
			type_home = types.GetType(xmlData.type_home);
			type_work = types.GetType(xmlData.type_work);
			type_leisure = types.GetType(xmlData.type_leisure);
			area_home = xmlData.area_home;
			area_work = xmlData.area_work;
			area_leisure = xmlData.area_leisure;
		}
	}
}