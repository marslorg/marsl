<?php 
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<li <?php if ($cat_type == 0): ?>class="submenu"<?php endif; ?>>
	<a class="hide" <?php if ($cat_type == 0): ?>href="#"<?php endif; ?><?php if ($cat_type == 1): ?>href="index.php?id=<?php echo $cat_id; ?>"<?php endif; ?>>
		<?php echo $cat_name; ?>
	</a>
	<ul>
		<?php foreach ($links as $link): ?>
		<li>
			<a href="index.php?id=<?php echo $link['id']; ?>">
				<?php echo $link['name']; ?>
			</a>
		</li>
		<?php endforeach; ?>
	</ul>
</li>