<?php
include_once(dirname(__FILE__)."/../includes/dbsocket.php");

class Main {

    public function display() {
        $db = new DB();

        $healthy = $db->isHealthy();
        $healthy = $healthy && file_exists(dirname(__FILE__)."/../albums/health");
        $healthy = $healthy && file_exists(dirname(__FILE__)."/../files/health");
        $healthy = $healthy && file_exists(dirname(__FILE__)."/../news/health");
        $healthy = $healthy && file_exists(dirname(__FILE__)."/../shared/health");

        if ($healthy) {
            http_response_code(200);
            echo "HEALTHY";
        }
        else {
            http_response_code(500);
            echo "UNHEALTHY";
        }
	}

}

$display = new Main();
$display->display();
?>