<?php
include_once (dirname(__FILE__)."/includes/errorHandler.php");
include_once(dirname(__FILE__)."/includes/config.inc.php");

class API {
    public function display() {
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        header("Content-Type: application/json; charset=UTF-8");
        $config = new Configuration();
        if (array_key_exists("HTTP_REFERER", $_SERVER) && strtolower(substr($_SERVER['HTTP_REFERER'], 0, strlen($config->getDomain()))) == strtolower($config->getDomain())) {
            $host = $_SERVER['HTTP_HOST'];
            $requestUri = $config->getBasePath()."/api/".$_GET['uri'];
            $fullUri = $config->getDomain().$requestUri;
            $requestMethod = $_SERVER['REQUEST_METHOD'];

            $message = $requestMethod.$host.$requestUri;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $fullUri);

            if ($requestMethod == "POST" || $requestMethod == "PUT") {
                $rawData = file_get_contents("php://input");
                $message = $message.$rawData;
                curl_setopt($ch, CURLOPT_POSTFIELDS, $rawData);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                if ($requestMethod == "POST") {
                    curl_setopt($ch, CURLOPT_POST, 1);
                } else {
                    curl_setopt($ch, CURLOPT_PUT, 1);
                }
            } else {
                if ($requestMethod == "DELETE") {
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                } else {
                    curl_setopt($ch, CURLOPT_HTTPGET, 1);
                }
            }

            $hash = hash_hmac('sha512', $message, $config->getSecret());
            curl_setopt($ch, CURLOPT_USERPWD, $config->getAppKey().":".$hash);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);
            $httpResult = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            http_response_code($httpResult);
            curl_close($ch);
            echo $response;
        }
    }
}

$display = new API();
$display->display();
?>