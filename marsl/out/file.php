<?php

namespace marsl;

include_once(dirname(__FILE__)."/includes/errorHandler.php");
include_once(dirname(__FILE__)."/autoload.php");

use marsl\includes\DB;
use marsl\includes\Encryption;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\modules\board\BoardBase;
use marsl\user\Authentication;
use marsl\user\Role;

class File
{
    private Authentication $authentication;
    private BoardBase $boardBase;
    private DB $db;
    private Encryption $encryption;
    private IRequestParametersService $requestParametersService;
    private Role $role;

    public function __construct(
        Authentication $authentication,
        BoardBase $boardBase,
        DB $db,
        Encryption $encryption,
        IRequestParametersService $requestParametersService,
        Role $role
    ) {
        $this->authentication = $authentication;
        $this->boardBase = $boardBase;
        $this->db = $db;
        $this->encryption = $encryption;
        $this->requestParametersService = $requestParametersService;
        $this->role = $role;
    }

    public function display(): void
    {
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

        $this->db->connect();

        $fileID = $this->requestParametersService->fromGet()->getIntegerParameter("file", -1);
        $scope = $this->requestParametersService->fromGet()->getStringParameter("scope", "");

        if ($scope == "board") {
            $result = $this->db->query("SELECT `board`, `servername`, `realname`, `key` FROM `attachment` JOIN `post_attachment` USING(`file`) JOIN `post` USING(`post`) JOIN `thread` USING(`thread`) WHERE `file`='$fileID'");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['board'])
                    && is_string($row['servername'])
                    && is_string($row['realname'])
                    && is_string($row['key'])) {
                    $boardID = intval(strval($row['board']));
                    $location = $this->boardBase->getLocation($boardID);
                    if ($this->boardBase->readAllowed($boardID, $this->role->getRole())
                    && $this->authentication->locationReadAllowed($location, $this->role->getRole())
                    && $this->authentication->moduleAdminAllowed("board", $this->role->getRole())) {
                        $servername = $row['servername'];
                        $realname = $row['realname'];
                        $key = $row['key'];
                        $encContent = file_get_contents("files/".$servername);

                        if ($encContent) {
                            $fileContent = $this->encryption->decrypt($encContent, $key);
                            header("Content-Type: application/octet-stream");
                            header("Content-Disposition: attachment; filename=\"$realname\"");
                            echo $fileContent;
                        }
                    }
                }
            }
        }

        $this->db->close();
    }

}

$file = ComponentBuilder::buildDependencies()->make('marsl\File');

if ($file instanceof File) {
    $file->display();
}
