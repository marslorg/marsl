<?php

namespace marsl\includes;

include_once(dirname(__FILE__)."/errorHandler.php");
include_once(dirname(__FILE__)."/../autoload.php");

use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;

class LocationBase
{
    private DB $db;

    public function __construct(
        DB $db,
    ) {
        $this->db = $db;
    }

    /*
     * Get the location for the standard page.
     */
    public function getHomeLocation(): int
    {
        $homepage = -1;
        $result = $this->db->query("SELECT `homepage` FROM `homepage`");
        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['homepage'])) {
                $homepage = intval(strval($row['homepage']));
            }
        }
        return $homepage;
    }
}
