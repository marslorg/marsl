<?php
include_once(dirname(__FILE__)."/../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../user/user.php");
include_once(dirname(__FILE__)."/../../user/role.php");
include_once(dirname(__FILE__)."/../../user/auth.php");
include_once(dirname(__FILE__)."/../../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../../includes/basic.php");
include_once(dirname(__FILE__)."/../../includes/config.inc.php");

class SharedImagesUploader {

private $db;

    public function __construct() {
        $this->db = new DB();
        $this->db->connect();
    }

    public function upload() {
        $auth = new Authentication($this->db);
		$role = new Role($this->db);
        $user = new User($this->db);
        
        if ($auth->moduleAdminAllowed("news", $role->getRole())) {
            if ($auth->checkToken($_GET['authTime'], $_GET['authToken'])) {
                $imageFolder = "../../shared/image/";
                reset ($_FILES);
                $temp = current($_FILES);
                if (is_uploaded_file($temp['tmp_name'])) {
                    if (preg_match("/([^\w\s\d\-_~,;:\[\]\(\).])|([\.]{2,})/", $temp['name'])) {
                        header("HTTP/1.1 400 Invalid file name.");
                        return;
                    }
                    if (!in_array(strtolower(pathinfo($temp['name'], PATHINFO_EXTENSION)), array("gif", "jpg", "png"))) {
                        header("HTTP/1.1 400 Invalid extension.");
                        return;
                    }
                    $random = mt_rand();
                    $fileName = $random.$temp['name'];
                    $fileToWrite = $imageFolder.$fileName;
                    $config = new Configuration();
                    $fileAbsolutePath = $config->getDomain().$config->getBasePath()."/shared/image/".$fileName;
                    move_uploaded_file($temp['tmp_name'], $fileToWrite);
                    echo json_encode(array('location' => $fileAbsolutePath));
                }
                else {
                    header("HTTP/1.1 500 Server Error");
                }
            }
            else {
                header("HTTP/1.1 403 Wrong authentication");
            }
        }
        else {
            header("HTTP/1.1 403 Forbidden");
        }
    }
}

$siu = new SharedImagesUploader();
$siu->upload();
?>