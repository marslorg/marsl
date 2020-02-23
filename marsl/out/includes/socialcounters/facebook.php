<?php 
include_once (dirname(__FILE__)."/../config.inc.php");

class Facebook {
	private $apiURL = 'https://graph.facebook.com';
	
	private function getAccessToken() {
		$config = new Configuration();
		$url = sprintf(
				'%s/oauth/access_token?client_id=%s&client_secret=%s&grant_type=client_credentials',
			$this->apiURL,
			$config->getFBAppID(),
			$config->getFBAppSecret()
		);
		$accessTokenJSON = file_get_contents($url);
		if ($accessTokenJSON === false) {
			return '';
		}
		else {
			$accessTokenArray = json_decode($accessTokenJSON, true);
			return $accessTokenArray['access_token'];
		}
	}
	
	public function getCount() {
		$config = new Configuration();
		//$accessToken = $this->getAccessToken();
		$accessToken = "EAABZBWrDehpIBAOkONrmDLXegKKpkzdrOqaNCYD6noqB2DELueySapzMZBznKaMopfWHhCoevBPJ4l7Eb2xcLDGFr9hoiy1eVdje59TSQF9TRHmvzDJcTTOLPzgN2ZAiknRo05e2ZBP8iqHkCOWQiIpTOvzZA7EBFXzVTVeLEHAZDZD";
		$url = sprintf(
			'%s/%s?fields=fan_count&access_token=%s',
			$this->apiURL,
			$config->getFBPageID(),
			$accessToken
		);
		
		$result = 0;
		$fanCountJSON = file_get_contents($url);
		if ($fanCountJSON === false) {
			$result = 0;
		}
		else { 
			$fanCountArray = json_decode($fanCountJSON, true);
			$fanCount = $fanCountArray['fan_count'];
			if ($fanCount > 999 && $fanCount <= 999999) {
				$result = floor($fanCount / 1000) . 'K';
			}
			elseif ($fanCount > 999999) {
				$result = floor ($fanCount / 1000000) . 'M';
			}
			else {
				$result = $fanCount;
			}
		}
		return $result;
	}
}
$facebook = new Facebook();
$config = new Configuration();
?>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="assets/css/silicon-counters.css" />
		<link rel="stylesheet" type="text/css" href="assets/font-awesome/css/font-awesome.css" />
		<script type="text/javascript" src="../jscripts/jquery/jquery.js"></script>
		<script type="text/javascript" src="assets/js/silicon-counters.js?v=1"></script>
	</head>
	<body style="margin:0">
		<div class="silicon_counters">
			<ul class="sc_vertical">
				<li class="count-facebook">
					<a data-color-hover="#5E80BF" style="background-color: #3B5998; padding: 3px 3px 3px 3px;" class="icon" href="https://www.facebook.com/<?php echo $config->getFBPageID(); ?>" target="_blank" rel="nofollow">
						<span class="fa fa-fw fa-facebook-official" style="font-size:55px; color:#fff" data-color-hover="#fff"></span>
						<!-- <span class="items">
							<span data-color-hover="#fff" class="count" style="font-family:Verdana,Arial,Helvetica,Sans-Serif;font-size:16px; color:#fff"><?php echo $facebook->getCount(); ?></span>
							<span data-color-hover="#fff" class="label" style="font-family:Verdana,Arial,Helvetica,Sans-Serif;font-size:11px; color:#fff">Fans</span>
						</span>-->
					</a>
				</li>
			</ul>
		</div>
	</body>
</html>