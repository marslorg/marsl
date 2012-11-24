<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
$j = 0;
?>
<div class="<?php if (($nb_id%2)==0): ?>newsblock<?php endif; ?><?php if (($nb_id%2)==1): ?>newsblock2<?php endif; ?>">
	<h3 class="pagename"><?php echo $page['name']; ?></h3>
	<?php foreach($news as $article): ?>
	<?php if ($j==0): ?>
	<h5><?php echo $article['headline']; ?></h5>
	<h4><?php echo $article['title']; ?></h4>
	<span class="portaltext">
		<?php if ($article['picture']!="empty"): ?>
		<img src="news/<?php echo $article['picture']; ?>" width="<?php echo $article['width']; ?>" height="<?php echo $article['height']; ?>" class="portalimg" />
		<?php endif; ?>
		<?php echo $article['teaser']; ?> 
		<?php echo $article['photograph']; ?>
		<a href="index.php?id=<?php echo $article['location']; ?>&amp;show=<?php echo $article['news']; ?>&amp;action=read">Mehr..</a>
	</span>
	<?php endif; ?>
	<?php if ($j!=0): ?>
	<span class="portallinks">
		<a href="index.php?id=<?php echo $article['location']; ?>&amp;show=<?php echo $article['news']; ?>&amp;action=read">
			<?php echo $article['headline']; ?>: <?php echo $article['title']; ?>
		</a>
	</span>
	<?php endif; ?>
	<hr />
	<?php $j++; ?>
	<?php endforeach; ?>
</div>