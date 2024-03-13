<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<h4 class="center">
	<?php if ($showFirstPage): ?><a href="<?php echo $uri; ?><?php echo $this->getPageURIFormatted(1); ?>">«</a><?php endif; ?>
	<?php if ($showPreviousPage): ?><a href="<?php echo $uri; ?><?php echo $this->getPageURIFormatted($page - 1); ?>">‹</a><?php endif; ?>
	<?php for ($i = $startPage - 1; $i<$endPage; $i++): $j = $i+1; ?>
	<?php if ($j!=$page): ?>
	<a href="<?php echo $uri; ?><?php echo $this->getPageURIFormatted($j); ?>"><?php endif; ?><?php echo $j; ?><?php if ($j!=$page): ?></a>
	<?php endif; ?>
	<?php endfor; ?>
	<?php if ($showNextPage): ?><a href="<?php echo $uri; ?><?php echo $this->getPageURIFormatted($page + 1); ?>">›</a><?php endif; ?>
	<?php if ($showLastPage): ?><a href="<?php echo $uri; ?><?php echo $this->getPageURIFormatted(ceil($pages)); ?>">»</a><?php endif; ?>
</h4>
<?php foreach($news as $article): ?>
<div class="post">
			
	<div class="post-title"><h2><?php echo $article['title']; ?></h2></div>
	
	<div class="post-date"><h3><?php echo $article['headline']; ?></h3></div>

	<div class="post-body">
		<?php if ($article['picture1']!="empty"): ?>
		<span class="teaserpicture">
			<img src="news/<?php echo $article['picture1']; ?>" />
			<?php echo $article['photograph1']; ?>
		</span>
		<?php endif; ?>
		<b><?php echo $article['city']; ?> (<?php echo $article['author']; ?>)</b>&nbsp;-&nbsp;<?php echo $article['teaser']; ?> 
		<?php if (!empty($article['text'])): ?>
		<br /><br /><strong><a href="<?php echo $article['newsURI'];?>">Hier geht es weiter</a></strong>
		<?php endif; ?>	
		<div class="clearer">&nbsp;</div>

	
	</div>

	<div class="post-meta">Datum: <?php echo $article['date']; ?></div>			

</div>
<?php endforeach; ?>
<h4 class="center">
	<?php if ($showFirstPage): ?><a href="<?php echo $uri; ?><?php echo $this->getPageURIFormatted(1); ?>">«</a><?php endif; ?>
	<?php if ($showPreviousPage): ?><a href="<?php echo $uri; ?><?php echo $this->getPageURIFormatted($page - 1); ?>">‹</a><?php endif; ?>
	<?php for ($i = $startPage - 1; $i<$endPage; $i++): $j = $i+1; ?>
	<?php if ($j!=$page): ?>
	<a href="<?php echo $uri; ?><?php echo $this->getPageURIFormatted($j); ?>"><?php endif; ?><?php echo $j; ?><?php if ($j!=$page): ?></a>
	<?php endif; ?>
	<?php endfor; ?>
	<?php if ($showNextPage): ?><a href="<?php echo $uri; ?><?php echo $this->getPageURIFormatted($page + 1); ?>">›</a><?php endif; ?>
	<?php if ($showLastPage): ?><a href="<?php echo $uri; ?><?php echo $this->getPageURIFormatted(ceil($pages)); ?>">»</a><?php endif; ?>
</h4>
