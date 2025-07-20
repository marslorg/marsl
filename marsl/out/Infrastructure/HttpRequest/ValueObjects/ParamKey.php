<?php

namespace marsl\Infrastructure\HttpRequest\ValueObjects;

include_once(dirname(__FILE__)."/../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../autoload.php");

use marsl\Infrastructure\Base\ValueObjects\StringValueObject;

class ParamKey extends StringValueObject
{
    /**
     * ParamKey constructor.
     *
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }
}
