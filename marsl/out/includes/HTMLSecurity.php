<?php

namespace marsl\includes;

include_once(dirname(__FILE__)."/errorHandler.php");
include_once(dirname(__FILE__)."/../autoload.php");

class HTMLSecurity
{
    public function convertToHTMLEntities(string|null $dirt): string
    {
        if ($dirt != null) {
            return htmlentities($dirt, 0, 'UTF-8');
        } else {
            return "";
        }
    }
}
