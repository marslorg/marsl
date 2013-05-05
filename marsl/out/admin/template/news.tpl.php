<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
<h4 class="center">
	<?php for ($i = 0; $i<$pages; $i++): $j = $i+1; ?>
	<?php if ($j!=$page): ?>
	<a href="index.php?var=module&amp;module=news&amp;action=news&amp;page=<?php echo $j; ?>"><?php endif; ?><?php echo $j; ?><?php if ($j!=$page): ?></a>
	<?php endif; ?>
	<?php endfor; ?>
</h4>
<?php foreach($news as $article): ?>
<table class="newstable">
	<tr>
		<td class="newsinformation">
		Korrigiert: <?php if ($article['corrected']): ?><img src="../includes/graphics/ok32.png" /><?php endif; if (!$article['corrected']): ?><img src="../includes/graphics/cancel32.png" /><?php endif; ?><br />
			Autor: <?php echo $article['author']; ?><br />
			IP: <?php echo $article['authorIP']; ?><br />
			Kategorie: <?php echo $article['location']; ?><br />
			Datum: <?php echo $article['date']; ?><br />
			eingereicht am: <?php echo $article['postdate']; ?><br />
			<?php if($article['editLink']): ?>
			<a href="index.php?var=module&amp;module=news&amp;action=edit&amp;id=<?php echo $article['id']; ?>">Editieren</a> 
			<a href="index.php?var=module&amp;module=news&amp;action=news&amp;page=<?php echo $page; ?>&amp;do=del&amp;id=<?php echo $article['id']; ?>&amp;time=<?php echo $authTime; ?>&amp;token=<?php echo $authToken; ?>" onclick="return confirm('M&ouml;chtest du den Eintrag wirklich l&ouml;schen?')">L&ouml;schen</a>
			<?php endif; ?>
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
			<br /><br /><strong><a href="index.php?var=module&amp;module=news&amp;action=details&amp;id=<?php echo $article['id']; ?>">Hier geht es weiter</a></strong>
			<?php endif; ?>
		</td>
	</tr>
</table>
<hr class="newsseparator" />
<?php endforeach; ?>
<h4 class="center">
	<?php for ($i = 0; $i<$pages; $i++): $j = $i+1; ?>
	<?php if ($j!=$page): ?>
	<a href="index.php?var=module&amp;module=news&amp;action=news&amp;page=<?php echo $j; ?>"><?php endif; ?><?php echo $j; ?><?php if ($j!=$page): ?></a>
	<?php endif; ?>
	<?php endfor; ?>
</h4>