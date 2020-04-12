<?php
include_once(dirname(__FILE__)."/errorHandler.php");
include_once(dirname(__FILE__)."/config.inc.php");

class DB {
	
	private $mysqllink;
	
	/*
	 * Connect to the database.
	 */
	public function connect() {
		$config = new Configuration();
		$this->mysqllink = mysqli_connect($config->getDBHost(), $config->getDBUser(), $config->getDBPass(), $config->getDBName());
	}
	
	/*
	 * Send a mysql query to the database and return the result.
	 */
	public function query($query) {
		$config = new Configuration();
		$res = mysqli_query($this->mysqllink, $query);
		return $res;
	}
	
	/*
	 * Send a mysql query to the database and return whether there is an existing row for this query.
	 */
	public function isExisting($query) {
		$config = new Configuration();
		$res = mysqli_query($this->mysqllink, $query);
		$numRows = mysqli_num_rows($res);
		
		return ($numRows > 0);
	}

	/*
	* Returns an array out of a mysql result.
	*/
	public function fetchArray($result) {
		return mysqli_fetch_array($result);
	}

	/*
	* Escapes a given string and returns the clean result.
	*/
	public function escapeString($dirt) {
		return mysqli_real_escape_string($this->mysqllink, $dirt);
	}

	/*
	* Returns the auto generated id used in the latest query.
	*/
	public function lastInsertedID() {
		return mysqli_insert_id($this->mysqllink);
	}

	/*
	* Returns the number of rows in the result
	*/
	public function getRowCount($result) {
		return mysqli_num_rows($result);
	}
	
	/*
	 * Close the connection to the database.
	 */
	public function close() {
		mysqli_close($this->mysqllink);
	}
}
?>