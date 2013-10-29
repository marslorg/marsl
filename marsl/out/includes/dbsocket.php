<?php
include_once(dirname(__FILE__)."/errorHandler.php");
include_once(dirname(__FILE__)."/config.inc.php");

class DB {
	
	/*
	 * Connect to the database.
	 */
	public function connect() {
		$config = new Configuration();
		@mysql_connect($config->getDBHost(), $config->getDBUser(), $config->getDBPass());
	}
	
	/*
	 * Send a mysql query to the database and return the result.
	 */
	public function query($query) {
		$config = new Configuration();
		mysql_select_db($config->getDBName());
		$res = mysql_query($query);
		return $res;
	}
	
	/*
	 * Send a mysql query to the database and return whether there is an existing row for this query.
	 */
	public function isExisting($query) {
		$config = new Configuration();
		mysql_select_db($config->getDBName());
		$res = mysql_query($query);
		$numRows = mysql_num_rows($res);
		
		return ($numRows > 0);
	}
	
	/*
	 * Close the connection to the database.
	 */
	public function close() {
		mysql_close();
	}
}
?>