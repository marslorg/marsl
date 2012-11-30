<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");

?>
<fieldset class="portalform">
	<form method="post" action="index.php?var=module&amp;module=portal">
		<table class="portaltable">
			<tr>
				<td colspan="2" class="center">
					<input type="hidden" name="authTime" value="<?php echo $authTime; ?>" />
					<input type="hidden" name="authToken" value="<?php echo $authToken; ?>" />
					<button name="action" value="submit"> &Uuml;bernehmen </button>
				</td>
			</tr>
			<?php foreach ($news as $article): ?>
			<tr>
				<td>
					<a href="index.php?var=module&amp;module=news&amp;action=details&amp;id=<?php echo $article['id']; ?>" target="_blank">
						<?php echo $article['headline']; ?>: <?php echo $article['title']; ?> (<?php echo $article['date']; ?>)
					</a>
				</td>
				<td><input type="checkbox" name="<?php echo $article['id']; ?>" value="1" <?php if ($article['featured']=="1"): ?>checked<?php endif; ?> /></td>
			</tr>
			<?php endforeach; ?>
		</table>
	</form>
</fieldset>