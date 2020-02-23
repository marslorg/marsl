<?php 
include_once (dirname(__FILE__)."/../config.inc.php");

class GooglePlus {
	private $apiURL = 'https://www.googleapis.com/plus/v1/people/';
	
	public function getCount() {
		$config = new Configuration();
		
		$result = 0;
		$fanCountJSON = file_get_contents($this->apiURL . $config->getGPPageID() . '?key=' . $config->getGPAPIKey());
		if ($fanCountJSON === false) {
			$result = 0;
		}
		else { 
			$fanCountArray = json_decode($fanCountJSON, true);
			$fanCount = $fanCountArray['circledByCount'];
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
$googlePlus = new GooglePlus();
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
				<li class="count-googleplus">
					<a data-color-hover="#DC493C" style="background-color: #DC493C; padding: 10px 10px 10px 10px;" class="icon" href="https://plus.google.com/<?php echo $config->getGPPageID(); ?>" target="_blank" rel="nofollow">
						<span class="fa fa-fw fa-google-plus" style="font-size:24px; color:#fff" data-color-hover="#fff"></span>
						<span class="items">
							<span data-color-hover="#fff" class="count" style="font-family:Verdana,Arial,Helvetica,Sans-Serif;font-size:16px; color:#fff"><?php echo $googlePlus->getCount(); ?></span>
							<span data-color-hover="#fff" class="label" style="font-family:Verdana,Arial,Helvetica,Sans-Serif;font-size:11px; color:#fff">Fans</span>
						</span>
					</a>
				</li>
			</ul>
		</div>
	</body>
</html>