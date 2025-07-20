<?php

namespace marsl\Infrastructure\HttpRequest\Adapters\Driven\Implementation;

include_once(dirname(__FILE__)."/../../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../../autoload.php");

use Exception;
use marsl\Infrastructure\HttpRequest\Ports\Driven\IHttpFireAndForgetClient;
use marsl\Infrastructure\HttpRequest\ValueObjects\Port;
use marsl\Infrastructure\HttpRequest\ValueObjects\Request;
use marsl\Infrastructure\HttpRequest\ValueObjects\Url;

class HttpFireAndForgetClient implements IHttpFireAndForgetClient
{
    public function sendRequest(Url $url, Request $request): bool
    {
        $protocol = $url->getProtocol()->getValue() == "https" ? "ssl://" : "";
        $port = $url->getPort()->getValue() != -1 ? $url->getPort()->getValue() : ($protocol != "" ? 443 : 80);
        try {
            $socket = fsockopen(
                $protocol.$url->getHost()->getValue(),
                $port,
                $errorNumber,
                $errorString,
                0.1
            );
        } catch (Exception $e) {
            $socket = null;
        }

        if (!$socket) {
            return false;
        }

        fwrite($socket, $request->getValue());
        fclose($socket);

        return true;
    }
}
