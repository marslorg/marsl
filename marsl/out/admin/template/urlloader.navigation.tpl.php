<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
<?php foreach ($categories as $category): ?>
<li>
	<a class="hide" <?php if ($category['type'] == 0): ?>href="#"<?php endif; ?><?php if ($category['type'] == 1): ?>href="index.php?var=urlloader&amp;id=<?php echo $category['id']; ?>"<?php endif; ?>>
		<?php echo $category['name']; ?>
	</a>
	<ul>
		<?php if (array_key_exists($category['id'], $categoryLinks)): ?>
		<?php foreach ($categoryLinks[$category['id']] as $link): ?>
		<li>
			<a href="index.php?var=urlloader&amp;id=<?php echo $link['id']; ?>">
				<?php echo $link['name']; ?>
			</a>
		</li>
		<?php endforeach; ?>
		<?php endif; ?>
	</ul>
</li>
<?php endforeach; ?>