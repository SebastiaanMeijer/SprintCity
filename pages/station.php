<?php
	require_once 'includes/master.inc.php';
	if(!$Auth->loggedIn()) redirect('../login.php');
?>
				<div class="area">
					<h2>Station</h2>
					<table>
<?php 
if (isset($_GET['action']))
{
	switch($_GET['action'])
	{
		case "edit":
			include 'pages/station/edit.php';
			break;
		default:
			include 'pages/station/list.php';
			break;
	}
}
else
{
	include 'pages/station/list.php';
}
?>
					</table>
				</div>