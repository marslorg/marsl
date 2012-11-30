<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
<form method="post" action="index.php?var=role">
	<fieldset class="roleform">
		<input name="role" type="text" />
		<input type="hidden" name="authTime" value="<?php echo $authTime; ?>" />
		<input type="hidden" name="authToken" value="<?php echo $authToken; ?>" />
		<button type="submit" value="new" name="action"> Rolle eintragen </button>
	</fieldset>
</form>
<table class="roletable">
	<form method="post" action="index.php?var=role">
		<?php foreach($roles as $roleArray): ?>
		<tr class="standardfont">
			<td><input type="text" name="name" value="<?php echo $roleArray['name']; ?>" /></td>
			<td>
				<input type="hidden" name="authTime" value="<?php echo $authTime; ?>" />
				<input type="hidden" name="authToken" value="<?php echo $authToken; ?>" />
				<button type="submit" name="role" value="<?php echo $roleArray['role']; ?>"> Name &auml;ndern </button>
			</td>
			<td><a href="index.php?var=role&amp;action=del&amp;role=<?php echo $roleArray['role']; ?>&amp;time=<?php echo $authTime; ?>&amp;token=<?php echo $authToken; ?>">Rolle löschen</a></td>
		</tr>
		<?php endforeach; ?>
	</form>
</table>