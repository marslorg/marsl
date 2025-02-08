<?php

namespace marsl\admin\ajax;

include_once(dirname(__FILE__)."/../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../autoload.php");

use Exception;
use marsl\ComponentBuilder;
use marsl\includes\Basic;
use marsl\includes\DB;
use marsl\Infrastructure\PHPConfiguration\Adapters\Drivers\Service\IPHPConfigurationService;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\user\Authentication;
use marsl\user\Role;

class GalleryUploader
{
    private Authentication $authentication;
    private Basic $basic;
    private DB $db;
    private IPHPConfigurationService $phpConfigurationService;
    private IRequestParametersService $requestParametersService;
    private Role $role;

    public function __construct(
        Authentication $authentication,
        Basic $basic,
        DB $db,
        IPHPConfigurationService $phpConfigurationService,
        IRequestParametersService $requestParametersService,
        Role $role
    ) {
        $this->authentication = $authentication;
        $this->basic = $basic;
        $this->db = $db;
        $this->phpConfigurationService = $phpConfigurationService;
        $this->requestParametersService = $requestParametersService;
        $this->role = $role;
    }

    /*
     * The backend for the PLUploader.
     */
    public function display(): void
    {
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        $directory = $this->requestParametersService->fromGet()->getStringParameter("dir", "");
        $album = $this->requestParametersService->fromGet()->getIntegerParameter("id", -1);
        if ($album != -1) {
            $result = $this->db->query("SELECT `folder` FROM `album` WHERE `album`='$album'");
            while ($row = $this->db->fetchArray($result)) {
                $directory = $row['folder'];
            }
        }
        $moduleAdmin = $this->authentication->moduleAdminAllowed("gallery", $this->role->getRole());
        if ($moduleAdmin) {
            $uploadResult = $this->upload();
            if (isset($uploadResult['filename'])) {
                $fileName = $uploadResult['filename'];
                if (is_string($fileName) && is_string($directory)) {
                    $uploadTmpDir = $this->phpConfigurationService->getConfigurationEntryAsString("upload_tmp_dir");
                    $filePath = $uploadTmpDir . $directory . DIRECTORY_SEPARATOR . $fileName;
                    if (file_exists($filePath)) {
                        $sub = strtolower(substr($filePath, -4));
                        if ($sub == ".jpg" || $sub == ".png" || $sub == ".gif") {
                            if (!file_exists("../../albums/".$directory)) {
                                mkdir("../../albums/".$directory);
                            }
                            $length = strlen($fileName) - 4;
                            $sub2 = substr($fileName, 0, $length);
                            $fileName = $sub2."_".$this->basic->randomHash().$sub;
                            $this->thumb($filePath, "../../albums/".$directory."/".$fileName, 1920, 1920, true);
                            @unlink($filePath);
                            @rmdir($this->phpConfigurationService->getConfigurationEntryAsString("upload_tmp_dir") . $directory);
                            if ($this->requestParametersService->fromGet()->getIntegerParameter("id", -1) != -1) {
                                $this->thumb("../../albums/".$directory."/".$fileName, "../../albums/".$directory."thumb_".$fileName, 200, 200, true);
                                $this->db->query("INSERT INTO `picture`(`album`,`filename`,`deleted`,`visible`) VALUES('$album','$fileName','0','0')");
                            }
                        } else {
                            @unlink($filePath);
                            @rmdir($this->phpConfigurationService->getConfigurationEntryAsString("upload_tmp_dir") . $directory);
                        }
                    }
                }
            }
            echo json_encode($uploadResult);
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
     * @return array<string, array<string, string>|string>
     */
    private function upload(): array
    {
        $directory = $this->requestParametersService->fromGet()->getStringParameter("dir", "");
        $album = $this->requestParametersService->fromGet()->getIntegerParameter("id", -1);
        if ($album != -1) {
            $result = $this->db->query("SELECT `folder` FROM `album` WHERE `album`='$album'");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['folder'])) {
                    $directory = $row['folder'];
                }
            }
        }
        // Settings
        $targetDir = $this->phpConfigurationService->getConfigurationEntryAsString("upload_tmp_dir") . $directory;
        //$targetDir = 'uploads';

        $maxFileAge = 5 * 3600; // Temp file age in seconds

        // 5 minutes execution time
        @set_time_limit(5 * 60);

        // Uncomment this one to fake upload time
        // usleep(5000);

        // Get parameters
        $chunk = $this->requestParametersService->fromRequest()->getIntegerParameter("chunk", 0);
        $chunks = $this->requestParametersService->fromRequest()->getIntegerParameter("chunks", 0);
        $fileName = $this->requestParametersService->fromRequest()->getStringParameter("name", "");

        // Clean the fileName for security reasons
        $fileName = (string)preg_replace('/[^\w\._]+/', '_', $fileName);

