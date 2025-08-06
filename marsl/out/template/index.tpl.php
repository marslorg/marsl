<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
?>
<!DOCTYPE HTML>
<html lang="de">
	<head>
		<title><?php echo $title; ?></title>
		<base href="<?php echo $baseURL; ?>/" />
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="mobile-web-app-capable" content="yes" />
		<meta name="apple-itunes-app" content="app-id=1570808940" />
		<meta name="google-play-app" content="app-id=de.music2web.www" />
		<link rel="icon" sizes="192x192" href="includes/graphics/icon_192x192.png" />
		<link rel="apple-touch-icon" sizes="192x192" href="includes/graphics/icon_192x192.png" />
		<link rel="android-touch-icon" href="includes/graphics/icon_192x192.png" />
		<?php if ($image != null): ?>
		<meta property="og:image" content="<?php echo $baseURL; ?>/<?php echo $image; ?>" />
		<meta property="og:title" content="<?php echo $title; ?>" />
		<?php endif; ?>
		<script type="text/javascript" src="includes/jscripts/jquery/jquery.js"></script>
		<script type="text/javascript" src="includes/jscripts/jquery/jquery-ui.js"></script>
		<script type="text/javascript" src="includes/jscripts/photoswipe/photoswipe.min.js"></script>
		<script type="text/javascript" src="includes/jscripts/photoswipe/photoswipe-ui-default.min.js"></script>
		<script type="text/javascript" src="includes/jscripts/plupload/js/plupload.full.min.js"></script>
		<script type="text/javascript" src="includes/jscripts/plupload/js/jquery.plupload.queue/jquery.plupload.queue.js"></script>
		<script type="text/javascript" src="includes/jscripts/plupload/js/i18n/de.js"></script>
		<link rel="alternate" type="application/rss+xml" title="<?php echo $title; ?> - RSS Feed" href="<?php echo $baseURL; ?>/rss.php" />
		<link rel="icon" href="includes/graphics/favicon.ico" type="image/x-icon" />
		<link rel="stylesheet" href="styles/style.css?v=34" type="text/css" />
		<link rel="stylesheet" href="styles/menu.css?v=6" type="text/css" />
		<link rel="stylesheet" href="styles/portal.css" type="text/css" />
		<link rel="stylesheet" href="styles/mobile.css?v=18" type="text/css" media="all and (max-width: 1023px)" />
		<link rel="stylesheet" href="includes/jscripts/plupload/js/jquery.plupload.queue/css/jquery.plupload.queue.css" type="text/css" />
		<link rel="stylesheet" href="includes/shariff/shariff.complete.css" type="text/css" />
		<link rel="stylesheet" href="includes/jscripts/photoswipe/photoswipe.css" type="text/css" />
		<link rel="stylesheet" href="includes/jscripts/photoswipe/default-skin/default-skin.css" type="text/css" />
		<link rel="stylesheet" href="node_modules/smart-app-banner/dist/smart-app-banner.css?v=2" type="text/css" media="screen">
		<link rel="manifest" href="./manifest.json?v=2" />
	</head>
	<body>
		<input type="hidden" id="serverName" name="serverName" value="<?php echo $serverName; ?>" />
		<?php if ($showContentForWeb): ?>
		<div class="head">
			<div class="mainhead">
				<a href="https://www.music2web.de">
					<img src="includes/graphics/logo.png" alt="Home" />
				</a>
				<?php $this->displaySearchBox(); ?>
			</div>
			<input type="checkbox" id="menu_responsive" />
			<label for="menu_responsive" class="menu_responsive_label">
				<span>&#9776;</span>
				Navigation
			</label>
			<div class="menu">
				<ul>
					<?php $this->navigation->display(); ?>
				</ul>
			</div>
			<hr class="naviseparator" />
		</div>
		<?php endif; ?>
		<div class="body">
			<div class="content" <?php if (!$showContentForWeb): ?>style="margin-top: 0px;"<?php endif; ?>>
				<?php $this->urlLoader->display(); ?>
			</div>
			<?php if ($showContentForWeb): ?>
			<div class="footer">
				<a href="https://www.music2web.de/jobs-469">Jobs</a> | <a href="https://www.music2web.de/kontakt-407">Kontakt</a> | <a href="https://www.music2web.de/datenschutz-738">Datenschutzerkl&auml;rung</a> | <a href="https://www.music2web.de/impressum-186">Impressum</a>
				<div class="center">
					<a href="https://www.instagram.com/music2web" target="_blank"><img src="includes/graphics/socialicons/instagram.png" height="40px" alt="Folgt uns auf Instagram" /></a>
					<a href="https://www.facebook.com/music2web" target="_blank"><img src="includes/graphics/socialicons/facebook.png" height="40px" alt="Folgt uns auf Facebook" /></a>
					<a href="https://apps.apple.com/de/app/music2web-de/id1570808940" target="_blank"><img src="includes/graphics/app/apple.svg" height="40px" alt="Laden im App Store" /></a>
					<a href="https://play.google.com/store/apps/details?id=de.music2web.www&gl=DE" target="_blank"><img src="includes/graphics/app/google.png" height="40px" alt="Jetzt bei Google" /></a>
				</div>
			</div>
			<div id="footer-permission">
				<span id="description">
					Bleibe immer über aktuelle Nachrichten von uns informiert und abonniere jetzt unsere Push-Meldungen.
				</span>
				<span id="buttons">
					<span id="accept"><a href="javascript:void(0)" title="Ja">Ja</a></span>
					<span id="reject"><a href="javascript:void(0)" title="Nein">Nein</a></span>
				</span>
			</div>
			<?php endif; ?>
		</div>
		<!--  immediately before </body> -->
		<script src="includes/shariff/shariff.complete.js"></script>
		<?php if ($showContentForWeb): ?>
		<script src="node_modules/smart-app-banner/dist/smart-app-banner.js"></script>
		<script src="smartBanner.js?v=2"></script>
		<?php endif; ?>
		<script src="app.js"></script>
	</body>
</html>