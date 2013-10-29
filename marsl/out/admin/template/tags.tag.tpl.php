<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
<script type="text/javascript">
$(document).ready(function() {
	$('#fieldSwitcher').change(function() {
		if ($('select#fieldSwitcher').val()=='rename') {
			$('#fieldToSwitch').removeAttr('disabled');
		}
		else {
			$('#fieldToSwitch').attr('disabled', 'disabled');
		}
	}).trigger('change');
});
</script>
<br /><br />
<div>
	Ein <a href="index.php?var=tags&amp;action=edit&amp;tagid=<?php echo $duplicateID; ?>" target="_blank">Eintrag</a> mit diesem Namen existiert schon. Entscheide nun, was du tun m&ouml;chtest:<br /><br />
	<form method="post" action="index.php?var=tags&amp;action=edit&amp;tagid=<?php echo $id; ?>">
		Aktion w&auml;hlen: 
		<select id="fieldSwitcher" name="do" class="tagExistAction">
			<option value="saveDuplicate">Daten von <?php echo $tag; ?> behalten.</option>
			<option value="moveToDuplicate">Daten von <?php echo $oldTag; ?> nach <?php echo $tag; ?> &uuml;bernehmen.</option>
			<option value="autoRename"><?php echo $tag; ?> in &quot;<?php echo $autoTag; ?>&quot; umbenennen.</option>
			<option value="rename">Eintrag selber umbenennen.</option>
		</select>
		<br /><br />
		<input type="text" name="tag" value="<?php echo $tag; ?>" class="tagExists" disabled="disabled" id="fieldToSwitch" />
		<br /><br />
		<input type="hidden" name="targetTag" value="<?php echo $tag; ?>" />
		<input type="hidden" name="duplicateID" value="<?php echo $duplicateID; ?>" />
		<input type="hidden" name="autoTag" value="<?php echo $autoTag; ?>" />
		<input type="hidden" name="authTime" value="<?php echo $authTime; ?>" />
		<input type="hidden" name="authToken" value="<?php echo $authToken; ?>" />
		<button type="submit" name="action" value="tagExists"> &Auml;ndern </button>
	</form>
</div>