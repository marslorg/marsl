<?php

namespace marsl\admin;

include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../autoload.php");


use marsl\ComponentBuilder;
use marsl\includes\Basic;
use marsl\includes\DB;
use marsl\includes\Configuration;
use marsl\Infrastructure\PHPConfiguration\Adapters\Drivers\Service\IPHPConfigurationService;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\user\Authentication;
use marsl\user\Role;
use marsl\user\User;

class Gallery
{
    private Authentication $authentication;
    private Basic $basic;
    private Configuration $configuration;
    private DB $db;
    private IPHPConfigurationService $phpConfigurationService;
    private IRequestParametersService $requestParametersService;
    private Role $role;
    private User $user;

    public function __construct(
        Authentication $authentication,
        Basic $basic,
        Configuration $configuration,
        DB $db,
        IPHPConfigurationService $phpConfigurationService,
        IRequestParametersService $requestParametersService,
        Role $role,
        User $user
    ) {
        $this->authentication = $authentication;
        $this->basic = $basic;
        $this->configuration = $configuration;
        $this->db = $db;
        $this->phpConfigurationService = $phpConfigurationService;
        $this->requestParametersService = $requestParametersService;
        $this->role = $role;
        $this->user = $user;
    }

    /*
     * Creates a new gallery dataset in the database and thumbnails files in a given folder.
     * Expects a folder location via post request.
     */
    public function newGal(): void
    {
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        date_default_timezone_set($this->configuration->getTimezone());
        if ($this->user->isAdmin()) {
            $authTime = $this->requestParametersService->fromGet()->getIntegerParameter("time");
            $authToken = $this->requestParametersService->fromGet()->getStringParameter("token");
            if ($this->authentication->checkToken($authTime, $authToken)) {
                if ($this->authentication->moduleExtendedAllowed("gallery", $this->role->getRole())
                    || $this->authentication->moduleAdminAllowed("gallery", $this->role->getRole())) {
                    $id = $this->requestParametersService->fromGet()->getIntegerParameter("id", -1);
                    if ($id != -1) {
                        $folder = "";
                        $result = $this->db->query("SELECT `folder` FROM `album` WHERE `album`='$id'");
                        while ($row = $this->db->fetchArray($result)) {
                            if (is_string($row['folder'])) {
                                $folder = $row['folder'];
                            }
                        }
                        $dir = "../albums/";
                        $path = $dir.$folder;
                        $handle = opendir($path);
                        if (!$handle) {
                            header("Location: index.php?var=module&module=gallery&failure=1");
                        } else {
                            $maxTime = intval($this->phpConfigurationService->getConfigurationEntryAsString("max_execution_time")) - 1;
                            if ($maxTime > 10) {
                                $maxTime = 10;
                            }
                            $start = time();
                            $i = 0;
                            $pause = $this->requestParametersService->fromPost()->getIntegerParameter("pause", -1); //will continue here after break
                            while ($file = readdir($handle)) {
                                $file = $this->db->escapeString($file);
                                $cur = time();
                                $diff = $cur - $start;
                                if ($diff < $maxTime) {
                                    if ($i > $pause) {
                                        if ($file != "." && $file != "..") {
                                            if (!is_dir($file)) {
                                                $sub = strtolower(substr($file, -4));
                                                $sub2 = substr($file, 0, 6);
                                                if ($sub2 != "thumb_") {
                                                    if ($sub == ".jpg" || $sub == ".png" || $sub == ".gif") {
                                                        if (!$this->db->isExisting("SELECT `album` FROM `picture` WHERE `album`='$id' AND `filename`='$file' LIMIT 1")) {
                                                            $this->db->query("INSERT INTO `picture`(`album`,`filename`,`deleted`,`visible`) VALUES('$id','$file','0','0')");
                                                            $from = $path.$file;
                                                            $to = $path."thumb_".$file;
                                                            $this->thumb($from, $to, 200, 200, true);
                                                            chmod($to, 0644);
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    $i++;
                                } else {
                                    $newPause = $i - 1;
                                    header("Location: gallery.php?id=".$id."&pause=".$newPause."&time=".$authTime."&token=".$authToken);
                                }
                            }
                        }
                        require_once("template/gallery.newgal.tpl.php");
                    } else {
                        if ($this->requestParametersService->fromPost()->getStringParameter("action", "") != "") {
                            $folder = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("folder"));
                            $photograph = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("photograph"));
                            $location = $this->requestParametersService->fromPost()->getIntegerParameter("category");
                            $day = $this->requestParametersService->fromPost()->getIntegerParameter("day");
                            $month = $this->requestParametersService->fromPost()->getIntegerParameter("month");
                            $year = $this->requestParametersService->fromPost()->getIntegerParameter("year");
                            $date = 0;
                            if (checkdate($month, $day, $year)) {
                                $date = mktime(0, 0, 0, $month, $day, $year);
                            } else {
                                $date = time();
                            }
                            $description = $this->db->escapeString($this->basic->cleanHTML($this->requestParametersService->fromPost()->getStringParameter("description")));
                            $author = $this->user->getID();
                            $authorIP = $this->db->escapeString($this->requestParametersService->fromServer()->getStringParameter("REMOTE_ADDR"));
                            $postdate = time();
                            $location = $this->requestParametersService->fromPost()->getIntegerParameter("category");
                            if ($this->authentication->locationExtendedAllowed($location, $this->role->getRole())
                                || $this->authentication->locationAdminAllowed($location, $this->role->getRole())) {
                                $this->db->query("INSERT INTO `album`(`name`,`author`,`author_ip`,`photograph`,`description`,`folder`,`visible`,`deleted`,`date`,`postdate`,`location`)
								VALUES(' ','$author','$authorIP','$photograph','$description','$folder','0','0','$date','$postdate','$location')");
                                $id = $this->db->lastInsertedID();
                                header("Location: gallery.php?id=".$id."&time=".$authTime."&token=".$authToken);
                            } else {
                                header("Location: index.php?var=module&module=gallery");
                            }
                        } else {
                            header("Location: index.php?var=module&module=gallery");
                        }
                    }
                }
            }
        }
        $this->db->close();
    }

    private function thumb(string $file, string $save, int $width, int $height, bool $prop = true): bool
    {
        $infos = getimagesize($file);

        if (is_bool($infos)) {
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
                imagejpeg($imgB, $save);
            }
        } elseif ($infos[2] == 3) {
            $imgA = imagecreatefrompng($file);

            if ($iNewW > 0  && $iNewH > 0 && $imgA != false) {
                $imgB = imagecreatetruecolor($iNewW, $iNewH);
                imagecopyresampled($imgB, $imgA, 0, 0, 0, 0, $iNewW, $iNewH, $infos[0], $infos[1]);
                ImageDestroy($imgA);
                imagepng($imgB, $save);
            }
        } else {
            return false;
        }
        return true;
    }
}


$gallery = ComponentBuilder::buildDependencies()->make('marsl\admin\Gallery');

if ($gallery instanceof Gallery) {
    $gallery->newGal();
}
