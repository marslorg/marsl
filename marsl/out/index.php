<?php
include_once(dirname(__FILE__)."/includes/basic.php");
include_once(dirname(__FILE__)."/includes/errorHandler.php");
include_once(dirname(__FILE__)."/modules/navigation.php");
include_once(dirname(__FILE__)."/modules/urlloader.php");
include_once(dirname(__FILE__)."/includes/dbsocket.php");
include_once(dirname(__FILE__)."/includes/config.inc.php");
include_once(dirname(__FILE__)."/user/auth.php");
include_once(dirname(__FILE__)."/user/role.php");

class Main {

	private $db;
	private $auth;

	public function __construct() {
		$this->db = new DB();
		$this->db->connect();
		$this->role = new Role($this->db);
		$this->auth = new Authentication($this->db, $this->role);
	}
	
	/*
	 * Initialize the frontend screen.
	 */
	public function display() {
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
		$config = new Configuration();
		date_default_timezone_set($config->getTimezone());
		$fbcomments = $config->getFBComments();
		
		$basic = new Basic($this->db, $this->auth, $this->role);
		$title = $basic->convertToHTMLEntities($basic->getTitle());
		$image = $basic->convertToHTMLEntities($basic->getImage());
		$serverName = $basic->convertToHTMLEntities($config->getClusterServer());
		$domain = $config->getDomain();
		$navigation = new Navigation($this->db, $this->auth, $this->role);
		$urlloader = new URLLoader($this->db, $this->auth, $this->role);
		$showContentForWeb = !$this->auth->isAppAllowed();
		
		require_once("template/index.tpl.php");
		
		$this->db->close();
		
	}
	
	private function displaySearchBox() {
		$basic = new Basic($this->db, $this->auth, $this->role);
		$searchList = array();
		$modules = $basic->getModules();
		foreach ($modules as $module) {
			$file = $module['file'];
			$class = $module['class'];
			include_once(dirname(__FILE__)."/modules/".$file.".php");
			$searchClass = new $class($this->db, $this->auth, $this->role);
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