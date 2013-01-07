<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<h4 class="center">
	<?php for ($i = 0; $i<$pages; $i++): $j = $i+1; ?>
	<?php if ($j!=$page): ?>
	<a href="index.php?id=<?php echo $location; ?>&amp;page=<?php echo $j; ?>"><?php endif; ?><?php echo $j; ?><?php if ($j!=$page): ?></a>
	<?php endif; ?>
	<?php endfor; ?>
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
		<br /><br /><strong><a href="index.php?id=<?php echo $location; ?>&amp;show=<?php echo $article['id']; ?>&amp;action=read">Hier geht es weiter</a></strong>
		<?php endif; ?>	
		<div class="clearer">&nbsp;</div>

	
	</div>

	<div class="post-meta">Datum: <?php echo $article['date']; ?></div>			

</div>
<?php endforeach; ?>
<h4 class="center">
	<?php for ($i = 0; $i<$pages; $i++): $j = $i+1; ?>
	<?php if ($j!=$page): ?>
	<a href="index.php?id=<?php echo $location; ?>&amp;page=<?php echo $j; ?>"><?php endif; ?><?php echo $j; ?><?php if ($j!=$page): ?></a>
	<?php endif; ?>
	<?php endfor; ?>
</h4>