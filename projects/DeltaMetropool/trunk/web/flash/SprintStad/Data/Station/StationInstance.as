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
		public var transform_area_undeveloped_rural:Number = 0;
		public var count_home_total:Number = 0;
		public var count_home_transform:Number = 0;
		public var count_work_total:Number = 0;
		public var count_work_transform:Number = 0;
		public var count_worker_total:Number = 0;
		public var count_worker_transform:Number = 0;
		public var count_traveler_bonus:Number = 0;
		public var count_citizen_bonus:Number = 0;
		
		public function StationInstance() 
		{}
		
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
			result.transform_area_undeveloped_rural = station.transform_area_undeveloped_rural;
			result.count_home_total = station.count_home_total;
			result.count_home_transform = station.count_home_transform;
			result.count_work_total = station.count_work_total;
			result.count_work_transform = station.count_work_transform;
			result.count_worker_total = station.count_worker_total;
			result.count_worker_transform = station.count_worker_transform;
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
			result.transform_area_undeveloped_rural = station.transform_area_undeveloped_rural;
			result.count_home_total = station.count_home_total;
			result.count_home_transform = station.count_home_transform;
			result.count_work_total = station.count_work_total;
			result.count_work_transform = station.count_work_transform;
			result.count_worker_total = station.count_worker_total;
			result.count_worker_transform = station.count_worker_transform;
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
			result.transform_area_undeveloped_rural = transform_area_undeveloped_rural;
			result.count_home_total = count_home_total;
			result.count_home_transform = count_home_transform;
			result.count_work_total = count_work_total;
			result.count_work_transform = count_work_transform;
			result.count_worker_total = count_worker_total;
			result.count_worker_transform = count_worker_transform;
			return result;
		}
		
		public function ApplyRound(round:Round):void
		{
			this.round = round;
			ApplyProgram(round.exec_program, round.new_transform_area);
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
		
		public function ApplyProgram(program:Program, new_transform_area:int):void
		{
			// calculate new transform area
			var constants:Constants = Data.Get().GetConstants();
			var totalTransformArea:Number = GetTotalTransformArea();
			var programTransformArea:int = program.TotalArea();
			
			var home_per_area:Number = GetAverageHomeDensity();
			var work_per_area:Number = GetAverageWorkDensity();
			var worker_per_area:Number = GetAverageWorkerDensity();
			
			var transform_area_cultivated_home_delta:Number = totalTransformArea <= 0 ? 0 :
				new_transform_area * (station.transform_area_cultivated_home / station.GetTotalTransformArea());
			var transform_area_cultivated_work_delta:Number = totalTransformArea <= 0 ? 0 :
				new_transform_area * (station.transform_area_cultivated_work / station.GetTotalTransformArea());
			var transform_area_cultivated_mixed_delta:Number = totalTransformArea <= 0 ? 0 :
				new_transform_area * (station.transform_area_cultivated_mixed / station.GetTotalTransformArea());
			
			var transform_area_undeveloped_rural_delta:Number = totalTransformArea <= 0 ? 0 :
				programTransformArea * (transform_area_undeveloped_rural / totalTransformArea);
			
			var transform_area_undeveloped_urban_delta = programTransformArea -
														 (transform_area_cultivated_home_delta +
														  transform_area_cultivated_work_delta +
														  transform_area_cultivated_mixed_delta +
														  transform_area_undeveloped_rural_delta);
			
			area_cultivated_home -= transform_area_cultivated_home_delta;
			area_cultivated_work -= transform_area_cultivated_work_delta;
			area_cultivated_mixed -= transform_area_cultivated_mixed_delta;
			area_undeveloped_urban -= transform_area_undeveloped_urban_delta;
			area_undeveloped_rural -= transform_area_undeveloped_rural_delta;
			transform_area_cultivated_home -= transform_area_cultivated_home_delta;
			transform_area_cultivated_work -= transform_area_cultivated_work_delta;
			transform_area_cultivated_mixed -= transform_area_cultivated_mixed_delta;
			transform_area_undeveloped_urban -= transform_area_undeveloped_urban_delta;
			transform_area_undeveloped_rural -= transform_area_undeveloped_rural_delta;
			
			count_home_total -= transform_area_cultivated_home_delta * home_per_area;
			count_home_transform -= transform_area_cultivated_home_delta * home_per_area;
			count_work_total -= transform_area_cultivated_work_delta * work_per_area;
			count_work_transform -= transform_area_cultivated_work_delta * work_per_area;
			count_worker_total -= (transform_area_cultivated_work_delta + transform_area_cultivated_mixed_delta) * worker_per_area;
			count_worker_transform -= (transform_area_cultivated_work_delta + transform_area_cultivated_mixed_delta) * worker_per_area;
			
			area_cultivated_home += program.area_home;
			area_cultivated_work += program.area_work;
			area_cultivated_mixed += program.area_leisure;
			
			count_home_total += program.area_home * GetHomeDensity(program);
			count_work_total += program.area_work * GetWorkAreaDensity(program);
			count_worker_total += program.area_work * GetWorkPeopleDensity(program) + program.area_leisure * GetLeisurePeopleDensity(program);
			
			if (round != null)
			{
				POVN = round.POVN;
				PWN = round.PWN;
				count_worker_total += round.worker_bonus;
				count_traveler_bonus += round.traveler_bonus;
				count_citizen_bonus += round.citizen_bonus;
			}
			
			var citizens:Number = count_home_total * constants.average_citizens_per_home + count_citizen_bonus;
			IWD = (citizens + count_worker_total) /  (area_cultivated_home + area_cultivated_work + area_cultivated_mixed);
			MNG = Math.min(citizens * 5, count_worker_total) / Math.max(citizens * 5, count_worker_total) * 100;
		}
		
		private function GetHomeDensity(program:Program):Number
		{
			if (program.type_home.type.search("average_") > -1)
				return GetAverageHomeDensity();
			else
				return program.type_home.area_density;
		}
		
		private function GetAverageHomeDensity():Number
		{
			if (station.area_cultivated_home > 0)
				return station.count_home_total / station.area_cultivated_home;
			else
				return Data.Get().GetTypes().GetTypesOfCategory("average_home")[0].area_density;
		}
		
		private function GetWorkAreaDensity(program:Program):Number
		{
			if (program.type_work.type.search("average_") > -1)
				return GetAverageWorkDensity();
			else
				return program.type_work.area_density;
		}
		
		private function GetAverageWorkDensity():Number
		{
			if (station.area_cultivated_work > 0)
				return station.count_work_total / station.area_cultivated_work;
			else
				return Data.Get().GetTypes().GetTypesOfCategory("average_work")[0].area_density;
		}
		
		private function GetAverageWorkerDensity():Number
		{
			if (station.area_cultivated_work + station.area_cultivated_mixed > 0)
				return station.count_worker_total / (station.area_cultivated_work + station.area_cultivated_mixed);
			else
				return Data.Get().GetTypes().GetTypesOfCategory("average_work")[0].people_density;
		}
		
		private function GetWorkPeopleDensity(program:Program):Number
		{
			if (program.type_work.type.search("average_") > -1)
				if (station.area_cultivated_work + station.area_cultivated_mixed > 0)
				{
					return station.count_worker_total / (station.area_cultivated_work + station.area_cultivated_mixed);
				}
				else
				{
					// TODO: copied from GetAverageWorkerDensity(), use different value here?
					return Data.Get().GetTypes().GetTypesOfCategory("average_work")[0].people_density;
				}
			else
				return program.type_work.people_density;
		}
		
		private function GetLeisurePeopleDensity(program:Program):Number
		{
			if (program.type_leisure.type.search("average_") > -1)
				if (station.area_cultivated_work + station.area_cultivated_mixed > 0)
				{
					return station.count_worker_total / (station.area_cultivated_work + station.area_cultivated_mixed);
				}
				else
				{
					// TODO: copied from GetAverageWorkerDensity(), use different value here?
					return Data.Get().GetTypes().GetTypesOfCategory("average_work")[0].people_density;
				}
			else
				return program.type_leisure.people_density;
		}
		
		public function SetRound(round:Round):void
		{
			this.POVN = round.POVN;
			this.PWN = round.PWN;
		}
		
		public function GetTotalArea():Number
		{
			return area_cultivated_home +
				area_cultivated_work +
				area_cultivated_mixed +
				area_undeveloped_urban +
				area_undeveloped_rural;
		}
		
		public function GetTotalTransformArea():Number
		{
			return transform_area_cultivated_home +
				transform_area_cultivated_work +
				transform_area_cultivated_mixed +
				transform_area_undeveloped_urban +
				transform_area_undeveloped_rural;
		}
		
		public function ToString():String
		{
			var result:String = ""
			result += "------------------------\n";
			result += (station != null ? station.name : "null") + " in round " + (round != null ? round.name : "null") + "\n";
			result += "POVN:" + POVN + " PWN:" + PWN + " IWD:" + IWD + " MNG:" + MNG + "\n";
			result += "area: (h:" + area_cultivated_home + " w:" + area_cultivated_work + " l:" + area_cultivated_mixed + " u:" + area_undeveloped_urban + " r:" + area_undeveloped_rural + ")\n";
			result += "transform: (h:" + transform_area_cultivated_home + " w:" + transform_area_cultivated_work + " l:" + transform_area_cultivated_mixed + " u:" + transform_area_undeveloped_urban + " r:" + transform_area_undeveloped_rural + ")\n";
			result += "houses: (total:" + count_home_total + " tranform:" + count_home_transform + ")\n";
			result += "bvo work: (total:" + count_work_total + " transform:" + count_work_transform + ")\n";
			result += "workers: (total:" + count_worker_total + " transform:" + count_worker_transform + ")";
			return result;
		}
	}
}