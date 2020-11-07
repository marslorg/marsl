<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../includes/basic.php");
include_once(dirname(__FILE__)."/../user/auth.php");
include_once(dirname(__FILE__)."/../user/role.php");
include_once(dirname(__FILE__)."/module.php");

class Newsletter implements Module {

	private $db;
	private $auth;

	public function __construct($db, $auth) {
		$this->db = $db;
		$this->auth = $auth;
	}

	/*
	 * Initiate the module's frontend view.
	 */
	public function display() {
		
	}
	
	/*
	 * Initiate the module's admin view.
	 */
	public function admin() {
		$authTime = time();
		$authToken = $this->auth->getToken($authTime);
		$role = new Role($this->db);
		$basic = new Basic($this->db, $this->auth);
		
		if ($this->auth->moduleAdminAllowed("newsletter", $role->getRole()) && $this->auth->moduleExtendedAllowed("newsletter", $role->getRole())) {
		
			if (!isset($_GET['action'])) {
				
				$temporaryKey = $basic->tempFileKey();
				
				$allRoles = $role->getRoles();
				
				$roles = array();
				
				foreach($allRoles as $curRole) {
					array_push($roles, array('role'=>htmlentities($curRole['role'], null, "ISO-8859-1"),'name'=>htmlentities($curRole['name'], null, "ISO-8859-1")));
				}
				
				require_once("template/newsletter.tpl.php");
			}
		}
	}
	
	/*
	 * Returns whether the module has a search function.
	 */
	public function isSearchable() {
		
	}
	
	/*
	 * Returns an array of the module's fulltext searchable types.
	 */
	public function getSearchList() {
		
	}
	
	/*
	 * Performs a fulltext search on the searchable attributes.
	 */
	public function search($query, $type) {
		
	}
	
	/*
	 * Returns whether the module has a tag function.
	 */
	public function isTaggable() {
		
	}
	
	/*
	 * Returns an array of the module's  taggable types.
	 */
	public function getTagList() {
		
	}
	
	/*
	 * Pushs a tag string to the module.
	 */
	public function addTags($tagString, $type, $news) {
		
	}
	
	/*
	 * Returns the tag string of the module.
	 */
	public function getTagString($type, $news) {
		
	}
	
	/*
	 * Returns an array of the tags.
	 */
	public function getTags($type, $news) {
		
	}
	
	/*
	 * Displays the information of a tag.
	 */
	public function displayTag($tagID, $type) {
		
	}
	
	/*
	 * Returns a page specific image.
	 */
	public function getImage() {
		
	}
	
	/*
	 *
	 */
	public function getTitle() {
		
	}
}
?>