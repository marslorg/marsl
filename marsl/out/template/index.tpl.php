<?php 
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
include_once (dirname(__FILE__)."/../includes/tracking.php");
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title><?php echo $title; ?></title>
		<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="mobile-web-app-capable" content="yes" />
		<link rel="icon" sizes="192x192" href="includes/graphics/mobicon.png" />
		<link rel="apple-touch-icon" sizes="192x192" href="includes/graphics/mobicon.png" />
		<?php if ($image!=null): ?>
		<meta property="og:image" content="<?php echo $domain; ?>/<?php echo $image; ?>" />
		<meta property="og:title" content="<?php echo $title; ?>" />
		<?php endif; ?>
		<script type="text/javascript" src="includes/jscripts/jquery/jquery.js"></script>
		<script type="text/javascript" src="includes/jscripts/jquery/jquery-ui.js"></script>
		<script type="text/javascript" src="includes/jscripts/slimbox/js/slimbox2.js"></script>
		<script type="text/javascript" src="includes/jscripts/plupload/js/plupload.full.min.js"></script>
		<script type="text/javascript" src="includes/jscripts/plupload/js/jquery.plupload.queue/jquery.plupload.queue.js"></script>
		<script type="text/javascript" src="includes/jscripts/plupload/js/i18n/de.js"></script>
		<link rel="alternate" type="application/rss+xml" title="<?php echo $title; ?> - RSS Feed" href="<?php echo $domain; ?>/rss.php" />
		<link rel="icon" href="includes/graphics/favicon.ico" type="image/x-icon" />
		<link rel="stylesheet" href="includes/jscripts/slimbox/css/slimbox2.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="styles/style.css?v=26" type="text/css" />
		<link rel="stylesheet" href="styles/menu.css?v=6" type="text/css" />
		<link rel="stylesheet" href="styles/portal.css" type="text/css" />
		<link rel="stylesheet" href="styles/mobile.css?v=8" type="text/css" media="all and (max-width: 1023px)" />
		<link rel="stylesheet" href="includes/jscripts/plupload/js/jquery.plupload.queue/css/jquery.plupload.queue.css" type="text/css" />
		<link rel="stylesheet" href="includes/shariff/shariff.complete.css" type="text/css" />
	</head>
	<body>
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
					<?php $navigation->display(); ?>
				</ul>
			</div>
			<hr class="naviseparator" />
		</div>
		<div class="body">
			<div class="content">
				<?php $urlloader->display(); ?>
			</div>
			<div class="right_box">
				<iframe src="includes/socialcounters/facebook.php" scrolling="no" style="border:none;overflow:hidden;padding: 0px 0px 0px 0px;" width="75px" height="75px" name="Facebook Counter" title="Facebook Counter"></iframe><br />
				<iframe src="includes/socialcounters/twitter.php" scrolling="no" style="border:none;overflow:hidden;padding: 0px 0px 0px 0px;" width="75px" height="75px" name="Facebook Counter" title="Facebook Counter"></iframe><br />
				<div class="right_ads">
					<!-- Anzeigen:<br />-->
					<?php
					$hostname = strtolower(gethostbyaddr($_SERVER['REMOTE_ADDR']));
					$googlebot = (substr($hostname, -10) == "google.com") || (substr($hostname, -13) == "googlebot.com");
					?>
					<?php
		
				 	// Konfiguration
				
				  	$m_lt_check       = "0";      # Erzeugt beim Wert 1 eine Testausgabe
				
				  	$m_lt_res_pre     = "";    # HTML-Code vor der Ausgabe
				  	$m_lt_res_suf     = "";   # HTML-Code nach der Ausgabe
				  	$m_lt_res_sep     = "<br />"; # HTML-Code zwichen den Links, falls mehr als ein Link gebucht wurde
				
				  	$m_lt_res_charset = "UTF-8";  # Zeichensatz
				
				 	// !!! Folgender Code sollte nicht ge�ndert werden !!!
				
	  			  	$m_lt_url='http://serv7.buywords.de/mod/linktrade/res.html?v=2&account_id=8003&domain='.$_SERVER['HTTP_HOST'].'&url='.urlencode($_SERVER['REQUEST_URI']).'&qs='.urlencode($_SERVER['QUERY_STRING']).'&ip='.$_SERVER['REMOTE_ADDR'].'&charset='.urlencode($m_lt_res_charset).'&res_check='.$m_lt_check.'&res_pre='.urlencode($m_lt_res_pre).'&res_suf='.urlencode($m_lt_res_suf).'&res_sep='.urlencode($m_lt_res_sep); $m_lt_res='';
				  
				  	if(function_exists('curl_init')) {
				
				   		$m_lt_handle=curl_init();
				
					   	curl_setopt($m_lt_handle,CURLOPT_URL,$m_lt_url);
				   		curl_setopt($m_lt_handle,CURLOPT_RETURNTRANSFER,1);
				   		curl_setopt($m_lt_handle,CURLOPT_TIMEOUT,3);
				   		curl_setopt($m_lt_handle,CURLOPT_CONNECTTIMEOUT,3);
				
				   		$m_lt_res=curl_exec($m_lt_handle); curl_close($m_lt_handle);
				
				  	}
				  	elseif(@ini_get('allow_url_fopen')) {
				
				   		$m_lt_res=@file_get_contents($m_lt_url);
				
				  	}
				  
				  	if ($googlebot) {
						$m_lt_res = str_replace('<a', '<a rel="nofollow"', $m_lt_res);
				  	}
				  
				  	$m_lt_res = str_replace('<a', '<a target="_blank"', $m_lt_res);
				
				  	if(strpos($m_lt_res,'<m_lt_code>')) {
				
				   		echo trim(str_replace('<m_lt_code>','',$m_lt_res));
				
				  	}
				
				 	//
				
					?>
					<?php if ($googlebot): ?>
						<!-- <a href="https://extrem16.de" rel="nofollow" target="_blank">Extrem16</a>--> 
					<?php endif; ?>
					<?php if (!$googlebot): ?>					
						<!-- <a href="https://extrem16.de" target="_blank">Extrem16</a>--> 
					<?php endif; ?>
					<?php if (((!isset($_GET['id']))&&(!isset($_GET['scope'])))||(isset($_GET['id'])&&($_GET['id']=="178"))): ?>
					<?php endif; ?>
				</div>
			</div>
			<div class="footer">
				<a href="https://www.music2web.de/index.php?id=469">Jobs</a> | <a href="https://www.music2web.de/index.php?id=407">Kontakt</a> | <a href="https://www.music2web.de/index.php?id=738">Datenschutzerkl&auml;rung</a> | <a href="https://www.music2web.de/index.php?id=186">Impressum</a>
			</div>
		</div>
		<!--  immediately before </body> -->
		<script src="includes/shariff/shariff.complete.js"></script>
		<?php
			$tracking = new Tracking();
			$tracking->doTrack($title);
		?>
	</body>
</html>