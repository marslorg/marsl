<?php 
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<!DOCTYPE HTML>
<!-- Original template bei arcsin for Wordpress under GPL. Can be viewed at: http://templates.arcsin.se/wp-demo/2009/06/freshmade-software/ -->
<html>
	<head>
		<title><?php echo $title; ?></title>
		<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
		<meta property="fb:app_id" content="<?php echo $fbcomments; ?>" />
		<?php if ($image!=null): ?>
		<meta property="og:image" content="<?php echo $domain; ?>/<?php echo $image; ?>" />
		<meta property="og:title" content="<?php echo $title; ?>" />
		<?php endif; ?>
		<script type="text/javascript" src="includes/jscripts/jquery/jquery.js"></script>
		<script type="text/javascript" src="includes/jscripts/jquery/jquery-ui.js"></script>
		<script type="text/javascript" src="includes/jscripts/slimbox/js/slimbox2.js"></script>
		<script type="text/javascript" src="http://bp.yahooapis.com/2.4.21/browserplus-min.js"></script>
		<script type="text/javascript" src="includes/jscripts/plupload/js/plupload.full.js"></script>
		<script type="text/javascript" src="includes/jscripts/plupload/js/jquery.plupload.queue/jquery.plupload.queue.frontend.js"></script>
		<script type="text/javascript" src="includes/jscripts/plupload/js/i18n/de.js"></script>
		<link rel="alternate" type="application/rss+xml" title="<?php echo $title; ?> - RSS Feed" href="<?php echo $domain; ?>/rss.php" />
		<link rel="stylesheet" href="includes/jscripts/slimbox/css/slimbox2.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="styles/style.css" type="text/css" />
		<link rel="stylesheet" href="styles/portal.css" type="text/css" />
		<link rel="stylesheet" href="includes/jscripts/plupload/js/jquery.plupload.queue/css/jquery.plupload.queue.css" type="text/css" />
	</head>
	<body>
	
		<div id="wrapper">
			<div id="header">
	
				<div id="logo">
					<img src="includes/graphics/logo.png" alt="Logo" />
					<h1 id="site-title"><a href="http://www.mlrecords.de">marsl CMS</a></h1>
					<h2 id="site-slogan">Standard Theme</h2>
				</div>

				<div id="topnav">
					<div class="moduletab_menu">
						<ul class="menu">
							<li><a href="<?php echo $domain; ?>/rss.php" target="_blank">RSS Feed</a></li>
							<li><a href="index.php?id=100">Disclaimer</a>
							<li><a href="index.php?id=74">Impressum</a></li>
						</ul>
					</div>
				</div>
				<div id="search">
					<?php $this->displaySearchBox(); ?>
				</div>
			</div>
			<div id="topmenu">
				<ul class="menu">
					<?php $navigation->display(); ?>
				</ul>
			</div>
		</div>
		<div id="content-menu_wrap">
			<div id="container-leftmenu-content">
				<?php $urlloader->display(); ?>
			</div>
		</div>
		<div id="footer-wrapper">

			<div class="center-wrapper">

				<div id="footer">

					<div class="left">
				
						<ul class="tabbed">
							<li>Home</li>
							<li class="page_item page-item-30">Impressum</li>
							<li class="page_item page-item-2">Kontakt</li>
							<li class="page_item page-item-4">Jobs</li>
						</ul>

						<div class="clearer">&nbsp;</div>

					</div>

					<div class="right">
						<a href="#top">Top ^</a>
					</div>
			
					<div class="clearer">&nbsp;</div>

				</div>

			</div>

		</div>
	</body>
</html>
