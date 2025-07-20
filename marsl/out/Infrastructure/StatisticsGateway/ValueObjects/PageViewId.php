<?php

namespace marsl\Infrastructure\StatisticsGateway\ValueObjects;

include_once(dirname(__FILE__)."/../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../autoload.php");

use InvalidArgumentException;
use marsl\Infrastructure\Base\ValueObjects\StringValueObject;

class PageViewId extends StringValueObject
{
    /**
     * PageViewId constructor.
     *
     * @param string $value
     */
    public function __construct(string $value)
    {
        if (strlen($value) !== 6) {
            throw new InvalidArgumentException("PageViewId must be exactly 6 characters long.");
        }
        $this->value = $value;
    }

    public static function generateNew(): PageViewId
    {
        return new PageViewId(substr(md5(uniqid((string)rand(), true)), 0, 6));
    }
}
