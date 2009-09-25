package SprintStad.Data.Station 
{
	import adobe.utils.CustomActions;
	import SprintStad.Data.Constants.Constants;
	import SprintStad.Data.Data;
	import SprintStad.Data.Program.Program;
	import SprintStad.Data.Round.Round;
	import SprintStad.Debug.Debug;
	public class StationInstance
	{
		public var station:Station = null;
		private var round:Round = null;
		
		public var POVN:Number = 0;
		public var PWN:Number = 0;
		public var IWD:Number = 0;
		public var MNG:Number = 0;
		public var area_cultivated_home:Number = 0;
		public var area_cultivated_work:Number = 0;
		public var area_cultivated_mixed:Number = 0;
		public var area_undeveloped_urban:Number = 0;
		public var area_undeveloped_rural:Number = 0;
		public var transform_area_cultivated_home:Number = 0;
		public var transform_area_cultivated_work:Number = 0;
		public var transform_area_cultivated_mixed:Number = 0;
		public var transform_area_undeveloped_urban:Number = 0;
		public var transform_area_undeveloped_mixed:Number = 0;
		public var count_home_total:Number = 0;
		public var count_home_transform:Number = 0;
		public var count_work_total:Number = 0;
		public var count_work_transform:Number = 0;
		
		public function StationInstance() 
		{
			
		}
		
		public static function Create(station:Station):StationInstance
		{
			var result:StationInstance = new StationInstance();
			result.station = station;
			result.POVN = station.POVN;
			result.PWN = station.PWN;
			result.IWD = station.IWD;
			result.MNG = station.MNG;
			result.area_cultivated_home = station.area_cultivated_home;
			result.area_cultivated_work = station.area_cultivated_work;
			result.area_cultivated_mixed = station.area_cultivated_mixed;
			result.area_undeveloped_urban = station.area_undeveloped_urban;
			result.area_undeveloped_rural = station.area_undeveloped_rural;
			result.transform_area_cultivated_home = station.transform_area_cultivated_home;
			result.transform_area_cultivated_work = station.transform_area_cultivated_work;
			result.transform_area_cultivated_mixed = station.transform_area_cultivated_mixed;
			result.transform_area_undeveloped_urban = station.transform_area_undeveloped_urban;
			result.transform_area_undeveloped_mixed = station.transform_area_undeveloped_mixed;
			result.count_home_total = station.count_home_total;
			result.count_home_transform = station.count_home_transform;
			result.count_work_total = station.count_work_total;
			result.count_work_transform = station.count_work_transform;
			return result;
		}
		
		public function ApplyProgram(program:Program):void
		{
			// calculate new transform area
			var constants:Constants = Data.Get().GetConstants();
			var totalTransformArea:Number = GetTotalTransformArea();
			var programTransformArea:int = program.TotalArea();
			
			var home_per_area:Number = station.count_home_total / station.area_cultivated_home;
			var work_per_area:Number = station.count_work_total / station.area_cultivated_work;
			
			var transform_area_cultivated_home_delta:Number = 
				programTransformArea * (transform_area_cultivated_home / totalTransformArea);
			var transform_area_cultivated_work_delta:Number = 
				programTransformArea * (transform_area_cultivated_work / totalTransformArea);
			var transform_area_cultivated_mixed_delta:Number = 
				programTransformArea * (transform_area_cultivated_mixed / totalTransformArea);
			var transform_area_undeveloped_urban_delta:Number = 
				programTransformArea * (transform_area_undeveloped_urban / totalTransformArea);
			var transform_area_undeveloped_mixed_delta:Number = 
				programTransformArea * (transform_area_undeveloped_mixed / totalTransformArea);
			
			area_cultivated_home -= transform_area_cultivated_home_delta;
			area_cultivated_work -= transform_area_cultivated_work_delta;
			area_cultivated_mixed -= transform_area_cultivated_mixed_delta;
			area_undeveloped_urban -= transform_area_undeveloped_urban_delta;
			area_undeveloped_rural -= transform_area_undeveloped_mixed_delta;
			transform_area_cultivated_home -= transform_area_cultivated_home_delta;
			transform_area_cultivated_work -= transform_area_cultivated_work_delta;
			transform_area_cultivated_mixed -= transform_area_cultivated_mixed_delta;
			transform_area_undeveloped_urban -= transform_area_undeveloped_urban_delta;
			transform_area_undeveloped_mixed -= transform_area_undeveloped_mixed_delta;
						
			count_home_total = area_cultivated_home * home_per_area;
			count_home_transform = transform_area_cultivated_home * home_per_area;
			count_work_total = area_cultivated_work * work_per_area;
			count_work_transform = transform_area_cultivated_work * work_per_area;
			
			area_cultivated_home += program.home_area;
			area_cultivated_work += program.work_area;
			area_cultivated_mixed += program.leisure_area;
			
			count_home_total += program.home_area * program.home_type.density;
			count_work_total += program.work_area * program.work_type.density;			
			
			if (round != null)
			{
				POVN = round.POVN;
				PWN = round.PWN;
			}
			
			IWD = (count_home_total * constants.average_citizens_per_home +
				count_work_total * constants.average_workers_per_bvo) / 
				(area_cultivated_home + area_cultivated_work);
			var citizens:Number = count_home_total * constants.average_citizens_per_home;
			var workers:Number = count_work_total * constants.average_workers_per_bvo;
			MNG = Math.min(citizens, workers) / Math.max(citizens, workers) * 100;
		}
		
		public function SetRound(round:Round):void
		{
			this.POVN = round.POVN;
			this.PWN = round.PWN;
		}
		
		public function GetTotalTransformArea():Number
		{
			return transform_area_cultivated_home +
				transform_area_cultivated_work +
				transform_area_cultivated_mixed +
				transform_area_undeveloped_urban +
				transform_area_undeveloped_mixed;
		}
	}

}