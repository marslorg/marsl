<?php

namespace marsl\Infrastructure\RequestParameters\Adapters\Driven\Implementation;

include_once(dirname(__FILE__)."/../../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../../autoload.php");

use marsl\Infrastructure\RequestParameters\Ports\Driven\IParameters;

class Parameters implements IParameters
{
    public function getGetParameters(): array
    {
        return $_GET;
    }

    public function getPostParameters(): array
    {
        return $_POST;
    }

    public function getRequestParameters(): array
    {
        return $_REQUEST;
    }

    public function getFilesParameters(): array
    {
        return $_FILES;
    }

    public function getCookieParameters(): array
    {
        return $_COOKIE;
    }

    public function getServerParameters(): array
    {
        return $_SERVER;
    }
}
