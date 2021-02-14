<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
<h2>Neue App anlegen:</h2>
<fieldset class="apisetup">
	<form method="post" action="index.php?var=api">
		<input type="text" name="appname" />
        <input type="hidden" name="authTime" value="<?php echo $authTime; ?>" />
		<input type="hidden" name="authToken" value="<?php echo $authToken; ?>" />
		<button type="submit" name="action" value="create"> App anlegen </button>
	</form>
</fieldset>
<table class="applist">
	<tr>
		<th>Name</th>
		<th>Öffentlicher Schlüssel</th>
		<th>Secret</th>
	</tr>
	<?php foreach($apps as $appClientData): ?>
	<tr>
		<td><?php echo $appClientData['name']; ?></td>
		<td><?php echo $appClientData['key']; ?></td>
		<td><?php echo $appClientData['secret']; ?></td>
	</tr>
	<tr>
		<td colspan="3" class="center"><a href="index.php?var=api&action=delete&id=<?php echo $appClientData['id']; ?>&time=<?php echo $authTime; ?>&token=<?php echo $authToken; ?>" onclick="return confirm('M&ouml;chtest du die App-Daten wirklich l&ouml;schen?')">Löschen</a></td>
	</tr>
	<?php endforeach ?>
</table>