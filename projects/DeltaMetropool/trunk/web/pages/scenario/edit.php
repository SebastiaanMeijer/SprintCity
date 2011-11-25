<?php
	require_once 'includes/master.inc.php';
	if(!$Auth->loggedIn()) redirect('../login.php');
	
	$class = new Loop('odd', 'even');
	
	$scenarioId = GetScenarioId();
	$scenario = null;
	$stations = null;
	$otherStations = null;
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
		global $scenarioId, $scenario, $stations, $otherStations, $demand, $types, $usedRoundInfos, $scenarioInUse;
		$scenarioInUse = Scenario::isScenarioInUse($scenarioId);
		if (isset($_POST['FormAction']))
		{		
			$stations = array();
			if (!is_null($scenarioId))
			{
				$scenario = new Scenario($scenarioId);
			}
			else
			{
				$scenario = new Scenario();
			}
			$scenario->load($_POST);
			if(isset($_POST['stations']))
			{
				foreach($_POST['stations'] as $code)
				{
					$stations[] = array_pop(Station::getStationByCode($code));
				}
			}
			else
			{
				$stations = Station::getStationsOfScenario($scenarioId);
			}
			$demand = GetEmptyDemandData();
			if (ValidateForm())
			{		
				//Form validated - Save all the things!
				$scenario->save();
				SaveDemand($scenario->id);
				ScenarioStation::setStationsForScenario($scenario->id, $stations);
				DisplayMessage('success', 'Success', array('De wijzigingen zijn opgeslagen.'));
			}
			$otherStations = Station::getStationsNotOfScenario($scenarioId);
		}
		else 
		{
			// get station data
			if (!is_null($scenarioId))
			{
				$scenario = new Scenario($scenarioId);
				$stations = Station::getStationsOfScenario($scenarioId);
				$otherStations = Station::getStationsNotOfScenario($scenarioId);
				$demand = GetDemandData($scenarioId);
			}
			else
			{
				$scenario = new Scenario();
				$stations = array();
				$otherStations = Station::getAllStations();
				$demand = GetEmptyDemandData();
			}
		}
	}
	
	function SaveDemand($scenarioId)
	{
		global $usedRoundInfos, $types, $scenarioInUse;
		if($scenarioInUse)
			return;
		
		$demandTable = Demand::getDemandForScenario($scenarioId);
		
		//Make new entries if they aren't in the database yet
		if(empty($demandTable))
		{
			foreach ($types as $type_key => $type)
			{
				foreach ($usedRoundInfos as $roundInfo_key => $roundInfo)
				{
					$new_demand = new Demand();
					$new_demand->scenario_id = $scenarioId;
					$new_demand->round_info_id = $roundInfo_key;
					$new_demand->type_id = $type_key;
					$demandTable[] = $new_demand;
				}
			}
		}

		//Set values
		foreach($demandTable as $demandrow)
		{
			$type_id = $demandrow->type_id;
			$round_info_id = $demandrow->round_info_id;
			$key = implode(",", array('type', $type_id, $round_info_id));
			if($demandrow->amount != $_POST[$key])
			{
				$demandrow->amount = $_POST[$key];
				$demandrow->save();
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
		global $types, $usedRoundInfos, $scenarioInUse;
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
				$key = implode(",", array('type', $type_key, $roundInfo_key));
				$demand[$index][3][] = $roundInfo_key;
				if(isset($_POST['FormAction']) && !$scenarioInUse)
					$demand[$index][4][] = $_POST[$key];
				else
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
		global $scenario, $stations, $demand, $types, $usedRoundInfos, $scenarioInUse;
		$errors = array();
		$warnings = array();
		
		if (is_null($scenario->name) || $scenario->name == "")
			$errors[] = "Er is geen scenario naam ingevuld.";
		else if(!Scenario::isScenarioNameUnique($scenario->name, $scenario->id))
			$errors[] = "De opgegeven scenario naam bestaat al.";
		
		if(is_null($scenario->description) || $scenario->description == "")
			$warnings[] = "Er is geen omschrijving ingevuld.";
		
		if(is_null($scenario->init_map_position_x) || is_null($scenario->init_map_position_y) ||  $scenario->init_map_position_x == "" || $scenario->init_map_position_y == "")
			$errors[] = "De initiele kaartpositie is niet (volledig) ingevoerd.";
		
		if(is_null($scenario->init_map_scale) || $scenario->init_map_scale == "")
			$errors[] = "Er is geen initiele kaart schaal ingevoerd.";
		
		if(!$scenarioInUse && empty($stations))
			$errors[] = "Er zijn geen stations toegevoegd aan het scenario";
		
	
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
	
	function GenerateDemandForm($demand)
	{
		global $class, $usedRoundInfos, $scenarioInUse;
		$inputAppend = "";
		if($scenarioInUse)
		{
			$inputAppend = "DISABLED";
		}
		
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
				echo "\t\t\t\t\t\t\t\t\t\t" . '<td><input name="type,' . $type[0] . ',' . $type[3][$i] . '" size="8" value="' . $type[4][$i] . '" '. $inputAppend . '></td>' . "\n";
			echo "\t\t\t\t\t\t\t\t\t" . '</tr>' . "\n";
		}
		echo "\t\t\t\t\t\t\t\t" . '</table>' . "\n";
	}
?>
						<tr>
							<td>
								<form id="scenarioform" name="scenarioform" onsubmit="formatStations()" method="POST">
								<table class="data">
									<tr>
										<th colspan="2">Algemeen</th>
									</tr>
									<tr class="<?php echo $class; ?>">
										<td>Naam</td>
										<td><input type="text" name="name" maxLength="255" style="width: 350px;" value="<?php echo $scenario->name; ?>"></td>
									</tr>
									<tr class="<?php echo $class; ?>">
										<td>Omschrijving</td>
										<td><textarea name="description" rows="12" style="width:350px;"><?php echo $scenario->description; ?></textarea></td>
									</tr>
									<tr class="<?php echo $class; ?>">
										<td>Kaart positie</td>
										<td>
											X <input type="text" name="init_map_position_x" maxLenght="5" value="<?php echo $scenario->init_map_position_x; ?>">
											Y <input type="text" name="init_map_position_y" maxLenght="5" value="<?php echo $scenario->init_map_position_y; ?>">
										</td>
									</tr>
									<tr class="<?php echo $class; ?>">
										<td>Kaart schaal</td>
										<td><input type="text" name="init_map_scale" maxLenght="5" value="<?php echo $scenario->init_map_scale; ?>"></td>
									</tr>
								</table>
								
								<script>
									$(function() {
										$( "#stationlist" ).sortable();
										$( "#stationlist" ).disableSelection();
									});
									
									function removeStation(element)
									{
										var select = document.getElementById("newstation");
										var list = document.getElementById("stationlist");
										var newoption = document.createElement('option');
										newoption.value = element.id;
										newoption.innerHTML = element.innerHTML;
										select.add(newoption);
										list.removeChild(element);
									}
									
									function addStation(selectedIndex)
									{
										var select = document.getElementById("newstation");
										var list = document.getElementById("stationlist");
										var code = select.options[select.selectedIndex].value;
										var name = select.options[select.selectedIndex].text;
										var newelement = document.createElement('li');
										newelement.innerHTML = name;
										newelement.id = code;
										newelement.setAttribute('ondblclick', "removeStation(this)");
										select.remove(select.selectedIndex);
										list.insertBefore(newelement, list.lastChild);
									}
									
									//Make sure the stations are returned in the correct order.
									function formatStations()
									{
										var stationlist = document.getElementById("stationlist");
										var scenarioform = document.getElementById("scenarioform");
										for(var i = 0; i < stationlist.children.length; i++)
										{
											var inputfield = document.createElement('input');
											inputfield.type = "hidden";
											inputfield.name = "stations[]";
											inputfield.value = stationlist.children[i].id;
											scenarioform.appendChild(inputfield);
										}
										document.scenarioform.submit();
									}
								</script>

								<table class="data">
									<tr>
										<th>Stations</th>
									</tr>
									<tr>
										<td>
											<?php
												if($scenarioInUse)
												{
													echo "Dit scenario is al ingezet voor een spel.<br>U kunt de stations en marktvraag niet meer wijzigen.";
												}
												else
												{
													echo "Sleep de stations om de volgorde te wijzigen. <br> Dubbelklik op een station om te verwijderen.";
												}
											?>
										</td>
									</tr>
									<tr>
										<td>
											<?php
												if($scenarioInUse)
												{
													echo "<ul class=\"data\">";
													foreach($stations as $station)
													{
														$variant = '';
														if (!is_null($station->variant))
															$variant = '[' . $station->variant . ']';
														echo "<li id=".$station->code." name=".$station->code . ">" . $station->name . $variant . "</li>";
													}
												}
												else
												{
													echo "<ul class=\"data\" id='stationlist' >";
													foreach($stations as $station)
													{
														$variant = '';
														if (!is_null($station->variant))
															$variant = '[' . $station->variant . ']';
														echo "<li id=".$station->code." name=".$station->code." ondblclick=\"removeStation(this)\">" . $station->name . $variant . "</li>";
													}
												}
												?>
											</ul>
										</td>
									</tr>
									<tr>
										<td>
											<select id="newstation">
												<?php
													foreach($otherStations as $station)
													{
														$variant = '';
														if (!is_null($station->variant))
															$variant = '[' . $station->variant . ']';
														echo "<option value=" . $station->code . ">" . $station->name . $variant . "</option>";
													}
												?>
											</select>
											<?php
												if(!$scenarioInUse)
												{
													echo "<button  type=\"button\" onclick=\"addStation(newstation.selectedIndex)\">Voeg toe</button>";
												}
											?>
										</td>
								</table>

								<?php
									GenerateDemandForm($demand);
								?>
								<button type="submit" name="FormAction" value="Save">Opslaan</button>
								</form>
							</td>
						</tr>

