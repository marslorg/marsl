<?php
include_once(dirname(__FILE__)."/../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../../../user/auth.php");

class PushToken
{
    private $db;
    private $auth;
    private $requestMethod;
    
    public function __construct($db, $auth, $requestMethod) {
        $this->db = $db;
        $this->auth = $auth;
        $this->requestMethod = $requestMethod;
    }

    public function create($type) {
        $postBody = file_get_contents("php://input");
        $pushToken = json_decode($postBody, true);
        $httpResult = 0;
        $result = array();

        if ($type == "webpush") {
            list($httpResult, $result) = $this->createWebPushToken($pushToken, $type, $result);
        }
        else if ($type == "expo") {
            list($httpResult, $result) = $this->createExpoPushToken($pushToken, $type, $result);
        }
        else {
            $httpResult = 404;
        }
        http_response_code($httpResult);
        $jsonMessage = json_encode($result);
        echo $jsonMessage;
    }

    private function createExpoPushToken($pushToken, $type, $result) {
        if (isset($pushToken['pushtoken'])) {
            $httpResult = 200;
            $pushToken = $this->db->escapeString($pushToken['pushtoken']);
            $type = $this->db->escapeString($type);
            if ($this->db->isExisting("SELECT `type` FROM `pushtoken` WHERE `type`='$type' AND `pushtoken`='$pushToken'")) {
                $result = $this->getDuplicatePayloadMessage($result);
            }
            else {
                $this->db->query("INSERT INTO `pushtoken`(`type`,`pushtoken`) VALUES('$type', '$pushToken')");
                $result = $this->getCreatedPayloadMessage($result);
            }
        }
        else {
            list($httpResult, $result) = $this->getMalformedPayloadMessage($result);
        }

        return array($httpResult, $result);
    }

    private function createWebPushToken($pushToken, $type, $result) {
        if (isset($pushToken['endpoint']) && isset($pushToken['keys']) && isset($pushToken['keys']['auth']) && isset($pushToken['keys']['p256dh'])) {
            $httpResult = 200;
            $endpoint = $this->db->escapeString($pushToken['endpoint']);
            $auth = $this->db->escapeString($pushToken['keys']['auth']);
            $key = $this->db->escapeString($pushToken['keys']['p256dh']);
            $type = $this->db->escapeString($type);
            if ($this->db->isExisting("SELECT `type` FROM `pushtoken` WHERE `type`='$type' AND `endpoint`='$endpoint' AND `auth`='$auth' AND `key`='$key' LIMIT 1")) {
                $result = $this->getDuplicatePayloadMessage($result);
            }
            else {
                $this->db->query("INSERT INTO `pushtoken`(`type`, `endpoint`,`auth`,`key`) VALUES('$type', '$endpoint','$auth','$key')");
                $result = $this->getCreatedPayloadMessage($result);
            }
        }
        else {
            list($httpResult, $result) = $this->getMalformedPayloadMessage($result);
        }

        return array($httpResult, $result);
    }

    private function getCreatedPayloadMessage($result) {
        $result['message'] = "The resource has been created.";
        $result['state'] = "CREATED";

        return $result;
    }

    private function getDuplicatePayloadMessage($result) {
        $result['message'] = "The resource does already exist.";
        $result['state'] = "DUPLICATE";

        return $result;
    }

    private function getMalformedPayloadMessage($result) {
        $httpResult = 400;
        $result['message'] = "The payload was malformed.";
        $result['state'] = "MALFORMED";

        return array($httpResult, $result);
    }
}
?>