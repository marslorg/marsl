<?php 
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<div class="box">
	<div class="box-title">
		<?php if ($cat_type == 1): ?><a href="index.php?id=<?php echo $cat_id; ?>"><?php endif; ?>
			<?php echo $cat_name; ?>
		<?php if ($cat_type == 1): ?></a><?php endif; ?>
	</div>

	<div class="box-content">
		<ul class="nice-list">
			<?php foreach ($links as $link): ?>
			<li>
				<a href="index.php?id=<?php echo $link['id']; ?>">
					<?php echo $link['name']; ?>
				</a>
			</li>
			<?php endforeach; ?>
									
		</ul>
	</div>
</div>