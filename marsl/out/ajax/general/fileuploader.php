<?php
include_once (dirname(__FILE__)."/../../includes/errorHandler.php");
include_once (dirname(__FILE__)."/../../includes/basic.php");
include_once (dirname(__FILE__)."/../../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../../includes/encryption.php");
include_once (dirname(__FILE__)."/../../user/auth.php");
include_once(dirname(__FILE__)."/../../user/role.php");
include_once(dirname(__FILE__)."/../../user/user.php");

class FileUploader {

	private $db;
	private $auth;
	private $role;

	public function __construct() {
		$this->db = new DB();
		$this->db->connect();
		$this->role = new Role($this->db);
		$this->auth = new Authentication($this->db, $this->role);
	}

	public function display() {
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
		
		$user = new User($this->db, $this->role);
		
		$authToken = $_GET['token'];
		$authTime = $_GET['time'];
		
		if ($this->auth->checkToken($authTime, $authToken)) {
			if (!$user->isGuest()) {
				$directory = $this->db->escapeString($_GET['temporary']);
				
				$uploadResult = $this->upload();
				
				if (isset($uploadResult['filename'])) {
					$fileName = $this->db->escapeString($uploadResult['filename']);
					$filePath = ini_get("upload_tmp_dir") . $directory . DIRECTORY_SEPARATOR . $fileName;
					if (file_exists($filePath)) {
						$fileContent = file_get_contents($filePath);
						$key = $this->generateKey();
						$newFile = $this->generateFileName();
						$crypt = new Encryption();
						$cryptedContent = $crypt->encrypt($fileContent, $key);
						file_put_contents("../../files/".$newFile, $cryptedContent);
						@unlink($filePath);
						@rmdir(ini_get("upload_tmp_dir") . $directory);
						$this->db->query("INSERT INTO `attachment`(`servername`, `realname`, `key`, `temporary`) VALUES('$newFile', '$fileName', '$key', '$directory')");
					}
				}
				echo json_encode($uploadResult);
			}
		}
		
		$this->db->close();
	}
	
	/**
	 *
	 * Copyright 2009, Moxiecode Systems AB
	 * Released under GPL License.
	 *
	 * License: http://www.plupload.com/license
	 * Contributing: http://www.plupload.com/contributing
	 */
	private function upload() {
		$directory = $_GET['temporary'];
		// Settings
		$targetDir = ini_get("upload_tmp_dir") . $directory;
		//$targetDir = 'uploads';
	
		$cleanupTargetDir = true; // Remove old files
		$maxFileAge = 5 * 3600; // Temp file age in seconds
	
		// 5 minutes execution time
		@set_time_limit(5 * 60);
	
		// Uncomment this one to fake upload time
		// usleep(5000);
	
		// Get parameters
		$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
		$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
		$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';
	
		// Clean the fileName for security reasons
		$fileName = preg_replace('/[^\w\._]+/', '_', $fileName);
	
		// Make sure the fileName is unique but only if chunking is disabled
		if ($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
			$ext = strrpos($fileName, '.');
			$fileName_a = substr($fileName, 0, $ext);
			$fileName_b = substr($fileName, $ext);
	
			$count = 1;
			while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
				$count++;
	
			$fileName = $fileName_a . '_' . $count . $fileName_b;
		}
	
		$filePath = $targetDir . DIRECTORY_SEPARATOR . $_FILES['file']['name'];
	
		// Create target dir
		if (!file_exists($targetDir))
			@mkdir($targetDir);
	
		// Remove old temp files
		if ($cleanupTargetDir && is_dir($targetDir) && ($dir = opendir($targetDir))) {
			while (($file = readdir($dir)) !== false) {
				$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;
	
				// Remove temp file if it is older than the max age and is not the current file
				if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge) && ($tmpfilePath != "{$filePath}.part")) {
					@unlink($tmpfilePath);
				}
			}
	
			closedir($dir);
		} else {
			$result = array('jsonrpc'=>"2.0", 'error'=>array('code'=>"100", 'message'=>"Failed to open temp directory."), 'id'=>"id");
			return $result;
		}
	
	
		// Look for the content type header
		if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
			$contentType = $_SERVER["HTTP_CONTENT_TYPE"];
	
		if (isset($_SERVER["CONTENT_TYPE"]))
			$contentType = $_SERVER["CONTENT_TYPE"];
	
		// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
		if (strpos($contentType, "multipart") !== false) {
			if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
				// Open temp file
				$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
				if ($out) {
					// Read binary input stream and append it to temp file
					$in = fopen($_FILES['file']['tmp_name'], "rb");
	
					if ($in) {
						while ($buff = fread($in, 4096))
							fwrite($out, $buff);
					} else {
						$result = array('jsonrpc'=>"2.0", 'error'=>array('code'=>"101", 'message'=>"Failed to open input stream"), 'id'=>"id");
						return $result;
					}
					fclose($in);
					fclose($out);
					@unlink($_FILES['file']['tmp_name']);
				} else {
					$result = array('jsonrpc'=>"2.0", 'error'=>array('code'=>"102", 'message'=>"Failed to open output stream."), 'id'=>"id");
					return $result;
				}
			} else {
				$result = array('jsonrpc'=>"2.0", 'error'=>array('code'=>"103", 'message'=>"Failed to move uploaded file."), 'id'=>"id");
				return $result;
			}
		} else {
			// Open temp file
			$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
			if ($out) {
				// Read binary input stream and append it to temp file
				$in = fopen("php://input", "rb");
	
				if ($in) {
					while ($buff = fread($in, 4096))
						fwrite($out, $buff);
				} else {
					$result = array('jsonrpc'=>"2.0", 'error'=>array('code'=>"101", 'message'=>"Failed to open input stream."), 'id'=>"id");
					return $result;
				}
	
				fclose($in);
				fclose($out);
			} else {
				$result = array('jsonrpc'=>"2.0", 'error'=>array('code'=>"102", 'message'=>"Failed to open output stream."), 'id'=>"id");
				return $result;
			}
		}
	
		// Check if file has been uploaded
		if (!$chunks || $chunk == $chunks - 1) {
			// Strip the temp .part suffix off
			rename("{$filePath}.part", $filePath);
		}
	
	
		// Return JSON-RPC response
		$result = array('jsonrpc'=>"2.0", 'result'=>"null", 'id'=>"id", 'filename'=>$_FILES['file']['name']);
		return $result;
	}
	
	private function generateFileName() {
		$basic = new Basic($this->db, $this->auth, $this->role);
		$filename = $this->db->escapeString($basic->randomHash());
		while ($this->db->isExisting("SELECT * FROM `attachment` WHERE `servername`='$filename' LIMIT 1")) {
			$filename = $this->db->escapeString($basic->randomHash());
		}
		return $filename;
	}
	
	private function generateKey() {
		$basic = new Basic($this->db, $this->auth, $this->role);
		$key = $this->db->escapeString($basic->randomHash());
		while ($this->db->isExisting("SELECT * FROM `attachment` WHERE `key`='$key' LIMIT 1")) {
			$key = $this->db->escapeString($basic->randomHash());
		}
		return $key;
	}
}

$fu = new FileUploader();
$fu->display();
?>