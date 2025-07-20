<?php

namespace marsl\Infrastructure\HttpRequest\ValueObjects;

include_once(dirname(__FILE__)."/../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../autoload.php");

use marsl\Infrastructure\Base\ValueObjects\StringValueObject;

class Url extends StringValueObject
{
    private Host $host;
    private Port $port;
    private Protocol $protocol;

    /**
     * Url constructor.
     */
    public function __construct(Host $host, Port $port, Protocol $protocol)
    {
        $this->host = $host;
        $this->port = $port;
        $this->protocol = $protocol;

        $this->value = $protocol->getValue().".//".$host->getValue().($port->getValue() != -1 ? ":".$port->getValue() : "");
    }

    public function getHost(): Host
    {
        return $this->host;
    }

    public function getPort(): Port
    {
        return $this->port;
    }

    public function getProtocol(): Protocol
    {
        return $this->protocol;
    }
}
