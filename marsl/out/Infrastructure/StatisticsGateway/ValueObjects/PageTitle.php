<?php

namespace marsl\Infrastructure\StatisticsGateway\ValueObjects;

include_once(dirname(__FILE__)."/../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../autoload.php");

use marsl\Infrastructure\Base\ValueObjects\StringValueObject;

class PageTitle extends StringValueObject
{
    /**
     * PageTitle constructor.
     *
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }
}
