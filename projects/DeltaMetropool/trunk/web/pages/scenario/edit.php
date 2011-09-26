<?php
	require_once 'includes/master.inc.php';
	if(!$Auth->loggedIn()) redirect('../login.php');
	
	$class = new Loop('odd', 'even');
	
	$scenarioId = GetScenarioId();
	$scenario = null;
	$stations = null;
	$demand = null;
	$usedRoundInfos = Round::getUsedRoundInfos();
	$types = Type::getSpecificTypes();
	InitData();
	$submitAction = GetSubmitAction();
	
	function GetScenarioId()
	{
		$scenarioId = null;
		if (!isset($_GET['scenario']) && isset($_POST['id']) && $_POST['id'] != "")
		{
			$scenario = Scenario::getScenarioById($_POST['id']);
			if (sizeof($scenario) > 0)
			{
				$keys = array_keys($scenario);
				$scenarioId = $scenario[$keys[0]]->id;
			}
		}
		else if (isset($_GET['scenario']))
		{
			$scenarioId = $_GET['scenario'];
		}
		return $scenarioId;
	}
	
	function InitData()
	{
		global $scenarioId, $scenario, $stations, $demand, $types, $usedRoundInfos;
		if (isset($_POST['FormAction']))
		{
			if (!is_null($scenarioId))
			{
				$scenario = new Scenario($scenarioId);
				$stations = Station::getStationsOfScenario($scenarioId);
				$demand = GetDemandData($scenarioId);
			}
			else
			{
				$scenario = new Scenario();
				$stations = array();
				$demand = GetEmptyDemandData();
			}
			$scenario->load($_POST);
			
			if (ValidateForm())
			{
				// TODO: save data here
				//$stationId = $station->save();
				//foreach ($rounds as $round)
				//{
				//	$round->station_id = $stationId;
				//	$round->save();
				//}
				DisplayMessage('success', 'Success', array('De wijzigingen zijn opgeslagen.'));
			}
		}
		else 
		{
			// get station data
			if (!is_null($scenarioId))
			{
				$scenario = new Scenario($scenarioId);
				$stations = Station::getStationsOfScenario($scenarioId);
				$demand = GetDemandData($scenarioId);
			}
			else
			{
				$scenario = new Scenario();
				$stations = array();
				$demand = GetEmptyDemandData();
			}
		}
	}
	
	function GetDemandData($scenarioId)
	{
		$demand = array();
		$result = Demand::getDemandDescriptionForScenario($scenarioId);
		while ($row=mysql_fetch_row($result))
		{
			$row[3] = explode(',', $row[3]);
			$row[4] = explode(',', $row[4]);
			$demand[] = $row;
		}
		return $demand;
	}
	
	function GetEmptyDemandData()
	{
		global $types, $usedRoundInfos;
		$demand = array();
		foreach ($types as $type_key => $type)
		{
			$index = sizeof($demand);
			$demand[$index][0] = $type_key;
			$demand[$index][1] = $type->name;
			$demand[$index][2] = $type->color;
			$demand[$index][3] = array();
			$demand[$index][4] = array();
			foreach ($usedRoundInfos as $roundInfo_key => $roundInfo)
			{
				$demand[$index][3][] = $roundInfo_key;
				$demand[$index][4][] = 0;
			}
		}
		return $demand;
	}
	
	function GetSubmitAction()
	{
		global $scenarioId, $submitAction;
		$submitAction = "admin.php?view=scenario";
		if (isset($_GET['action']))
			$submitAction .= "&action=" . $_GET['action'];
		if (!is_null($scenarioId))
			$submitAction .= "&station=" . $scenarioId;
	}
	
	function ValidateForm()
	{
		// TODO: validate form data
		/*
		global $station, $rounds;
		$errors = array();
		$warnings = array();
		
		// validate code
		if (is_null($station->code) || $station->code == "")
			$errors[] = "Er is geen station code ingevuld.";
		else if (!Station::isStationCodeUnique($station->code, $station->id))
			$errors[] = "De opgegeven station code bestaat al.";
		
		// validate name
		if (is_null($station->name) || $station->name == "")
			$errors[] = "Er is geen station naam ingevuld.";
		else if (!Station::isStationNameUnique($station->name, $station->id))
			$errors[] = "De opgegeven station naam bestaat al.";	
		
		// validate transform areas
		$totalTransformInRounds = 0;
		foreach ($rounds as $key => $value)
			$totalTransformInRounds += $value->new_transform_area;
		$totalTransformInStation = 
			$station->transform_area_cultivated_home + 
			$station->transform_area_cultivated_work + 
			$station->transform_area_cultivated_mixed + 
			$station->transform_area_undeveloped_urban + 
			$station->transform_area_undeveloped_rural;
		if ($totalTransformInRounds > $totalTransformInStation)
			$errors[] = "In de rondes wordt meer transformatie gebied vrijgegeven dan er aanwezig is om het station (rondes totaal: " . $totalTransformInRounds . " ha, station totaal: " . $totalTransformInStation . " ha)";
		else if ($totalTransformInRounds < $totalTransformInStation)
			$warnings[] = "In de rondes wordt minder transformatie gebied vrijgegeven dan er aanwezig is om het station (rondes totaal: " . $totalTransformInRounds . " ha, station totaal: " . $totalTransformInStation . " ha). Niet al het transformatie gebied kan hierdoor in het spel bebouwd worden.";
		
		// output errors
		if (sizeof($errors) > 0)
			DisplayMessage('error', 'Foutmelding', $errors);
		
		// output warnings
		if (sizeof($warnings) > 0)
			DisplayMessage('warning', 'Waarschuwing', $warnings);
		
		return sizeof($errors) == 0;
		*/
		return true;
	}
	
	function DisplayMessage($type, $header, $messages)
	{
		echo "\t\t\t\t\t\t" . '<tr class="' . $type . '"><td>' . "\n";
		echo "\t\t\t\t\t\t\t" . '<h3>' . $header . '</h3>' . "\n";
		if (sizeof($messages) > 0)
		{
			echo "\t\t\t\t\t\t\t" . '<ul>' . "\n";
			foreach ($messages as $key => $value)
				echo "\t\t\t\t\t\t\t\t" . '<li>' . $value . '</li>' . "\n";
			echo "\t\t\t\t\t\t\t" . '</ul>' . "\n";
		}
		echo "\t\t\t\t\t\t" . '</td></tr>';
	}

	function GenerateDemandForm($demand)
	{
		global $class, $usedRoundInfos;
		echo "\t\t\t\t\t\t\t\t" . '<table class="data">' . "\n";
		echo "\t\t\t\t\t\t\t\t\t" . '<tr>' . "\n";
		echo "\t\t\t\t\t\t\t\t\t\t" . '<th colspan="' . (sizeof($usedRoundInfos) + 1) . '">Marktvraag</th>' . "\n";
		echo "\t\t\t\t\t\t\t\t\t" . '</tr>' . "\n";
		echo "\t\t\t\t\t\t\t\t\t" . '<tr class="' . $class . '">' . "\n";
		echo "\t\t\t\t\t\t\t\t\t\t" . '<td></td>' . "\n";
		foreach ($usedRoundInfos as $key => $value)
			echo "\t\t\t\t\t\t\t\t\t\t" . '<td><center>20' . $value->number . '</center></td>' . "\n";
		echo "\t\t\t\t\t\t\t\t\t" . '</tr>' . "\n";
		foreach ($demand as $type)
		{
			echo "\t\t\t\t\t\t\t\t\t" . '<tr class="' . $class . '">' . "\n";
			echo "\t\t\t\t\t\t\t\t\t\t" . '<td>' . "\n";
			echo "\t\t\t\t\t\t\t\t\t\t\t" . '<div style="float:left; display:block; margin-right: 5px; width: 16px; height: 16px; background-color: #' . $type[2] . '"></div>' . "\n";
			echo "\t\t\t\t\t\t\t\t\t\t\t" . $type[1] . "\n";
			echo "\t\t\t\t\t\t\t\t\t\t" . '</td>' . "\n";
			for ($i = 0; $i < sizeof($type[3]); $i++)
				echo "\t\t\t\t\t\t\t\t\t\t" . '<td><input name="type,' . $type[0] . ',' . $type[3][$i] . '" size="8" value="' . $type[4][$i] . '"></td>' . "\n";
			echo "\t\t\t\t\t\t\t\t\t" . '</tr>' . "\n";
		}
		echo "\t\t\t\t\t\t\t\t" . '</table>' . "\n";
	}