        // Make sure the fileName is unique but only if chunking is disabled
        if ($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
            $ext = strrpos($fileName, '.');

            if (!$ext) {
                throw new Exception();
            }

            $fileName_a = substr($fileName, 0, $ext);
            $fileName_b = substr($fileName, $ext);

            $count = 1;
            while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b)) {
                $count++;
            }

            $fileName = $fileName_a . '_' . $count . $fileName_b;
        }

        $fileParameter = $this->requestParametersService->fromFiles()->getArrayParameter("file", array());

        $fileNameFromParameter = "";

        if (array_key_exists("name", $fileParameter) && is_string($fileParameter['name'])) {
            $fileNameFromParameter = $fileParameter['name'];
        }

        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileNameFromParameter;

        // Create target dir
        if (!file_exists($targetDir)) {
            @mkdir($targetDir);
        }

        // Remove old temp files
        if (is_dir($targetDir) && ($dir = opendir($targetDir))) {
            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

                // Remove temp file if it is older than the max age and is not the current file
                if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge) && ($tmpfilePath != "{$filePath}.part")) {
                    @unlink($tmpfilePath);
                }
            }

            closedir($dir);
        } else {
            $result = array('jsonrpc' => "2.0", 'error' => array('code' => "100", 'message' => "Failed to open temp directory."), 'id' => "id");
            return $result;
        }

        $contentType = $this->requestParametersService->fromServer()->getStringParameter("HTTP_CONTENT_TYPE", "");

        if ($contentType == "") {
            $contentType = $this->requestParametersService->fromServer()->getStringParameter("CONTENT_TYPE", "");
        }

        // Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
        if (strpos($contentType, "multipart") !== false) {
            if (array_key_exists("tmp_name", $fileParameter) && is_string($fileParameter['tmp_name']) && is_uploaded_file($fileParameter['tmp_name'])) {
                // Open temp file
                $out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
                if ($out) {
                    // Read binary input stream and append it to temp file
                    $in = fopen($fileParameter['tmp_name'], "rb");

                    if ($in) {
                        while ($buff = fread($in, 4096)) {
                            fwrite($out, $buff);
                        }
                    } else {
                        $result = array('jsonrpc' => "2.0", 'error' => array('code' => "101", 'message' => "Failed to open input stream"), 'id' => "id");
                        return $result;
                    }
                    fclose($in);
                    fclose($out);
                    @unlink($fileParameter['tmp_name']);
                } else {
                    $result = array('jsonrpc' => "2.0", 'error' => array('code' => "102", 'message' => "Failed to open output stream."), 'id' => "id");
                    return $result;
                }
            } else {
                $result = array('jsonrpc' => "2.0", 'error' => array('code' => "103", 'message' => "Failed to move uploaded file."), 'id' => "id");
                return $result;
            }
        } else {
            // Open temp file
            $out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
            if ($out) {
                // Read binary input stream and append it to temp file
                $in = fopen("php://input", "rb");

                if ($in) {
                    while ($buff = fread($in, 4096)) {
                        fwrite($out, $buff);
                    }
                } else {
                    $result = array('jsonrpc' => "2.0", 'error' => array('code' => "101", 'message' => "Failed to open input stream."), 'id' => "id");
                    return $result;
                }

                fclose($in);
                fclose($out);
            } else {
                $result = array('jsonrpc' => "2.0", 'error' => array('code' => "102", 'message' => "Failed to open output stream."), 'id' => "id");
                return $result;
            }
        }

        // Check if file has been uploaded
        if (!$chunks || $chunk == $chunks - 1) {
            // Strip the temp .part suffix off
            rename("{$filePath}.part", $filePath);
        }


        // Return JSON-RPC response
        $result = array('jsonrpc' => "2.0", 'result' => "null", 'id' => "id", 'filename' => $fileNameFromParameter);
        return $result;
    }

    private function thumb(string $file, string $save, int $width, int $height, bool $prop = true): bool
    {
        $infos = getimagesize($file);

        if (!$infos) {
            return false;
        }

        $iNewW = 0;
        $iNewH = 0;

        if ($prop) {
            $iWidth = $infos[0];
            $iHeight = $infos[1];
            $iRatioW = $width / $iWidth;
            $iRatioH = $height / $iHeight;
            if ($iRatioW < $iRatioH) {
                $iNewW = (int)($iWidth * $iRatioW);
                $iNewH = (int)($iHeight * $iRatioW);
            } else {
                $iNewW = (int)($iWidth * $iRatioH);
                $iNewH = (int)($iHeight * $iRatioH);
            }
        } else {
            $iNewW = (int)$width;
            $iNewH = (int)$height;
        }
        if ($infos[2] == 1) {
            $imgA = imagecreatefromgif($file);

            if ($iNewW > 0  && $iNewH > 0 && $imgA != false) {
                $imgB = imagecreatetruecolor($iNewW, $iNewH);
                imagecopyresampled($imgB, $imgA, 0, 0, 0, 0, $iNewW, $iNewH, $infos[0], $infos[1]);
                ImageDestroy($imgA);
                imagegif($imgB, $save);
            }
        }
        if ($infos[2] == 2) {
            $imgA = imagecreatefromjpeg($file);
            if ($iNewW > 0  && $iNewH > 0 && $imgA != false) {
                $imgB = imagecreatetruecolor($iNewW, $iNewH);
                imagecopyresampled(
                    $imgB,
                    $imgA,
                    0,
                    0,
                    0,
                    0,
                    $iNewW,
                    $iNewH,
                    $infos[0],
                    $infos[1]
                );
                ImageDestroy($imgA);
                imagejpeg($imgB, $save, 100);
            }
        } elseif ($infos[2] == 3) {
            $imgA = imagecreatefrompng($file);
            if ($iNewW > 0  && $iNewH > 0 && $imgA != false) {
                $imgB = imagecreatetruecolor($iNewW, $iNewH);
                imagecopyresampled($imgB, $imgA, 0, 0, 0, 0, $iNewW, $iNewH, $infos[0], $infos[1]);
                ImageDestroy($imgA);
                imagepng($imgB, $save, 0);
            }
        } else {
            return false;
        }
        return true;
    }
}

$galleryUploader = ComponentBuilder::buildDependencies()->make('marsl\admin\ajax\GalleryUploader');

if ($galleryUploader instanceof GalleryUploader) {
    $galleryUploader->display();
}
