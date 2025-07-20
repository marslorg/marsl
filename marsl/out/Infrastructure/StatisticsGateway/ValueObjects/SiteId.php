<?php

namespace marsl\Infrastructure\StatisticsGateway\ValueObjects;

include_once(dirname(__FILE__)."/../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../autoload.php");

use marsl\Infrastructure\Base\ValueObjects\IntegerValueObject;

class SiteId extends IntegerValueObject
{
    /**
     * SiteId constructor.
     *
     * @param int $value
     */
    public function __construct(int $value)
    {
        $this->value = $value;
    }
}
