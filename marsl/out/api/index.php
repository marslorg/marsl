<?php
include_once(dirname(__FILE__)."/../includes/basic.php");
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../includes/config.inc.php");
include_once(dirname(__FILE__)."/../user/auth.php");
include_once(dirname(__FILE__)."/../user/role.php");

class Main {

	private $db;
	private $auth;
	private $role;

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
		$config = new Configuration();
		$apiBasePath = $config->getBasePath()."/api/";
		header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
        header("Access-Control-Max-Age: 3600");
		header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

        if ($this->auth->isAppAllowed()) {
			$requestMethod = $_SERVER['REQUEST_METHOD'];
			$requestUri = $_SERVER['REQUEST_URI'];
			$requestUri = substr($requestUri, strlen($apiBasePath));
            $requestUri = rtrim($requestUri, "/");
            $requestUri = filter_var($requestUri, FILTER_SANITIZE_URL);
            $explodedRequestUri = explode('/', $requestUri);
			$apiVersion = array_shift($explodedRequestUri);
			$class = array_shift($explodedRequestUri);
			$method = array_shift($explodedRequestUri);
			$fileToInclude = dirname(__FILE__)."/controllers/".$apiVersion."/".$class.".php";
			include_once($fileToInclude);
			$controller = new $class($this->db, $this->auth, $requestMethod);
			$controller->$method(...$explodedRequestUri);
        }
	
		$this->db->close();
		
	}	
}

$display = new Main();
$display->display();
?>