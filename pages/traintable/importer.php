<?php
	require_once './includes/Excel/reader.php';
	require_once 'includes/master.inc.php';
	
	define('SHEET_TRAIN_FREQUENCIES', 0);
	define('FREQ_ROW_TRAIN_NAMES', 2);
	define('FREQ_ROW_TRAIN_TYPES', 3);
	define('FREQ_ROW_DATA_START', 5);
	define('FREQ_COLUMN_STATION_CODES', 1);
	define('FREQ_COLUMN_STATION_NAMES', 2);
	define('FREQ_COLUMN_DATA_START', 3);
	
	define('SHEET_CHAIN_VALUES', 3);
	define('CHAIN_ROW_DATA_START', 3);
	define('CHAIN_COLUMN_STATION_NAMES', 2);
	define('CHAIN_COLUMN_CHAIN_VALUES', 9);
	
	define('SHEET_TRAVELERS', 4);
	define('TRAVELERS_ROW_DATA_START', 2);
	define('TRAVELERS_COLUMN_STATION_CODES', 1);
	define('TRAVELERS_COLUMN_TRAVELERS', 3);
	
	if(!$Auth->loggedIn()) redirect('../login.php');
	
	if (isset($_POST['FormAction']))
	{
		if(strtolower(substr($_FILES['trainTableFileName']['name'], -3))==='xls')
		{
			ini_set('memory_limit', '-1');
			$data = new Spreadsheet_Excel_Reader();
			$data->setOutputEncoding('ISO-8859-1//TRANSLIT//IGNORE');
			$data->read($_FILES['trainTableFileName']['tmp_name']);
	
			ImportFrequencyTable($data);
		}
		else
		{
			echo "Alleen bestanden van het XLS formaat kunnen worden geimporteerd";
		}
	}
	else 
	{
	}
	
	function ImportFrequencyTable($data)
	{
		$trainTable = new TrainTable();
		$trainTable->SetData($_FILES['trainTableFileName']['name']);
		$trainTable->SetImportTimestamp();
		
		$columnToTrainId = array();
		
		for ($i=FREQ_COLUMN_DATA_START; $i <= $data->sheets[SHEET_TRAIN_FREQUENCIES]['numCols']; $i++)
		{
			if(isset($data->sheets[SHEET_TRAIN_FREQUENCIES]['cells'][FREQ_ROW_TRAIN_NAMES][$i])) {
				$trainName =  $data->sheets[SHEET_TRAIN_FREQUENCIES]['cells'][FREQ_ROW_TRAIN_NAMES][$i];
				$trainType =  $data->sheets[SHEET_TRAIN_FREQUENCIES]['cells'][FREQ_ROW_TRAIN_TYPES][$i];
				$train = new TrainTableTrain();
				$train->SetData($trainTable->id, $trainName, $trainType);
				$columnToTrainId[$i] = $train->id;
			}
		}
		
		$entryValues = "";
		
		for ($i=FREQ_ROW_DATA_START; $i <= $data->sheets[SHEET_TRAIN_FREQUENCIES]['numRows']; $i++)
		{
			if (isset($data->sheets[SHEET_TRAIN_FREQUENCIES]['cells'][$i][FREQ_COLUMN_STATION_CODES])) {
				$stationCode = $data->sheets[SHEET_TRAIN_FREQUENCIES]['cells'][$i][FREQ_COLUMN_STATION_CODES];
				$stationName = $data->sheets[SHEET_TRAIN_FREQUENCIES]['cells'][$i][FREQ_COLUMN_STATION_NAMES];
				$station = new TrainTableStation();
				$station->SetData($trainTable->id, $stationCode, $stationName);
				for ($j=FREQ_COLUMN_DATA_START; $j <= $data->sheets[SHEET_TRAIN_FREQUENCIES]['numCols']; $j++)
				{
					if (isset($columnToTrainId[$j]) && isset($data->sheets[SHEET_TRAIN_FREQUENCIES]['cells'][$i][$j]))
					{
						if ($entryValues !== "")
						{
							$entryValues .= ", ";
						}
						$entryValues .= "($columnToTrainId[$j], $station->id, " . $data->sheets[SHEET_TRAIN_FREQUENCIES]['cells'][$i][$j] . ")";
					}
				}
			}
		}
		
		$db = Database::getDatabase();
		$db->query("
			INSERT INTO TrainTableEntry (train_id, station_id, frequency)
			VALUES " . $entryValues, 
			array());
		
		
		// the excel reader can't read the station names in the chain sheet, so assume the station order of the traveler sheet
		// applies to the chain sheet as well
		$offset = CHAIN_ROW_DATA_START - TRAVELERS_ROW_DATA_START;
		for ($i=TRAVELERS_ROW_DATA_START; $i <= $data->sheets[SHEET_TRAVELERS]['numRows']; $i++)
		{
			if (isset($data->sheets[SHEET_TRAVELERS]['cells'][$i][TRAVELERS_COLUMN_STATION_CODES])) {
				$stationCode = $data->sheets[SHEET_TRAVELERS]['cells'][$i][TRAVELERS_COLUMN_STATION_CODES];
				$stationArray = TrainTableStation::getStationByCode($trainTable->id, $stationCode);
				$keys = array_keys($stationArray);
				$station = $stationArray[$keys[0]];
				$station->travelers = $data->sheets[SHEET_TRAVELERS]['cells'][$i][TRAVELERS_COLUMN_TRAVELERS];
				$station->chain = $data->sheets[SHEET_CHAIN_VALUES]['cells'][$i + $offset][CHAIN_COLUMN_CHAIN_VALUES];
				$station->save();
			}
		}
		header("Location: admin.php?view=traintable");
	}
?>
