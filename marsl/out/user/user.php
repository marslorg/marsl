<?php

namespace marsl\user;

include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../autoload.php");

use marsl\includes\Basic;
use marsl\includes\DB;
use marsl\includes\Mailer;

class User
{
    private Basic $basic;
    private DB $db;
    private Mailer $mailer;
    private Role $role;
    private UserBase $userBase;

    private bool $headAdmin;
    private bool $headAdminSet;
    private bool $root;
    private bool $rootSet;
    private bool $isAdmin;
    private bool $isAdminSet;

    /**
     * @var array<int, string>
     */
    private array $acronymsByID;

    /**
     * @var array<int, int>
     */
    private array $registerDatesByUser;

    /*
     * Constructs the session of the user.
     */
    public function __construct(
        Basic $basic,
        DB $db,
        Mailer $mailer,
        Role $role,
        UserBase $userBase
    ) {
        $this->basic = $basic;
        $this->db = $db;
        $this->mailer = $mailer;
        $this->role = $role;
        $this->userBase = $userBase;

        $this->headAdminSet = false;
        $this->headAdmin = false;
        $this->root = false;
        $this->rootSet = false;
        $this->isAdmin = false;
        $this->isAdminSet = false;
        $this->acronymsByID = array();
        $this->registerDatesByUser = array();
    }

    /*
     * Returns whether logged in user is root or directly under the root user.
     */
    public function isHead(): bool
    {
        if (!$this->headAdminSet) {
            $roleID = $this->role->getRole();
            if ($this->db->isExisting("SELECT `role` FROM `role` WHERE `name`='root' AND `role`='$roleID' LIMIT 1")) {
                $this->headAdmin = true;
            } elseif ($this->db->isExisting("SELECT `name` FROM `role_editor` JOIN `role` ON `master`=`role` WHERE `slave`='$roleID' AND `name`='root' LIMIT 1")) {
                $this->headAdmin = true;
            }
        }

        $this->headAdminSet = true;

        return $this->headAdmin;
    }

    public function isRoot(): bool
    {
        if (!$this->rootSet) {
            $roleID = $this->role->getRole();

            if ($this->db->isExisting("SELECT `role` FROM `role` WHERE `name`='root' AND `role`='$roleID' LIMIT 1")) {
                $this->root = true;
            }
        }

        $this->rootSet = true;

        return $this->root;
    }

    /*
     * Returns whether the logged in user is a guest.
     */
    public function isGuest(): bool
    {
        return $this->userBase->isGuest();
    }

    /*
     * Returns whether the logged in user has access rights on the admin panel.
     */
    public function isAdmin(): bool
    {
        if (!$this->isAdminSet) {
            $roleID = $this->role->getRole();
            $location = $this->db->isExisting("SELECT `role` FROM `rights` WHERE `role` = '$roleID' AND `admin` = '1' LIMIT 1");
            $module = $this->db->isExisting("SELECT `role` FROM `rights_module` WHERE `role` = '$roleID' AND `admin` = '1' LIMIT 1");
            $master = $this->db->isExisting("SELECT `master` FROM `role_editor` WHERE `master` = '$roleID' LIMIT 1");
            $this->isAdmin = $location || $module || $master;
        }

        $this->isAdminSet = true;

        return $this->isAdmin;
    }

    /*
     * Returns the session.
     */
    public function getSession(): string
    {
        return $this->userBase->getSession();
    }

    /*
     * Logout a user.
     */
    public function logout(Authentication $auth): void
    {
        $session = $this->db->escapeString($this->basic->session());
        $lastlogout = time();
        if ($this->userBase->getSession() != "") {
            $oldsession = $this->db->escapeString($this->userBase->getSession());
            $this->db->query("UPDATE `user` SET `lastlogout`='$lastlogout', `sessionid`='$session' WHERE `sessionid`='$oldsession'");
        }
        setcookie("sessionid", "destroyed", time() - 3600);
    }

    /*
     * Login a user.
     */
    public function login(string $nickname, string $password, Authentication $auth): bool
    {
        if (empty($nickname) || empty($password)) {
            return false;
        } else {
            $nickname = $this->db->escapeString($nickname);
            if ($this->db->isExisting("SELECT `nickname` FROM `user` WHERE LOWER(`nickname`)=LOWER('$nickname') LIMIT 1")) {
                $result = $this->db->query("SELECT `regdate` FROM `user` WHERE LOWER(`nickname`)=LOWER('$nickname')");
                $regdate = -1;
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['regdate'])) {
                        $regdate = intval(strval($row['regdate']));
                    }
                }
                $password = $this->db->escapeString($this->hashPassword($regdate, $password));
                if ($this->db->isExisting("SELECT `nickname` FROM `user` WHERE LOWER(`nickname`)=LOWER('$nickname') AND `password`='$password' LIMIT 1")) {
                    $lastlogin = time();
                    $session = $this->db->escapeString($this->basic->session());

                    $this->db->query("UPDATE `user` SET `lastlogin`='$lastlogin', `sessionid`='$session' WHERE LOWER(`nickname`)=LOWER('$nickname') AND `password`='$password'");
                    setcookie("sessionid", $session, time() + (3600 * 24 * 365));
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }

