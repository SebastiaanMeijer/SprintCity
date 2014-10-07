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
					<h2>Time table</h2>
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
	if (isset($_REQUEST['intent']) && $_REQUEST['intent'] == "submit_xls")
	{
?>
						<tr class="error">
							<td>
								File not found. The selected file is probably larger than the limite of <?php echo getUploadLimit(); ?>MB.
								Change the following variables in php.ini: post_max_size=20M, upload_max_filesize=20M.
							</td>
						</tr>
<?php
	}
	include 'pages/traintable/list.php';
}

function getUploadLimit()
{
	$max_upload = (int)(ini_get('upload_max_filesize'));
	$max_post = (int)(ini_get('post_max_size'));
	$memory_limit = (int)(ini_get('memory_limit'));
	return min($max_upload, $max_post, $memory_limit);
}
?>
					</table>
				</div>