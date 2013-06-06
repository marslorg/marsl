<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
<h2>AGB/Regeln f&uuml;r Anmeldedialog einstellen:</h2>
<form method="post" action="index.php?var=module&amp;module=register">
	<select name="location">
		<option value=""></option>
		<?php foreach($links as $link): ?>
		<option value="<?php echo $link['id']; ?>" <?php if ($link['id']==$id): ?>selected<?php endif; ?>>
			<?php echo $link['name']; ?>
		</option>
		<?php endforeach;?>
	</select>
	<input type="hidden" name="authTime" value="<?php echo $authTime; ?>" />
	<input type="hidden" name="authToken" value="<?php echo $authToken; ?>" />
	<button type="submit" name="action" value="send"> &Auml;ndern </button>
</form>