<?php

namespace marsl\modules;

include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../includes/web-push-php-6.0.5/vendor/autoload.php");
include_once(dirname(__FILE__)."/../includes/slugify/vendor/autoload.php");
include_once(dirname(__FILE__)."/../autoload.php");

use DateTime;
use DateTimeZone;
use Cocur\Slugify\Slugify;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use marsl\ComponentBuilder;
use marsl\includes\Basic;
use marsl\includes\Configuration;
use marsl\includes\DB;
use marsl\includes\Mailer;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\modules\Module;
use marsl\modules\Navigation;
use marsl\user\Authentication;
use marsl\user\Role;
use marsl\user\User;

class News implements Module
{
    private Authentication $authentication;
    private Basic $basic;
    private Configuration $configuration;
    private DB $db;
    private IRequestParametersService $requestParametersService;
    private Mailer $mailer;
    private Navigation $navigation;
    private Role $role;
    private User $user;

    private int $PAGINATION_DISTANCE = 3;

    /**
     * @var array<string>
     */
    private array $baseLinks = array();

    public function __construct(
        Authentication $authentication,
        Basic $basic,
        Configuration $configuration,
        DB $db,
        IRequestParametersService $requestParametersService,
        Mailer $mailer,
        Navigation $navigation,
        Role $role,
        User $user
    ) {
        $this->authentication = $authentication;
        $this->basic = $basic;
        $this->configuration = $configuration;
        $this->db = $db;
        $this->mailer = $mailer;
        $this->navigation = $navigation;
        $this->requestParametersService = $requestParametersService;
        $this->role = $role;
        $this->user = $user;
    }

