<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<?php if ($success): ?><div class="success">Du hast dich erfolgreich registriert. Um deine E-Mail-Adresse freizuschalten, haben wir dir eine E-Mail geschickt, die du best&auml;tigen musst. <a href="index.php">Hier geht es zur&uuml;ck!</a></div><?php endif; ?>
<?php if (!$success): ?>
<h2>Registrierung:</h2>
<?php if ($captcha): ?><div class="caution">Leider hast du das CAPTCHA nicht richtig gel&ouml;st.</div><?php endif; ?>
<?php if ($mailFailure): ?><div class="caution">Leider ist deine E-Mail-Adresse nicht richtig.</div><?php endif; ?>
<?php if ($passwordFailure): ?><div class="caution">Leider stimmen die Passw&ouml;rter nicht &uuml;berein.</div><?php endif; ?>
<?php if ($nicknameFailure): ?><div class="caution">Leider ist der Benutzername schon vergeben.</div><?php endif; ?>
<form method="post" action="index.php?id=<?php echo $location; ?>">
	<table class="userdatatable">
		<tr>
			<td>Benutzername*: </td><td><div class="center"><input type="text" name="nickname" value="<?php echo $nickname; ?>" /></div></td>
		</tr>
		<tr>
			<td>Passwort*: </td><td><div class="center"><input type="password" name="password" /></div></td>
		</tr>
		<tr>
			<td>Passwort wdh.*: </td><td><div class="center"><input type="password" name="password2" /></div></td>
		</tr>
		<tr>
			<td>E-Mail*: </td><td><div class="center"><input type="text" name="mail" value="<?php echo $mail; ?>" /></div></td>
		</tr>
			<td>E-Mail wdh.*: </td><td><div class="center"><input type="text" name="mail2" value="<?php echo $mail2; ?>" /></div></td>
		</tr>
		<tr>
			<td colspan="2"><?php echo $recaptcha; ?></td>
		</tr>
		<tr>
			<td colspan="2">
				<div class="center">
					<button type="submit" name="action" value="send"> Registrieren </button>
					<button type="reset"> Zur&uuml;cksetzen </button>
				</div>
			</td>
		</tr>
	</table>
</form>
<?php endif; ?>