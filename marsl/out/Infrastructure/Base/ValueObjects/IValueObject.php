<?php

namespace marsl\Infrastructure\Base\ValueObjects;

include_once(dirname(__FILE__)."/../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../autoload.php");

interface IValueObject
{
    /**
     * @return mixed
     */
    public function getValue();

    public function equals(IValueObject $other): bool;
}
