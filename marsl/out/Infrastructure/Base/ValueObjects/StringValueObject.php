<?php

namespace marsl\Infrastructure\Base\ValueObjects;

include_once(dirname(__FILE__)."/../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../autoload.php");

use InvalidArgumentException;

abstract class StringValueObject extends ValueObject
{
    public function getValue(): string
    {
        if (!is_string($this->value)) {
            throw new InvalidArgumentException("Value must be a string.");
        }
        return $this->value;
    }
}
