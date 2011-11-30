<?php
	require_once 'includes/master.inc.php';
	require_once 'pages/station/form.php';
	
	if(!$Auth->loggedIn()) redirect('../login.php');
	
	$class = new Loop('odd', 'even');
	
	$disabled = "";
	$stationId = GetStationId();
	$station = null;
	$rounds = null;
	$usedRoundInfos = Round::getUsedRoundInfos();
	InitData();
	$submitAction = GetSubmitAction();
	
	
	function GetStationId()
	{
		$stationId = null;
		if (!isset($_GET['station']) && isset($_POST['code']) && $_POST['code'] != "")
		{
			$station = Station::getStationByCode($_POST['code']);
			if (sizeof($station) > 0)
			{
				$keys = array_keys($station);
				$stationId = $station[$keys[0]]->id;
			}
		}
		else if (isset($_GET['station']))
		{
			$stationId = $_GET['station'];
		}
		return $stationId;
	}
	
	function InitData()
	{
		global $disabled, $stationId, $station, $rounds, $usedRoundInfos;
		$inUse = Station::isStationInUse($stationId);
		FormInit($inUse);
		if($inUse)
			$disabled = "DISABLED";
			
		if (isset($_POST['FormAction']))
		{
			if (!is_null($stationId))
			{
				$station = new Station($stationId);
				$rounds = Round::getRoundsByStation($stationId);
			}
			else
			{
				$station = new Station();
				foreach ($usedRoundInfos as $key => $value)
				{
					$round = new Round();
					$round->round_info_id = $key;
					$rounds[] = $round;
				}
			}
			$station->load($_POST);
			if(!$inUse)
			{
				foreach ($rounds as $key => $value)
				{
					$rounds[$key]->new_transform_area = $_POST['new_transform_area,' . $value->round_info_id];
					$rounds[$key]->POVN = $_POST['POVN,' . $value->round_info_id];
					$rounds[$key]->PWN = $_POST['PWN,' . $value->round_info_id];
				}
			}
			if (ValidateForm())
			{
				$stationId = $station->save();
				foreach ($rounds as $round)
				{
					$round->station_id = $stationId;
					$round->save();
				}
				DisplayMessage('success', 'Success', array('De wijzigingen zijn opgeslagen.'));
			}
		}
		else 
		{
			// get station data
			if (!is_null($stationId))
			{
				$station = Station::getStationById($stationId);
				$station = $station[$stationId];
			}
			else
			{
				$station = Station::getDefaultStation();
			}
			
			// get round data
			if (!is_null($stationId))
			{
				$rounds = Round::getRoundsByStation($stationId);
			}
		}
	}
	
	function GetSubmitAction()
	{
		global $stationId, $submitAction;
		$submitAction = "admin.php?view=station";
		if (isset($_GET['action']))
			$submitAction .= "&action=" . $_GET['action'];
		if (!is_null($stationId))
			$submitAction .= "&station=" . $stationId;
	}
	
	function ValidateForm()
	{
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

	function GenerateForm($form_fields, $station)
	{
		global $class;
		foreach ($form_fields as $key => $value)
		{
			echo "\t\t\t\t\t\t\t\t\t" . '<tr class="' . $class . '">' . "\n";
			echo "\t\t\t\t\t\t\t\t\t\t" . '<td>' . $value['label'] . '</td>' . "\n";
			echo "\t\t\t\t\t\t\t\t\t\t" . '<td>' . "\n";
			switch($value['tag'])
			{
				case "input":
					echo "\t\t\t\t\t\t\t\t\t\t\t" . '<input type="' . $value['type'] . '" name="' . $value['name'] . '" maxlength="' . $value['maxlength'] . '" value="' . $station->{$value['name']} . '" '. $value['disabled'] . '>' . "\n";
				break;
				case "textarea":
					echo "\t\t\t\t\t\t\t\t\t\t\t" . '<textarea name="' . $value['name'] . '" rows="' . $value['rows'] . '" style="width:350px;">' . $station->{$value['name']} . '</textarea>' . "\n";
				break;
			}
			echo "\t\t\t\t\t\t\t\t\t\t" . '</td>' . "\n";
			echo "\t\t\t\t\t\t\t\t\t\t" . '<td>' . $value['description'] . '</td>' . "\n";
			echo "\t\t\t\t\t\t\t\t\t" . '</tr>' . "\n";
		}
	}
	
	function GenerateEmptyRoundsForm()
	{
		global $class, $usedRoundInfos, $disabled;
?>
								<table class="data">
									<tr>
										<th colspan="<?php echo sizeof($usedRoundInfos) + 1; ?>">Rondes</th>
									</tr>
									<tr class="<?php echo $class;?>">
										<td></td>
		<?php
		foreach ($usedRoundInfos as $key => $value)
			echo "\t\t\t\t\t\t\t\t\t\t" . '<td><center>20' . $value->number . '</center></td>' . "\n";
		?>
									</tr>
									<tr class="<?php echo $class;?>">
										<td>Beschikbaar transformatiegebied</td>
		<?php
		foreach ($usedRoundInfos as $key => $value)
			echo "\t\t\t\t\t\t\t\t\t\t" . '<td><input name="new_transform_area,' . $value->id . '" size="8" value="0" '.$disabled.'></td>' . "\n";
		?>
									</tr>
									<tr class="<?php echo $class;?>">
										<td>POVN</td>
		<?php
		foreach ($usedRoundInfos as $key => $value)
			echo "\t\t\t\t\t\t\t\t\t\t" . '<td><input name="POVN,' . $value->id . '" size="8" value="0" '.$disabled.'></td>' . "\n";
		?>
									</tr>
									<tr class="<?php echo $class;?>">
										<td>PWN</td>
		<?php
		foreach ($usedRoundInfos as $key => $value)
			echo "\t\t\t\t\t\t\t\t\t\t" . '<td><input name="PWN,' . $value->id . '" size="8" value="0" '.$disabled.'></td>' . "\n";
		?>
									</tr>
								</table>
<?php
	}
	
	function GenerateRoundsForm($rounds)
	{
		global $class, $usedRoundInfos, $disabled;
		echo $disabled
?>
								<table class="data">
									<tr>
										<th colspan="<?php echo sizeof($rounds) + 1; ?>">Rondes</th>
									</tr>
									<tr class="<?php echo $class;?>">
										<td></td>
		<?php
		foreach ($rounds as $key => $value)
			echo "\t\t\t\t\t\t\t\t\t\t" . '<td><center>20' . $usedRoundInfos[$value->round_info_id]->number . '</center></td>' . "\n";
		?>
									</tr>
									<tr class="<?php echo $class;?>">
										<td>Beschikbaar transformatiegebied</td>
		<?php
		foreach ($rounds as $key => $value)
			echo "\t\t\t\t\t\t\t\t\t\t" . '<td><input name="new_transform_area,' . $value->round_info_id . '" value="' . $value->new_transform_area . '" size="8" '.$disabled.'></td>' . "\n";
		?>
									</tr>
									<tr class="<?php echo $class;?>">
										<td>POVN</td>
		<?php
		foreach ($rounds as $key => $value)
			echo "\t\t\t\t\t\t\t\t\t\t" . '<td><input name="POVN,' . $value->round_info_id . '" value="' . $value->POVN . '" size="8" '.$disabled.'></td>' . "\n";
		?>
									</tr>
									<tr class="<?php echo $class;?>">
										<td>PWN</td>
		<?php
		foreach ($rounds as $key => $value)
			echo "\t\t\t\t\t\t\t\t\t\t" . '<td><input name="PWN,' . $value->round_info_id . '" value="' . $value->PWN . '" size="8" '.$disabled.'></td>' . "\n";
		?>
									</tr>
								</table>
<?php
	}
?>
						<tr>
							
								<?php
									if($disabled != "")
									{
										$games = Station::getGamesByStation($station->id);
										
										echo "<td>Dit station is momenteel in gebruik in een spel. Verwijder de volgende spel(len) om het station te mogen wijzigen:<br><br>";
										echo "<ul>";
										while ($row = mysql_fetch_array($games))
										{
											echo "<li>";
											echo $row["id"]." ".$row["name"]."<br>";
											echo "</li>";
										}
										echo "</ul>";
										echo "</td>";
									}
								?>
									
								<form action="<?php echo $submitAction ?>" method="POST">
								<table class="data">
									<tr>
										<th colspan="3">Algemeen</th>
									</tr>
<?php
	GenerateForm($form_fields_stats, $station);
	$image = "./images/stations/" . $station->code . ".png";
?>
									<tr class="<?php echo $class; ?>">
										<td>Achtergrond kaart</td>
										<td><a href="<?php echo $image; ?>" target="_blank"><img width=350 src="<?php echo $image; ?>"></a></td>
										<td><a href="<?php echo $image; ?>" target="_blank"><?php echo $image; ?></a></td>
									</tr>
									<tr>
										<th colspan="3">Profiel</th>
									</tr>
<?php GenerateForm($form_fields_profile, $station)?>
									<tr>
										<th colspan="3">Stationsgebied</th>
									</tr>
<?php GenerateForm($form_fields_area, $station)?>
									<tr>
										<th colspan="3">Transformatiegebied</th>
									</tr>
<?php GenerateForm($form_fields_transform, $station)?>
								</table>
								
<?php
if (is_null($rounds))
	GenerateEmptyRoundsForm();
else
	GenerateRoundsForm($rounds);
?>
									<button type="submit" name="FormAction" value="Save">Opslaan</button>
								</form>
							</td>
						</tr>

