<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Passwort vergessen? - <?php echo $title; ?></title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" href="styles/style.css" type="text/css" />
	</head>
	<body>
	<form method="post" action="../adminforgot.php">
		<fieldset class="forgotform1">
			<legend class="standardfont">Passwort vergessen?</legend>
			<table>
				<?php if(!$init&&$success&&$topic=="password"): ?>
				<tr class="standardfont"><td colspan="2">Dir wurde eine E-Mail zur Passwortwiederherstellung geschickt.</td></tr>
				<?php endif; ?>
				<?php if(!$init&&!$success&&$topic=="password"): ?>
				<tr class="standardfont"><td colspan="2">Leider ist uns dein Benutzername nicht bekannt.</td></tr>
				<?php endif; ?>
				<tr class="standardfont"><td>Benutzername: </td><td><input type="text" name="nickname" /></td></tr>
				<tr class="standardfont"><td colspan="2"><button type="submit" name="action" value="password"> Passwort anfordern </button></td></tr>
			</table>
		</fieldset>
	</form>
	<form method="post" action="../adminforgot.php">
		<fieldset class="forgotform2">
			<legend class="standardfont">Benutzername vergessen?</legend>
			<table>
				<?php if(!$init&&$success&&$topic=="nickname"): ?>
				<tr class="standardfont"><td colspan="2">Dir wurde eine E-Mail mit deinem Benutzernamen geschickt.</td></tr>
				<?php endif; ?>
				<?php if (!$init&&!$success&&$topic=="nickname"): ?>
				<tr class="standardfont"><td colspan="2">Leider ist uns deine E-Mail-Adresse nicht bekannt.</td></tr>
				<?php endif; ?>
				<tr class="standardfont"><td>E-Mail: </td><td><input type="text" name="mail" /></td></tr>
				<tr class="standardfont"><td colspan="2"><button type="submit" name="action" value="nickname"> Benutzername anfordern </button></td></tr>
			</table>
		</fieldset>
	</form>
	</body>
</html>