    /*
     * Displays the admin interface of the news module.
     */
    public function admin(): void
    {
        $modules = $this->basic->getModules();
        $moduleTags = array();

        $dateTime = new DateTime("now", new DateTimeZone($this->configuration->getTimezone()));

        $domain = $this->configuration->getDomain();

        foreach ($modules as $module) {
            $classPath = "\\marsl\\modules\\".$module['class'];
            $class = ComponentBuilder::buildDependencies()->make($classPath);
            if ($class instanceof Module) {
                if ($class->isTaggable()) {
                    $tagList = $class->getTagList();
                    foreach ($tagList as $tagType) {
                        $typeID = $module['file']."_".$tagType['type'];
                        $typeName = $tagType['text'];
                        array_push($moduleTags, array('type' => $typeID, 'name' => $typeName));
                    }
                }
            }
        }
        if ($this->authentication->moduleAdminAllowed("news", $this->role->getRole())) {
            require_once(dirname(__FILE__)."/../admin/template/news.navigation.tpl.php");
            $getAction = $this->requestParametersService->fromGet()->getStringParameter("action", "");
            $postAction = $this->requestParametersService->fromPost()->getStringParameter("action", "");
            $postCorrected = $this->requestParametersService->fromPost()->getIntegerParameter("corrected", 0);
            if ($getAction == "") {
                /*
                 * TODO Tag-Editing
                 */
                $headline = "";
                $corrected = false;
                $title = "";
                $category = "";
                $day = "DD";
                $month = "MM";
                $year = "YYYY";
                $teaser = "";
                $text = "";
                $city = "";
                $tmpModuleTags = array();
                foreach ($moduleTags as $moduleTag) {
                    $moduleTag['tags'] = "";
                    array_push($tmpModuleTags, $moduleTag);
                }
                $moduleTags = $tmpModuleTags;
                $new = true;
                if ($postAction != "") {
                    $new = false;
                    if ($this->authentication->checkToken(
                        $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                        $this->requestParametersService->fromPost()->getStringParameter("authToken")
                    )) {
                        $failed = false;
                        $author = $this->user->getID();
                        $authorIP = $this->db->escapeString($this->requestParametersService->fromServer()->getStringParameter("REMOTE_ADDR", ""));
                        $headline = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("headline", ""));
                        $title = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("title", ""));
                        $location = $this->requestParametersService->fromPost()->getIntegerParameter("category", -1);
                        $corrected = $postCorrected;
                        $tmpModuleTags = array();
                        foreach ($moduleTags as $moduleTag) {
                            $moduleTag['tags'] = $this->requestParametersService->fromPost()->getStringParameter($moduleTag['type'], "");
                            array_push($tmpModuleTags, $moduleTag);
                        }
                        $moduleTags = $tmpModuleTags;
                        $date = "";
                        $postdate = time();
                        $city = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("city", ""));

                        $month = $this->requestParametersService->fromPost()->getIntegerParameter("month", 0);
                        $day = $this->requestParametersService->fromPost()->getIntegerParameter("day", 0);
                        $year = $this->requestParametersService->fromPost()->getIntegerParameter("year", 0);
                        if (checkdate($month, $day, $year)) {
                            $date = mktime(0, 0, 0, $month, $day, $year);
                        } else {
                            $date = time();
                        }
                        $teaser = $this->db->escapeString(
                            $this->basic->cleanHTML(
                                $this->requestParametersService->fromPost()->getStringParameter("teaser", "")
                            )
                        );
                        $text = $this->db->escapeString(
                            $this->basic->cleanHTML(
                                $this->requestParametersService->fromPost()->getStringParameter("text", "")
                            )
                        );
                        if ($this->authentication->locationAdminAllowed($location, $this->role->getRole())
                            || $this->authentication->locationExtendedAllowed($location, $this->role->getRole())) {

                            $picture1 = $this->requestParametersService->fromPost()->getIntegerParameter("picture1", 0);

                            $picture2 = $this->requestParametersService->fromPost()->getIntegerParameter("picture2", 0);
                            if ($picture2 != 0) {
                                $result = $this->db->query("SELECT `url` FROM `news_picture` WHERE `picture`='$picture2'");
                                while ($row = $this->db->fetchArray($result)) {
                                    if (is_string($row['url'])) {
                                        $fileName = $row['url'];
                                        $fileLink = "../news/".$fileName;
                                        $oldIMG = imagecreatefromjpeg($fileLink);
                                        $newIMG = imagecreatetruecolor(640, 320);
                                        $pic2X = $this->requestParametersService->fromPost()->getIntegerParameter("pic2X", 0);
                                        $pic2Y = $this->requestParametersService->fromPost()->getIntegerParameter("pic2Y", 0);
                                        $pic2W = $this->requestParametersService->fromPost()->getIntegerParameter("pic2W", 0);
                                        $pic2H = $this->requestParametersService->fromPost()->getIntegerParameter("pic2H", 0);
                                        unlink($fileLink);
                                        $fileName = "r".$fileName;
                                        $fileLink = "../news/".$fileName;
                                        $this->db->query("UPDATE `news_picture` SET `url`='$fileName' WHERE `picture`='$picture2'");
                                        if (!is_bool($oldIMG)) {
                                            imagecopyresampled($newIMG, $oldIMG, 0, 0, $pic2X, $pic2Y, 640, 320, $pic2W, $pic2H);
                                            ImageDestroy($oldIMG);
                                            imagejpeg($newIMG, $fileLink);
                                        }
                                    }
                                }
                            }

                            $this->db->query("INSERT INTO `news`(`author`,`author_ip`,`headline`,`title`,`teaser`,`text`,`picture1`,`picture2`,`date`,`visible`,`deleted`,`location`,`city`,`postdate`,`corrected`) 
							VALUES('$author','$authorIP','$headline','$title','$teaser','$text','$picture1','$picture2','$date','0','0','$location','$city','$postdate','$corrected')");
                            $newsID = (int)$this->db->lastInsertedID();
                            foreach ($moduleTags as $moduleTag) {

                                $type = explode("_", $moduleTag['type']);
                                $file = $type[0];
                                $scope = $type[1];
                                $module = $this->basic->getModule($file);
                                if (isset($module['class'])) {
                                    $classPath = "\\marsl\\modules\\".$module['class'];
                                    $class = ComponentBuilder::buildDependencies()->make($classPath);

                                    if ($class instanceof Module) {
                                        $class->addTags($moduleTag['tags'], $scope, $newsID);
                                    }
                                }
                            }
                            $administrators = $this->user->getAdminUsers();
                            foreach ($administrators as $administrator) {
                                $administratorRole = $this->role->getRolebyUser($administrator);
                                if ($this->authentication->moduleAdminAllowed("news", $administratorRole)) {
                                    if ($this->authentication->locationAdminAllowed($location, $administratorRole)) {
                                        $this->mailer->sendNewArticleMail($administrator);
                                    }
                                }
                            }
                            $headline = "";
                            $title = "";
                            $category = "";
                            $day = "DD";
                            $month = "MM";
                            $year = "YYYY";
                            $teaser = "";
                            $text = "";
                            $city = "";
                            $corrected = false;
                            $tmpModuleTags = array();
                            foreach ($moduleTags as $moduleTag) {
                                $moduleTag['tags'] = "";
                                array_push($tmpModuleTags, $moduleTag);
                            }
                            $moduleTags = $tmpModuleTags;
                        }
                    }
                }
                $locations = array();
                $result = $this->db->query("SELECT `id`, `name` FROM `navigation` WHERE `module`='news' AND `type` IN ('1','2') ORDER BY `pos`");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['id'])
                        && is_string($row['name'])) {
                        $id = intval(strval($row['id']));
                        if ($this->authentication->locationAdminAllowed($id, $this->role->getRole())
                            || $this->authentication->locationExtendedAllowed($id, $this->role->getRole())) {
                            array_push($locations, array('location' => $id,'name' => $this->basic->convertToHTMLEntities($row['name'])));
                        }
                    }
                }
                $authTime = time();
                $authToken = $this->authentication->getToken($authTime);
                require_once(dirname(__FILE__)."/../admin/template/news.write.tpl.php");
            } elseif ($getAction == "queue") {
                $this->doGetActions();
                $news = array();
                $result = $this->db->query("SELECT
				`news`, `author`, `corrected`, `author_ip`, `location`, `headline`, `title`, `teaser`, `text`, `picture1`, `picture2`, `city`, `date`, `postdate`, `url`, `photograph`
				FROM `news`
				LEFT JOIN `news_picture` ON `picture`=`picture1`
				WHERE `visible`='0' AND `deleted`='0'");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['location'])
                        && is_string($row['news'])
                        && is_string($row['author'])
                        && is_string($row['corrected'])
                        && is_string($row['author_ip'])
                        && ($row['headline'] == null || is_string($row['headline']))
                        && ($row['title'] == null || is_string($row['title']))
                        && ($row['teaser'] == null || is_string($row['teaser']))
                        && ($row['text'] == null || is_string($row['text']))
                        && ($row['picture1'] == null || is_string($row['picture1']))
                        && ($row['picture2'] == null || is_string($row['picture2']))
                        && ($row['url'] == null || is_string($row['url']))
                        && ($row['photograph'] == null || is_string($row['photograph']))
                        && ($row['city'] == null || is_string($row['city']))
                        && is_string($row['date'])
                        && is_string($row['postdate'])) {
                        $location = intval(strval($row['location']));
                        if ($this->authentication->locationAdminAllowed($location, $this->role->getRole())) {
                            $id = intval(strval($row['news']));
                            $author = intval(strval($row['author']));
                            $corrected = intval(strval($row['corrected']));
                            $authorName = $this->basic->convertToHTMLEntities($this->user->getAcronymbyID($author, $this->authentication));
                            $authorIP = $this->basic->convertToHTMLEntities($row['author_ip']);
                            $locationName = $this->basic->convertToHTMLEntities($this->navigation->getNamebyID($location));
                            $headline = $this->basic->convertToHTMLEntities(is_string($row['headline']) ? $row['headline'] : "");
                            $title = $this->basic->convertToHTMLEntities(is_string($row['title']) ? $row['title'] : "");
                            $teaser = is_string($row['teaser']) ? $row['teaser'] : "";
                            $text = is_string($row['text']) ? $row['text'] : "";
                            $picID1 = is_string($row['picture1']) ? intval(strval($row['picture1'])) : 0;
                            $picID2 = is_string($row['picture2']) ? intval(strval($row['picture2'])) : 0;
                            $picture1 = $this->basic->convertToHTMLEntities(is_string($row['url']) ? $row['url'] : "");
                            if ($picture1 == "") {
                                $picture1 = "empty";
                            }
                            $photograph1 = "";
                            if (is_string($row['photograph']) && $row['photograph'] != "") {
                                $photograph1 = "<br />Foto: ".$this->basic->convertToHTMLEntities($row['photograph']);
                            }
                            $city = $this->basic->convertToHTMLEntities(is_string($row['city']) ? $row['city'] : "");
                            $dateTime->setTimestamp(intval(strval($row['date'])));
                            $date = $dateTime->format("d\.m\.Y");
                            $dateTime->setTimestamp(intval(strval($row['postdate'])));
                            $postdate = $dateTime->format("d\. M Y \u\m H\:i\:s");
                            array_push($news, array('author' => $authorName,'authorIP' => $authorIP,'news' => $id, 'location' => $locationName, 'headline' => $headline, 'title' => $title, 'teaser' => $teaser, 'picture1' => $picture1, 'photograph1' => $photograph1, 'city' => $city, 'date' => $date, 'postdate' => $postdate, 'text' => $text, 'corrected' => $corrected));
                        }
                    }
                }
                $authTime = time();
                $authToken = $this->authentication->getToken($authTime);
                require_once(dirname(__FILE__)."/../admin/template/news.queue.tpl.php");
            } elseif ($getAction == "edit") {
                $id = $this->requestParametersService->fromGet()->getIntegerParameter("id", -1);
                $result = $this->db->query("SELECT `corrected`, `headline`, `title`, `location`, `date`, `teaser`, `text`, `picture1`, `picture2`, `city` FROM `news` WHERE `news`='$id' AND `deleted`='0'");
                $picture1 = 0;
                $picture2 = 0;
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['location'])
                        && is_string($row['corrected'])
                        && ($row['headline'] == null || is_string($row['headline']))
                        && ($row['title'] == null || is_string($row['title']))
                        && ($row['teaser'] == null || is_string($row['teaser']))
                        && ($row['text'] == null || is_string($row['text']))
                        && ($row['picture1'] == null || is_string($row['picture1']))
                        && ($row['picture2'] == null || is_string($row['picture2']))
                        && ($row['city'] == null || is_string($row['city']))
                        && is_string($row['date'])) {
                        $location = intval(strval($row['location']));
                        if ($this->authentication->locationAdminAllowed($location, $this->role->getRole())) {
                            $corrected = intval(strval($row['corrected']));
                            $headline = $this->basic->convertToHTMLEntities(is_string($row['headline']) ? $row['headline'] : "");
                            $title = $this->basic->convertToHTMLEntities(is_string($row['title']) ? $row['title'] : "");
                            $category = $location;
                            $dateTime->setTimestamp(intval(strval($row['date'])));
                            $day = $dateTime->format("d");
                            $month = $dateTime->format("m");
                            $year = $dateTime->format("Y");
                            $teaser = is_string($row['teaser']) ? $row['teaser'] : "";
                            $text = is_string($row['text']) ? $row['text'] : "";
                            $picture1 = is_string($row['picture1']) ? intval(strval($row['picture1'])) : 0;
                            $picture2 = is_string($row['picture2']) ? intval(strval($row['picture2'])) : 0;
                            $city = $this->basic->convertToHTMLEntities(is_string($row['city']) ? $row['city'] : "");
                            $tmpModuleTags = array();
                            foreach ($moduleTags as $moduleTag) {

                                $type = explode("_", $moduleTag['type']);
                                $file = $type[0];
                                $scope = $type[1];
                                $module = $this->basic->getModule($file);

                                if (isset($module['class'])) {
                                    $classPath = "\\marsl\\modules\\".$module['class'];
                                    $class = ComponentBuilder::buildDependencies()->make($classPath);

                                    if ($class instanceof Module) {
                                        $moduleTag['tags'] = $this->basic->convertToHTMLEntities($class->getTagString($scope, $id));

                                        array_push($tmpModuleTags, $moduleTag);
                                    }
                                }
                            }
                            $moduleTags = $tmpModuleTags;
                            $new = true;
                            $failed = false;
                            if ($postAction != "") {
                                $new = false;
                                if ($this->authentication->checkToken(
                                    $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                                    $this->requestParametersService->fromPost()->getStringParameter("authToken")
                                )) {
                                    $failed = false;
                                    $author = $this->user->getID();
                                    $authorIP = $this->db->escapeString($this->requestParametersService->fromServer()->getStringParameter("REMOTE_ADDR", ""));
                                    $headline = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("headline", ""));
                                    $title = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("title", ""));
                                    $location = $this->requestParametersService->fromPost()->getIntegerParameter("category", 0);
                                    $corrected = $postCorrected;
                                    $date = "";
                                    $postdate = time();
                                    $city = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("city", ""));
                                    $month = $this->requestParametersService->fromPost()->getIntegerParameter("month", 0);
                                    $day = $this->requestParametersService->fromPost()->getIntegerParameter("day", 0);
                                    $year = $this->requestParametersService->fromPost()->getIntegerParameter("year", 0);
                                    if (checkdate($month, $day, $year)) {
                                        $date = mktime(0, 0, 0, $month, $day, $year);
                                    } else {
                                        $date = time();
                                    }
                                    $teaser = $this->db->escapeString($this->basic->cleanHTML($this->requestParametersService->fromPost()->getStringParameter("teaser", "")));
                                    $text = $this->db->escapeString($this->basic->cleanHTML($this->requestParametersService->fromPost()->getStringParameter("text", "")));

                                    $picture1 = $this->requestParametersService->fromPost()->getIntegerParameter("picture1", $picture1);

                                    $picture2Posted = $this->requestParametersService->fromPost()->getIntegerParameter("picture2", 0);
                                    if ($picture2Posted != 0) {
                                        $picture2 = $picture2Posted;
                                        $result = $this->db->query("SELECT `url` FROM `news_picture` WHERE `picture`='$picture2'");
                                        while ($row = $this->db->fetchArray($result)) {
                                            if (is_string($row['url'])) {
                                                $fileName = $row['url'];
                                                $fileLink = "../news/".$fileName;
                                                $oldIMG = imagecreatefromjpeg($fileLink);
                                                $newIMG = imagecreatetruecolor(640, 320);
                                                $pic2X = $this->requestParametersService->fromPost()->getIntegerParameter("pic2X", 0);
                                                $pic2Y = $this->requestParametersService->fromPost()->getIntegerParameter("pic2Y", 0);
                                                $pic2W = $this->requestParametersService->fromPost()->getIntegerParameter("pic2W", 0);
                                                $pic2H = $this->requestParametersService->fromPost()->getIntegerParameter("pic2H", 0);
                                                unlink($fileLink);
                                                $fileName = "r".$fileName;
                                                $fileLink = "../news/".$fileName;
                                                $this->db->query("UPDATE `news_picture` SET `url`='$fileName' WHERE `picture`='$picture2'");

                                                if (!is_bool($oldIMG)) {
                                                    imagecopyresampled($newIMG, $oldIMG, 0, 0, $pic2X, $pic2Y, 640, 320, $pic2W, $pic2H);
                                                    ImageDestroy($oldIMG);
                                                    imagejpeg($newIMG, $fileLink);
                                                }
                                            }
                                        }
                                    }

                                    $tmpModuleTags = array();
                                    foreach ($moduleTags as $moduleTag) {
                                        $moduleTag['tags'] = $this->requestParametersService->fromPost()->getStringParameter($moduleTag['type'], "");
                                        array_push($tmpModuleTags, $moduleTag);
                                    }
                                    $moduleTags = $tmpModuleTags;
                                    $admin = $this->user->getID();
                                    $adminIP = $this->db->escapeString($this->requestParametersService->fromServer()->getStringParameter("REMOTE_ADDR", ""));
                                    $this->db->query("UPDATE `news` SET `date`='$date', `admin`='$admin', `admin_ip`='$adminIP', `headline`='$headline', `title`='$title', `teaser`='$teaser', `text`='$text', `picture1`='$picture1', `picture2`='$picture2', `location`='$location', `city`='$city', `corrected`='$corrected' WHERE `news`='$id'");
                                    foreach ($moduleTags as $moduleTag) {

                                        $type = explode("_", $moduleTag['type']);
                                        $file = $type[0];
                                        $scope = $type[1];
                                        $module = $this->basic->getModule($file);

                                        if (isset($module['class'])) {
                                            $classPath = "\\marsl\\modules\\".$module['class'];
                                            $class = ComponentBuilder::buildDependencies()->make($classPath);

                                            if ($class instanceof Module) {
                                                $class->addTags($moduleTag['tags'], $scope, $id);
                                            }
                                        }

                                    }
                                    $headline = $this->basic->convertToHTMLEntities($this->requestParametersService->fromPost()->getStringParameter("headline", ""));
                                    $title = $this->basic->convertToHTMLEntities($this->requestParametersService->fromPost()->getStringParameter("title", ""));
                                    $category = $this->requestParametersService->fromPost()->getIntegerParameter("category", 0);
                                    $month = $this->requestParametersService->fromPost()->getIntegerParameter("month", 0);
                                    $day = $this->requestParametersService->fromPost()->getIntegerParameter("day", 0);
                                    $year = $this->requestParametersService->fromPost()->getIntegerParameter("year", 0);
                                    $teaser = $this->basic->cleanHTML($this->requestParametersService->fromPost()->getStringParameter("teaser", ""));
                                    $text = $this->basic->cleanHTML($this->requestParametersService->fromPost()->getStringParameter("text", ""));
                                    $city = $this->basic->cleanHTML($this->requestParametersService->fromPost()->getStringParameter("city", ""));
                                    $tmpModuleTags = array();
                                    foreach ($moduleTags as $moduleTag) {
                                        $moduleTag['tags'] = $this->basic->convertToHTMLEntities($this->requestParametersService->fromPost()->getStringParameter($moduleTag['type'], ""));
                                        array_push($tmpModuleTags, $moduleTag);
                                    }
                                    $moduleTags = $tmpModuleTags;
                                }
                            }
                            $locations = array();
                            $result2 = $this->db->query("SELECT `id`, `name` FROM `navigation` WHERE `module`='news' AND `type` IN('1','2') ORDER BY `pos`");
                            while ($row2 = $this->db->fetchArray($result2)) {
                                if (is_string($row2['id'])
                                    && is_string($row2['name'])) {
                                    $location = intval(strval($row2['id']));
                                    if ($this->authentication->locationAdminAllowed($location, $this->role->getRole())
                                        || $this->authentication->locationExtendedAllowed($location, $this->role->getRole())) {
                                        array_push($locations, array('location' => $location,'name' => $this->basic->convertToHTMLEntities($row2['name'])));
                                    }
                                }
                            }
                            $authTime = time();
                            $authToken = $this->authentication->getToken($authTime);
                            require_once(dirname(__FILE__)."/../admin/template/news.edit.tpl.php");
                        }
                    }
                }
            } elseif ($getAction == "news") {
                $this->doGetActions();
                $page = $this->requestParametersService->fromGet()->getIntegerParameter("page", $this->getPage());
                $result = $this->db->query("SELECT COUNT(`visible`) AS rowcount FROM `news` WHERE `visible`='1' AND `deleted`='0'");
                $pages = $this->db->getRowCount($result) / 10;
                $start = $page * 10 - 10;
                $end = 10;
                $news = array();
                $result = $this->db->query("SELECT
				`news`, `corrected`, `author`, `author_ip`, `location`, `date`, `postdate`, `headline`, `title`, `picture1`, `city`, `teaser`, `text`, `url`, `photograph`
				FROM `news`
				LEFT JOIN `news_picture` ON `picture`=`picture1`
				WHERE `visible`='1' AND `deleted`='0' ORDER BY `postdate` DESC LIMIT $start,$end");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['location'])
                        && is_string($row['news'])
                        && is_string($row['author'])
                        && is_string($row['corrected'])
                        && is_string($row['author_ip'])
                        && ($row['headline'] == null || is_string($row['headline']))
                        && ($row['title'] == null || is_string($row['title']))
                        && ($row['teaser'] == null || is_string($row['teaser']))
                        && ($row['text'] == null || is_string($row['text']))
                        && ($row['picture1'] == null || is_string($row['picture1']))
                        && ($row['url'] == null || is_string($row['url']))
                        && ($row['photograph'] == null || is_string($row['photograph']))
                        && ($row['city'] == null || is_string($row['city']))
                        && is_string($row['date'])
                        && is_string($row['postdate'])) {
                        $id = intval(strval($row['news']));
                        $corrected = intval(strval($row['corrected']));
                        $author = $this->basic->convertToHTMLEntities($this->user->getAcronymbyID(intval(strval($row['author'])), $this->authentication));
                        $authorIP = $this->basic->convertToHTMLEntities($row['author_ip']);
                        $category = intval(strval($row['location']));
                        $editLink = ($this->authentication->locationAdminAllowed($category, $this->role->getRole()));
                        $location = $this->navigation->getNamebyID($category);
                        $dateTime->setTimestamp(intval(strval($row['date'])));
                        $date = $dateTime->format("d\.m\.Y");
                        $dateTime->setTimestamp(intval(strval($row['postdate'])));
                        $postdate = $dateTime->format("d\. M Y \u\m H\:i\:s");
                        $headline = $this->basic->convertToHTMLEntities(is_string($row['headline']) ? $row['headline'] : "");
                        $title = $this->basic->convertToHTMLEntities(is_string($row['title']) ? $row['title'] : "");
                        $picture1 = $this->basic->convertToHTMLEntities(is_string($row['url']) ? $row['url'] : "");
                        if ($picture1 == "") {
                            $picture1 = "empty";
                        }
                        $photograph1 = "";
                        if (is_string($row['photograph']) && $row['photograph'] != "") {
                            $photograph1 = "<br />Foto: ".$this->basic->convertToHTMLEntities($row['photograph']);
                        }
                        $city = $this->basic->convertToHTMLEntities(is_string($row['city']) ? $row['city'] : "");
                        $teaser = is_string($row['teaser']) ? $row['teaser'] : "";
                        $text = is_string($row['text']) ? $row['text'] : "";
                        array_push($news, array('text' => $text,'teaser' => $teaser,'city' => $city,'picture1' => $picture1, 'photograph1' => $photograph1, 'title' => $title,'headline' => $headline,'id' => $id,'editLink' => $editLink,'date' => $date,'postdate' => $postdate,'location' => $location,'author' => $author,'authorIP' => $authorIP, 'corrected' => $corrected));
                    }
                }
                $authTime = time();
                $authToken = $this->authentication->getToken($authTime);
                require_once(dirname(__FILE__)."/../admin/template/news.tpl.php");
            } elseif ($getAction == "details") {
                $this->doGetActions();
                $id = $this->requestParametersService->fromGet()->getIntegerParameter("id", -1);
                $result = $this->db->query("SELECT
				`visible`, `location`, `author`, `corrected`, `author_ip`, `headline`, `title`, `teaser`, `text`, `city`, `date`, `postdate`,
				`news_picture1`.`url` AS `url1`, `news_picture1`.`photograph` AS `photograph1`,
				`news_picture2`.`url` AS `url2`, `news_picture2`.`photograph` AS `photograph2`, `news_picture2`.`subtitle` AS `subtitle`
				FROM `news`
				LEFT JOIN `news_picture` AS `news_picture1` ON `news_picture1`.`picture` = `picture1`
				LEFT JOIN `news_picture` AS `news_picture2` ON `news_picture2`.`picture` = `picture2`
				WHERE `news`='$id' AND `deleted`='0'");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['location'])
                        && is_string($row['visible'])
                        && is_string($row['author'])
                        && is_string($row['corrected'])
                        && is_string($row['author_ip'])
                        && ($row['headline'] == null || is_string($row['headline']))
                        && ($row['title'] == null || is_string($row['title']))
                        && ($row['teaser'] == null || is_string($row['teaser']))
                        && ($row['text'] == null || is_string($row['text']))
                        && ($row['url1'] == null || is_string($row['url1']))
                        && ($row['url2'] == null || is_string($row['url2']))
                        && ($row['photograph1'] == null || is_string($row['photograph1']))
                        && ($row['photograph2'] == null || is_string($row['photograph2']))
                        && ($row['subtitle'] == null || is_string($row['subtitle']))
                        && ($row['city'] == null || is_string($row['city']))
                        && is_string($row['date'])
                        && is_string($row['postdate'])) {
                        $location = intval(strval($row['location']));
                        $submitLink = ((intval(strval($row['visible'])) == 0) && ($this->authentication->locationAdminAllowed($location, $this->role->getRole())));
                        $editLink = ($this->authentication->locationAdminAllowed($location, $this->role->getRole()));
                        $author = intval(strval($row['author']));
                        $corrected = intval(strval($row['corrected']));
                        $authorName = $this->basic->convertToHTMLEntities($this->user->getAcronymbyID($author, $this->authentication));
                        $authorIP = $this->basic->convertToHTMLEntities($row['author_ip']);
                        $locationName = $this->basic->convertToHTMLEntities($this->navigation->getNamebyID($location));
                        $headline = $this->basic->convertToHTMLEntities(is_string($row['headline']) ? $row['headline'] : "");
                        $title = $this->basic->convertToHTMLEntities(is_string($row['title']) ? $row['title'] : "");
                        $teaser = is_string($row['teaser']) ? $row['teaser'] : "";
                        $text = is_string($row['text']) ? $row['text'] : "";
                        $picture1 = $this->basic->convertToHTMLEntities(is_string($row['url1']) ? $row['url1'] : "");
                        if ($picture1 == "") {
                            $picture1 = "empty";
                        }
                        $photograph1 = "";
                        if (is_string($row['photograph1']) && $row['photograph1'] != "") {
                            $photograph1 = "<br />Foto: ".$this->basic->convertToHTMLEntities($row['photograph1']);
                        }
                        $picture2 = $this->basic->convertToHTMLEntities(is_string($row['url2']) ? $row['url2'] : "");
                        if ($picture2 == "") {
                            $picture2 = "empty";
                        }
                        $subtitle2 = $this->basic->convertToHTMLEntities(is_string($row['subtitle']) ? $row['subtitle'] : "");
                        $photograph2 = "";
                        if (is_string($row['photograph2']) && $row['photograph2'] != "") {
                            $photograph2 = " Foto: ".$this->basic->convertToHTMLEntities($row['photograph2']);
                        }
                        $city = $this->basic->convertToHTMLEntities(is_string($row['city']) ? $row['city'] : "");
                        $dateTime->setTimestamp(intval(strval($row['date'])));
                        $date = $dateTime->format("d\.m\.Y");
                        $dateTime->setTimestamp(intval(strval($row['postdate'])));
                        $postdate = $dateTime->format("\a\m d\. M Y \u\m H\:i\:s");
                        $authTime = time();
                        $authToken = $this->authentication->getToken($authTime);
                        require_once(dirname(__FILE__)."/../admin/template/news.details.tpl.php");
                    }
                }
            }
        }
    }

    /*
     * Displays the frontend of a news module.
     */
    public function display(): void
    {
        $dateTime = new DateTime("now", new DateTimeZone($this->configuration->getTimezone()));

        $pageID = $this->navigation->getPageID();

        if ($this->authentication->moduleReadAllowed("news", $this->role->getRole())) {
            if ($this->getAction() == null) {
                $location = -1;
                if ($pageID > -1) {
                    $location = $pageID;
                } else {
                    $location = $this->basic->getHomeLocation();
                }
                $uri = $this->getBaseURI($location);
                $result = $this->db->query("SELECT `maps_to` FROM `navigation` WHERE `id` = '$location' AND `type`='4'");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['maps_to'])) {
                        $location = intval(strval($row['maps_to']));
                    }
                }
                list($start, $end, $page, $pages, $startPage, $endPage, $showFirstPage, $showPreviousPage, $showNextPage, $showLastPage) = $this->getPagination($location);
                $news = array();
                $result = $this->db->query("SELECT
				`date`, `postdate`, `author`, `teaser`, `text`, `city`, `headline`, `title`, `news`, `url`, `photograph`
				FROM `news`
				LEFT JOIN `news_picture` ON `picture`=`picture1`
				WHERE `visible`='1' AND `deleted`='0' AND `location`='$location' ORDER BY `postdate` DESC LIMIT $start,$end");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['date'])
                        && is_string($row['postdate'])
                        && is_string($row['author'])
                        && ($row['url'] == null || is_string($row['url']))
                        && ($row['photograph'] == null || is_string($row['photograph']))
                        && ($row['teaser'] == null || is_string($row['teaser']))
                        && ($row['text'] == null || is_string($row['text']))
                        && ($row['city'] == null || is_string($row['city']))
                        && ($row['headline'] == null || is_string($row['headline']))
                        && ($row['title'] == null || is_string($row['title']))
                        && is_string($row['news'])) {
                        $dateTime->setTimestamp(intval(strval($row['date'])));
                        $date = $dateTime->format("d\.m\.Y");
                        $dateTime->setTimestamp(intval(strval($row['postdate'])));
                        $postdate = $dateTime->format("d\.m\.Y");
                        $author = intval(strval($row['author']));
                        $authorName = strtolower($this->basic->convertToHTMLEntities($this->user->getAcronymbyID($author, $this->authentication)));
                        $picture1 = $this->basic->convertToHTMLEntities(is_string($row['url']) ? $row['url'] : "");
                        if ($picture1 == "") {
                            $picture1 = "empty";
                        }
                        $photograph1 = "";
                        if (is_string($row['photograph']) && $row['photograph'] != "") {
                            $photograph1 = "<br />Foto: ".$this->basic->convertToHTMLEntities($row['photograph']);
                        }
                        $teaser = $this->nofollowOutboundLinks(is_string($row['teaser']) ? $row['teaser'] : "");
                        $text = $this->nofollowOutboundLinks(is_string($row['text']) ? $row['text'] : "");
                        $city = $this->basic->convertToHTMLEntities(is_string($row['city']) ? $row['city'] : "");
                        $headline = $this->basic->convertToHTMLEntities(is_string($row['headline']) ? $row['headline'] : "");
                        $title = $this->basic->convertToHTMLEntities(is_string($row['title']) ? $row['title'] : "");
                        $id = intval(strval($row['news']));
                        $newsURI = $this->generateLink($location, null, "read", is_string($row['headline']) ? $row['headline'] : "", is_string($row['title']) ? $row['title'] : "", $id);
                        array_push($news, array('city' => $city,'headline' => $headline,'title' => $title,'id' => $id, 'newsURI' => $newsURI, 'date' => $date,'postdate' => $postdate,'author' => $authorName,'picture1' => $picture1, 'photograph1' => $photograph1, 'teaser' => $teaser,'text' => $text));
                    }
                }
                require_once(dirname(__FILE__)."/../template/news.main.tpl.php");
            } elseif ($this->getAction() == "read") {
                $location = $pageID;
                $result = $this->db->query("SELECT `maps_to` FROM `navigation` WHERE `id` = '$location' AND `type`='4'");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['maps_to'])) {
                        $location = intval(strval($row['maps_to']));
                    }
                }
                $news = $this->getNewsID();
                $result = $this->db->query("SELECT
				`date`, `author`, `teaser`, `text`, `city`, `headline`, `title`,
				`news_picture1`.`url` AS `url1`, `news_picture1`.`photograph` AS `photograph1`,
				`news_picture2`.`url` AS `url2`, `news_picture2`.`photograph` AS `photograph2`, `news_picture2`.`subtitle` AS `subtitle`
				FROM `news`
				LEFT JOIN `news_picture` AS `news_picture1` ON `news_picture1`.`picture` = `picture1`
				LEFT JOIN `news_picture` AS `news_picture2` ON `news_picture2`.`picture` = `picture2`
				WHERE `location`='$location' AND `news`='$news' AND `visible`='1' AND `deleted`='0'");

                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['date'])
                        && is_string($row['author'])
                        && ($row['url1'] == null || is_string($row['url1']))
                        && ($row['photograph1'] == null || is_string($row['photograph1']))
                        && ($row['url2'] == null || is_string($row['url2']))
                        && ($row['photograph2'] == null || is_string($row['photograph2']))
                        && ($row['subtitle'] == null || is_string($row['subtitle']))
                        && ($row['teaser'] == null || is_string($row['teaser']))
                        && ($row['text'] == null || is_string($row['text']))
                        && ($row['city'] == null || is_string($row['city']))
                        && ($row['headline'] == null || is_string($row['headline']))
                        && ($row['title'] == null || is_string($row['title']))) {
                        $dateTime->setTimestamp(intval(strval($row['date'])));
                        $date = $dateTime->format("d\.m\.Y");
                        $author = intval(strval($row['author']));
                        $authorName = strtolower($this->basic->convertToHTMLEntities($this->user->getAcronymbyID($author, $this->authentication)));
                        $picture1 = $this->basic->convertToHTMLEntities(is_string($row['url1']) ? $row['url1'] : "");
                        if ($picture1 == "") {
                            $picture1 = "empty";
                        }
                        $photograph1 = "";
                        if (is_string($row['photograph1']) && $row['photograph1'] != "") {
                            $photograph1 = "<br />Foto: ".$this->basic->convertToHTMLEntities($row['photograph1']);
                        }
                        $picture2 = $this->basic->convertToHTMLEntities(is_string($row['url2']) ? $row['url2'] : "");
                        if ($picture2 == "") {
                            $picture2 = "empty";
                        }
                        $subtitle2 = $this->basic->convertToHTMLEntities(is_string($row['subtitle']) ? $row['subtitle'] : "");
                        $photograph2 = "";
                        if (is_string($row['photograph2']) && $row['photograph2'] != "") {
                            $photograph2 = " Foto: ".$this->basic->convertToHTMLEntities($row['photograph2']);
                        }
                        $teaser = $this->nofollowOutboundLinks(is_string($row['teaser']) ? $row['teaser'] : "");
                        $text = $this->nofollowOutboundLinks(is_string($row['text']) ? $row['text'] : "");
                        $city = $this->basic->convertToHTMLEntities(is_string($row['city']) ? $row['city'] : "");
                        $headline = $this->basic->convertToHTMLEntities(is_string($row['headline']) ? $row['headline'] : "");
                        $title = $this->basic->convertToHTMLEntities(is_string($row['title']) ? $row['title'] : "");
                        $url = $this->configuration->getDomain().$this->configuration->getBasePath()."/".$this->generateLink($pageID, null, "read", $headline, $title, $this->getNewsID());

                        $modules = $this->basic->getModules();
                        $moduleTags = array();
                        foreach ($modules as $module) {
                            $classPath = "\\marsl\\modules\\".$module['class'];
                            $class = ComponentBuilder::buildDependencies()->make($classPath);
                            if (($class instanceof Module) && $class->isTaggable()) {
                                $tagList = $class->getTagList();
                                foreach ($tagList as $tagType) {
                                    $typeID = $module['file']."_".$tagType['type'];
                                    $typeName = $tagType['text'];
                                    array_push($moduleTags, array('type' => $typeID, 'name' => $typeName, 'tags' => $class->getTags($tagType['type'], $news)));
                                }
                            }
                        }

                        require_once(dirname(__FILE__)."/../template/news.tpl.php");
                    }
                }
            }
        }
    }

    /**
     * @return array<int, bool|float|int|string>
     */
    private function getPagination(int $location): array
    {
        $result = $this->db->query("SELECT COUNT(`visible`) AS rowcount FROM `news` WHERE `visible`='1' AND `deleted`='0' AND `location`='$location'");
        $pages = $this->db->getRowCount($result) / 10;
        $page = $this->getPage();
        $startPage = 1;
        if ($page - $this->PAGINATION_DISTANCE > 1) {
            $startPage = $page - $this->PAGINATION_DISTANCE;
        }
        $endPage = $pages;
        if ($page + $this->PAGINATION_DISTANCE <= $endPage) {
            $endPage = $page + $this->PAGINATION_DISTANCE;
        }
        $showFirstPage = $page > 1;
        $showPreviousPage = $page > 2;
        $showNextPage = $page < $pages - 1;
        $showLastPage = $page < $pages;
        $start = $page * 10 - 10;
        $end = 10;

        return array($start, $end, $page, $pages, $startPage, $endPage, $showFirstPage, $showPreviousPage, $showNextPage, $showLastPage);
    }

    /*
     * Executes some smaller functions on a news article.
     */
    private function doGetActions(): void
    {
        $do = $this->requestParametersService->fromGet()->getStringParameter("do", "");
        if ($do == "submit") {
            $this->submitArticleToFrontend();
        } elseif ($do == "del") {
            $this->deleteArticle();
        }
    }

    private function deleteArticle(): void
    {
        if ($this->authentication->checkToken(
            $this->requestParametersService->fromGet()->getIntegerParameter("time"),
            $this->requestParametersService->fromGet()->getStringParameter("token")
        )) {
            $id = $this->requestParametersService->fromGet()->getIntegerParameter("id", -1);
            $result = $this->db->query("SELECT `location` FROM `news` WHERE `news`='$id'");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['location'])) {
                    if ($this->authentication->locationAdminAllowed(intval(strval($row['location'])), $this->role->getRole())) {
                        $this->db->query("UPDATE `news` SET `deleted`='1' WHERE `news`='$id'");
                    }
                }
            }
        }
    }

    private function submitArticleToFrontend(): void
    {
        if ($this->authentication->checkToken(
            $this->requestParametersService->fromGet()->getIntegerParameter("time"),
            $this->requestParametersService->fromGet()->getStringParameter("token")
        )) {
            $id = $this->requestParametersService->fromGet()->getIntegerParameter("id", -1);
            $result = $this->db->query("SELECT `location`, `headline`, `title`, `teaser` FROM `news` WHERE `news`='$id'");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['location'])
                    && ($row['headline'] == null || is_string($row['headline']))
                    && ($row['title'] == null || is_string($row['title']))
                    && ($row['teaser'] == null || is_string($row['teaser']))) {
                    $location = intval(strval($row['location']));
                    if ($this->authentication->locationAdminAllowed($location, $this->role->getRole())) {
                        $admin = $this->user->getID();
                        $adminIP = $this->db->escapeString($this->requestParametersService->fromServer()->getStringParameter("REMOTE_ADDR", ""));
                        $this->db->query("UPDATE `news` SET `visible`='1', `admin`='$admin', `admin_ip`='$adminIP' WHERE `news`='$id'");
                        if ($this->authentication->locationReadAllowed($location, $this->role->getGuestRole())) {
                            $headline = is_string($row['headline']) ? $row['headline'] : "";
                            $title = is_string($row['title']) ? $row['title'] : "";
                            $teaser = is_string($row['teaser']) ? $row['teaser'] : "";
                            $messageTitle = $headline.": ".$title;
                            $teaser = str_replace("<br />", "\n", $teaser);
                            $teaser = strip_tags($teaser);
                            $teaser = html_entity_decode($teaser);
                            $uri = $this->generateLink($location, null, "read", $headline, $title, $id);
                            $this->expoPushArticle($uri, $messageTitle);
                            $this->webPushArticle($uri, $messageTitle, $teaser);
                        }
                    }
                }
            }
        }
    }

    private function expoPushArticle(string $uri, string $messageTitle): void
    {
        $result = $this->db->query("SELECT `pushtoken` FROM `pushtoken` WHERE `type` = 'expo'");
        $multipleMessagesArray = array();
        $multipleMessagesArrayIdx = 0;
        $multipleMessagesArray[$multipleMessagesArrayIdx] = array();
        $currentMessageIdx = 0;
        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['pushtoken'])) {
                if ($currentMessageIdx >= 100) {
                    $multipleMessagesArrayIdx++;
                    $multipleMessagesArray[$multipleMessagesArrayIdx] = array();
                    $currentMessageIdx = 0;
                }
                array_push($multipleMessagesArray[$multipleMessagesArrayIdx], $this->buildExpoPushArray($row['pushtoken'], $uri, $messageTitle));
                $currentMessageIdx++;
            }
        }

        for ($i = 0; $i <= $multipleMessagesArrayIdx; $i++) {
            list($httpResult, $body) = $this->sendExpoPushArray($multipleMessagesArray[$i]);

            if (is_int($httpResult) && is_string($body)) {
                $this->handleExpoResponse($httpResult, $body);
            }
        }
    }

    private function handleExpoResponse(int $httpResult, string $body): void
    {
        if ($httpResult == 200) {
            $response = json_decode($body);
            // @phpstan-ignore property.nonObject
            $dataArray = $response->data;
            // @phpstan-ignore foreach.nonIterable
            foreach ($dataArray as $data) {
                // @phpstan-ignore property.nonObject
                if ($data->status == "error") {
                    // @phpstan-ignore property.nonObject
                    if (is_string($data->message)) {
                        // @phpstan-ignore property.nonObject
                        $stripOutExpoPushTokenFirstPartArray = explode("ExponentPushToken[", $data->message);
                        if (sizeof($stripOutExpoPushTokenFirstPartArray) > 1) {
                            $messageWithoutExpoPushTokenFirstPart = $stripOutExpoPushTokenFirstPartArray[1];
                            $tokenArray = explode("]", $messageWithoutExpoPushTokenFirstPart);

                            // PHPStan error. See https://github.com/phpstan/phpstan/issues/3995
                            // @phpstan-ignore greater.alwaysTrue
                            if (sizeof($tokenArray) > 0) {
                                $token = $tokenArray[0];
                                $expoToken = "ExponentPushToken[".$token."]";
                                $expoToken = $this->db->escapeString($expoToken);
                                $this->db->query("DELETE FROM `pushtoken` WHERE `pushtoken`='$expoToken'");
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param array<string, string> $expoPushArray
     * @return array<int, int|string>
     */
    private function sendExpoPushArray(array $expoPushArray): array
    {
        $expoPushJSON = json_encode($expoPushArray);
        $ch = curl_init("https://exp.host/--/api/v2/push/send");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");

        if ($expoPushJSON) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $expoPushJSON);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Content-Length: ' . strlen($expoPushJSON)));
        }

        $response = curl_exec($ch);
        $httpResult = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        $body = "";

        if (is_string($response)) {
            $body = substr($response, $header_size);
        }

        curl_close($ch);
        return array($httpResult, $body);
    }

    /**
     * @return array<string, string|false>
     */
    private function buildExpoPushArray(string $pushToken, string $uri, string $messageTitle): array
    {
        $expoArray = array();
        $expoArray['to'] = $pushToken;
        $expoArray['title'] = "Neuer Artikel auf " . $this->configuration->getTitle();
        $expoArray['body'] = $messageTitle;
        $expoArray['sound'] = "default";

        $payloadArray = array();
        $payloadArray['uri'] = $uri;
        $payloadJSON = json_encode($payloadArray);

        $expoArray['data'] = $payloadJSON;
        return $expoArray;
    }

    private function webPushArticle(string $uri, string $messageTitle, string $teaser): void
    {
        if (extension_loaded('gmp')) {
            $payloadJSON = $this->buildPayloadJSON($uri, $messageTitle, $teaser);

            if ($payloadJSON) {
                $webPush = $this->buildWebPushObject($payloadJSON);

                foreach ($webPush->flush() as $report) {
                    if (!$report->isSuccess()) {
                        $response = $report->getResponse();
                        if ($response != null) {
                            $statusCode = $response->getStatusCode();
                            if ($statusCode == 410 || $statusCode == 404) {
                                $endpoint = $report->getRequest()->getUri()->__toString();
                                $endpoint = $this->db->escapeString($endpoint);
                                $this->db->query("DELETE FROM `pushtoken` WHERE `endpoint`='$endpoint'");
                            }
                        }
                    }
                }
            }
        }
    }

    private function buildPayloadJSON(string $uri, string $messageTitle, string $teaser): string|false
    {
        $url = $this->configuration->getDomain().$this->configuration->getBasePath()."/".$uri;
        $icon = $this->configuration->getDomain().$this->configuration->getBasePath()."/includes/graphics/icon_512x512.png";

        $payloadArray = array();
        $payloadArray['title'] = $messageTitle;
        $payloadArray['body'] = $teaser;
        $payloadArray['icon'] = $icon;
        $payloadArray['data'] = $url;
        $payloadArray['requireInteraction'] = true;

        $payloadJSON = json_encode($payloadArray);
        return $payloadJSON;
    }

    private function buildWebPushObject(string $payloadJSON): WebPush
    {
        $auth = [
            'VAPID' => [
                'subject' => $this->configuration->getDomain().$this->configuration->getBasePath(),
                'publicKey' => $this->configuration->getWebPushPublicKey(),
                'privateKey' => $this->configuration->getWebPushPrivateKey()
            ]
        ];
        $webPush = new WebPush($auth);
        $result = $this->db->query("SELECT `endpoint`, `key`, `auth` FROM `pushtoken` WHERE `type` = 'webpush'");
        while ($row = $this->db->fetchArray($result)) {
            $subscription = Subscription::create([
                'endpoint' => $row['endpoint'],
                'keys' => ['p256dh' => $row['key'], 'auth' => $row['auth']]
            ]);
            $webPush->queueNotification($subscription, $payloadJSON);
        }
        return $webPush;
    }

    /*
     * Interface method stub.
    */
    public function isSearchable(): bool
    {
        return true;
    }

    /**
     * Returns the fulltext searchable types of this module.
     * @return array<int, array<string, string>>
    */
    public function getSearchList(): array
    {
        $types = array();
        array_push($types, array('type' => "all", 'text' => "Alle Nachrichten"));
        return $types;
    }

    /*
     * Performs a fulltext search over the attributes of the news table.
    */
    public function search(string $query, string $type): void
    {
        $roleID = $this->role->getRole();
        if ($this->authentication->moduleReadAllowed("news", $roleID)) {
            $query = $this->db->escapeString($query);
            $queryString = "";
            if (strlen($query) >= 8 && substr($query, 0, 4) == "\\\\\\\"" && substr($query, -4) == "\\\\\\\"") {
                $queryWord = substr($query, 4, -4);
                $query = "\"".$queryWord."\"";
                $queryWordClean = $this->db->escapeString($queryWord);
                $queryString = $queryString = "(`title` LIKE '%".$queryWordClean."%' OR `headline` LIKE '%".$queryWordClean."%' OR `teaser` LIKE '%".$queryWordClean."%' OR `text` LIKE '%".$queryWordClean."%')";
            } else {
                $queryWords = preg_split('/\s+/', $query, -1, PREG_SPLIT_NO_EMPTY);
                $queryWordCount = 1;
                if ($queryWords) {
                    foreach ($queryWords as $queryWord) {
                        $queryWordClean = $this->db->escapeString($queryWord);
                        if ($queryWordCount == 1) {
                            $queryString = "(`title` LIKE '%".$queryWordClean."%' OR `headline` LIKE '%".$queryWordClean."%' OR `teaser` LIKE '%".$queryWordClean."%' OR `text` LIKE '%".$queryWordClean."%')";
                        } else {
                            $queryString = $queryString." AND (`title` LIKE '%".$queryWordClean."%' OR `headline` LIKE '%".$queryWordClean."%' OR `teaser` LIKE '%".$queryWordClean."%' OR `text` LIKE '%".$queryWordClean."%')";
                        }
                        $queryWordCount++;
                    }
                }
            }

            if ($type == "standard") {
            } else {

                $page = $this->requestParametersService->fromGet()->getIntegerParameter("page", 1);
                $start = $page * 10 - 10;
                $end = 10;
                $startCounter = ($page - 1) * 10 + 1;

                $news = array();
                $topic = "";

                $pages = 0;

                if ($type == "all") {
                    $topic = "Alle Nachrichten";
                    $result = $this->db->query("SELECT COUNT(`visible`) AS rowcount FROM `news` JOIN `rights` ON (`rights`.`location`=`news`.`location`)
					WHERE ".$queryString."  AND `visible`='1' AND `deleted`='0' AND `read`='1' AND `role`='$roleID' ORDER BY `date` DESC");
                    $pages = intval(ceil($this->db->getRowCount($result) / 10));

                    $result = $this->db->query("SELECT `teaser`, `headline`, `title`, `news`, `news`.`location` AS `newslocation` FROM `news` JOIN `rights` ON (`rights`.`location`=`news`.`location`)
					WHERE ".$queryString."  AND `visible`='1' AND `deleted`='0' AND `read`='1' AND `role`='$roleID' ORDER BY `date` DESC LIMIT $start,$end");
                    while ($row = $this->db->fetchArray($result)) {
                        if (($row['teaser'] == null || is_string($row['teaser']))
                            && ($row['headline'] == null || is_string($row['headline']))
                            && ($row['title'] == null || is_string($row['title']))
                            && is_string($row['news'])
                            && is_string($row['newslocation'])) {
                            $teaser = is_string($row['teaser']) ? $row['teaser'] : "";
                            $headline = $this->basic->convertToHTMLEntities(is_string($row['headline']) ? $row['headline'] : "");
                            $title = $this->basic->convertToHTMLEntities(is_string($row['title']) ? $row['title'] : "");
                            $newsid = intval(strval($row['news']));
                            $location = intval(strval($row['newslocation']));
                            $newsURI = $this->generateLink($location, null, "read", $headline, $title, $newsid);
                            array_push($news, array('teaser' => $teaser, 'headline' => $headline, 'title' => $title, 'news' => $newsid, 'location' => $location, 'newsURI' => $newsURI));
                        }
                    }
                }

                list($startPage, $endPage, $showFirstPage, $showPreviousPage, $showNextPage, $showLastPage) = $this->getPaginationForSearch($pages, $page);

                require_once(dirname(__FILE__)."/../template/news.search.tpl.php");
            }
        }
    }

    /**
     * @return array<int, bool|int>
     */
    private function getPaginationForSearch(int $pages, int $page): array
    {
        $startPage = 1;
        if ($page - $this->PAGINATION_DISTANCE > 1) {
            $startPage = $page - $this->PAGINATION_DISTANCE;
        }
        $endPage = $pages;
        if ($page + $this->PAGINATION_DISTANCE <= $endPage) {
            $endPage = $page + $this->PAGINATION_DISTANCE;
        }
        $showFirstPage = $page > 1;
        $showPreviousPage = $page > 2;
        $showNextPage = $page < $pages - 1;
        $showLastPage = $page < $pages;

        return array($startPage, $endPage, $showFirstPage, $showPreviousPage, $showNextPage, $showLastPage);
    }

    /*
     * Interface method stub.
    */
    public function isTaggable(): bool
    {
        return true;
    }

    /**
     * Interface method stub.
     * @return array<int, array<string, string>>
    */
    public function getTagList(): array
    {
        $types = array();
        array_push($types, array('type' => "general", 'text' => "Allgemein"));
        return $types;
    }

    /*
     * Adds the tags for the general scope.
    */
    public function addTags(string $tagString, string $type, int $news): void
    {
        $tags = array_filter(explode(";", $tagString));
        $this->db->query("DELETE FROM `news_tag` WHERE `type`='general' AND `news`='$news'");
        foreach ($tags as $tag) {
            $tag = $this->db->escapeString($tag);
            $tag = trim($tag);
            $id = 0;
            if ((strlen($tag) > 0) && (!$this->db->isExisting("SELECT `tag` FROM `general` WHERE `tag`='$tag' LIMIT 1"))) {
                $this->db->query("INSERT INTO `general`(`tag`) VALUES('$tag')");
            }

            $result = $this->db->query("SELECT `id` FROM `general` WHERE `tag`='$tag'");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['id'])) {
                    $id = intval(strval($row['id']));
                }
            }
            $this->db->query("INSERT INTO `news_tag`(`tag`,`news`,`type`) VALUES('$id','$news','general')");
        }
    }

    /*
     * Returns the tags for the general scope.
    */
    public function getTagString(string $type, int $news): string
    {
        $retString = array();

        $result = $this->db->query("SELECT `general`.`tag` AS tagname FROM `general` JOIN `news_tag` ON(`general`.`id`=`news_tag`.`tag`) WHERE `type`='general' AND `news`='$news' ORDER BY `general`.`tag`");
        while ($row = $this->db->fetchArray($result)) {
            array_push($retString, $row['tagname']);
        }

        return implode(";", $retString);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getTags(string $type, int $news): array
    {
        $ret = array();
        $result = $this->db->query("SELECT `id`, `general`.`tag` AS tagname FROM `general` JOIN `news_tag` ON(`general`.`id`=`news_tag`.`tag`) WHERE `type`='general' AND `news`='$news' ORDER BY `general`.`tag`");
        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['id'])
            && is_string($row['tagname'])) {
                $id = intval(strval($row['id']));
                $tag = $row['tagname'];
                $uri = "";
                if ($this->configuration->getEnableOldURIs()) {
                    $uri = "index.php?tag=".$id."&scope=news_".$type;
                } else {
                    $tagPart = $this->generateTagPart($tag, $id);
                    $uri = "tag/news_".$type."/".$tagPart;
                }
                array_push($ret, array('id' => $id, 'tag' => $tag, 'uri' => $uri));
            }
        }

        return $ret;
    }

    public function displayTag(): void
    {
        $tagID = $this->getTagID();
        $articles = array();
        $tagName = "";
        $result = $this->db->query("SELECT `tag` FROM `general` WHERE `id`='$tagID'");

        $dateTime = new DateTime("now", new DateTimeZone($this->configuration->getTimezone()));

        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['tag'])) {
                $tagName = $this->basic->convertToHTMLEntities($row['tag']);
            }
        }
        $result = $this->db->query("SELECT `news`, `headline`, `title`, `date`, `location`, `name` FROM `news_tag` JOIN `news` USING (`news`) JOIN `navigation` ON (`news`.`location` = `navigation`.`id`) WHERE `tag`='$tagID' AND `news_tag`.`type`='general' ORDER BY `date` DESC");
        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['location'])
                && is_string($row['news'])
                && ($row['headline'] == null || is_string($row['headline']))
                && ($row['title'] == null || is_string($row['title']))
                && is_string($row['date'])
                && is_string($row['name'])) {
                $location = intval(strval($row['location']));
                if ($this->authentication->locationReadAllowed($location, $this->role->getRole())) {
                    $news = intval(strval($row['news']));
                    $headline = $this->basic->convertToHTMLEntities(is_string($row['headline']) ? $row['headline'] : "");
                    $title = $this->basic->convertToHTMLEntities(is_string($row['title']) ? $row['title'] : "");
                    $dateTime->setTimestamp(intval(strval($row['date'])));
                    $date = $dateTime->format("d\.m\.Y");
                    $location = $location;
                    $locationName = $this->basic->convertToHTMLEntities($row['name']);
                    $newsURI = $this->generateLink($location, $locationName, "read", $headline, $title, $news);
                    array_push($articles, array('news' => $news, 'headline' => $headline, 'title' => $title, 'date' => $date, 'location' => $location, 'locationName' => $locationName, 'newsURI' => $newsURI));
                }
            }
        }
        require_once(dirname(__FILE__)."/../template/news.tag.tpl.php");
    }

    public function getImage(): string|null
    {
        if ($this->getAction() != null) {
            if ($this->getAction() == "read") {
                if ($this->authentication->moduleReadAllowed("news", $this->role->getRole())) {
                    $newsID = $this->getNewsID();
                    $pageID = $this->navigation->getPageID();
                    $location = $pageID;
                    $result = $this->db->query("SELECT `maps_to` FROM `navigation` WHERE `id` = '$location' AND `type`='4'");
                    while ($row = $this->db->fetchArray($result)) {
                        if (is_string($row['maps_to'])) {
                            $location = intval(strval($row['maps_to']));
                        }
                    }
                    if ($this->authentication->locationReadAllowed($location, $this->role->getRole())) {
                        $picture = "empty";
                        $result = $this->db->query("SELECT `url` FROM `news` JOIN `news_picture` ON `picture`=`picture2` WHERE `location`='$location' AND `news`='$newsID' AND `visible`='1' AND `deleted`='0'");
                        while ($row = $this->db->fetchArray($result)) {
                            if (is_string($row['url'])) {
                                $picture = "news/".$this->basic->convertToHTMLEntities($row['url']);
                            }
                        }
                        if ($picture == "empty") {
                            return null;
                        } else {
                            return $picture;
                        }
                    } else {
                        return null;
                    }
                } else {
                    return null;
                }
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function getTitle(): string|null
    {
        if ($this->getAction() != null) {
            if ($this->getAction() == "read") {
                if ($this->authentication->moduleReadAllowed("news", $this->role->getRole())) {
                    $newsID = $this->getNewsID();
                    $pageID = $this->navigation->getPageID();
                    $location = $pageID;
                    $headline = "";
                    $title = "";
                    $result = $this->db->query("SELECT `maps_to` FROM `navigation` WHERE `id` = '$location' AND `type`='4'");
                    while ($row = $this->db->fetchArray($result)) {
                        if (is_string($row['maps_to'])) {
                            $location = intval(strval($row['maps_to']));
                        }
                    }
                    if ($this->authentication->locationReadAllowed($location, $this->role->getRole())) {
                        $newsTitle = "";
                        $picture = "empty";
                        $result = $this->db->query("SELECT `headline`, `title` FROM `news` WHERE `location`='$location' AND `news`='$newsID' AND `visible`='1' AND `deleted`='0'");
                        while ($row = $this->db->fetchArray($result)) {
                            if (($row['headline'] == null || is_string($row['headline']))
                                && ($row['title'] == null || is_string($row['title']))) {
                                $headline = is_string($row['headline']) ? $row['headline'] : "";
                                $title = is_string($row['title']) ? $row['title'] : "";
                                $newsTitle = $headline.": ".$title;
                            }
                        }
                        if ((strlen($headline) == 0) && (strlen($title) == 0)) {
                            return null;
                        } else {
                            return $newsTitle;
                        }
                    } else {
                        return null;
                    }
                } else {
                    return null;
                }
            } else {
                return null;
            }
        } else {
            return null;
        }
    }



    public function getRestfulURIPartFromOldURL(): string
    {
        $uri = "";
        if ($this->requestParametersService->fromGet()->getStringParameter("tag", "") != ""
            && $this->requestParametersService->fromGet()->getIntegerParameter("id", -1) == -1) {
            $uri = $this->getRestfulURIPartForTag($uri);
        } else {
            $uri = $this->getRestfulURIPartForStandardPage($uri);
        }
        return $uri;
    }

    private function getRestfulURIPartForStandardPage(string $uri): string
    {
        $action = $this->getAction();
        if ($action != "") {
            $newsID = $this->getNewsID();
            $newsURIPart = "";
            $result = $this->db->query("SELECT `headline`, `title` FROM `news` WHERE `news`='$newsID'");
            while ($row = $this->db->fetchArray($result)) {
                if (($row['headline'] == null || is_string($row['headline']))
                    && ($row['title'] == null || is_string($row['title']))) {
                    $headline = $this->basic->convertToHTMLEntities(is_string($row['headline']) ? $row['headline'] : "");
                    $title = $this->basic->convertToHTMLEntities(is_string($row['title']) ? $row['title'] : "");
                    $newsURIPart = $this->generateNewsPart($headline, $title, $newsID);
                }
            }
            $uri = $uri."/".$newsURIPart;
        }

        $page = $this->getPage();
        if ($page > 1) {
            $uri = $uri."/".$page;
        }

        return $uri;
    }

    private function getRestfulURIPartForTag(string $uri): string
    {
        $scope = $this->getScope();
        if ($scope != "") {
            $uri = $uri."/".$scope;
            $tagID = $this->getTagID();
            $explodedScope = explode("_", $scope);
            $subScope = $this->db->escapeString($explodedScope[1]);
            $tagName = "";
            $result = $this->db->query("SELECT `tag` FROM `".$subScope."` WHERE `id`='$tagID'");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['tag'])) {
                    $tagName = $this->basic->convertToHTMLEntities($row['tag']);
                }
            }
            $tagPart = $this->generateTagPart($tagName, $tagID);
            $uri = $uri."/".$tagPart;
        }

        return $uri;
    }

    private function generateTagPart(string $tagName, int $tagID): string
    {
        $slugify = new Slugify();
        $tagPart = $slugify->slugify($tagName)."-".$tagID;

        return $tagPart;
    }

    private function generateNewsPart(string $headline, string $title, int $newsID): string
    {
        $slugify = new Slugify();
        $newsPart = $slugify->slugify($headline."-".$title)."-".$newsID;
        return $newsPart;
    }

    private function getTagID(): int
    {
        $tagID = $this->requestParametersService->fromGet()->getIntegerParameter("tag", -1);
        $requestURI = $this->requestParametersService->fromGet()->getStringParameter("request_uri", "");

        if ($tagID == -1 && $requestURI != "") {
            $explodedRequestURI = explode('/', $requestURI);
            if (sizeof($explodedRequestURI) > 2) {
                $tag = $explodedRequestURI[2];
                $explodedTag = explode('-', $tag);
                $explodedTagSlugSize = sizeof($explodedTag);

                // PHPStan error. See https://github.com/phpstan/phpstan/issues/3995
                // @phpstan-ignore greater.alwaysTrue
                if ($explodedTagSlugSize > 0) {
                    $tagID = intval($explodedTag[$explodedTagSlugSize - 1]);
                }
            }
        }

        return $tagID;
    }

    private function getNewsID(): int
    {
        $newsID = $this->requestParametersService->fromGet()->getIntegerParameter("show", -1);
        $requestURI = $this->requestParametersService->fromGet()->getStringParameter("request_uri", "");

        if ($newsID == -1 && $requestURI != "") {
            $explodedRequestURI = explode('/', $requestURI);
            if (sizeof($explodedRequestURI) > 1) {
                $newsSlug = $explodedRequestURI[1];
                $explodedNewsSlug = explode('-', $newsSlug);
                $explodedNewsSlugSize = sizeof($explodedNewsSlug);
                if ($explodedNewsSlugSize > 1) {
                    $newsID = intval($explodedNewsSlug[$explodedNewsSlugSize - 1]);
                }
            }
        }
        return $newsID;
    }

    public function getPage(): int
    {
        $page = $this->requestParametersService->fromGet()->getIntegerParameter("page", 1);
        $requestURI = $this->requestParametersService->fromGet()->getStringParameter("request_uri", "");

        if ($page == 1 && $requestURI != "") {
            $explodedRequestURI = explode('/', $requestURI);
            if (sizeof($explodedRequestURI) > 1) {
                $pageSlug = $explodedRequestURI[1];
                $explodedPageSlug = explode('-', $pageSlug);
                $explodedPageSlugSize = sizeof($explodedPageSlug);
                if ($explodedPageSlugSize == 1) {
                    $page = intval($pageSlug);
                }
            }
        }
        return $page;
    }

    private function getAction(): string
    {
        $action = $this->requestParametersService->fromGet()->getStringParameter("action", "");
        $requestURI = $this->requestParametersService->fromGet()->getStringParameter("request_uri", "");

        if ($action == "" && $requestURI != "") {
            $explodedRequestURI = explode('/', $requestURI);
            if (sizeof($explodedRequestURI) > 2) {
                $action = $explodedRequestURI[2];
            } elseif (sizeof($explodedRequestURI) > 1) {
                if ($this->getNewsID() != -1) {
                    $action = "read";
                }
            }
        }
        return $action;
    }

    private function getScope(): string
    {
        $scope = $this->requestParametersService->fromGet()->getStringParameter("scope", "");
        $requestURI = $this->requestParametersService->fromGet()->getStringParameter("request_uri", "");

        if ($scope == "" && $requestURI != "") {
            $explodedRequestURI = explode('/', $requestURI);
            if (sizeof($explodedRequestURI) > 1) {
                $scope = $explodedRequestURI[1];
            }
        }
        return $scope;
    }

    public function getOldURIPartFromRestfulURL(): string
    {
        $uri = "";
        $uriType = "";
        $requestURI = $this->requestParametersService->fromGet()->getStringParameter("request_uri", "");

        if ($requestURI != "") {
            $explodedRequestURI = explode('/', $requestURI);
            if (sizeof($explodedRequestURI) > 1) {
                $uriType = $explodedRequestURI[0];
                if ($uriType != "tag") {
                    $uriType = "standard";
                }
            }
        }

        if ($uriType == "tag") {
            $uri = $this->getOldURIPartForTagURL();
        } elseif ($uriType == "standard") {
            $uri = $this->getOldURIPartForStandardPages($uri);
        }

        return $uri;
    }

    private function getOldURIPartForStandardPages(string $uri): string
    {
        $uri = "";
        $newsID = $this->getNewsID();
        $action = $this->getAction();
        if ($action != "" && $newsID != -1) {
            $uri = $uri."&show=".$newsID."&action=".$action;
        }

        $page = $this->getPage();
        if ($page > 1) {
            $uri = $uri."&page=".$page;
        }

        return $uri;
    }

    private function getOldURIPartForTagURL(): string
    {
        $uri = "";
        $scope = $this->getScope();
        $tagID = $this->getTagID();
        if ($scope != "" && $tagID > 0) {
            $uri = "index.php?tag=".$tagID."&scope=".$scope;
        }

        return $uri;
    }

    private function nofollowOutboundLinks(string $content): string|null
    {
        return preg_replace_callback(
            '~<(a\s[^>]+)>~isU',
            function ($match) {

                list($original, $tag) = $match;

                if (strpos($tag, "nofollow")) {
                    return $original;
                } elseif (strpos($tag, $this->configuration->getDomain())) {
                    return $original;
                } else {
                    return "<$tag rel=\"nofollow\">";
                }
            },
            $content
        );
    }

    private function getBaseURI(int $location): string
    {
        $uri = "";

        if (array_key_exists($location, $this->baseLinks)) {
            $uri = $this->baseLinks[$location];
        } else {
            $uri = $this->navigation->getRelativeURI($location, null, false);
            $this->baseLinks[$location] = $uri;
        }

        return $uri;
    }

    public function generateLink(int $location, string|null $locationName, string $action, string $headline, string $title, int $newsID): string
    {
        $link = "";

        if (array_key_exists($location, $this->baseLinks)) {
            $baseLink = $this->baseLinks[$location];
        } else {
            $baseLink = $this->navigation->getRelativeURI($location, $locationName, false);
            $this->baseLinks[$location] = $baseLink;
        }
        $uri = $this->baseLinks[$location];
        if ($action == "read") {
            $link = $uri.$this->getRelativeURI($action, $headline, $title, $newsID);
        }

        return $link;
    }

    public function getRelativeURI(string $action, string $headline, string $title, int $id): string
    {
        $uri = "";

        if ($this->configuration->getEnableOldURIs()) {
            if ($action == "read") {
                $uri = "&show=".$id."&action=read";
            }
        } else {
            if ($action == "read") {
                $uri = "/".$this->generateNewsPart($headline, $title, $id);
            }
        }

        return $uri;
    }

    public function getPageURIFormatted(int $page): string
    {
        $result = "";
        if ($page > 1) {
            if ($this->configuration->getEnableOldURIs()) {
                $result = "&page=".$page;
            } else {
                $result = "/".$page;
            }
        }
        return $result;
    }
}
