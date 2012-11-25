<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<?php if (isset($_GET['autostart'])): ?>
<script type="text/javascript">
	jQuery(function($) {
		$("#first-thumb").click();
	});
</script>
<?php endif; ?>
<div class="gallery">
	Fotos: <?php echo $photograph; ?><br /><br />
	<table class="albumtable">
		<tr>
			<?php 
			$i = 0;
			$j = 0;
			foreach($pictures as $picture): $i++; $j++; ?>
			<td class="center">
				<a href="<?php echo $picture['picture']; ?>" rel="lightboxgallery" title="<?php echo $picture['subtitle']; ?> Foto: <?php echo $photograph; ?>" <?php if(isset($_GET['autostart'])&&($j==1)): ?>id="first-thumb"<?php endif; ?>>
					<img border="0" src="<?php echo $picture['thumb']; ?>">
				</a>
			</td>
			<?php if ($i==3): $i=0; ?>
			</tr>
			<tr>
			<?php endif; ?>
			<?php endforeach; ?>
		</tr>
	</table>
</div>