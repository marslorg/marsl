<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<div class="news">
	<h4 class="pagination">
		<?php if ($showFirstPage): ?><a href="index.php?id=<?php echo $location; ?>&amp;page=1">«</a><?php endif; ?>
		<?php if ($showPreviousPage): ?><a href="index.php?id=<?php echo $location; ?>&amp;page=<?php echo $page - 1; ?>">‹</a><?php endif; ?>
		<?php for ($i = $startPage - 1; $i<$endPage; $i++): $j = $i+1; ?>
		<?php if ($j!=$page): ?>
		<a href="index.php?id=<?php echo $location; ?>&amp;page=<?php echo $j; ?>"><?php endif; ?><?php echo $j; ?><?php if ($j!=$page): ?></a>
		<?php endif; ?>
		<?php endfor; ?>
		<?php if ($showNextPage): ?><a href="index.php?id=<?php echo $location; ?>&amp;page=<?php echo $page + 1; ?>">›</a><?php endif; ?>
		<?php if ($showLastPage): ?><a href="index.php?id=<?php echo $location; ?>&amp;page=<?php echo ceil($pages); ?>">»</a><?php endif; ?>
	</h4>
	<?php foreach($news as $article): ?>
	<span class="newstable">
		<div class="newsrow">
			<div class="albuminformation">
				<img src="includes/graphics/square.gif" /> Datum: <?php echo $article['date']; ?>
			</div>
			<div class="newscontent">
				<h3 class="headline"><?php echo $article['headline']; ?></h3>
				<h2><?php echo $article['title']; ?></h2>
				<?php if ($article['picture1']!="empty"): ?>
				<span class="teaserpicture">
					<img src="news/<?php echo $article['picture1']; ?>" />
					<?php echo $article['photograph1']; ?>
				</span>
				<?php endif; ?>
				<b><?php echo $article['city']; ?> (<?php echo $article['author']; ?>)</b>&nbsp;<img src="includes/graphics/square.gif" />&nbsp;&nbsp;<?php echo $article['teaser']; ?> 
				<?php if (!empty($article['text'])): ?>
				<br /><br /><strong><a href="index.php?id=<?php echo $location; ?>&amp;show=<?php echo $article['id']; ?>&amp;action=read">Hier geht es weiter</a></strong>
				<?php endif; ?>				
			</div>
		</div>
	</span>
	<div class="albuminformationmobile">
		<img src="includes/graphics/square.gif" /> Datum: <?php echo $article['date']; ?>
	</div>
	<hr class="newsseparator" />
	<?php endforeach; ?>
	<h4 class="pagination">
		<?php if ($showFirstPage): ?><a href="index.php?id=<?php echo $location; ?>&amp;page=1">«</a><?php endif; ?>
		<?php if ($showPreviousPage): ?><a href="index.php?id=<?php echo $location; ?>&amp;page=<?php echo $page - 1; ?>">‹</a><?php endif; ?>
		<?php for ($i = $startPage - 1; $i<$endPage; $i++): $j = $i+1; ?>
		<?php if ($j!=$page): ?>
		<a href="index.php?id=<?php echo $location; ?>&amp;page=<?php echo $j; ?>"><?php endif; ?><?php echo $j; ?><?php if ($j!=$page): ?></a>
		<?php endif; ?>
		<?php endfor; ?>
		<?php if ($showNextPage): ?><a href="index.php?id=<?php echo $location; ?>&amp;page=<?php echo $page + 1; ?>">›</a><?php endif; ?>
		<?php if ($showLastPage): ?><a href="index.php?id=<?php echo $location; ?>&amp;page=<?php echo ceil($pages); ?>">»</a><?php endif; ?>
	</h4>
</div>