<?php

namespace marsl\admin\ajax;

include_once(dirname(__FILE__)."/../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../autoload.php");

use Exception;
use marsl\ComponentBuilder;
use marsl\includes\DB;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\user\Authentication;
use marsl\user\Role;

class NewsPhoto
{
    private Authentication $authentication;
    private DB $db;
    private IRequestParametersService $requestParametersService;
    private Role $role;

    public function __construct(
        Authentication $authentication,
        DB $db,
        IRequestParametersService $requestParametersService,
        Role $role
    ) {
        $this->authentication = $authentication;
        $this->db = $db;
        $this->requestParametersService = $requestParametersService;
        $this->role = $role;
    }

    public function display(): void
    {
        if ($this->authentication->moduleAdminAllowed("news", $this->role->getRole())) {

            if ($this->authentication->checkToken(
                $this->requestParametersService->fromPost()->getIntegerParameter("authTime", 0),
                $this->requestParametersService->fromPost()->getStringParameter("authToken", "")
            )) {
                mt_srand(time());
                $random = mt_rand();
                $dir = "../../news/";
                $imgData = $this->requestParametersService->fromPost()->getStringParameter("data", "");
                $imgData = str_replace('data:image/jpeg;base64,', '', $imgData);
                $imgData = str_replace(' ', '+', $imgData);
                $imgData = base64_decode($imgData);
                $fileName = $random.$this->requestParametersService->fromPost()->getStringParameter("fileName", "");
                $fileLink = $dir.$fileName;
                if (file_put_contents($fileLink, $imgData)) {
                    $picinfo = @getimagesize($fileLink);

                    if (!$picinfo) {
                        throw new Exception();
                    }

                    if (getimagesize($fileLink)) {
                        $width = $picinfo[0];
                        $height = $picinfo[1];
                        $fileName = $this->db->escapeString($fileName);
                        $photograph = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("photograph", ""));
                        if ($this->requestParametersService->fromPost()->getStringParameter("type", "") == "teaser") {
                            if (($width > 200) || ($height > 200)) {
                                $this->thumb($fileLink, $fileLink, 200, 200, true);
                            }
                            $this->db->query("INSERT INTO `news_picture`(`url`, `photograph`) VALUES('$fileName', '$photograph')");
                            $pictureID = $this->db->lastInsertedID();
                            $result = array('type' => "success", 'id' => $pictureID, 'file' => $fileName);
                            echo json_encode($result);
                        }

                        if ($this->requestParametersService->fromPost()->getStringParameter("type", "") == "text") {
                            $subtitle = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("subtitle", ""));
                            if (($width > 1280) || ($height > 1280)) {
                                $this->thumb($fileLink, $fileLink, 1280, 1280, true);
                            }
                            if (($width < 640) || ($height < 320)) {
                                unlink($fileLink);
                                $result = array('type' => "error", 'code' => "2", 'text' => "The size of the photo is not correct.");
                                echo json_encode($result);
                            } else {
                                $this->db->query("INSERT INTO `news_picture`(`url`, `photograph`,`subtitle`) VALUES('$fileName', '$photograph','$subtitle')");
                                $pictureID = $this->db->lastInsertedID();
                                $result = array('type' => "success", 'id' => $pictureID, 'file' => $fileName);
                                echo json_encode($result);
                            }
                        }
                    } else {
                        unlink($fileLink);
                        $result = array('type' => "error", 'code' => "1", 'text' => "Not a picture.");
                        echo json_encode($result);
                    }

                } else {
                    $result = array('type' => "error", 'code' => "3", 'text' => "File system error.");
                    echo json_encode($result);
                }

            }

        }

        $this->db->close();

    }


    private function thumb(string $file, string $save, int $width, int $height, bool $prop = true): bool
    {
        $infos = getimagesize($file);

        if (!$infos) {
            return false;
        }

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
                @unlink($file);
                imagejpeg($imgB, $save);
            }
        } else {
            return false;
        }
        return true;
    }

}


$newsPhoto = ComponentBuilder::buildDependencies()->make('marsl\admin\ajax\NewsPhoto');

if ($newsPhoto instanceof NewsPhoto) {
    $newsPhoto->display();
}