    /*
     * Gets the ID of the logged in user.
     */
    public function getID(): int
    {
        return $this->userBase->getID();
    }

    /*
     * Gets a user ID by a given name.
     */
    public function getIDbyName(string $name): int
    {
        return $this->userBase->getIDbyName($name);
    }

    /*
     * Get the password of a user.
     */
    public function getPassbyID(int $id): string
    {
        return $this->userBase->getPassbyID($id);
    }

    /*
     * Get the primary e-mail of a user.
     */
    public function getMailbyID(int $id): string
    {
        return $this->userBase->getMailByID($id);
    }

    /*
     * Get the nickname of a user.
     */
    public function getNickbyID(int $id): string
    {
        return $this->userBase->getNickbyID($id);
    }

    /*
     * Get the acronym of a user.
     */
    public function getAcronymbyID(int $id, Authentication $auth): string
    {
        if (!array_key_exists($id, $this->acronymsByID)) {
            $acronym = "";
            $user = $id;
            $result = $this->db->query("SELECT `acronym` FROM `user` WHERE `user`='$user'");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['acronym'])) {
                    $acronym = $this->basic->convertToHTMLEntities($row['acronym']);
                    if (empty($acronym)) {
                        $acronym = $this->getNickbyID($user);
                    }
                }
            }
            $this->acronymsByID[$id] = $acronym;
        }
        return $this->acronymsByID[$id];
    }

    /*
     * Get nickname by giving an e-mail adress.
     */
    public function getNickbyMail(string $mail): string
    {
        return $this->userBase->getNickbyMail($mail);
    }

    /*
     * Change the role of a user.
     */
    public function changeRole(int $user, int $role): void
    {
        $this->db->query("UPDATE `user` SET `role`='$role' WHERE `user`='$user'");
    }

    /*
     * Get the registration date of a user.
     */
    public function getRegisterDate(int $user): int
    {
        if (!array_key_exists($user, $this->registerDatesByUser)) {
            $regdate = -1;
            $result = $this->db->query("SELECT `regdate` FROM `user` WHERE `user`='$user'");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['regdate'])) {
                    $regdate = intval(strval($row['regdate']));
                }
            }
            $this->registerDatesByUser[$user] = $regdate;
        }
        return $this->registerDatesByUser[$user];
    }

    /*
     * Set the password of a user.
     */
    public function setPassword(int $user, string $password): bool
    {
        $time = $this->getRegisterDate($user);
        $hash = $this->db->escapeString($this->hashPassword($time, $password));
        $this->db->query("UPDATE `user` SET `password`='$hash' WHERE `user`='$user'");
        return true;
    }

    /*
     * Update a role of a user and check whether the destination role is a possible role of the user.
     */
    public function updateRole(int $user, int $roleID): void
    {
        $ownRole = $this->role->getRole();
        if ($ownRole != $roleID) {
            $possibleRoles = $this->role->getPossibleRoles($ownRole);
            foreach ($possibleRoles as $possibleRole) {
                if ($possibleRole == $roleID) {
                    $this->db->query("UPDATE `user` SET `role` = '$roleID' WHERE `user`='$user'");
                }
            }
        }
    }

    /*
     * Update the acronym of the user.
     */
    public function updateAcronym(int $user, string $acronym): bool
    {
        $acronym = $this->db->escapeString($acronym);
        if (empty($acronym)) {
            $this->db->query("UPDATE `user` SET `acronym` = NULL WHERE `user`='$user'");
            return true;
        } else {
            if ((!$this->db->isExisting("SELECT `acronym` FROM `user` WHERE LOWER(`acronym`)=LOWER('$acronym') AND NOT (`user`='$user') LIMIT 1")) && (!$this->db->isExisting("SELECT `nickname` FROM `user` WHERE LOWER(`nickname`)=LOWER('$acronym') AND NOT(`user`='$user') LIMIT 1"))) {
                $this->db->query("UPDATE `user` SET `acronym`='$acronym' WHERE `user`='$user'");
                $proofacronym = null;
                $result = $this->db->query("SELECT `acronym` FROM `user` WHERE `user`='$user'");
                while ($row = $this->db->fetchArray($result)) {
                    $proofacronym = $row['acronym'];
                }
                if ($acronym == $proofacronym) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }

    /*
     * Update the nickname of the user.
     */
    public function updateNickname(int $user, string $nickname): bool
    {
        if (strlen($nickname) >= 4) {
            $nickname = $this->db->escapeString($nickname);
            if ((!$this->db->isExisting("SELECT `nickname` FROM `user` WHERE LOWER(`nickname`)=LOWER('$nickname') AND NOT (`user`='$user') LIMIT 1")) && (!$this->db->isExisting("SELECT `acronym` FROM `user` WHERE LOWER(`acronym`)=LOWER('$nickname') AND NOT(`user`='$user') LIMIT 1"))) {
                $this->db->query("UPDATE `user` SET `nickname`='$nickname' WHERE `user`='$user'");
                $proofnick = null;
                $result = $this->db->query("SELECT `nickname` FROM `user` WHERE `user`='$user'");
                while ($row = $this->db->fetchArray($result)) {
                    $proofnick = $row['nickname'];
                }
                if ($nickname == $proofnick) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /*
     * Update the prename of the user.
     */
    public function updatePrename(int $user, string $prename): bool
    {
        $prename = $this->db->escapeString($prename);
        $this->db->query("UPDATE `user` SET `prename`='$prename' WHERE `user`='$user'");
        return true;
    }

    /*
     * Update the family name of a user.
     */
    public function updateName(int $user, string $name): bool
    {
        $name = $this->db->escapeString($name);
        $this->db->query("UPDATE `user` SET `name`='$name' WHERE `user`='$user'");
        return true;
    }

    /*
     * Register a user.
     */
    public function register(string $nickname, string $password, string $mail, Authentication $auth, bool $shouldSendConfirmationMail): bool
    {
        $nickname = $this->db->escapeString($nickname);
        if (strlen($nickname) >= 4) {
            if ((!$this->db->isExisting("SELECT `nickname` FROM `user` WHERE LOWER(`nickname`)=LOWER('$nickname') LIMIT 1")) && (!$this->db->isExisting("SELECT `acronym` FROM `user` WHERE LOWER(`acronym`)=LOWER('$nickname') LIMIT 1"))) {
                $regdate = time();
                $hashPassword = $this->db->escapeString($this->hashPassword($regdate, $password));
                $session = $this->db->escapeString($this->basic->randomHash().$this->basic->randomHash());
                $roleID = $this->role->getUserRole();
                $this->db->query("INSERT INTO `user`(`nickname`,`password`,`postcount`,`regdate`,`sessionid`,`deleted`,`role`) VALUES('$nickname','$hashPassword','0','$regdate','$session','0','$roleID')");
                $user = $this->getIDbyName($nickname);
                $confirmID = $this->db->escapeString($this->basic->confirmID());
                $mail = $this->db->escapeString($mail);
                $this->db->query("INSERT INTO `email`(`email`,`user`,`confirmed`,`time`,`confirm_id`,`primary`) VALUES('$mail','$user','0','$regdate','$confirmID','1')");
                if ($shouldSendConfirmationMail) {
                    $this->mailer->sendConfirmationMail($user, $mail);
                }
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /*
     * Hash a password.
     */
    public function hashPassword(int $time, string $password): string
    {
        $digitSum = $this->digitSum($time);
        $hash = $password;
        for ($i = 0; $i <= $digitSum; $i++) {
            $hash = hash("sha512", $hash.$time);
        }
        return $hash;
    }

    /*
     * Calculate the digit sum of a UNIX Timestamp or other integer.
     */
    private function digitSum(int $time): int
    {
        if ($time < 0) {
            $time = (-1) * $time;
        } elseif ($time == 0) {
            $time = 1;
        }
        $strDigits = strval($time);
        $intDigitSum = 0;
        for ($i = 0; $i < strlen($strDigits); $i++) {
            $intDigitSum = $intDigitSum + intval($strDigits[$i]);
        }
        return $intDigitSum;
    }

    /**
     * Get a list of all admin users.
     *
     * @return array<int, int>
     */
    public function getAdminUsers(): array
    {
        $admins = array();
        $adminRoles = $this->role->getAdminRoles();
        foreach ($adminRoles as $adminRole) {
            $roleID = $adminRole['role'];
            $result = $this->db->query("SELECT `user` FROM `user` WHERE `role`='$roleID' AND `deleted`='0'");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['user'])) {
                    array_push($admins, intval(strval($row['user'])));
                }
            }
        }
        return $admins;
    }
}
