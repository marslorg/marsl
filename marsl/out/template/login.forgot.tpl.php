<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<h2>Passwort vergessen?</h2>
<form method="post" action="forgot.php">
	<table>
		<?php if(!$init&&$success&&$topic=="password"): ?>
		<tr class="standardfont"><td colspan="2">Dir wurde eine E-Mail zur Passwortwiederherstellung geschickt.</td></tr>
		<?php endif; ?>
		<?php if(!$init&&!$success&&$topic=="password"): ?>
		<tr class="standardfont"><td colspan="2">Leider ist uns dein Benutzername nicht bekannt.</td></tr>
		<?php endif; ?>
		<tr class="standardfont"><td>Benutzername: </td><td><input type="text" name="nickname" /></td></tr>
		<tr class="standardfont">
			<td colspan="2">
				<input type="hidden" name="location" value="<?php echo $location; ?>" />
				<button type="submit" name="action" value="password"> Passwort anfordern </button>
			</td>
		</tr>
	</table>
</form>
<h2>Benutzername vergessen?</h2>
<form method="post" action="forgot.php">
	<table>
		<?php if(!$init&&$success&&$topic=="nickname"): ?>
		<tr class="standardfont"><td colspan="2">Dir wurde eine E-Mail mit deinem Benutzernamen geschickt.</td></tr>
		<?php endif; ?>
		<?php if (!$init&&!$success&&$topic=="nickname"): ?>
		<tr class="standardfont"><td colspan="2">Leider ist uns deine E-Mail-Adresse nicht bekannt.</td></tr>
		<?php endif; ?>
		<tr class="standardfont"><td>E-Mail: </td><td><input type="text" name="mail" /></td></tr>
		<tr class="standardfont">
			<td colspan="2">
				<input type="hidden" name="location" value="<?php echo $location; ?>" />
				<button type="submit" name="action" value="nickname"> Benutzername anfordern </button>
			</td>
		</tr>
	</table>
</form>