?>
						<tr>
							<td>
								<form action="<?php echo $submitAction ?>" method="POST">
								<table class="data">
									<tr>
										<th colspan="3">Algemeen</th>
									</tr>
									<tr class="<?php echo $class; ?>">
										<td>Naam</td>
										<td><input type="text" name="name" maxLength="255" style="width: 350px;" value="<?php echo $scenario->name; ?>"></td>
										<td></td>
									</tr>
									<tr class="<?php echo $class; ?>">
										<td>Omschrijving</td>
										<td><textarea name="description" rows="12" style="width:350px;"><?php echo $scenario->description; ?></textarea></td>
										<td></td>
									</tr>
									<tr class="<?php echo $class; ?>">
										<td>Kaart positie</td>
										<td>
											X <input type="text" name="init_map_position_x" maxLenght="5" value="<?php echo $scenario->init_map_position_x; ?>">
											Y <input type="text" name="init_map_position_y" maxLenght="5" value="<?php echo $scenario->init_map_position_y; ?>">
										</td>
										<td></td>
									</tr>
									<tr class="<?php echo $class; ?>">
										<td>Kaart schaal</td>
										<td><input type="text" name="init_map_scale" maxLenght="5" value="<?php echo $scenario->init_map_scale; ?>"></td>
										<td></td>
									</tr>
								<table>
								
								<table class="data">
									<tr>
										<th colspan="3">Stationnen</th>
									</tr>
								<table>

<?php
	GenerateDemandForm($demand);
?>
								<button type="submit" name="FormAction" value="Save">Opslaan</button>
								</form>
							</td>
						</tr>

