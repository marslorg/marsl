<?php

namespace marsl\ajax\general;

include_once(dirname(__FILE__)."/../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../autoload.php");


use marsl\ComponentBuilder;
use marsl\includes\Basic;
use marsl\includes\DB;
use marsl\includes\Encryption;
use marsl\Infrastructure\PHPConfiguration\Adapters\Drivers\Service\IPHPConfigurationService;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\user\Authentication;
use marsl\user\Role;
use marsl\user\User;

class FileUploader
{
    private Authentication $authentication;
    private Basic $basic;
    private DB $db;
    private Encryption $encryption;
    private IPHPConfigurationService $phpConfigurationService;
    private IRequestParametersService $requestParametersService;
    private User $user;

    public function __construct(
        Authentication $authentication,
        Basic $basic,
        DB $db,
        Encryption $encryption,
        IPHPConfigurationService $phpConfigurationService,
        IRequestParametersService $requestParametersService,
        User $user
    ) {
        $this->authentication = $authentication;
        $this->basic = $basic;
        $this->db = $db;
        $this->encryption = $encryption;
        $this->phpConfigurationService = $phpConfigurationService;
        $this->requestParametersService = $requestParametersService;
        $this->user = $user;
    }

    public function display(): void
    {
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

        if ($this->authentication->checkToken(
            $this->requestParametersService->fromGet()->getIntegerParameter("time"),
            $this->requestParametersService->fromGet()->getStringParameter("token")
        )) {
            if (!$this->user->isGuest()) {
                $directory = $this->db->escapeString($this->requestParametersService->fromGet()->getStringParameter("temporary"));

                $uploadResult = $this->upload();

                if (isset($uploadResult['filename'])) {
                    $fileName = $uploadResult['filename'];
                    if (is_string($fileName)) {
                        $fileName = $this->db->escapeString($fileName);
                        $filePath = $this->phpConfigurationService->getConfigurationEntryAsString("upload_tmp_dir") . $directory . DIRECTORY_SEPARATOR . $fileName;
                        if (file_exists($filePath)) {
                            $fileContent = file_get_contents($filePath);
                            if (is_string($fileContent)) {
                                $key = $this->generateKey();
                                $newFile = $this->generateFileName();
                                $cryptedContent = $this->encryption->encrypt($fileContent, $key);
                                file_put_contents("../../files/".$newFile, $cryptedContent);
                                @unlink($filePath);
                                @rmdir($this->phpConfigurationService->getConfigurationEntryAsString("upload_tmp_dir") . $directory);
                                $this->db->query("INSERT INTO `attachment`(`servername`, `realname`, `key`, `temporary`) VALUES('$newFile', '$fileName', '$key', '$directory')");
                            }
                        }
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
     * @return array<string, array<string, string>|string>
     */
    private function upload(): array
    {
        $directory = $this->requestParametersService->fromGet()->getStringParameter("temporary", "");
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

            $extPosition = null;

            if (is_int($ext)) {
                $extPosition = $ext;

                $fileName_a = substr($fileName, 0, $extPosition);
                $fileName_b = substr($fileName, $extPosition);

                $count = 1;
                while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b)) {
                    $count++;
                }

                $fileName = $fileName_a . '_' . $count . $fileName_b;
            }
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

        // Look for the content type header
        $contentType = $this->requestParametersService->fromServer()->getStringParameter("HTTP_CONTENT_TYPE", "");
        $contentType = $this->requestParametersService->fromServer()->getStringParameter("CONTENT_TYPE", $contentType);

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

    private function generateFileName(): string
    {
        $filename = $this->db->escapeString($this->basic->randomHash());
        while ($this->db->isExisting("SELECT `servername` FROM `attachment` WHERE `servername`='$filename' LIMIT 1")) {
            $filename = $this->db->escapeString($this->basic->randomHash());
        }
        return $filename;
    }

    private function generateKey(): string
    {
        $key = $this->db->escapeString($this->basic->randomHash());
        while ($this->db->isExisting("SELECT `key` FROM `attachment` WHERE `key`='$key' LIMIT 1")) {
            $key = $this->db->escapeString($this->basic->randomHash());
        }
        return $key;
    }
}


$fileUploader = ComponentBuilder::buildDependencies()->make('marsl\ajax\general\FileUploader');

if ($fileUploader instanceof FileUploader) {
    $fileUploader->display();
}
