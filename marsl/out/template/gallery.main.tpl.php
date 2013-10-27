<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<div class="gallery">
	<h4 class="center">
		<?php for ($i = 0; $i<$pages; $i++): $j = $i+1; ?>
		<?php if ($j!=$page): ?>
		<a href="index.php?id=<?php echo $location; ?>&amp;page=<?php echo $j; ?>"><?php endif; ?><?php echo $j; ?><?php if ($j!=$page): ?></a>
		<?php endif; ?>
		<?php endfor; ?>
	</h4>
	<?php foreach($galleries as $gallery): ?>
	<table class="albumtable">
		<tr>
			<td class="albuminformation">
				<img src="includes/graphics/square.gif" /> Fotos: <?php echo $gallery['photograph']; ?> | <?php echo $gallery['date']; ?>
			</td>
			<td class="albumdescription">
				<img src="<?php echo $gallery['picture']; ?>" class="albumpicture" /><?php echo $gallery['description']; ?> 
				<br /><br /><strong><a href="index.php?id=<?php echo $location; ?>&amp;show=<?php echo $gallery['album']; ?>&amp;action=thumb">Hier geht es weiter</a></strong>
			</td>
		</tr>
	</table>
	<hr class="albumseparator" />
	<?php endforeach; ?>
	<h4 class="center">
		<?php for ($i = 0; $i<$pages; $i++): $j = $i+1; ?>
		<?php if ($j!=$page): ?>
		<a href="index.php?id=<?php echo $location; ?>&amp;page=<?php echo $j; ?>"><?php endif; ?><?php echo $j; ?><?php if ($j!=$page): ?></a>
		<?php endif; ?>
		<?php endfor; ?>
	</h4>
</div>