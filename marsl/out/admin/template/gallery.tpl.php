<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
<h4 class="center">
	<?php for ($i = 0; $i < $pages; $i++): $j = $i+1; ?>
	<?php if ($j!=$page): ?>
	<a href="index.php?var=module&amp;module=gallery&amp;action=albums&amp;page=<?php echo $j; ?>"><?php endif; ?><?php echo $j; ?><?php if($j!=$page): ?></a>
	<?php endif; ?>
	<?php endfor; ?>
</h4>
<?php foreach($galleries as $gallery): ?>
<table class="newstable">
	<tr>
		<td class="newsinformation">
			Fotograf: <?php echo $gallery['photograph']; ?><br />
			Author: <?php echo $gallery['author']; ?><br />
			IP: <?php echo $gallery['authorIP']; ?><br />
			Kategorie: <?php echo $gallery['location']; ?><br />
			Datum: <?php echo $gallery['date']; ?><br />
			eingereicht am: <?php echo $gallery['postdate']; ?><br />
			<a href="index.php?var=module&amp;module=gallery&amp;action=details&amp;id=<?php echo $gallery['album']; ?>">Details</a> 
			<?php if ($gallery['editLink']): ?>
			| <a href="index.php?var=module&amp;module=gallery&amp;action=edit&amp;id=<?php echo $gallery['album']; ?>">Editieren</a> 
			| <a href="index.php?var=module&amp;module=gallery&amp;action=albums&amp;page=<?php echo $page; ?>&amp;do=del&amp;id=<?php echo $gallery['album']; ?>&amp;time=<?php echo $authTime; ?>&amp;token=<?php echo $authToken; ?>" onclick="return confirm('M&ouml;chtest du den Eintrag wirklich l&ouml;schen?')">L&ouml;schen</a>
			<?php endif; ?>
		</td>
		<td class="news">
			<?php echo $gallery['description']; ?>
		</td>
	</tr>
</table>
<hr class="newsseparator" />
<?php endforeach; ?>
<h4 class="center">
	<?php for ($i = 0; $i < $pages; $i++): $j = $i+1; ?>
	<?php if ($j!=$page): ?>
	<a href="index.php?var=module&amp;module=gallery&amp;action=albums&amp;page=<?php echo $j; ?>"><?php endif; ?><?php echo $j; ?><?php if($j!=$page): ?></a>
	<?php endif; ?>
	<?php endfor; ?>
</h4>