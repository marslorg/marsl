<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>";
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">
	<channel>
		<title><?php echo $feedtitle; ?></title>
		<link><?php echo $feedlink; ?></link>
		<description><?php echo $feeddescription; ?></description>
		<atom:link href="<?php echo $feedlink; ?>/rss.php" rel="self" type="application/rss+xml" />
		<?php foreach($items as $item): ?>
		<item>
			<title><?php echo $item['title']; ?></title>
			<description>
				<p><?php echo $item['teaser']; ?></p>
				<p><?php echo $item['text']; ?></p>
			</description>
			<link><?php echo $item['link']; ?></link>
			<guid isPermaLink="true"><?php echo $item['link']; ?></guid>
			<pubDate><?php echo $item['date']; ?></pubDate>
			<?php $tags=$item['tags']; ?>
			<?php foreach($tags as $tag): ?>
			<category><?php echo $tag; ?></category>
			<?php endforeach; ?>
			<?php if ($item['newsPicture']!="empty"||$item['teaserPicture']!="empty"): ?>
			<media:content url="<?php if ($item['newsPicture']!="empty"): ?><?php echo $item['newsPicture']; ?><?php endif; ?><?php if ($item['newsPicture']=="empty"): ?><?php echo $item['teaserPicture']; ?><?php endif; ?>">
				<media:thumbnail url="<?php if ($item['teaserPicture']!="empty"): ?><?php echo $item['teaserPicture']; ?><?php endif; ?><?php if ($item['teaserPicture']=="empty"): ?><?php echo $item['newsPicture']; ?><?php endif; ?>" />
			</media:content>
			<?php endif; ?>
		</item>
		<?php endforeach; ?>
	</channel>
</rss>