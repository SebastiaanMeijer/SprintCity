<?php
	require_once './includes/Excel/reader.php';
	require_once 'includes/master.inc.php';
	if(!$Auth->loggedIn()) redirect('../login.php');
	
	$class = new Loop('odd', 'even');
	
	$trainTableId = GetTrainTableId();
	$trainTable = null;
	InitData();
	
	function GetTrainTableId()
	{
		$trainTableId = null;
		if (!isset($_GET['trainTable']) && isset($_POST['id']) && $_POST['id'] != "")
		{
			$trainTable = TrainTable::getTrainTableById($_POST['id']);
			if (sizeof($trainTable) > 0)
			{
				$keys = array_keys($trainTable);
				$trainTableId = $trainTable[$keys[0]]->id;
			}
		}
		else if (isset($_GET['trainTable']))
		{
			$trainTableId = $_GET['trainTable'];
		}
		return $trainTableId;
	}
	
	function InitData()
	{
		global $trainTableId, $trainTable;
		if (isset($_POST['FormAction']))
		{
		}
		else 
		{
			if (!is_null($trainTableId))
			{
			}
			else
			{
				//$trainTable = new TrainTable();
			}
		}
	}
	
	
	function GetSubmitAction()
	{
		global $scenarioId, $submitAction;
		$submitAction = "admin.php?view=traintable";
		if (isset($_GET['action']))
			$submitAction .= "&action=" . $_GET['action'];
		if (!is_null($scenarioId))
			$submitAction .= "&station=" . $scenarioId;
	}
	
	function ValidateForm()
	{
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
	
?>
						<tr>
							<td>
								<form action="./admin.php?view=traintable" enctype="multipart/form-data" method="POST">
									<input type="hidden" name="action" value="import">
								<table class="data">
									<tr class="<?php echo $class; ?>">
										<td>Excel bestand dienstregeling</td>
										<td><input name="trainTableFileName" type="file" size="20M"></td>
									</tr>
								</table>
									<input type="submit" name="FormAction" value="Laad gegevens">
								</form>
							</td>
						</tr>
						


