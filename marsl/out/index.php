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

		$config = new Configuration();
		date_default_timezone_set($config->getTimezone());
		$fbcomments = $config->getFBComments();
		$db = new DB();
		$db->connect();
		
		$basic = new Basic();
		$title = htmlentities($basic->getTitle());
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
		
	
		require_once("template/search.box.tpl.php");
	}
	
}

$display = new Main();
$display->display();
?>