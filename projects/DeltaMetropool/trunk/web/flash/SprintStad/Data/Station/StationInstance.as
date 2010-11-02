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
		public var round:Round = null;
		
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
		
		public static function CreateInitial(station:Station):StationInstance
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
			for (var i:int = 2; i < Data.Get().current_round_id; i++)
			{
				var round:Round = station.GetRoundById(i)
				if (round != null)
					result.ApplyRound(station.GetRoundById(i));
			}
			return result;
		}
		
		public function Copy():StationInstance
		{
			var result:StationInstance = new StationInstance();
			result.station = station;
			result.POVN = POVN;
			result.PWN = PWN;
			result.IWD = IWD;
			result.MNG = MNG;
			result.area_cultivated_home = area_cultivated_home;
			result.area_cultivated_work = area_cultivated_work;
			result.area_cultivated_mixed = area_cultivated_mixed;
			result.area_undeveloped_urban = area_undeveloped_urban;
			result.area_undeveloped_rural = area_undeveloped_rural;
			result.transform_area_cultivated_home = transform_area_cultivated_home;
			result.transform_area_cultivated_work = transform_area_cultivated_work;
			result.transform_area_cultivated_mixed = transform_area_cultivated_mixed;
			result.transform_area_undeveloped_urban = transform_area_undeveloped_urban;
			result.transform_area_undeveloped_mixed = transform_area_undeveloped_mixed;
			result.count_home_total = count_home_total;
			result.count_home_transform = count_home_transform;
			result.count_work_total = count_work_total;
			result.count_work_transform = count_work_transform;
			return result;
		}
		
		public function ApplyRound(round:Round):void
		{
			this.round = round;
			ApplyProgram(round.exec_program);
			this.round = null;
		}
		
		public function ApplyStaticRoundInfo(round:Round):void
		{
			if (round != null)
			{
				POVN = round.POVN;
				PWN = round.PWN;
			}
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
			
			count_home_total -= transform_area_cultivated_home_delta * home_per_area;
			count_home_transform -= transform_area_cultivated_home_delta * home_per_area;
			count_work_total -= transform_area_cultivated_work_delta * work_per_area;
			count_work_transform -= transform_area_cultivated_work_delta * work_per_area;
			
			area_cultivated_home += program.area_home;
			area_cultivated_work += program.area_work;
			area_cultivated_mixed += program.area_leisure;
			
			count_home_total += program.area_home * GetHomeDensity(program);
			count_work_total += program.area_work * GetWorkDensity(program);
			
			if (round != null)
			{
				POVN = round.POVN;
				PWN = round.PWN;
			}
			
			IWD = (count_home_total * constants.average_citizens_per_home +
				count_work_total * constants.average_workers_per_bvo) / 
				(area_cultivated_home + area_cultivated_work);
			//Debug.out("IWD: " + IWD);
			var citizens:Number = count_home_total * constants.average_citizens_per_home;
			var workers:Number = count_work_total * constants.average_workers_per_bvo;
			MNG = Math.min(citizens * 5, workers) / Math.max(citizens * 5, workers) * 100;
			//Debug.out("MNG: " + MNG);
		}
		
		private function GetHomeDensity(program:Program):Number
		{
			if (program.type_home.type.search("average_") > -1)
				return station.count_home_total / area_cultivated_home;
			else
				return program.type_home.density;
		}
		
		private function GetWorkDensity(program:Program):Number
		{
			if (program.type_work.type.search("average_") > -1)
				return station.count_work_total / area_cultivated_work;
			else
				return program.type_work.density;
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