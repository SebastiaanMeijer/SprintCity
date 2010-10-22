<?php
	require_once 'mobilityheader.php';
?>
				
<p class="ovTitle">Openbaar Vervoer</p>
<div id="nslogo"></div>
<div class="stationText">
	<form class="form" name="ambitions" action="" method="post">

	<table>
	<caption>Ambities</caption>
	<?php
		$ambitionCount = 5;
		for($i = 0; $i < $ambitionCount; $i++)
		{
			?>
			<tr>
			<td class="checkbox"><input type="checkbox" name="checkbox" onClick="checkMax()"></td>
			<td class="leftAlign">Ambitie</td><br />
			<?php
		}
	?>		
	</table>
	<h1>Motivatie</h1>
		<p>
			<textarea class="textfield" type="text" name="motivation">Plaats hier je motivatie voor de geselecteerde ambities!</textarea>
		</p>
	<p class="inputbutton"><input type="submit" value="Ambities vastleggen"><br /></p>
	</form>

	<form class="form" name="input" action="mobility.php" method="post">
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
				<td>Station1</td>
				<td><input class="input" type="text" name="povn1" value="old povn"/></td>
				</tr>
				<?php
				}
			?>		
		</table>
		<h1>Motivatie</h1>
		<p>
			<textarea class="textfield" type="text" name="motivation">Plaats hier je motivatie voor de aangepaste netwerkwaarden!</textarea>
		</p>
		<p><input type="submit" value="Doorvoeren"></p>
	</form>
</div>