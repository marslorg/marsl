<?php 
include_once (dirname(__FILE__)."/../config.inc.php");

class Twitter {
	private $apiURL = 'https://cdn.syndication.twimg.com/widgets/followbutton/info.json?screen_names=';
	
	public function getCount() {
		$config = new Configuration();
		$result = 0;
		$fanCountJSON = file_get_contents($this->apiURL . $config->getTwitterPageID());
		if ($fanCountJSON === false) {
			$result = 0;
		}
		else { 
			$fanCountArray = json_decode($fanCountJSON, true);
			$fanCount = $fanCountArray[0]['followers_count'];
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
$twitter = new Twitter();
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
				<li class="count-twitter">
					<a data-color-hover="#0084b4" style="background-color: #33ccff; padding: 3px 3px 3px 3px;" class="icon" href="https://www.twitter.com/<?php echo $config->getTwitterPageID(); ?>" target="_blank" rel="nofollow">
						<span class="fa fa-fw fa-twitter" style="font-size:55px; color:#fff" data-color-hover="#fff"></span>
						<!--<span class="items">
							<span data-color-hover="#fff" class="count" style="font-family:Verdana,Arial,Helvetica,Sans-Serif;font-size:16px; color:#fff"><?php echo $twitter->getCount(); ?></span>
							<span data-color-hover="#fff" class="label" style="font-family:Verdana,Arial,Helvetica,Sans-Serif;font-size:11px; color:#fff">Fans</span>
						</span>-->
					</a>
				</li>
			</ul>
		</div>
	</body>
</html>