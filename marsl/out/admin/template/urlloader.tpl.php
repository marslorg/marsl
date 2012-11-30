<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
<form method="post" action="index.php?var=module&amp;module=urlloader">
	<fieldset class="homepageform">
		<select name="homepage">
			<?php foreach($locations as $location): ?>
			<option value="<?php echo $location['id']; ?>" <?php if ($location['id']==$homepage): ?>selected<?php endif; ?>>
				<?php echo $location['name']; ?>
			</option>
			<?php endforeach; ?>
		</select>
		<input type="hidden" value="<?php echo $authTime; ?>" name="authTime" />
		<input type="hidden" value="<?php echo $authToken; ?>" name="authToken" />
		<button type="submit" name="action" value="change"> Startseite einstellen </button>
	</fieldset>
</form>