<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
<?php foreach($news as $article): ?>
<table class="newstable">
	<tr>
		<td class="newsinformation">
			Autor: <?php echo $article['author']; ?><br />
			IP: <?php echo $article['authorIP']; ?><br />
			Kategorie: <?php echo $article['location']; ?><br />
			Datum: <?php echo $article['date']; ?><br />
			eingereicht am: <?php echo $article['postdate']; ?><br />
			<a href="index.php?var=module&amp;module=news&amp;action=queue&amp;do=submit&amp;id=<?php echo $article['news']; ?>&amp;time=<?php echo $authTime; ?>&amp;token=<?php echo $authToken; ?>">Freischalten</a> 
			<a href="index.php?var=module&amp;module=news&amp;action=edit&amp;id=<?php echo $article['news']; ?>">Editieren</a> 
			<a href="index.php?var=module&amp;module=news&amp;action=queue&amp;do=del&amp;id=<?php echo $article['news']; ?>&amp;time=<?php echo $authTime; ?>&amp;token=<?php echo $authToken; ?>" onclick="return confirm('M&ouml;chtest du den Eintrag wirklich l&ouml;schen?')">L&ouml;schen</a>
		</td>
		<td class="news">
			<h3 class="headline"><?php echo $article['headline']; ?></h3>
			<h2><?php echo $article['title']; ?></h2>
			<?php if ($article['picture1']!="empty"): ?>
			<span class="teaserpicture">
				<img src="../news/<?php echo $article['picture1']; ?>" />
				<?php echo $article['photograph1']; ?>
			</span>
			<?php endif; ?>
			<b><?php echo $article['city']; ?></b>&nbsp;<img src="../includes/graphics/square.gif" />&nbsp;&nbsp;<?php echo $article['teaser']; ?> 
			<?php if (!empty($article['text'])): ?>
			<a href="index.php?var=module&amp;module=news&amp;action=details&amp;id=<?php echo $article['news']; ?>">Mehr..</a>
			<?php endif; ?>
		</td>
	</tr>
</table>
<hr class="newsseparator" />
<?php endforeach; ?>