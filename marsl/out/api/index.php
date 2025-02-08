<?php

namespace marsl\api;

include_once(dirname(__FILE__)."/../includes/basic.php");
include_once(dirname(__FILE__)."/../autoload.php");

use marsl\ComponentBuilder;
use marsl\includes\DB;
use marsl\includes\Configuration;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\user\Authentication;

class Main
{
    private Authentication $authentication;
    private Configuration $configuration;
    private DB $db;
    private IRequestParametersService $requestParametersService;

    public function __construct(
        Authentication $authentication,
        Configuration $configuration,
        DB $db,
        IRequestParametersService $requestParametersService,
    ) {
        $this->authentication = $authentication;
        $this->configuration = $configuration;
        $this->db = $db;
        $this->requestParametersService = $requestParametersService;
    }

    /*
     * Initialize the frontend screen.
     */
    public function display(): void
    {
        $apiBasePath = $this->configuration->getBasePath()."/api/";
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

        if ($this->authentication->isAppAllowed()) {
            $requestUri = $this->requestParametersService->fromServer()->getStringParameter("REQUEST_URI", "");
            $requestUri = substr($requestUri, strlen($apiBasePath));
            $requestUri = rtrim($requestUri, "/");
            $requestUri = filter_var($requestUri, FILTER_SANITIZE_URL);

            if (is_string($requestUri)) {
                $explodedRequestUri = explode('/', $requestUri);
                $apiVersion = array_shift($explodedRequestUri);
                $class = array_shift($explodedRequestUri);
                $classPath = "\\marsl\\api\\controllers\\v".$apiVersion."\\".$class;
                $method = array_shift($explodedRequestUri);
                $controller = ComponentBuilder::buildDependencies()->make($classPath);
                $controller->$method(...$explodedRequestUri);
            } else {
                http_response_code(400);
            }
        }

        $this->db->close();

    }
}

$main = ComponentBuilder::buildDependencies()->make('marsl\api\Main');

if ($main instanceof Main) {
    $main->display();
}
