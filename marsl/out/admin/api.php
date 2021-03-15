<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
include_once (dirname(__FILE__)."/../includes/dbsocket.php");
include_once (dirname(__FILE__)."/../user/user.php");
include_once (dirname(__FILE__)."/../includes/basic.php");
include_once (dirname(__FILE__)."/../user/auth.php");
include_once (dirname(__FILE__)."/../user/role.php");

class API {

    private $db;
    private $auth;
    private $role;

    public function __construct($db, $auth, $role) {
        $this->db = $db;
        $this->auth = $auth;
        $this->role = $role;
    }

    public function admin() {
        $user = new User($this->db, $this->role);
        $basic = new Basic($this->db, $this->auth, $this->role);

        if ($user->isRoot()) {
            if (isset($_POST['action'])) {
                if ($_POST['action'] == "create") {
                    $this->createApp($basic);
                }
            }

            if (isset($_GET['action'])) {
                if ($_GET['action'] == "delete") {
                    $this->deleteApp();
                }
            }

            $apps = array();
            $result = $this->db->query("SELECT * FROM `app`");
            while ($row = $this->db->fetchArray($result)) {
                array_push($apps,array('id'=>$row['id'],'name'=>htmlentities($row['name'], null, "UTF-8"),'key'=>$row['key'],'secret'=>$row['secret']));
            }
            
            $authTime = time();
            $authToken = $this->auth->getToken($authTime);
            require_once("template/api.tpl.php");
        }
    }

    private function deleteApp() {
        if ($this->auth->checkToken($_GET['time'], $_GET['token'])) {
            $id = $this->db->escapeString($_GET['id']);
            $this->db->query("DELETE FROM `app` WHERE `id`='$id'");
        }
    }

    private function createApp($basic) {
        if ($this->auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
            $appName = $this->db->escapeString($_POST['appname']);
            $appKey = $basic->randomHash();
            $appSecret = $basic->randomSHA512();
            $this->db->query("INSERT INTO `app`(`name`,`key`,`secret`) VALUES('$appName','$appKey','$appSecret')");
        }
    }
}

?>
