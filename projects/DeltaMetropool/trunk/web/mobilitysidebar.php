<?php
	/* ambitions */
	$motivation = $_POST['motivation'];
	$ambitionBoxes = $_POST['ambitionCheckbox'];
	
	require_once('includes/master.inc.php');
	require_once 'mobilityheader.php';

	if (ClientSession::hasSession(session_id()))
	{
		//Send POST-information to database here
				
		$db = Database::getDatabase();
		$query = NULL;
		
		if(isset($ambitionBoxes))
		{
			// $query = "";
			// $args = array();
			// $db->query($query, $args);
		}
		
		if(isset($motivation))
		{	
			// $query = "
				// UPDATE teaminstance
				// SET value_description = " . $motivation . "
				// WHERE teaminstance.team_id = team.id
					// AND team.name = NS;
						// ";
			// $args = array();
			// $db->query($query, $args);
		}
	}
	
?>
				
<p class="ovTitle">Openbaar Vervoer</p>
<div id="nslogo"></div>
<div class="stationText">
	<div class="sidebarWindow">
		<?php
		
		if(!isset($motivation))
		{
		?>
		<form class="form" action="mobilitysidebar.php" method="post">
			<table class="ambitions">
			<caption>Ambities</caption>
			<?php
				$ambitionCount = 5;
				for($i = 0; $i < $ambitionCount; $i++)
				{
					?>
					<tr>
					<td class="checkbox"><input type="checkbox" name="ambitionCheckbox[]" value= <?php echo $i; ?> onClick="checkMax()"></td>
					<td class="leftAlign">Ambitie nummertje <?php echo $i; ?></td><br />
					<?php
				}
			?>
			</table>
			<h1>Motivatie</h1>
				<p>
					<textarea class="textfield" type="text" name="motivation">[ Plaats hier je motivatie voor de geselecteerde ambities! ]</textarea>
				</p>
			<p class="inputbutton"><input type="submit" value="Ambities vastleggen" onClick="showConfirm()"><br /></p>
			</form>
		<?php
		}
		else // what comes after sending:
		{
			if (isset($ambitionBoxes))
			{
				echo("<p class=\"ovTitle\">Ambities</p>
					<br /><b>Je hebt de volgende ambities geselecteerd:</b> <br />");
				
				foreach($ambitionBoxes as $ambitionBox)
				{
					echo $ambitionBox . "<br />";
				}
			}
			else
			{
				echo "<br /> Je hebt geen ambities! <br />";
			}
			
			echo("<br /><b>Met als motivatie:</b> <br />\"");
			echo $motivation . "\"<br /><br />";
		}
		?>
	</div>
	<?php
	if(isset($motivation))
	{
		?>
		<div class="sidebarWindow">
			<p class="ovTitle">Netwerkwaarden</p>
			<form class="form" name="input" action="mobilitysidebar.php" method="post">
				<table>
					<tr>
						<th>Station</th>
						<th>Netwerkwaarde</th>

					<?php
						$stationCount = 5;
						for($i = 0; $i < $stationCount; $i++)
						{
							?>
							<tr>
							<td>Station <?php echo $i; ?></td>
							<td><input class="input" type="text" name="povn1" value="old povn"/></td>
							</tr>
							<?php
						}
					?>		
				</table>
				<h1>Motivatie</h1>
				<p>
					<textarea class="textfield" type="text" name="motivation">[ Plaats hier je motivatie voor de aangepaste netwerkwaarden! ]</textarea>
				</p>
				<p><input type="submit" value="Doorvoeren"></p>
			</form>
		</div>
		<?php
	}
	?>
</div>