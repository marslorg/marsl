<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
<br /><br />
<?php if(!$updateNickname): ?>
<span class="content">Der Benutzername existiert schon oder ist k&uuml;rzer als vier Buchstaben.</span><br /><br />
<?php endif;
//if(!$updateMail): ?>
<!-- <span class="content">Die E-Mail-Adresse ist ung&uuml;ltig oder schon vergeben.</span><br /><br />-->
<?php //endif;
if(!$updateAcronym): ?>
<span class="content">Das K&uuml;rzel ist bereits vergeben.</span><br /><br />
<?php endif;
if(!$samePasswords): ?>
<span class="content">Leider stimmen die Passw&ouml;rter nicht überein.</span><br /><br />
<?php endif;
if(!$rightPassword): ?>
<span class="content">Leider ist dein altes Passwort falsch.</span><br /><br />
<?php endif;
if(!$safePassword): ?>
<span class="content">Dein Passwort wurde als unsicher eingestuft.</span><br /><br />
<?php endif; ?>
<fieldset class="userdataform">
	<form method="post" action="index.php?var=module&amp;module=userdata&amp;action=details&amp;user=<?php echo $userID; ?>">
		<table class="userdatatable">
			<tr class="standardfont">
				<td>Benutzername: </td>
				<td><input type="text" name="nickname" value="<?php echo $nickname; ?>" /></td>
			</tr>
			<tr class="standardfont">
				<td>Vorname: </td>
				<td><input type="text" name="prename" value="<?php echo $prename; ?>" /></td>
			</tr>
			<tr class="standardfont">
				<td>Nachname: </td>
				<td><input type="text" name="name" value="<?php echo $name; ?>" /></td>
			</tr>
			<!-- <tr class="standardfont">
				<td>E-Mail: </td>
				<td><input type="text" name="email" value="<?php //echo $email; ?>" /></td>
			</tr>-->
			<?php if($isMaster): ?>
			<tr class="standardfont">
				<td>K&uuml;rzel: </td>
				<td><input type="text" name="acronym" value="<?php echo $acronym; ?>" /></td>
			</tr>
			<tr class="standardfont">
				<td>Rolle: </td>
				<td>
					<select name="role">
						<?php foreach($roles as $roleID): ?>
						<option value="<?php echo $roleID['role']; ?>" <?php if($roleID['role']==$userRole): ?>selected<?php endif; ?>>
							<?php echo $roleID['name']; ?>
						</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<?php endif; ?>
			<tr class="standardfont">
				<td colspan="2">
					<input type="hidden" name="authTime" value="<?php echo $authTime; ?>" />
					<input type="hidden" name="authToken" value="<?php echo $authToken; ?>" />
					<button type="submit" name="change"> Daten &auml;ndern </button>
					<button type="reset"> Zur&uuml;cksetzen </button>
				</td>
			</tr>
		</table>
	</form>
	<?php if($userID==$ownID): ?>
	<form method="post" action="index.php?var=module&amp;module=userdata&amp;action=details&amp;user=<?php echo $userID; ?>">
		<table class="userdatatable">
			<tr class="standardfont">
				<td>Altes Passwort: </td>
				<td><input type="password" name="oldPassword" /></td>
			</tr>
			<tr class="standardfont">
				<td>Neues Passwort: </td>
				<td><input type="password" name="newPassword" /></td>
			</tr>
			<tr class="standardfont">
				<td>Passwort wiederholen: </td>
				<td><input type="password" name="proofPassword" /></td>
			</tr>
			<tr class="standardfont">
				<td colspan="2">
					<input type="hidden" name="authTime" value="<?php echo $authTime; ?>" />
					<input type="hidden" name="authToken" value="<?php echo $authToken; ?>" />
					<button type="submit" name="passwordChange"> Passwort &auml;ndern </button>
					<button type="reset"> Zur&uuml;cksetzen </button>
				</td>
			</tr>
		</table>
	</form>
	<?php endif; ?>
	<form method="post" action="index.php?var=module&amp;module=userdata&amp;action=details&amp;user=<?php echo $userID; ?>">
		<table class="userdatatable">
			<tr>
				<td>E-Mail-Adresse eintragen: </td>
				<td>
					<input type="text" name="email" />
					<input type="hidden" name="authTime" value="<?php echo $authTime; ?>" />
					<input type="hidden" name="authToken" value="<?php echo $authToken; ?>" />
					<button type="submit" name="entermail"> Absenden </button>
				</td>
			</tr>
		</table>
	</form>
	<table class="userdatatable">
		<?php foreach($emails as $email): ?>
		<tr <?php if ($email['confirmed']): ?>class="success"<?php endif; if (!$email['confirmed']): ?>class="caution"<?php endif; ?>>
			<td><?php echo $email['email']; ?></td>
			<td>
				<?php if ((!$email['primary'])&&($email['confirmed'])): ?>
				<a href="index.php?var=module&amp;module=userdata&amp;action=details&amp;user=<?php echo $userID; ?>&amp;primemail=<?php echo urlencode($email['email']); ?>&amp;time=<?php echo $authTime; ?>&amp;token=<?php echo $authToken; ?>"><img src="../includes/graphics/ok22.png" alt="Als Hauptadresse festlegen" /></a>
				<?php endif; ?>
				<?php if (!$email['primary']): ?>
				<a href="index.php?var=module&amp;module=userdata&amp;action=details&amp;user=<?php echo $userID; ?>&amp;delmail=<?php echo urlencode($email['email']); ?>&amp;time=<?php echo $authTime; ?>&amp;token=<?php echo $authToken; ?>"><img src="../includes/graphics/delete22.png" alt="L&ouml;schen" /></a>
				<?php endif; ?>
				<?php if (!$email['confirmed']): ?>
				<a href="index.php?var=module&amp;module=userdata&amp;action=details&amp;user=<?php echo $userID; ?>&amp;confmail=<?php echo urlencode($email['email']); ?>&amp;time=<?php echo $authTime; ?>&amp;token=<?php echo $authToken; ?>"><img src="../includes/graphics/mail22.png" alt="Best&auml;tigungsmail erneut senden" /></a>
				<?php endif; ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</table>
</fieldset>