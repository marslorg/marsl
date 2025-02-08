<?php

namespace marsl;

include_once(dirname(__FILE__)."/includes/errorHandler.php");
include_once(dirname(__FILE__)."/autoload.php");

use marsl\includes\Configuration;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;

class API
{
    private Configuration $configuration;
    private IRequestParametersService $requestParametersService;

    public function __construct(
        Configuration $configuration,
        IRequestParametersService $requestParametersService
    ) {
        $this->configuration = $configuration;
        $this->requestParametersService = $requestParametersService;
    }

    public function display(): void
    {
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        header("Content-Type: application/json; charset=UTF-8");
        $httpReferer = $this->requestParametersService->fromServer()->getStringParameter("HTTP_REFERER", "");
        if ($httpReferer != "" && strtolower(substr($httpReferer, 0, strlen($this->configuration->getDomain()))) == strtolower($this->configuration->getDomain())) {
            $host = $this->requestParametersService->fromServer()->getStringParameter("HTTP_HOST", "");
            $requestUri = $this->configuration->getBasePath()."/api/".$this->requestParametersService->fromGet()->getStringParameter("uri");
            $fullUri = $this->configuration->getDomain().$requestUri;
            $requestMethod = $this->requestParametersService->fromServer()->getStringParameter("REQUEST_METHOD", "");

            $message = $requestMethod.$host.$requestUri;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $fullUri);

            if ($requestMethod == "POST" || $requestMethod == "PUT") {
                $rawData = file_get_contents("php://input");

                if ($rawData) {
                    $message = $message.$rawData;
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $rawData);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                    if ($requestMethod == "POST") {
                        curl_setopt($ch, CURLOPT_POST, true);
                    } else {
                        curl_setopt($ch, CURLOPT_PUT, true);
                    }
                }
            } else {
                if ($requestMethod == "DELETE") {
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                } else {
                    curl_setopt($ch, CURLOPT_HTTPGET, true);
                }
            }

            $hash = hash_hmac('sha512', $message, $this->configuration->getSecret());
            curl_setopt($ch, CURLOPT_USERPWD, $this->configuration->getAppKey().":".$hash);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $httpResult = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            http_response_code($httpResult);
            curl_close($ch);
            echo $response;
        }
    }
}

$api = ComponentBuilder::buildDependencies()->make('marsl\API');

if ($api instanceof API) {
    $api->display();
}
