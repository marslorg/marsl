<?php
include_once(dirname(__FILE__)."/../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../../../includes/config.inc.php");
include_once(dirname(__FILE__)."/../../../user/auth.php");
include_once(dirname(__FILE__)."/../../../modules/navigation.php");

class Menu
{
    private $db;
    private $auth;
    private $requestMethod;
    
    public function __construct($db, $auth, $requestMethod) {
        $this->db = $db;
        $this->auth = $auth;
        $this->requestMethod = $requestMethod;
    }

    public function read() {
        $role = new Role($this->db);
        list($categories, $links) = $this->getNavigationStructure($role);

        $resultArray = array();
        foreach ($categories as $category) {
            $categoryID = $category['id'];
            $categoryName = $category['name'];
            $categoryTarget = null;
            if ($category['type'] == 1) {
                $categoryTarget = $this->getURIByID($category['id'], $categoryName, $role);
            }
            $childs = array();
            if ($category['type'] == 0 && array_key_exists($category['id'], $links)) {
                foreach ($links[$category['id']] as $link) {
                    $linkID = $link['id'];
                    $linkName = $link['name'];
                    $linkTarget = $this->getURIByID($link['id'], $linkName, $role);
                    array_push($childs, array('id' => $linkID, 'name' => $linkName, 'target' => $linkTarget));
                }
            }
            array_push($resultArray, array('id' => $categoryID, 'name' => $categoryName, 'target' => $categoryTarget, 'links' => $childs));
        }

        http_response_code(200);
        $jsonMessage = json_encode($resultArray);
        echo $jsonMessage;
    }

    private function getURIByID($id, $name, $role) {
        $navi = new Navigation($this->db, $this->auth, $role);
        $uri = $navi->getRelativeURI($id, $name, false);

        return $uri;
    }

    private function getNavigationStructure($role) {
        $guestRole = $role->getGuestRole();
        $categories = array();
        $links = array();
        $result = $this->db->query("SELECT `id`, `name`, `type`, `category` FROM `navigation` WHERE `type` IN ('0','1','2') ORDER BY `pos`");
        while ($row = $this->db->fetchArray($result)) {
            if ($this->auth->locationReadAllowed($row['id'], $guestRole)) {
                $id = $row['id'];
                $name = $row['name'];
                if ($row['type'] == 0 || $row['type'] == 1) {
                    array_push($categories, array('id' => $id, 'name' => $name, 'type' => $row['type']));
                }
                else if ($row['type'] == 2) {
                    if (!array_key_exists($row['category'], $links)) {
                        $links[$row['category']] = array();
                    }
                    array_push($links[$row['category']], array('id' => $id, 'name' => $name));
                }
            }
        }

        return array($categories, $links);
    }
}
?>

