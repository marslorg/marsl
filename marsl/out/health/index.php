<?php

namespace marsl\health;

include_once(dirname(__FILE__)."/../autoload.php");

use marsl\ComponentBuilder;
use marsl\includes\Basic;
use marsl\includes\DB;
use marsl\includes\Configuration;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;

class Main
{
    private Basic $basic;
    private Configuration $configuration;
    private DB $db;
    private IRequestParametersService $requestParametersService;

    public function __construct(
        Basic $basic,
        Configuration $configuration,
        DB $db,
        IRequestParametersService $requestParametersService
    ) {
        $this->basic = $basic;
        $this->configuration = $configuration;
        $this->db = $db;
        $this->requestParametersService = $requestParametersService;
    }

    public function display(): void
    {
        $startTimeExecution = microtime(true);
        $result = "";
        if ($this->requestParametersService->fromGet()->getBoolParameter("diagnostic", false)) {
            header("Cache-Control: no-cache, must-revalidate");
            header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
            header("Access-Control-Allow-Origin: *");
            header("Content-Type: application/json; charset=UTF-8");
            header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
            header("Access-Control-Max-Age: 3600");
            header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

            $startTimeDatabaseConnect = microtime(true);
            $this->db->connect();
            $stopTimeDatabaseConnect = microtime(true);

            $startTimeAuthConstruction = microtime(true);
            $stopTimeAuthConstruction = microtime(true);

            $resultArray = array();

            $resultArray['serverName'] = $this->basic->convertToHTMLEntities($this->configuration->getClusterServer());
            $mysqlLink = $this->db->getMySQLLink();
            if (!is_bool($mysqlLink)) {
                $resultArray['databaseLink'] = $mysqlLink->thread_id;
                $resultArray['persistentDatabaseConnections'] = mysqli_get_connection_stats($mysqlLink)['active_persistent_connections'];
            }
            $resultArray['databaseConnectTime'] = $stopTimeDatabaseConnect - $startTimeDatabaseConnect;
            $resultArray['databaseTime'] = $this->getTimeForDatabase($this->db);
            $resultArray['authConstructionTime'] = $stopTimeAuthConstruction - $startTimeAuthConstruction;
            $resultArray['albumsFolderTime'] = $this->getTimeForFolder("albums");
            $resultArray['filesFolderTime'] = $this->getTimeForFolder("files");
            $resultArray['newsFolderTime'] = $this->getTimeForFolder("news");
            $resultArray['sharedFolderTime'] = $this->getTimeForFolder("shared");

            $stopTimeExecution = microtime(true);
            $resultArray['executionTime'] = $stopTimeExecution - $startTimeExecution;

            $jsonMessage = json_encode($resultArray);
            $result = $jsonMessage;
        } else {
            $result = "HEALTHY";
        }
        http_response_code(200);
        echo $result;
    }

    private function getTimeForDatabase(DB $db): float
    {
        $startTimeForDatabase = microtime(true);
        $db->isHealthy();
        $stopTimeForDatabase = microtime(true);
        return $stopTimeForDatabase - $startTimeForDatabase;
    }

    private function getTimeForFolder(string $folderName): float
    {
        $startTimeForDatabase = microtime(true);
        $_ = file_exists(dirname(__FILE__)."/../".$folderName."/health");
        $stopTimeForDatabase = microtime(true);
        return $stopTimeForDatabase - $startTimeForDatabase;
    }

}

$main = ComponentBuilder::buildDependencies()->make('marsl\health\Main');

if ($main instanceof Main) {
    $main->display();
}
