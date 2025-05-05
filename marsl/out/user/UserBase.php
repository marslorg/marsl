<?php

namespace marsl\user;

include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../autoload.php");

use marsl\includes\HTMLSecurity;
use marsl\includes\DB;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;

class UserBase
{
    private HTMLSecurity $htmlSecurity;
    private DB $db;
    private IRequestParametersService $requestParametersService;

    /**
     * @var array<int, string>
     */
    private array $mailsByID;

    /**
     * @var array<int, string>
     */
    private array $nicksByID;

    /**
     * @var array<string, string>
     */
    private array $nicksByMail;

    /**
     * @var array<int, string>
     */
    private array $passwordsByID;

    /**
     * @var array<string, int>
     */
    private array $userIDsByName;

    private string $session;
    private int $userID;
    private bool $userIDSet;

    public function __construct(
        HTMLSecurity $htmlSecurity,
        DB $db,
        IRequestParametersService $requestParametersService
    ) {
        $this->htmlSecurity = $htmlSecurity;
        $this->db = $db;
        $this->requestParametersService = $requestParametersService;


        $this->mailsByID = array();
        $this->nicksByID = array();
        $this->nicksByMail = array();
        $this->passwordsByID = array();
        $this->userIDsByName = array();
        $this->session = "";
        $session = $this->requestParametersService->fromCookie()->getStringParameter("sessionid", "");
        if ($this->db->isExisting("SELECT `sessionid` FROM `user` WHERE `sessionid`='$session' LIMIT 1")) {
            $lastseen = time();
            $this->db->query("UPDATE `user` SET `lastseen` = '$lastseen' WHERE `sessionid` = '$session'");
            $this->session = $session;
        }

        $this->userID = -1;
        $this->userIDSet = false;

    }

    /*
     * Returns the session.
     */
    public function getSession(): string
    {
        return $this->session;
    }

    /*
     * Gets the ID of the logged in user.
     */
    public function getID(): int
    {
        if (!$this->userIDSet) {
            if ($this->getSession() != "") {
                $sessionid = $this->db->escapeString($this->getSession());
                $result = $this->db->query("SELECT `user` FROM `user` WHERE `sessionid`='$sessionid'");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['user'])) {
                        $this->userID = intval(strval($row['user']));
                    } else {
                        $this->userID = 0;
                    }
                }
            } else {
                $this->userID = 0;
            }
        }

        $this->userIDSet = true;

        return $this->userID;
    }

    /*
     * Gets a user ID by a given name.
     */
    public function getIDbyName(string $name): int
    {
        if (!array_key_exists($name, $this->userIDsByName)) {
            $name = $this->db->escapeString($name);
            $user = -1;
            $result = $this->db->query("SELECT `user` FROM `user` WHERE LOWER(`nickname`)=LOWER('$name')");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['user'])) {
                    $user = intval(strval($row['user']));
                }
            }
            $this->userIDsByName[$name]  = $user;
        }
        return $this->userIDsByName[$name];
    }

    /*
     * Get the primary e-mail of a user.
     */
    public function getMailbyID(int $id): string
    {
        if (!array_key_exists($id, $this->mailsByID)) {
            $mail = "";
            $user = $id;
            $result = $this->db->query("SELECT `email` FROM `email` NATURAL JOIN `user` WHERE `user`='$user' AND `confirmed`='1' AND `primary`='1'");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['email'])) {
                    $mail = $this->htmlSecurity->convertToHTMLEntities($row['email']);
                }
            }
            $this->mailsByID[$id] = $mail;
        }
        return $this->mailsByID[$id];
    }

    /*
     * Get the nickname of a user.
     */
    public function getNickbyID(int $id): string
    {
        if (!array_key_exists($id, $this->nicksByID)) {
            $name = "";
            $user = $id;
            $result = $this->db->query("SELECT `nickname` FROM `user` WHERE `user`='$user'");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['nickname'])) {
                    $name = $this->htmlSecurity->convertToHTMLEntities($row['nickname']);
                }
            }
            $this->nicksByID[$id] = $name;
        }
        return $this->nicksByID[$id];
    }

    /*
     * Get nickname by giving an e-mail adress.
     */
    public function getNickbyMail(string $mail): string
    {
        if (!array_key_exists($mail, $this->nicksByMail)) {
            $name = "";
            $mail = $this->db->escapeString($mail);
            $result = $this->db->query("SELECT `nickname` FROM `email` NATURAL JOIN `user` WHERE `email`='$mail' AND `confirmed`='1'");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['nickname'])) {
                    $name = $this->htmlSecurity->convertToHTMLEntities($row['nickname']);
                }
            }
            $this->nicksByMail[$mail]  = $name;
        }
        return $this->nicksByMail[$mail];
    }

    /*
     * Get the password of a user.
     */
    public function getPassbyID(int $id): string
    {
        if (!array_key_exists($id, $this->passwordsByID)) {
            $password = "";
            $user = $id;
            $result = $this->db->query("SELECT `password` FROM `user` WHERE `user`='$user'");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['password'])) {
                    $password = $row['password'];
                } else {
                    $password = "";
                }
            }
            $this->passwordsByID[$id] = $password;
        }
        return $this->passwordsByID[$id];
    }

    /*
     * Returns whether the logged in user is a guest.
     */
    public function isGuest(): bool
    {
        return $this->getSession() == "";
    }
}
