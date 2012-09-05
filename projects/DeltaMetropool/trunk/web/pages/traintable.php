<?php
	require_once 'includes/master.inc.php';
	if(!$Auth->loggedIn()) redirect('../login.php');
	//echo "request: <br>";
	//var_dump($_REQUEST);
	//echo "<br>";
	//echo "files: <br>";
	//var_dump($_FILES);
	//echo "<br>";
?>
				<div class="area">
					<h2>Dienstregeling</h2>
					<table>
<?php 
if (isset($_REQUEST['action']))
{
	switch($_REQUEST['action'])
	{
		case "edit":
			include 'pages/traintable/edit.php';
			break;
		case "import":
			include 'pages/traintable/importer.php';
			break;
		default:
			include 'pages/traintable/list.php';
			break;
	}
}
else
{
	include 'pages/traintable/list.php';
}
?>
					</table>
				</div>