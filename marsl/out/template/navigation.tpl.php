<?php 
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<?php foreach ($categories as $category): ?>
<li <?php if ($category['type'] == 0): ?>class="submenu"<?php endif; ?>>
	<a class="hide" <?php if ($category['type'] == 0): ?>href="#"<?php endif; ?><?php if ($category['type'] == 1): ?>href="<?php echo $category['link']; ?>"<?php endif; ?>>
		<?php echo $category['name']; ?>
	</a>
	<ul>
		<?php if ($category['type'] == 0 && array_key_exists($category['id'], $links)): ?>
		<?php foreach($links[$category['id']] as $link): ?>
		<li>
			<a href="<?php echo $link['link']; ?>">
				<?php echo $link['name']; ?>
			</a>
		</li>
		<?php endforeach; ?>
		<?php endif; ?>
	</ul>
</li>
<?php endforeach; ?>