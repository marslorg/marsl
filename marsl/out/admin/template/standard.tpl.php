<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
<fieldset class="roleform">
	<form method="post" action="index.php?var=standards">
		<table class="roletable">
			<tr class="standardfont">
				<td>Gastrolle: </td>
				<td>
					<select name="guest">
						<option></option>
						<?php foreach($roles as $roleID): ?>
						<option value="<?php echo $roleID['role']; ?>" <?php if($roleID['role']==$guest): ?>selected<?php endif; ?>>
							<?php echo $roleID['name']; ?>
						</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr class="standardfont">
				<td>Benutzerrolle: </td>
				<td>
					<select name="user">
						<option></option>
						<?php foreach($roles as $roleID): ?>
						<option value="<?php echo $roleID['role']; ?>" <?php if($roleID['role']==$stdUser): ?>selected<?php endif; ?>>
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
					<button type="submit" name="action" value="change"> Ändern </button>
				</td>
			</tr>
		</table>
	</form>
</fieldset>