package SprintStad.Calculators 
{
	import SprintStad.Calculators.Result.StationTypeEntry;
	import SprintStad.Data.Data;
	import SprintStad.Data.Station.Station;
	import SprintStad.Data.Station.StationInstance;
	import SprintStad.Data.StationTypes.StationType;
	import SprintStad.Data.StationTypes.StationTypes;
	import SprintStad.Debug.Debug;
	public class StationTypeCalculator
	{
		public function StationTypeCalculator() 
		{			
		}
		
		public static function GetStationTypeTop(station:StationInstance):Array
		{
			var data:Data = Data.Get();
			var stationTypes:StationTypes = data.GetStationTypes();
			var top:Array = new Array();
			for (var i:int = 0; i < stationTypes.GetStationTypeCount(); i++)
			{
				var type:StationType = stationTypes.GetStationType(i);
				Debug.out(station.station.name + " - POVN: " + station.POVN + " PWN: " + station.PWN + " IWD: " + station.IWD + " MNG: " + station.MNG);
				var POVN:Number = CalculateSimilarity(station.POVN, type.POVN, Math.max(type.POVN, data.GetStations().MaxPOVN));
				var PWN:Number = CalculateSimilarity(station.PWN, type.PWN, Math.max(type.PWN, data.GetStations().MaxPWN));
				var IWD:Number = CalculateSimilarity(station.IWD, type.IWD, Math.max(type.IWD, data.GetStations().MaxIWD));
				var MNG:Number = CalculateSimilarity(station.MNG, type.MNG, Math.max(type.MNG, data.GetStations().MaxMNG));
				var average:int = (POVN + PWN + IWD + MNG) / 4;
				top.push(new StationTypeEntry(average, type));
			}
			top.sort(compare);
			return top;
		}
		
		private static function CalculateSimilarity(stationValue, typeValue, maxValue):Number
		{
			return 100 - 100 * (Math.abs(stationValue - typeValue) / maxValue);
		}
		
		private static function compare(value1:StationTypeEntry, value2:StationTypeEntry)
		{
			return value2.similarity - value1.similarity;
		}
	}
}