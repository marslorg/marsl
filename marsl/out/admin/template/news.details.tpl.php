<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
?>
<table class="newstable">
	<tr>
		<td class="newsinformation">
			Autor: <?php echo $authorName; ?><br />
			IP: <?php echo $authorIP; ?><br />
			Kategorie: <?php echo $location; ?><br />
			Datum: <?php echo $date; ?><br />
			eingereicht am: <?php echo $postdate; ?><br />
			<?php if($submitLink): ?>
			<a href="index.php?var=module&amp;module=news&amp;action=details&amp;do=submit&amp;id=<?php echo $id; ?>&amp;time=<?php echo $authTime; ?>&amp;token=<?php echo $authToken; ?>">Freischalten</a> 
			<?php endif; ?>
			<?php if($editLink): ?>
			<a href="index.php?var=module&amp;module=news&amp;action=edit&amp;id=<?php echo $id; ?>">Editieren</a> 
			<a href="index.php?var=module&amp;module=news&amp;action=details&amp;do=del&amp;id=<?php echo $id; ?>&amp;time=<?php echo $authTime; ?>&amp;token=<?php echo $authToken; ?>" onclick="return confirm('M&ouml;chtest du den Eintrag wirklich l&ouml;schen?')">L&ouml;schen</a>
			<?php endif; ?>
		</td>
		<td class="news">
			<h3 class="headline"><?php echo $headline; ?></h3>
			<h2><?php echo $title; ?></h2>
			<?php if(($picture2=="empty")&&($picture1!="empty")): ?>
			<span class="teaserpicture">
				<img src="../news/<?php echo $picture1; ?>" />
				<?php echo $photograph1; ?>
			</span>
			<?php endif; ?>
			<b><?php echo $city; ?>&nbsp;<img src="../includes/graphics/square.gif" />&nbsp;&nbsp;&nbsp;<?php echo $teaser; ?></b><br />
			<?php if($picture2!="empty"): ?>
			<br />
			<span class="center">
				<div class="textpicture">
					<img src="../news/<?php echo $picture2; ?>" />
					<div class="subtitle"><b><?php echo $subtitle2; ?><?php echo $photograph2; ?></b></div>
				</div>
			</span>
			<?php endif; ?>
			<br /><?php echo $text; ?>
		</td>
	</tr>
</table>
<hr class="newsseparator" />