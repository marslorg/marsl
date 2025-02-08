<?php

namespace marsl\api\controllers\v1;

include_once(dirname(__FILE__)."/../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../autoload.php");

use marsl\includes\DB;

class PushToken
{
    private DB $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    public function create(string $type): void
    {
        $httpResult = 0;
        $postBody = file_get_contents("php://input");

        if (!$postBody) {
            http_response_code(400);
        } else {
            $pushToken = json_decode($postBody, true);
            if (!is_array($pushToken)) {
                http_response_code(500);
                return;
            }
            $result = array();

            if ($type == "webpush") {
                list($httpResult, $result) = $this->createWebPushToken($pushToken, $type, $result);
            } elseif ($type == "expo") {
                list($httpResult, $result) = $this->createExpoPushToken($pushToken, $type, $result);
            } else {
                $httpResult = 404;
            }

            if (is_int($httpResult)) {
                http_response_code($httpResult);
            }

            $jsonMessage = json_encode($result);
            echo $jsonMessage;
        }
    }

    /**
     * @param array<mixed> $pushToken
     * @param string $type
     * @param array<string> $result
     * @return array<int, array<string>|int>
     */
    private function createExpoPushToken(array $pushToken, string $type, array $result): array
    {
        if (isset($pushToken['pushtoken'])) {
            $actualPushToken = $pushToken['pushToken'];
            if (!is_string($actualPushToken)) {
                list($httpResult, $result) = $this->getMalformedPayloadMessage($result);
                return array($httpResult, $result);
            }
            $httpResult = 200;
            $curPushToken = $this->db->escapeString($actualPushToken);
            $type = $this->db->escapeString($type);
            if ($this->db->isExisting("SELECT `type` FROM `pushtoken` WHERE `type`='$type' AND `pushtoken`='$curPushToken'")) {
                $result = $this->getDuplicatePayloadMessage($result);
            } else {
                $this->db->query("INSERT INTO `pushtoken`(`type`,`pushtoken`) VALUES('$type', '$curPushToken')");
                $result = $this->getCreatedPayloadMessage($result);
            }
        } else {
            list($httpResult, $result) = $this->getMalformedPayloadMessage($result);
        }

        return array($httpResult, $result);
    }

    /**
     * @param array<mixed> $pushToken
     * @param string $type
     * @param array<string> $result
     * @return array<int, array<string>|int>
     */
    private function createWebPushToken(array $pushToken, string $type, array $result): array
    {
        if (isset($pushToken['endpoint'])
            && isset($pushToken['keys'])
            && is_array($pushToken['keys'])
            && isset($pushToken['keys']['auth'])
            && isset($pushToken['keys']['p256dh'])
            && is_string($pushToken['keys']['auth'])
            && is_string($pushToken['keys']['p256dh'])) {
            $httpResult = 200;
            $actualEndpoint = $pushToken['endpoint'];
            if (!is_string($actualEndpoint)) {
                list($httpResult, $result) = $this->getMalformedPayloadMessage($result);
                return array($httpResult, $result);
            }
            $endpoint = $this->db->escapeString($actualEndpoint);
            $auth = $this->db->escapeString($pushToken['keys']['auth']);
            $key = $this->db->escapeString($pushToken['keys']['p256dh']);
            $type = $this->db->escapeString($type);
            if ($this->db->isExisting("SELECT `type` FROM `pushtoken` WHERE `type`='$type' AND `endpoint`='$endpoint' AND `auth`='$auth' AND `key`='$key' LIMIT 1")) {
                $result = $this->getDuplicatePayloadMessage($result);
            } else {
                $this->db->query("INSERT INTO `pushtoken`(`type`, `endpoint`,`auth`,`key`) VALUES('$type', '$endpoint','$auth','$key')");
                $result = $this->getCreatedPayloadMessage($result);
            }
        } else {
            list($httpResult, $result) = $this->getMalformedPayloadMessage($result);
        }

        return array($httpResult, $result);
    }

    /**
     * @param array<string> $result
     * @return array<string>
     */
    private function getCreatedPayloadMessage(array $result): array
    {
        $result['message'] = "The resource has been created.";
        $result['state'] = "CREATED";

        return $result;
    }

    /**
     * @param array<string> $result
     * @return array<string>
     */
    private function getDuplicatePayloadMessage(array $result): array
    {
        $result['message'] = "The resource does already exist.";
        $result['state'] = "DUPLICATE";

        return $result;
    }

    /**
     * @param array<string> $result
     * @return array<int, array<string>|int>
     */
    private function getMalformedPayloadMessage(array $result): array
    {
        $httpResult = 400;
        $result['message'] = "The payload was malformed.";
        $result['state'] = "MALFORMED";

        return array($httpResult, $result);
    }
}
