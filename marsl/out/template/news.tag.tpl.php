<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<h1>Tag: <?php echo $tagName; ?></h1>
<h2>Artikel</h2>
<ul>
	<?php foreach ($articles as $article): ?>
	<li class="cbenews">
		<a href="<?php echo $article['newsURI']; ?>"><?php echo $article['headline']; ?>: <?php echo $article['title']; ?> (<?php echo $article['date']; ?>) in <?php echo $article['locationName']; ?></a>
	</li>
	<?php endforeach; ?>
</ul>