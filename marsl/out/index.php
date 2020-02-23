<?php
include_once(dirname(__FILE__)."/includes/basic.php");
include_once(dirname(__FILE__)."/includes/errorHandler.php");
include_once(dirname(__FILE__)."/modules/navigation.php");
include_once(dirname(__FILE__)."/modules/urlloader.php");
include_once(dirname(__FILE__)."/includes/dbsocket.php");
include_once(dirname(__FILE__)."/includes/config.inc.php");

class Main {
	
	/*
	 * Initialize the frontend screen.
	 */
	public function display() {
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
		$config = new Configuration();
		date_default_timezone_set($config->getTimezone());
		$fbcomments = $config->getFBComments();
		$db = new DB();
		$db->connect();
		
		$basic = new Basic();
		$title = htmlentities($basic->getTitle(), null, "ISO-8859-1");
		$image = htmlentities($basic->getImage(), null, "ISO-8859-1");
		$domain = $config->getDomain();
		$navigation = new Navigation();
		$urlloader = new URLLoader();
		
		require_once("template/index.tpl.php");
		
		$db->close();
		
	}
	
	private function displaySearchBox() {
		$basic = new Basic();
		$searchList = array();
		$modules = $basic->getModules();
		foreach ($modules as $module) {
			$file = $module['file'];
			$class = $module['class'];
			include_once(dirname(__FILE__)."/modules/".$file.".php");
			$searchClass = new $class;
			if ($searchClass->isSearchable()) {
				$typeArray = $searchClass->getSearchList();
				foreach ($typeArray as $type) {
					array_push($searchList, array('class'=>$file, 'type'=>$type['type'], 'text'=>$type['text']));
				}
			}
		}
		
	
		require_once("template/searchbox.tpl.php");
	}
	
}

$display = new Main();
$display->display();
?>