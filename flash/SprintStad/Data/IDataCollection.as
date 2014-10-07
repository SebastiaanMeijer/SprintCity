package SprintStad.Data 
{
	public interface IDataCollection 
	{
		function PostConstruct():void;
		function Clear():void;
		function ParseXML(xmlData:XML):void;
	}
}