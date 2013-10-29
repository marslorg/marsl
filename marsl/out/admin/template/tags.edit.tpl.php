<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
<h3>Tag umbenennen:</h3>
<form method="post" action="index.php?var=tags&amp;action=edit&amp;tagid=<?php echo $id; ?>">
	<input type="text" name="tag" value="<?php echo $tag; ?>" />
	<input type="hidden" name="authTime" value="<?php echo $authTime; ?>" />
	<input type="hidden" name="authToken" value="<?php echo $authToken; ?>" />
	<button type="submit" name="action" value="name"> &Auml;ndern </button>
</form>
<h3>Artikel:</h3>
<ul>
	<?php foreach($news as $article): ?>
	<li class="cbenews">
		<a href="index.php?var=module&amp;module=news&amp;action=details&amp;id=<?php echo $article['news']; ?>">
			<?php echo $article['headline']; ?>: <?php echo $article['title']; ?>
		</li>
	</li>
	<?php endforeach; ?>
</ul>