<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<h2>Daten &auml;ndern:</h2>
<form method="post" action="index.php?id=<?php echo $location; ?>" class="formTable">
	<table>
		<tr>
			<td>Vorname: </td>
			<td><div class="center"><input type="text" name="prename" value="<?php echo $prename; ?>" class="formprename" /></div></td>
		</tr>
		<tr>
			<td>Nachname: </td>
			<td><div class="center"><input type="text" name="name" value="<?php echo $name; ?>" class="formname" /></div></td>
		</tr>
		<tr>
			<td>Stra&szlig;e/Hausnummer: </td>
			<td>
				<div class="center">
					<input type="text" name="street" value="<?php echo $street; ?>" class="formstreet" />
					<input type="text" name="house" value="<?php echo $house; ?>" class="formhouse" />
				</div>
			</td>
		</tr>
		<tr>
			<td>PLZ/Stadt: </td>
			<td>
				<div class="center">
					<input type="text" name="zip" value="<?php echo $zip; ?>" class="formzip" />
					<input type="text" name="city" value="<?php echo $city; ?>" class="formcity" />
				</div>
			</td>
		</tr>
		<tr>
			<td>Geburtsdatum: </td>
			<td>
				<div class="center">
					<input type="text" name="day" value="<?php echo $day; ?>" class="formday" />
					<input type="text" name="month" value="<?php echo $month; ?>" class="formmonth" />
					<input type="text" name="year" value="<?php echo $year; ?>" class="formyear" />
				</div>
			</td>
		</tr>
		<tr>
			<td>Geschlecht: </td>
			<td>
				<div class="center">
					<select name="gender">
						<option value=""> </option>
						<option value="female" <?php if ($gender=="female"): ?>selected<?php endif; ?>>Weiblich</option>
						<option value="male" <?php if ($gender=="male"): ?>selected<?php endif; ?>>M&auml;nnlich</option>
					</select>
				</div>
			</td>
		</tr>
		<tr>
			<td></td>
			<td>
				<input type="hidden" name="job" value="<?php echo $job; ?>" />
				<input type="hidden" name="interests" value="<?php echo $interests; ?>" />
				<input type="hidden" name="info" value="<?php echo $info; ?>" />
				<input type="hidden" name="signature" value="<?php echo $signature; ?>" />
				<input type="hidden" name="authTime" value="<?php echo $authTime; ?>" />
				<input type="hidden" name="authToken" value="<?php echo $authToken; ?>" />
				<input type="hidden" name="userID" value="<?php echo $userID; ?>" />
				<div class="center">
					<button type="submit" name="action" value="edit"> &Auml;ndern </button>
					<button type="reset"> L&ouml;schen </button>
				</div>
			</td>
		</tr>
	</table>
</form>
<?php if ($passwordChange): ?>
<br />
<?php if (!$samePasswords): ?>
<div class="caution">
	Die eingegebenen Passw&ouml;rter sind nicht identisch!
</div>
<?php endif; ?>
<?php if (!$rightPassword): ?>
<div class="caution">
	Leider ist dein altes Passwort falsch!
</div>
<?php endif; ?>
<?php if ($samePasswords&&$rightPassword): ?>
<div class="success">
	Dein Passwort wurde erfolgreich ge&auml;ndert!
</div>
<?php endif; ?>
<?php endif; ?>
<h2>Passwort &auml;ndern:</h2>
<form method="post" action="index.php?id=<?php echo $location; ?>" class="formTable">
	<table>
		<tr>
			<td>Altes Passwort: </td>
			<td>
				<div class="center">
					<input type="password" name="oldPassword" class="formpassword" />
				</div>
			</td>
		</tr>
		<tr>
			<td>Neues Passwort: </td>
			<td>
				<div class="center">
					<input type="password" name="newPassword" class="formpassword" />
				</div>
			</td>
		</tr>
		<tr>
			<td>Passwort wiederholen: </td>
			<td>
				<div class="center">
					<input type="password" name="proofPassword" class="formpassword" />
				</div>
			</td>
		</tr>
		<tr>
			<td></td>
			<td>
				<input type="hidden" name="authTime" value="<?php echo $authTime; ?>" />
				<input type="hidden" name="authToken" value="<?php echo $authToken; ?>" />
				<input type="hidden" name="userID" value="<?php echo $userID; ?>" />
				<div class="center">
					<button type="submit" name="action" value="password"> &Auml;ndern </button>
					<button type="reset"> L&ouml;schen </button>
				</div>
			</td>
		</tr>
	</table>
</form>
<h2>E-Mail-Adressen:</h2>
<form method="post" action="index.php?id=<?php echo $location; ?>">
	<table class="userdatatable">
		<tr>
			<td>E-Mail-Adresse eintragen: </td>
			<td>
				<input type="text" name="email" class="formmail" />
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
			<a href="index.php?id=<?php echo $location; ?>&amp;primemail=<?php echo urlencode($email['email']); ?>&amp;time=<?php echo $authTime; ?>&amp;token=<?php echo $authToken; ?>"><img src="includes/graphics/ok22.png" alt="Als Hauptadresse festlegen" /></a>
			<?php endif; ?>
			<?php if (!$email['primary']): ?>
			<a href="index.php?id=<?php echo $location; ?>&amp;delmail=<?php echo urlencode($email['email']); ?>&amp;time=<?php echo $authTime; ?>&amp;token=<?php echo $authToken; ?>"><img src="includes/graphics/delete22.png" alt="L&ouml;schen" /></a>
			<?php endif; ?>
			<?php if (!$email['confirmed']): ?>
			<a href="index.php?id=<?php echo $location; ?>&amp;confmail=<?php echo urlencode($email['email']); ?>&amp;time=<?php echo $authTime; ?>&amp;token=<?php echo $authToken; ?>"><img src="includes/graphics/mail22.png" alt="Best&auml;tigungsmail erneut senden" /></a>
			<?php endif; ?>
		</td>
	</tr>
	<?php endforeach; ?>
</table>