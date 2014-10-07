<?php
	require_once 'includes/master.inc.php';
	if(!$Auth->loggedIn()) redirect('../login.php');
?>
						<tr>
							<td>
								<form action="./admin.php?view=traintable&intent=submit_xls" enctype="multipart/form-data" method="POST">
									<input type="hidden" name="action" value="import">
								<table class="data">
									<tr class="<?php echo $class; ?>">
										<td>Excel file time table</td>
										<td><input name="trainTableFileName" type="file"></td>
									</tr>
								</table>
									<input type="submit" name="FormAction" value="Laad gegevens">
								</form>
							</td>
						</tr>
						


