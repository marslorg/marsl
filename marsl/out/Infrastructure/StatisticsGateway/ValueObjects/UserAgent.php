<?php

namespace marsl\Infrastructure\StatisticsGateway\ValueObjects;

include_once(dirname(__FILE__)."/../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../autoload.php");

use marsl\Infrastructure\Base\ValueObjects\StringValueObject;

class UserAgent extends StringValueObject
{
    /**
     * UserAgent constructor.
     *
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;

        if (str_starts_with($value, "music2webapp")) {
            $this->value = "music2webapp";
        }
    }
}
