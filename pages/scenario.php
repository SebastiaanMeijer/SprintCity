<?php
	require_once 'includes/master.inc.php';
	if(!$Auth->loggedIn()) redirect('../login.php');
?>
				<div class="area">
					<h2>Scenario</h2>
					<table>
<?php 
if (isset($_GET['action']))
{
	switch($_GET['action'])
	{
		case "edit":
			include 'pages/scenario/edit.php';
			break;
		default:
			include 'pages/scenario/list.php';
			break;
	}
}
else
{
	include 'pages/scenario/list.php';
}
?>
					</table>
				</div>