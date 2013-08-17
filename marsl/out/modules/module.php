<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");

/*
 * General interface for the module classes.
 */
interface Module {
	/*
	 * Initiate the module's frontend view.
	 */
	public function display();
	
	/*
	 * Initiate the module's admin view.
	 */
	public function admin();
	
	/*
	 * Returns whether the module has a search function.
	 */
	public function isSearchable();
	
	/*
	 * Returns an array of the module's fulltext searchable types.
	 */
	public function getSearchList();
	
	/*
	 * Performs a fulltext search on the searchable attributes.
	 */
	public function search($query, $type);
	
	/*
	 * Returns whether the module has a tag function.
	*/
	public function isTaggable();
	
	/*
	 * Returns an array of the module's  taggable types.
	*/
	public function getTagList();
	
	/*
	 * Pushs a tag string to the module.
	*/
	public function addTags($tagString, $type, $news);
	
	/*
	 * Returns the tag string of the module.
	*/
	public function getTagString($type, $news);
	
	/*
	 * Returns an array of the tags.
	 */
	public function getTags($type, $news);
	
	/*
	 * Displays the information of a tag.
	 */
	public function displayTag($tagID, $type);
}
?>