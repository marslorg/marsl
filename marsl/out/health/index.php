<?php
include_once(dirname(__FILE__)."/../includes/config.inc.php");
include_once(dirname(__FILE__)."/../includes/basic.php");
include_once(dirname(__FILE__)."/../includes/dbsocket.php");

class Main {

    public function display() {
        $startTimeExecution = microtime(true);
        $result = "";
        if (isset($_GET['diagnostic']) && $_GET['diagnostic'] == "true") {
            header("Cache-Control: no-cache, must-revalidate");
            header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
            header("Access-Control-Allow-Origin: *");
            header("Content-Type: application/json; charset=UTF-8");
            header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
            header("Access-Control-Max-Age: 3600");
            header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

            $config = new Configuration();
            $db = new DB();

            $startTimeDatabaseConnect = microtime(true);
            $db->connect();
            $stopTimeDatabaseConnect = microtime(true);

            $role = new Role($db);
		    
            $startTimeAuthConstruction = microtime(true);
            $auth = new Authentication($db, $role);
            $stopTimeAuthConstruction = microtime(true);
            
            $basic = new Basic($db, $auth, $role);

            $resultArray['serverName'] = $basic->convertToHTMLEntities($config->getClusterServer());
            $resultArray['databaseConnectTime'] = $stopTimeDatabaseConnect - $startTimeDatabaseConnect;
            $resultArray['databaseTime'] = $this->getTimeForDatabase($db);
            $resultArray['authConstructionTime'] = $stopTimeAuthConstruction - $startTimeAuthConstruction;
            $resultArray['albumsFolderTime'] = $this->getTimeForFolder("albums");
            $resultArray['filesFolderTime'] = $this->getTimeForFolder("files");
            $resultArray['newsFolderTime'] = $this->getTimeForFolder("news");
            $resultArray['sharedFolderTime'] = $this->getTimeForFolder("shared");

            $stopTimeExecution = microtime(true);
            $resultArray['executionTime'] = $stopTimeExecution - $startTimeExecution;

            $jsonMessage = json_encode($resultArray);
            $result = $jsonMessage;
        }
        else {
            $result = "HEALTHY";
        }
        http_response_code(200);
        echo $result;
	}

    private function getTimeForDatabase($db) {
        $startTimeForDatabase = microtime(true);
        $db->isHealthy();
        $stopTimeForDatabase = microtime(true);
        return $stopTimeForDatabase - $startTimeForDatabase;
    }

    private function getTimeForFolder($folderName) {
        $startTimeForDatabase = microtime(true);
        file_exists(dirname(__FILE__)."/../".$folderName."/health");
        $stopTimeForDatabase = microtime(true);
        return $stopTimeForDatabase - $startTimeForDatabase;
    }

}

$display = new Main();
$display->display();
?>