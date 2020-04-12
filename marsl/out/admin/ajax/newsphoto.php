<?php
include_once(dirname(__FILE__)."/../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../user/user.php");
include_once(dirname(__FILE__)."/../../user/role.php");
include_once(dirname(__FILE__)."/../../user/auth.php");
include_once(dirname(__FILE__)."/../../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../../includes/basic.php");

class NewsPhoto {

	private $db;

	public function __construct() {
		$this->db = new DB();
		$this->db->connect();
	}
	
	public function display() {		
		$auth = new Authentication($this->db);
		$role = new Role($this->db);
		$user = new User($this->db);
		
		if ($auth->moduleAdminAllowed("news", $role->getRole())) {
			
			if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
				
				//$picture = $_FILES['picture'];
				
				mt_srand(time());
				$random = mt_rand();
				$dir = "../../news/";
				$imgData = $_POST['data'];
				$imgData = str_replace('data:image/jpeg;base64,', '', $imgData);
				$imgData = str_replace(' ', '+', $imgData);
				$imgData = base64_decode($imgData);
				//$fileName = $random.$picture['name'];
				$fileName = $random.$_POST['fileName'];
				$fileLink = $dir.$fileName;
				if (file_put_contents($fileLink, $imgData)) {
					$picinfo = @getimagesize($fileLink);
					if (getimagesize($fileLink)) {
						$width = $picinfo[0];
						$height = $picinfo[1];
						$fileName = $this->db->escapeString($fileName);
						$photograph = $this->db->escapeString(urldecode($_POST['photograph']));
						if ($_POST['type']=="teaser") {
							if (($width>200)||($height>200)) {
								$this->thumb($fileLink, $fileLink, 200, 200, TRUE);
							}
							$this->db->query("INSERT INTO `news_picture`(`url`, `photograph`) VALUES('$fileName', '$photograph')");
							$pictureID = $this->db->lastInsertedID();
							$result = array('type'=>"success", 'id'=>$pictureID, 'file'=>$fileName);
							echo json_encode($result);
						}
						
						if ($_POST['type']=="text") {
							$subtitle = $this->db->escapeString(urldecode($_POST['subtitle']));
							if (($width>1280)||($height>1280)) {
								$this->thumb($fileLink, $fileLink, 1280, 1280, TRUE);
							}
							if (($width<640)||($height<320)) {
								unlink($fileLink);
								$result = array('type'=>"error", 'code'=>"2", 'text'=>"The size of the photo is not correct.");
								echo json_encode($result);
							}
							else {
								$this->db->query("INSERT INTO `news_picture`(`url`, `photograph`,`subtitle`) VALUES('$fileName', '$photograph','$subtitle')");
								$pictureID = $this->db->lastInsertedID();
								$result = array('type'=>"success", 'id'=>$pictureID, 'file'=>$fileName);
								echo json_encode($result);
							}
						}
					}
					else {
						unlink($fileLink);
						$result = array('type'=>"error", 'code'=>"1", 'text'=>"Not a picture.");
						echo json_encode($result);
					}
				
				}
				else {
					$result = array('type'=>"error", 'code'=>"3", 'text'=>"File system error.");
					echo json_encode($result);
				}
				
			}
			
		}
		
		$this->db->close();
		
	}
	
	
	private function thumb($file, $save, $width, $height, $prop = TRUE) {
		$infos = getimagesize($file);
		if ($prop) {
			$iWidth = $infos[0];
			$iHeight = $infos[1];
			$iRatioW = $width / $iWidth;
			$iRatioH = $height / $iHeight;
			if ($iRatioW < $iRatioH) {
				$iNewW = $iWidth * $iRatioW;
				$iNewH = $iHeight * $iRatioW;
			}
			else {
				$iNewW = $iWidth * $iRatioH;
				$iNewH = $iHeight * $iRatioH;
			}
		}
		else {
			$iNewW = $width;
			$iNewH = $height;
		}
		if ($infos[2] == 2) {
			$imgA = imagecreatefromjpeg($file);
			$imgB = imagecreatetruecolor($iNewW,$iNewH);
			imagecopyresampled($imgB, $imgA, 0, 0, 0, 0, $iNewW,
			$iNewH, $infos[0], $infos[1]);
			ImageDestroy($imgA);
			@unlink($file);
			imagejpeg($imgB, $save);
		}
		else {
			return FALSE;
		}
	}
	
}

$np = new NewsPhoto();
$np->display();
?>