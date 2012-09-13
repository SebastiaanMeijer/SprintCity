<?php
	require_once 'includes/master.inc.php';
	if(!$Auth->loggedIn()) redirect('../login.php');
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
						


