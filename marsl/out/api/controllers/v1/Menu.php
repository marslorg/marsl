<?php

namespace marsl\api\controllers\v1;

include_once(dirname(__FILE__)."/../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../autoload.php");

use marsl\includes\DB;
use marsl\modules\Navigation;
use marsl\user\Authentication;
use marsl\user\Role;

class Menu
{
    private Authentication $authentication;
    private DB $db;
    private Navigation $navigation;
    private Role $role;

    public function __construct(
        Authentication $authentication,
        DB $db,
        Navigation $navigation,
        Role $role
    ) {
        $this->authentication = $authentication;
        $this->db = $db;
        $this->navigation = $navigation;
        $this->role = $role;
    }

    public function read(): void
    {
        list($categories, $links) = $this->getNavigationStructure();

        $resultArray = array();
        foreach ($categories as $category) {
            $categoryID = $category["id"];
            $categoryName = $category['name'];
            $categoryType = $category['type'];
            $categoryTarget = null;
            if (!is_int($categoryID) || !is_string($categoryName) || !is_int($categoryType)) {
                http_response_code(500);
                return;
            }
            if ($categoryType == 1) {
                $categoryTarget = $this->getURIByID($categoryID, $categoryName);
            }
            $childs = array();
            if ($categoryType == 0 && array_key_exists($categoryID, $links)) {
                foreach ($links[$categoryID] as $link) {
                    if (!is_array($link)) {
                        http_response_code(500);
                        return;
                    }
                    $linkID = $link['id'];
                    $linkName = $link['name'];
                    if (!is_int($linkID) || !is_string($linkName)) {
                        http_response_code(500);
                        return;
                    }
                    $linkTarget = $this->getURIByID($linkID, $linkName);
                    array_push($childs, array('id' => $linkID, 'name' => $linkName, 'target' => $linkTarget));
                }
            }
            array_push($resultArray, array('id' => $categoryID, 'name' => $categoryName, 'target' => $categoryTarget, 'links' => $childs));
        }

        http_response_code(200);
        $jsonMessage = json_encode($resultArray);
        echo $jsonMessage;
    }

    private function getURIByID(int $id, string $name): string
    {
        $uri = $this->navigation->getRelativeURI($id, $name, false);

        return $uri;
    }

    /**
     * @return array<int, array<array<string, mixed>>>
     */
    private function getNavigationStructure(): array
    {
        $guestRole = $this->role->getGuestRole();
        $categories = array();
        $links = array();
        $result = $this->db->query("SELECT `id`, `name`, `type`, `category` FROM `navigation` WHERE `type` IN ('0','1','2') ORDER BY `pos`");
        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['id']) && is_string($row['type']) && is_string($row['name'])) {
                $id = intval(strval($row['id']));
                $type = intval(strval($row['type']));
                if ($this->authentication->locationReadAllowed($id, $guestRole)) {
                    $name = strval($row['name']);
                    if ($type == 0 || $type == 1) {
                        array_push($categories, array('id' => $id, 'name' => $name, 'type' => $type));
                    } elseif ($type == 2 && is_string($row['category'])) {
                        $category = intval(strval($row['category']));
                        if (!array_key_exists($category, $links)) {
                            $links[$category] = array();
                        }
                        array_push($links[$category], array('id' => $id, 'name' => $name));
                    }
                }
            }
        }

        return array($categories, $links);
    }
}
