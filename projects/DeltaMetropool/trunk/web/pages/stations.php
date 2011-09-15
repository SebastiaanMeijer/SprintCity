<?php
	require_once 'includes/master.inc.php';
	if(!$Auth->loggedIn()) redirect('../login.php');
?>
				<div class="area">
					<h2>Stations</h2>
					<table>
<?php 
if (isset($_GET['action']))
{
	switch($_GET['action'])
	{
		case "edit":
			include 'pages/stations/edit.php';
			break;
		default:
			include 'pages/stations/list.php';
			break;
	}
}
else
{
	include 'pages/stations/list.php';
}
?>
					</table>
				</div>