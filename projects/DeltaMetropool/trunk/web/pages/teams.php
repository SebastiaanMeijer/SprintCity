<?php
// TODO: Add admin check
?>

<h2>Teams</h2>
<div class="area">
	<h2>Nieuw team</h2>
	<p>
		<form name="Nieuw Team" action="pages/submit_form.php" method="POST" enctype="application/x-www-form-urlencoded">
			<table>
				<tr>
					<td>
						Naam
					</td>
					<td>
						<input type="text" name="Name" maxlength="255">
					</td>
				</tr>
				<tr>
					<td>
						Opmerkingen
					</td>
					<td>
						<textarea name="notes" rows="6"></textarea>
					</td>
				</tr>
				<tr>
					<td>
						Computer
					</td>
					<td>
						<input type="checkbox" name="cpu">
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<button type="submit" name="Action" value="new_team">Toevoegen</button>
					</td>
				<tr>
			</table>
		</form>
	</p>
</div>
<div class="area">
	<h2>Teams</h2>
	Existing Teams list
</div>