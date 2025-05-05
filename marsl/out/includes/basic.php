<?php

namespace marsl\includes;

include_once(dirname(__FILE__)."/errorHandler.php");
include_once(dirname(__FILE__)."/htmlpurifier/library/HTMLPurifier.auto.php");
include_once(dirname(__FILE__)."/../autoload.php");

use HTMLPurifier;
use HTMLPurifier_Config;
use marsl\includes\DB;
use marsl\modules\URLLoader;

class Basic
{
    private Configuration $configuration;
    private DB $db;
    private HTMLSecurity $htmlSecurity;
    private LocationBase $locationBase;
    private PageBase $pageBase;

    public function __construct(
        Configuration $configuration,
        DB $db,
        HTMLSecurity $htmlSecurity,
        LocationBase $locationBase,
        PageBase $pageBase
    ) {
        $this->configuration = $configuration;
        $this->db = $db;
        $this->htmlSecurity = $htmlSecurity;
        $this->locationBase = $locationBase;
        $this->pageBase = $pageBase;
    }

    public function convertToHTMLEntities(string|null $dirt): string
    {
        return $this->htmlSecurity->convertToHTMLEntities($dirt);
    }

    /*
     * Cleans the HTML output of tinymce to prevent XSS attacks.
     */
    public function cleanHTML(string $dirt): string
    {
        $config = $this->getHTMLPurifierConfigDefault();
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('HTML.Doctype', 'XHTML 1.1');
        $config->set('Core.EscapeNonASCIICharacters', true);
        $def = $config->getHTMLDefinition(true);

        // PHPStan checks HTMLPurifier PHPDoc comments wrongly.
        // @phpstan-ignore method.nonObject
        $def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');
        $purifier = new HTMLPurifier($config);

        // PHPStan checks HTMLPurifier PHPDoc comments wrongly.
        // @phpstan-ignore return.type
        return $purifier->purify($dirt);
    }

    /*
     * Cleans the HTML output of tinymce only to some allowed elements. E.g. used in the board.
     */
    public function cleanStrict(string $dirt): string
    {
        $config = $this->getHTMLPurifierConfigDefault();
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
        $config->set('Core.EscapeNonASCIICharacters', true);
        $config->set('HTML.AllowedElements', array('a','b','strong','i','em','u','img','blockquote','s','br'));
        $config->set('HTML.AllowedAttributes', array('a.href', 'img.src', '*.alt', '*.title', '*.border', '*.align', '*.width', '*.height', 'img.vspace', 'img.hspace', 'a.target', 'a.rel'));
        $config->set('AutoFormat.RemoveEmpty.RemoveNbsp', true);
        $config->set('AutoFormat.RemoveEmpty', true);
        $config->set('Core.RemoveProcessingInstructions', true);
        $config->set('HTML.TargetBlank', true);
        $config->set('HTML.Nofollow', true);
        $purifier = new HTMLPurifier($config);

        // PHPStan checks HTMLPurifier PHPDoc comments wrongly.
        // @phpstan-ignore return.type
        return $purifier->purify($dirt);
    }

    /*
     * Get the location for the standard page.
     */
    public function getHomeLocation(): int
    {
        return $this->locationBase->getHomeLocation();
    }

    /*
     * Get the title of the current page.
     */
    public function getTitle(): string
    {
        $title = $this->pageBase->getTitle();
        if ($title != null) {
            return $this->pageBase->getTitle().$this->configuration->getTitle();
        } else {
            return $this->configuration->getTitle()." - ".$this->configuration->getSubTitle();
        }
    }

    /*
     * Gets the page corresponding thumbnail.
     */
    public function getImage(): string|null
    {
        return $this->pageBase->getImage();
    }

    /*
     * Check an input if it is an e-mail-adresse.
     */
    public function checkMail(string $email): bool
    {
        $regexp = "/^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$/i";
        if (!preg_match($regexp, $email)) {
            return false;
        }
        return true;
    }

