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