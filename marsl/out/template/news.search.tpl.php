<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<h4 class="pagination">
	<?php if ($showFirstPage): ?><a href="index.php?search=<?php echo $query; ?>&amp;scope=news_<?php echo $type; ?>&amp;page=1">&#171;</a><?php endif; ?>
	<?php if ($showPreviousPage): ?><a href="index.php?search=<?php echo $query; ?>&amp;scope=news_<?php echo $type; ?>&amp;page=<?php echo $page - 1; ?>">&lt;</a><?php endif; ?>
	<?php for ($i = $startPage - 1; $i<$endPage; $i++): $j = $i+1; ?>
	<?php if ($j!=$page): ?>
	<a href="index.php?search=<?php echo $query; ?>&amp;scope=news_<?php echo $type; ?>&amp;page=<?php echo $j; ?>"><?php endif; ?><?php echo $j; ?><?php if ($j!=$page): ?></a>
	<?php endif; ?>
	<?php endfor; ?>
	<?php if ($showNextPage): ?><a href="index.php?search=<?php echo $query; ?>&amp;scope=news_<?php echo $type; ?>&amp;page=<?php echo $page + 1; ?>">&gt;</a><?php endif; ?>
	<?php if ($showLastPage): ?><a href="index.php?search=<?php echo $query; ?>&amp;scope=news_<?php echo $type; ?>&amp;page=<?php echo ceil($pages); ?>">&#187;</a><?php endif; ?>
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
<h4 class="pagination">
	<?php if ($showFirstPage): ?><a href="index.php?search=<?php echo $query; ?>&amp;scope=news_<?php echo $type; ?>&amp;page=1">&#171;</a><?php endif; ?>
	<?php if ($showPreviousPage): ?><a href="index.php?search=<?php echo $query; ?>&amp;scope=news_<?php echo $type; ?>&amp;page=<?php echo $page - 1; ?>">&lt;</a><?php endif; ?>
	<?php for ($i = $startPage - 1; $i<$endPage; $i++): $j = $i+1; ?>
	<?php if ($j!=$page): ?>
	<a href="index.php?search=<?php echo $query; ?>&amp;scope=news_<?php echo $type; ?>&amp;page=<?php echo $j; ?>"><?php endif; ?><?php echo $j; ?><?php if ($j!=$page): ?></a>
	<?php endif; ?>
	<?php endfor; ?>
	<?php if ($showNextPage): ?><a href="index.php?search=<?php echo $query; ?>&amp;scope=news_<?php echo $type; ?>&amp;page=<?php echo $page + 1; ?>">&gt;</a><?php endif; ?>
	<?php if ($showLastPage): ?><a href="index.php?search=<?php echo $query; ?>&amp;scope=news_<?php echo $type; ?>&amp;page=<?php echo ceil($pages); ?>">&#187;</a><?php endif; ?>
</h4>