<?php

namespace marsl\Infrastructure\Base\ValueObjects;

include_once(dirname(__FILE__)."/../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../autoload.php");

use InvalidArgumentException;

abstract class IntegerValueObject extends ValueObject
{
    public function getValue(): int
    {
        if (!is_int($this->value)) {
            throw new InvalidArgumentException("Value must be an integer.");
        }
        return $this->value;
    }
}
