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
        while ($this->mysqllink == null) {
			set_error_handler(function() {});
			$this->mysqllink = mysqli_connect($config->getDBHost(), $config->getDBUser(), $config->getDBPass(), $config->getDBName());
			restore_error_handler();
		}
	}
	
	/*
	 * Send a mysql query to the database and return the result.
	 */
	public function query($query) {
		$res = mysqli_query($this->mysqllink, $query);
		return $res;
	}
	
	/*
	 * Send a mysql query to the database and return whether there is an existing row for this query.
	 */
	public function isExisting($query) {
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
	public function getRowCount($queryResult) {
		$row = $this->fetchArray($queryResult);
		return $row['rowcount'];
	}
	
	/*
	 * Close the connection to the database.
	 */
	public function close() {
		if ($this->mysqllink != null) {
			mysqli_close($this->mysqllink);
		}
	}

	public function isHealthy() {
		$result = true;
		try {
			$this->connect();
		}
		catch (Exception $e) {
			$result = false;
		}

		$result = $result && $this->isExisting("SELECT `nickname` FROM `user` WHERE LOWER(`nickname`)=LOWER('root') LIMIT 1");

		try {
			$this->close();
		}
		catch (Exception $e) {
			$result = false;
		}
		
		return $result;
	}
}
?>