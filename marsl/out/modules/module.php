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
}
?>