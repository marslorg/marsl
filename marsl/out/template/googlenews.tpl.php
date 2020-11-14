<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
	<?php foreach($items as $item):?>
	<url>
		<loc><?php echo $item['link']; ?></loc>
		<news:news>
		<news:publication>
			<news:name>Music2Web.de</news:name>
			<news:language>de</news:language>
		</news:publication>
		<news:publication_date><?php echo $item['date']; ?></news:publication_date>
		<news:title><?php echo $item['title']; ?></news:title>
		<news:keywords>entertainment, music, celebrities, arts, lifestyle, culture</news:keywords>
		</news:news>
	</url>
	<?php endforeach; ?>
</urlset>