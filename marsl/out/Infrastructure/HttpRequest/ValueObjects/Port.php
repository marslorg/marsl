<?php

namespace marsl\Infrastructure\HttpRequest\ValueObjects;

include_once(dirname(__FILE__)."/../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../autoload.php");

use marsl\Infrastructure\Base\ValueObjects\IntegerValueObject;

class Port extends IntegerValueObject
{
    /**
     * Port constructor.
     *
     * @param int $value. -1 is used to indicate that the port is not set and a standard port should be used.
     */
    public function __construct(int $value)
    {
        $this->value = $value;
    }
}
