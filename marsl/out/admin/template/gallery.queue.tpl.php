<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
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
			<a href="index.php?var=module&amp;module=gallery&amp;action=queue&amp;do=submit&amp;id=<?php echo $gallery['album']; ?>&amp;time=<?php echo $authTime; ?>&amp;token=<?php echo $authToken; ?>">Freischalten</a> 
			| <a href="index.php?var=module&amp;module=gallery&amp;action=edit&amp;id=<?php echo $gallery['album']; ?>">Editieren</a> 
			| <a href="index.php?var=module&amp;module=gallery&amp;action=details&amp;id=<?php echo $gallery['album']; ?>">Details</a> 
			| <a href="index.php?var=module&amp;module=gallery&amp;action=queue&amp;do=del&amp;id=<?php echo $gallery['album']; ?>&amp;time=<?php echo $authTime; ?>&amp;token=<?php echo $authToken; ?>">L&ouml;schen</a>
		</td>
		<td class="news">
			<?php echo $gallery['description']; ?>
		</td>
	</tr>
</table>
<hr class="newsseparator" />
<?php endforeach; ?>