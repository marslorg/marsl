<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<div class="post">
			
	<div class="post-title"><h1><?php echo $title; ?></h1></div>
	
	<div class="post-date"><?php echo $headline; ?></div>

	<div class="post-body">
		<?php if (($picture2=="empty")&&($picture1!="empty")): ?>
		<span class="teaserpicture">
			<img src="news/<?php echo $picture1; ?>" />
			<?php echo $photograph1; ?>
		</span>
		<?php endif; ?>
		<b><?php echo $city; ?> (<?php echo $authorName; ?>)&nbsp;-&nbsp;<?php echo $teaser; ?></b><br />
		<?php if ($picture2!="empty"): ?>
		<br />
		<span class="center">
			<div class="textpicture">
				<img src="news/<?php echo $picture2; ?>" />
				<div class="subtitle"><b><?php echo $subtitle2; ?><?php echo $photograph2; ?></b></div>
			</div>
		</span>
		<?php endif; ?>
		<br /><?php echo $text; ?><br /><br />
		<div class="clearer">&nbsp;</div>

	
	</div>

	<div class="post-meta">Datum: <?php echo $date; ?></div>			

</div>