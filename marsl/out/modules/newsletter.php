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
	private $role;

	public function __construct($db, $auth, $role) {
		$this->db = $db;
		$this->auth = $auth;
		$this->role = $role;
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
		$basic = new Basic($this->db, $this->auth, $this->role);
		
		if ($this->auth->moduleAdminAllowed("newsletter", $this->role->getRole()) && $this->auth->moduleExtendedAllowed("newsletter", $this->role->getRole())) {
		
			if (!isset($_GET['action'])) {
				
				$temporaryKey = $basic->tempFileKey();
				
				$allRoles = $this->role->getRoles();
				
				$roles = array();
				
				foreach($allRoles as $curRole) {
					array_push($roles, array('role'=>htmlentities($curRole['role'], null, "UTF-8"),'name'=>htmlentities($curRole['name'], null, "UTF-8")));
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