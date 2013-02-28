<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<h4 class="center">
	<?php for ($i = 0; $i<$pages; $i++): $j = $i+1; ?>
	<?php if ($j!=$page): ?>
	<a href="index.php?search=<?php echo $query; ?>&amp;scope=news_<?php echo $type; ?>&amp;page=<?php echo $j; ?>"><?php endif; ?><?php echo $j; ?><?php if ($j!=$page): ?></a>
	<?php endif; ?>
	<?php endfor; ?>
</h4>
<h2>Suche nach <i><?php echo $query; ?></i></h2>
<h3>in &quot;<?php echo $topic; ?>&quot;</h3>
<?php foreach($news as $article): ?>
<h3>
	<a href="index.php?id=<?php echo $article['location']; ?>&amp;show=<?php echo $article['news']; ?>&amp;action=read">
		<?php echo $startCounter; ?>. <?php echo $article['headline']; ?>: <?php echo $article['title']; ?>
	</a>
</h3>
<?php echo $article['teaser']; ?>
<hr class="newsseparator" />
<?php $startCounter++; ?>
<?php endforeach; ?>
<h4 class="center">
	<?php for ($i = 0; $i<$pages; $i++): $j = $i+1; ?>
	<?php if ($j!=$page): ?>
	<a href="index.php?search=<?php echo $query; ?>&amp;scope=news_<?php echo $type; ?>&amp;page=<?php echo $j; ?>"><?php endif; ?><?php echo $j; ?><?php if ($j!=$page): ?></a>
	<?php endif; ?>
	<?php endfor; ?>
</h4>