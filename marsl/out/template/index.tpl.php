<?php 
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title><?php echo $title; ?></title>
		<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
		<meta property="fb:app_id" content="<?php echo $fbcomments; ?>" />
		<?php if ($image!=null): ?>
		<meta property="og:image" content="<?php echo $domain; ?>/<?php echo $image; ?>" />
		<?php endif; ?>
		<script type="text/javascript" src="includes/jscripts/jquery/jquery.js"></script>
		<script type="text/javascript" src="includes/jscripts/jquery/jquery-ui.js"></script>
		<script type="text/javascript" src="includes/jscripts/slimbox/js/slimbox2.js"></script>
		<link rel="alternate" type="application/rss+xml" title="<?php echo $title; ?> - RSS Feed" href="<?php echo $domain; ?>/rss.php" />
		<link rel="icon" href="includes/graphics/favicon.ico" type="image/x-icon" />
		<link rel="stylesheet" href="includes/jscripts/slimbox/css/slimbox2.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="styles/style.css" type="text/css" />
		<link rel="stylesheet" href="styles/menu.css" type="text/css" />
		<link rel="stylesheet" href="styles/portal.css" type="text/css" />
	</head>
	<body>
		<div class="mainhead">
			<a href="http://www.music2web.de">
				<img src="includes/graphics/logo.png" alt="Home" />
			</a>
			<?php $this->displaySearchBox(); ?>
		</div>
		<div class="menu">
			<ul>
				<?php $navigation->display(); ?>
			</ul>
		</div>
		<hr class="naviseparator" />
		<div class="content">
			<?php $urlloader->display(); ?>
		</div>
		<div class="right_box">
			<div id="fb-root"></div>
			<script>(function(d, s, id) {
			  var js, fjs = d.getElementsByTagName(s)[0];
			  if (d.getElementById(id)) {return;}
			  js = d.createElement(s); js.id = id;
			  js.src = "//connect.facebook.net/de_DE/all.js#xfbml=1";
			  fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));</script>
			
			<div class="fb-like-box" data-href="http://www.facebook.com/music2web" data-width="230" data-show-faces="true" data-stream="true" data-header="true"></div>
			<div class="right_ads">
				Anzeigen:<br />
				<a href="http://de.dressfirst.com/Brautkleider-c2/" target="_blank">Brautkleider</a><br />
				<a href="http://se.dressfirst.com/Brollopsklanningar-c2/" target="_blank">br&ouml;llopskl&auml;nning</a><br />
				<a href="http://standesamtverzeichnis.de/" target="_blank">Standesamt</a><br />
				<a href="http://www.giga-music.de/" target="_blank">Konzert Tickets</a><br />
				<a href="http://www.infantologie.de/love/lovesongs.php" target="_blank">Songs &uuml;ber die Liebe</a><br />
				<a href="http://www.gs-kosmetik.de" target="_blank">Jafra Kosmetik</a>
			</div>
		</div>
		<div class="footer">
			<a href="http://www.music2web.de/index.php?id=469">Jobs</a> | <a href="http://www.music2web.de/index.php?id=407">Kontakt</a> | <a href="http://www.music2web.de/index.php?id=186">Impressum</a>
		</div>
		<div class="advertiser">Anzeigen: 
			<?php
	
			 // Konfiguration
			
			  $m_lt_check       = "0";      # Erzeugt beim Wert 1 eine Testausgabe
			
			  $m_lt_res_pre     = "";    # HTML-Code vor der Ausgabe
			  $m_lt_res_suf     = "";   # HTML-Code nach der Ausgabe
			  $m_lt_res_sep     = " "; # HTML-Code zwichen den Links, falls mehr als ein Link gebucht wurde
			
			  $m_lt_res_charset = "UTF-8";  # Zeichensatz
			
			 // !!! Folgender Code sollte nicht geändert werden !!!
			
			  $m_lt_url='http://serv5.seomate.de/mod/linktrade/res.html?v=2&account_id=993&domain='.$_SERVER['HTTP_HOST'].'&url='.urlencode($_SERVER['REQUEST_URI']).'&qs='.urlencode($_SERVER['QUERY_STRING']).'&ip='.$_SERVER['REMOTE_ADDR'].'&charset='.urlencode($m_lt_res_charset).'&res_check='.$m_lt_check.'&res_pre='.urlencode($m_lt_res_pre).'&res_suf='.urlencode($m_lt_res_suf).'&res_sep='.urlencode($m_lt_res_sep); $m_lt_res='';
			
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
			
			  if(strpos($m_lt_res,'<m_lt_code>')) {
			
			   echo trim(str_replace('<m_lt_code>','',$m_lt_res));
			
			  }
			
			 //
			
			?>
		</div>
		<!-- PowerPhlogger Code START -->
		<script language="JavaScript" type="text/javascript" src="pphlogger.js"></script>
		<noscript><img alt="" src="pphlogger/pphlogger.php?id=Music2Web"></noscript>
		<!-- PowerPhlogger Code END -->
		<!-- Clickheat Code START -->
		<script type="text/javascript" src="http://www.music2web.de/clickheat/js/clickheat.js"></script><script type="text/javascript"><!--
		clickHeatSite = 'Music2Web.de';clickHeatGroup = encodeURIComponent(window.location.pathname+window.location.search);clickHeatServer = 'http://www.music2web.de/clickheat/click.php';initClickHeat(); //-->
		</script>
		<!--  Clickheat Code END -->
	</body>
</html>