    /**
     * Get module information for a given unique file name.
     * @return array<string, string>|false
     */
    public function getModule(string $file): array|bool
    {
        $success = false;
        $module = array();
        $file = $this->db->escapeString($file);
        $result = $this->db->query("SELECT `name`, `class`, `file` FROM `module` WHERE `file`='$file'");
        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['name'])
            && is_string($row['class'])
            && is_string($row['file'])) {
                $module['name'] = $row['name'];
                $module['class']  = $row['class'];
                $module['file'] = $row['file'];
                $success = true;
            }
        }

        $methodResult = $module;

        if (!$success) {
            $methodResult = false;
        }
        return $methodResult;
    }

    /**
     * Get all modules.
     * @return list<array<string, string>>
     */
    public function getModules(): array
    {
        $modules = array();
        $result = $this->db->query("SELECT `name`, `file`, `class` FROM `module`");
        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['name'])
                && is_string($row['file'])
                && is_string($row['class'])) {
                array_push($modules, array('name' => $row['name'],'file' => $row['file'],'class' => $row['class']));
            }
        }
        return $modules;
    }

    /*
     * Set a new session ID.
     */
    public function session(): string
    {
        $session = $this->randomHash();
        $session = $this->db->escapeString($session);
        while ($this->db->isExisting("SELECT `sessionid` FROM `user` WHERE `sessionid`='$session' LIMIT 1")) {
            $session = $this->randomHash();
            $session = $this->db->escapeString($session);
        }
        return $session;
    }

    /*
     * Set a new double-opt-in confirmation ID.
     */
    public function confirmID(): string
    {
        $confirmID = $this->randomHash();
        $confirmID = $this->db->escapeString($confirmID);
        while ($this->db->isExisting("SELECT `confirm_id` FROM `email` WHERE `confirm_id`='$confirmID' LIMIT 1")) {
            $confirmID = $this->randomHash();
            $confirmID = $this->db->escapeString($confirmID);
        }
        return $confirmID;
    }

    /*
     * Get a random hash.
     */
    public function randomHash(): string
    {
        mt_srand(time());
        $randomHash = mt_rand().mt_rand().mt_rand().mt_rand();
        $randomHash = md5($randomHash);
        return $randomHash;
    }

    public function randomSHA512(): string
    {
        mt_srand(time());
        $randomHash = mt_rand().mt_rand().mt_rand().mt_rand().mt_rand().mt_rand().mt_rand().mt_rand().mt_rand().mt_rand().mt_rand().mt_rand().mt_rand();
        $randomHash = hash("sha512", $randomHash);
        return $randomHash;
    }

    /*
     * Return the numeric value of a month.
     */
    public function getNumericMonth(string $month): int
    {
        if ($month == "Jan") {
            return 1;
        }
        if ($month == "Feb") {
            return 2;
        }
        if ($month == "Mar") {
            return 3;
        }
        if ($month == "Apr") {
            return 4;
        }
        if ($month == "May") {
            return 5;
        }
        if ($month == "Jun") {
            return 6;
        }
        if ($month == "Jul") {
            return 7;
        }
        if ($month == "Aug") {
            return 8;
        }
        if ($month == "Sep") {
            return 9;
        }
        if ($month == "Oct") {
            return 10;
        }
        if ($month == "Nov") {
            return 11;
        }
        if ($month == "Dec") {
            return 12;
        }

        return 0;
    }

    public function tempFileKey(): string
    {
        $tempKey = $this->db->escapeString($this->randomHash());
        while ($this->db->isExisting("SELECT `temporary` FROM `attachment` WHERE `temporary`='$tempKey' LIMIT 1")) {
            $tempKey = $this->db->escapeString($this->randomHash());
        }
        return $tempKey;
    }

    private function getHTMLPurifierConfigDefault(): HTMLPurifier_Config
    {
        // PHPStan checks HTMLPurifier PHPDoc comments wrongly.
        // @phpstan-ignore return.type
        return HTMLPurifier_Config::createDefault();
    }
}
