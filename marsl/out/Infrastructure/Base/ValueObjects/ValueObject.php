<?php

namespace marsl\Infrastructure\Base\ValueObjects;

include_once(dirname(__FILE__)."/../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../autoload.php");

abstract class ValueObject implements IValueObject
{
    /**
     * @var mixed
     */
    protected $value;

    public function equals(IValueObject $other): bool
    {
        return $this->value === $other->getValue();
    }
}
