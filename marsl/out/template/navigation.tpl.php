<?php 
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<?php foreach($categories as $category): ?>
<div class="box">
	<div class="box-title">
		<?php if ($category['type'] == 1): ?><a href="<?php echo $category['link']; ?>"><?php endif; ?>
			<?php echo $category['name']; ?>
		<?php if ($category['type'] == 1): ?></a><?php endif; ?>
	</div>

	<div class="box-content">
		<ul class="nice-list">
			<?php if ($category['type'] == 0 && array_key_exists($category['id'], $links)): ?>
			<?php foreach ($links[$category['id']] as $link): ?>
			<li>
				<a href="<?php echo $link['link']; ?>">
					<?php echo $link['name']; ?>
				</a>
			</li>
			<?php endforeach; ?>
			<?php endif; ?>									
		</ul>
	</div>
</div>
<?php endforeach; ?>
