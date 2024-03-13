<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<div class="gallery">
	<h4 class="pagination">
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
	<?php foreach($galleries as $gallery): ?>
	<span class="albumtable">
		<div class="albumrow">
			<div class="albuminformation">
				<img src="includes/graphics/square.gif" /> Fotos: <?php echo $gallery['photograph']; ?> | <?php echo $gallery['date']; ?>
			</div>
			<div class="albumdescription">
				<img src="<?php echo $gallery['picture']; ?>" class="albumpicture" /><?php echo $gallery['description']; ?> 
				<br /><br /><strong><a href="<?php echo $gallery['galleryURI']; ?>">Hier geht es weiter</a></strong>
			</div>
		</div>
	</span>
	<div class="albuminformationmobile">
		<img src="includes/graphics/square.gif" /> Fotos: <?php echo $gallery['photograph']; ?> | <?php echo $gallery['date']; ?>
	</div>
	<hr class="albumseparator" />
	<?php endforeach; ?>
	<h4 class="pagination">
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
</div>