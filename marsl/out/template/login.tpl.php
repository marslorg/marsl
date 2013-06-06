<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<h2>Login</h2>
<form method="post" action="login.php">
	<table>
		<?php if($wrongpw): ?>
		<tr class="standardfont"><td colspan="2">Der Benutzername oder das Passwort sind falsch.</td></tr>
		<?php endif; ?>
		<tr class="standardfont"><td>Benutzer: </td><td><input type="text" name="nickname" /></td></tr>
		<tr class="standardfont"><td>Passwort: </td><td><input type="password" name="password" /></td></tr>
		<tr class="standardfont"><td><button type="submit" name="action" value="send"> Absenden </button></td><td><a href="index.php?id=<?php echo $location; ?>&amp;action=forgot" class="standardfont">Passwort vergessen?</a></td></tr>
	</table>
</form>