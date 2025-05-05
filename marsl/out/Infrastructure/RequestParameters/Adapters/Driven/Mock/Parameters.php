<?php

namespace marsl\Infrastructure\RequestParameters\Adapters\Driven\Mock;

include_once(dirname(__FILE__)."/../../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../../autoload.php");

use marsl\Infrastructure\RequestParameters\Ports\Driven\IParameters;

class Parameters implements IParameters
{
    public function getGetParameters(): array
    {
        return array();
    }

    public function getPostParameters(): array
    {
        return array();
    }

    public function getRequestParameters(): array
    {
        return array();
    }

    public function getFilesParameters(): array
    {
        return array();
    }

    public function getCookieParameters(): array
    {
        return array();
    }

    public function getServerParameters(): array
    {
        return array();
    }
}
