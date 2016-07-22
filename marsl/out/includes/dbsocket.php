<?php
include_once(dirname(__FILE__)."/errorHandler.php");
include_once(dirname(__FILE__)."/config.inc.php");

class DB {
	
	/*
	 * Connect to the database.
	 */
	public function connect() {
		$config = new Configuration();
		$GLOBALS['dbConnection'] = mysqli_connect($config->getDBHost(), $config->getDBUser(), $config->getDBPass(), $config->getDBName());
	}
	
	/*
	 * Send a mysql query to the database and return the result.
	 */
	public function query($query) {
		$res = mysqli_query($GLOBALS['dbConnection'], $query);
		return $res;
	}
	
	/*
	 * Send a mysql query to the database and return whether there is an existing row for this query.
	 */
	public function isExisting($query) {
		$res = mysqli_query($GLOBALS['dbConnection'], $query);
		$numRows = $this->getCount($res);
		
		return ($numRows > 0);
	}
	
	/*
	 * Close the connection to the database.
	 */
	public function close() {
		mysqli_close($GLOBALS['dbConnection']);
	}
	
	public function escape($sqlParam) {
		return mysqli_real_escape_string($GLOBALS['dbConnection'], $sqlParam);
	}
	
	public function fetchArray($sqlResult) {
		return mysqli_fetch_array($sqlResult);
	}
	
	public function getLastID() {
		return mysqli_insert_id($GLOBALS['dbConnection']);
	}
	
	public function getCount($result) {
		return mysqli_num_rows($result);
	}
}
?>