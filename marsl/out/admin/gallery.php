<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../user/user.php");
include_once(dirname(__FILE__)."/../user/auth.php");
include_once(dirname(__FILE__)."/../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../includes/basic.php");
include_once(dirname(__FILE__)."/../user/role.php");
include_once(dirname(__FILE__)."/../includes/config.inc.php");

class Gallery {
	
	private $db;

	public function __construct() {
		$this->db = new DB();
		$this->db->connect();
	}

	/*
	 * Creates a new gallery dataset in the database and thumbnails files in a given folder.
	 * Expects a folder location via post request.
	 */
	public function newGal() {
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
		$config = new Configuration();
		date_default_timezone_set($config->getTimezone());
		$user = new User($this->db);
		$basic = new Basic($this->db);
		$role = new Role($this->db);
		if ($user->isAdmin()) {
			$auth = new Authentication($this->db);
			$authTime = $_GET['time'];
			$authToken = $_GET['token'];
			if ($auth->checkToken($authTime, $authToken)) {
				if ($auth->moduleExtendedAllowed("gallery", $role->getRole())||$auth->moduleAdminAllowed("gallery", $role->getRole())) {
					if (isset($_GET['id'])) {
						$id = $this->db->escapeString($_GET['id']);
						$folder = "";
						$result = $this->db->query("SELECT `folder` FROM `album` WHERE `album`='$id'");
						while ($row = $this->db->fetchArray($result)) {
							$folder = $row['folder'];
						}
						$dir = "../albums/";
						$path = $dir.$folder;
						$handle = opendir($path);
						if (!$handle) {
							header("Location: index.php?var=module&module=gallery&failure=1");
						}
						else {
							$maxTime = ini_get("max_execution_time")-1;
							$start = time();
							$i = 0;
							$pause = -1; //will continue here after break
							if (isset($_POST['pause'])) {
								$pause = $_POST['pause'];
							}
							while($file = readdir($handle)) {
								$file = $this->db->escapeString($file);
								$cur = time();
								$diff = $cur-$start;
								if ($diff<$maxTime) {
									if ($i>$pause) {
										if ($file != "." && $file != "..") {
											if (!is_dir($file)) {
												$sub = strtolower(substr($file,-4));
												$sub2 = substr($file,0,6);
												if ($sub2 != "thumb_") {
													if ($sub==".jpg"||$sub==".png"||$sub==".gif") {
														if (!$this->db->isExisting("SELECT * FROM `picture` WHERE `album`='$id' AND `filename`='$file' LIMIT 1")) {
															$this->db->query("INSERT INTO `picture`(`album`,`filename`,`deleted`,`visible`) VALUES('$id','$file','0','0')");
															$from = $path.$file;
															$to = $path."thumb_".$file;
															$this->thumb($from,$to,200,200,TRUE);
															chmod($to,0644);
														}
													}
												}
											}
										}
									}
									$i++;
								}
								else {
									$newPause = $i-1;
									header("Location: gallery.php?id=".$id."&pause=".$newPause."&time=".$authTime."&token=".$authToken);
								}
							}
						}
						require_once("template/gallery.newgal.tpl.php");
					}
					else {
						if (isset($_POST['action'])) {
							$folder = $this->db->escapeString($_POST['folder']);
							$photograph = $this->db->escapeString($_POST['photograph']);
							$location = $this->db->escapeString($_POST['category']);
							$day = $this->db->escapeString($_POST['day']);
							$month = $this->db->escapeString($_POST['month']);
							$year = $this->db->escapeString($_POST['year']);
							$date = "";
							if (checkdate($month,$day,$year)) {
								$date = mktime(0,0,0,$month,$day,$year);
							}
							else {
								$date = time();
							}
							$description = $this->db->escapeString($basic->cleanHTML($_POST['description']));
							$author = $this->db->escapeString($user->getID());
							$authorIP = $this->db->escapeString($_SERVER['REMOTE_ADDR']);
							$postdate = time();
							$location = $this->db->escapeString($_POST['category']);
							if ($auth->locationExtendedAllowed($location, $role->getRole())||$auth->locationAdminAllowed($location, $role->getRole())) {
								$this->db->query("INSERT INTO `album`(`name`,`author`,`author_ip`,`photograph`,`description`,`folder`,`visible`,`deleted`,`date`,`postdate`,`location`)
								VALUES(' ','$author','$authorIP','$photograph','$description','$folder','0','0','$date','$postdate','$location')");
								$id = $this->db->lastInsertedID();
								header("Location: gallery.php?id=".$id."&time=".$authTime."&token=".$authToken);
							}
							else {
								header("Location: index.php?var=module&module=gallery");
							}
						}
						else {
							header("Location: index.php?var=module&module=gallery");
						}
					}
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
	   	if ($infos[2] == 1) {
	   		$imgA = imagecreatefromgif($file);
	   		$imgB = imagecreatetruecolor($iNewW,$iNewH);
	   		imagecopyresampled($imgB, $imgA, 0, 0, 0, 0, $iNewW, $iNewH, $infos[0], $infos[1]);
	   		ImageDestroy($imgA);
	   		imagegif($imgB, $save);
	   	}
		if ($infos[2] == 2) {
        	$imgA = imagecreatefromjpeg($file);
        	$imgB = imagecreatetruecolor($iNewW,$iNewH);
        	imagecopyresampled($imgB, $imgA, 0, 0, 0, 0, $iNewW,
        	$iNewH, $infos[0], $infos[1]);
            ImageDestroy($imgA);
        	imagejpeg($imgB, $save);
    	}
    	else if($infos[2] == 3) {
			$imgA = imagecreatefrompng($file);
        	$imgB = imagecreatetruecolor($iNewW, $iNewH);
        	imagecopyresampled($imgB, $imgA, 0, 0, 0, 0, $iNewW, $iNewH, $infos[0], $infos[1]);
            ImageDestroy($imgA);
            imagepng($imgB, $save);
   		}
    	else {
        	return FALSE;
		}
	}
}

$gallery = new Gallery();
$gallery->newGal();
?>