<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Passwort wiederherstellen - <?php echo $title; ?></title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" href="styles/style.css" type="text/css" />
	</head>
	<body>
		<form method="post" <?php if (isset($uid)&&isset($time)&&isset($authParameter)): ?>action="index.php?var=forgot&amp;action=recover&amp;subaction=set&amp;uid=<?php echo $uid; ?>&amp;time=<?php echo $time; ?>&amp;auth=<?php echo $authParameter; ?>"<?php endif; ?>>
			<fieldset class="recoverform">
				<legend class="standardfont">Passwort wiederherstellen</legend>
				<?php if (!$recover): ?>
				Der Link ist ung&uuml;ltig. Bitte lass dir die E-Mail mit dem Passwortlink nochmal zuschicken. <a href="index.php?var=forgot">Hier geht es weiter.</a>
				<?php endif; ?>
				<?php if(!$init&&$success): ?>
				Dein Passwort wurde neu gesetzt. <a href="index.php">Hier</a> geht es weiter.
				<?php endif; ?>
				<?php if(!$init&&!$success): ?>
				Die beiden eingegebenen Passw&ouml;rter stimmen nicht &uuml;berein.
				<?php endif; ?>
				<?php if ($recover&&!$success): ?>
				<table>
					<tr><td>Passwort: </td><td><input type="password" name="password" /></td></tr>
					<tr><td>Passwort wiederholen: </td><td><input type="password" name="password2" /></td></tr>
					<tr><td colspan="2"><button type="submit" name="action" value="send"> Absenden </button></td></tr>
				<?php endif; ?>
			</fieldset>
		</form>
	</body>
</html>