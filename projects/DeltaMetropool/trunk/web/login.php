<?PHP
require 'includes/master.inc.php';

// Kick out user if already logged in.
if($Auth->loggedIn()) redirect('admin.php');

// Try to log in...
if(!empty($_POST['username']))
{
	$Auth->login($_POST['username'], $_POST['password']);
	if($Auth->loggedIn())
		redirect('admin.php');
	else
		$Error->add('username', "Ongeldige gebruikersnaam of wachtwoord.");
}

// Clean the submitted username before redisplaying it.
$username = isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta name="keywords" content="">
<meta name="description" content="">
<title>Sprintstad Server</title>
<link rel="stylesheet" type="text/css" href="style/reset-fonts-grids.css">
<link rel="stylesheet" type="text/css" href="style/base.css">
<link rel="stylesheet" type="text/css" href="style/style.css">
</head>
<body>
	<div class="login">
		<h2>Login</h2>
		<form action="login.php" method="POST">
		<table>
			<tr>
				<td>Gebruikersnaam</td>
				<td><input type="text" name="username" value="<?PHP echo $username;?>" id="username" /></td>
			</tr>
			<tr>
				<td>Wachtwoord</td>
				<td>
					<input type="password" name="password" value="" id="password" />
				</td>
			</tr>
			<tr>
				<td colspan="2"><input type="submit" name="btnlogin" value="Login" id="btnlogin" /></td>
			</tr>
		</table>
		</form>
		<a href="index.php" style="display:block; margin:0 0 5px 5px; width: 95%; text-align: right;">terug</a>
	</div>
</body>
</html>