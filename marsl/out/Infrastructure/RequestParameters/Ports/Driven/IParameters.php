<?php

namespace marsl\Infrastructure\RequestParameters\Ports\Driven;

include_once(dirname(__FILE__)."/../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../autoload.php");

interface IParameters
{
    /**
     * @return array<mixed>
     */
    public function getGetParameters(): array;

    /**
     * @return array<mixed>
     */
    public function getPostParameters(): array;

    /**
     * @return array<mixed>
     */
    public function getRequestParameters(): array;

    /**
     * @return array<mixed>
     */
    public function getFilesParameters(): array;

    /**
     * @return array<mixed>
     */
    public function getCookieParameters(): array;

    /**
     * @return array<mixed>
     */
    public function getServerParameters(): array;
}
