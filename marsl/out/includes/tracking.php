<?php
include_once(dirname(__FILE__)."/PiwikTracker.php");
include_once(dirname(__FILE__)."/config.inc.php");

class Tracking {
	public function doTrack($title) {
		$config = new Configuration();
		
		$piwik = new PiwikTracker(1, $config->getDomain()."/piwik/");
		$piwik->disableSendImageResponse();
		$piwik->doTrackPageView($title);
	}
}
?>