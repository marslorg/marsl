<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
if (!$passwordProof): ?>
<span class="content">Leider stimmen die Passw&ouml;rter nicht überein.</span><br /><br />
<?php endif; 
if (!$emailProof): ?>
<span class="content">Die E-Mail-Adresse ist ung&uuml;ltig oder schon vergeben.</span><br /><br />
<?php endif;
if (!$registered&&isset($_POST['action'])): ?>
<span class="content">Der Benutzername existiert schon oder ist k&uuml;rzer als vier Buchstaben.</span><br /><br />
<?php endif; 
if ($registered): ?>
<span class="content">Der Benutzer wurde erfolgreich registriert.</span><br /><br />
<?php endif; ?>
<fieldset class="registerform">
	<form method="post" action="index.php?var=register">
		<table class="registertable">
			<tr class="standardfont">
				<td>Benutzername: </td>
				<td><input type="text" name="nickname" /></td>
			</tr>
			<tr class="standardfont">
				<td>Passwort: </td>
				<td><input type="password" name="password" /></td>
			</tr>
			<tr>
				<td>Passwort wiederholen: </td>
				<td><input type="password" name="password2" /></td>
			</tr>
			<tr class="standardfont">
				<td>E-Mail: </td>
				<td><input type="text" name="email" /></td>
			</tr>
			<tr class="standardfont">
				<td>Rolle: </td>
				<td>
					<select name="role">
						<option></option>
						<?php foreach($roles as $roleID): ?>
						<option value="<?php echo $roleID['role']; ?>">
							<?php echo $roleID['name']; ?>
						</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr class="standardfont">
				<td colspan="2" class="center">
					<input type="hidden" name="authTime" value="<?php echo $authTime; ?>" />
					<input type="hidden" name="authToken" value="<?php echo $authToken; ?>" />
					<button name="action" value="register"> Benutzer registrieren </button>
				</td>
			</tr>
		</table>
	</form>
</fieldset>