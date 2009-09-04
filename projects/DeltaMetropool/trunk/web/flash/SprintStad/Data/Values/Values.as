package SprintStad.Data.Values 
{
	public class Values
	{
		private var values:Array = new Array();
		public var description:String = "";
		
		public function Values() 
		{			
		}
		
		public function AddValue(value:Value):void
		{
			values.push(value);
		}
		
		public function GetValue(index:int):Value
		{
			return values[index];
		}
		
		public function GetValueCount():int
		{
			return values.length;
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