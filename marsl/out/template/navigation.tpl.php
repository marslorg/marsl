<?php 
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<?php foreach($categories as $category): ?>
<li>
	<?php if ($category['type'] == 1): ?><a href="index.php?id=<?php echo $category['id']; ?>"><?php endif; ?>
	<?php if ($category['type'] == 0): ?><a href="#"><?php endif; ?>
		<?php echo $category['name']; ?>
	</a>
	<ul>
		<?php if ($category['type'] == 0 && array_key_exists($category['id'], $links)): ?>
		<?php foreach ($links[$category['id']] as $link):?>
		<li>
			<a href="index.php?id=<?php echo $link['id']; ?>">
				<?php echo $link['name']; ?>
			</a>
		</li>
		<?php endforeach; ?>
		<?php endif; ?>
	</ul>
</li>
<?php endforeach; ?>