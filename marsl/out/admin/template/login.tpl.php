<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Admin Login - <?php echo $title; ?></title>
		<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
		<link rel="stylesheet" href="styles/style.css" type="text/css" />
	</head>
	<body>
	<form method="post" action="../adminlogin.php">
		<fieldset class="loginform">
			<legend class="standardfont">Admin Login</legend>
			<table>
				<?php if($wrongpw): ?>
				<tr class="standardfont"><td colspan="2">Der Benutzername oder das Passwort sind falsch.</td></tr>
				<?php endif; ?>
				<tr class="standardfont"><td>Benutzer: </td><td><input type="text" name="nickname" /></td></tr>
				<tr class="standardfont"><td>Passwort: </td><td><input type="password" name="password" /></td></tr>
				<tr class="standardfont"><td><button type="submit" name="action" value="send"> Absenden </button></td><td><a href="index.php?var=forgot" class="standardfont">Passwort vergessen?</a></td></tr>
			</table>
		</fieldset>
	</form>
	</body>
</html>