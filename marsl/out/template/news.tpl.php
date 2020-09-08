<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<div class="news">
	<span class="newstable">
		<div class="newsrow">
			<div class="newsinformation">
				<h3>Taglist:</h3>
				<?php foreach($moduleTags as $moduleTag):
				$tags = $moduleTag['tags'];
				?>
				<b><?php echo $moduleTag['name']; ?></b><br />
				<?php foreach($tags as $tag): ?>
				<a href="index.php?tag=<?php echo $tag['id']; ?>&amp;scope=<?php echo $moduleTag['type']; ?>"><?php echo htmlentities($tag['tag'], null, "UTF-8"); ?></a><br />
				<?php endforeach; ?>
				<br />
				<?php endforeach; ?>
				<br />
				<div class="newsbottom">
					<div class="shariff" data-backend-url="includes/shariff" data-url="<?php echo $url; ?>" data-services="[&quot;facebook&quot;,&quot;twitter&quot;]" data-theme="standard" data-orientation="vertical"></div>
					<br /><img src="includes/graphics/square.gif" /> Datum: <?php echo $date; ?>
				</div>
			</div>
			<div class="newscontent">
				<h3 class="headline"><?php echo $headline; ?></h3>
				<h2><?php echo $title; ?></h2>
				<?php if (($picture2=="empty")&&($picture1!="empty")): ?>
				<span class="teaserpicture">
					<img src="news/<?php echo $picture1; ?>" />
					<?php echo $photograph1; ?>
				</span>
				<?php endif; ?>
				<b><?php echo $city; ?> (<?php echo $authorName; ?>)&nbsp;<img src="includes/graphics/square.gif" />&nbsp;&nbsp;&nbsp;<?php echo $teaser; ?></b><br />
				<?php if ($picture2!="empty"): ?>
				<br />
				<span class="center">
					<div class="textpicture">
						<img src="news/<?php echo $picture2; ?>" />
						<div class="subtitle"><b><?php echo $subtitle2; ?><?php echo $photograph2; ?></b></div>
					</div>
				</span>
				<?php endif; ?>
				<br /><?php echo $text; ?>
			</div>
			<div class="newsinformationmobile">
				<br /><div class="shariff" data-backend-url="includes/shariff" data-url="<?php echo $url; ?>" data-services="[&quot;facebook&quot;,&quot;twitter&quot;]" data-theme="standard" data-orientation="horizontal"></div>
				<b>Taglist:</b><br />
				<?php foreach($moduleTags as $moduleTag):
				$tags = $moduleTag['tags'];
				?>
				<b><?php echo $moduleTag['name']; ?>: </b>
				<?php foreach($tags as $tag): ?>
				<a href="index.php?tag=<?php echo $tag['id']; ?>&amp;scope=<?php echo $moduleTag['type']; ?>"><?php echo htmlentities($tag['tag'], null, "UTF-8"); ?></a> 
				<?php endforeach; ?>
				<br />
				<?php endforeach; ?>
				<br />
				<div class="newsbottom">
					<img src="includes/graphics/square.gif" /> Datum: <?php echo $date; ?>
				</div>
			</div>
		</div>
	</span>
	<hr class="newsseparator" />
</div>