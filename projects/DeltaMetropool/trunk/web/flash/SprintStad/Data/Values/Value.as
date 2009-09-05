package SprintStad.Data.Values
{
	public class Value
	{		
		public var id:int = 0;
		public var title:String = "";
		public var description:String = "";
		public var checked:Boolean = false;
		
		public function Value() 
		{
			this.id = id;
			this.title = title;
			this.description = description;
			this.checked = checked;
		}
		
		public function Check():void
		{
			checked = true;
		}
		
		public function UnCheck():void
		{
			checked = false;
		}
		
		public function ToggeChecked():void
		{
			checked = !checked;
		}
		
		public function GetXmlString():String
		{
			var xmlString:String = "";
			
			xmlString += "<value>";
			xmlString += "<id>" + id + "</id>";
			xmlString += "<checked>" + int(checked) + "</checked>";
			xmlString += "</value>";
			
			return xmlString;
		}
	}
}