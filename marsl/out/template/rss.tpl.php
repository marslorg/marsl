<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
echo "<?xml version=\"1.0\" encoding=\"windows-1252\"?>";
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<title><?php echo $feedtitle; ?></title>
		<link><?php echo $feedlink; ?></link>
		<description><?php echo $feeddescription; ?></description>
		<atom:link href="<?php echo $feedlink; ?>/rss.php" rel="self" type="application/rss+xml" />
		<?php foreach($items as $item): ?>
		<item>
			<title><?php echo $item['title']; ?></title>
			<description><?php echo $item['teaser']; ?></description>
			<link><?php echo $item['link']; ?></link>
			<guid isPermaLink="true"><?php echo $item['link']; ?></guid>
			<pubDate><?php echo $item['date']; ?></pubDate>
		</item>
		<?php endforeach; ?>
	</channel>
</rss>