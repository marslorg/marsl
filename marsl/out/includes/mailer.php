<?php

namespace marsl\includes;

include_once(dirname(__FILE__)."/errorHandler.php");
include_once(dirname(__FILE__)."/../autoload.php");

use marsl\modules\Navigation;
use marsl\user\Authentication;
use marsl\user\UserBase;

class Mailer
{
    private Configuration $configuration;
    private DB $db;
    private Navigation $navigation;
    private UserBase $userBase;

    public function __construct(
        Configuration $configuration,
        DB $db,
        Navigation $navigation,
        UserBase $userBase
    ) {
        $this->configuration = $configuration;
        $this->db = $db;
        $this->navigation = $navigation;
        $this->userBase = $userBase;
    }

    public function sendConfirmationMail(int $userID, string $mail): void
    {
        $nickname = $this->userBase->getNickbyId($userID);
        $mail = $this->db->escapeString($mail);
        $result = $this->db->query("SELECT `confirm_id` FROM `email` WHERE `confirmed`='0' AND `user`='$userID' AND `email`='$mail'");
        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['confirm_id'])) {
                $confirm_id = $row['confirm_id'];
                $link = $this->configuration->getDomain().$this->configuration->getBasePath()."/confirm.php?mail=".$mail."&code=".$confirm_id;
                $msg = "Hallo ".$nickname.",\n";
                $msg .= "\n";
                $msg .= "du hast auf ".$this->configuration->getTitle()." eine neue E-Mail-Adresse eingetragen.\n";
                $msg .= "Es ist jetzt noch ein Schritt, damit diese verwendet werden kann.\n";
                $msg .= "Bitte klicke auf den Link unten, um zu zeigen, dass die E-Mail-Adresse wirklich dir geh�rt.\n";
                $msg .= "\n";
                $msg .= $link."\n";
                $msg .= "\n";
                $msg .= "Falls du mit dieser E-Mail nichts anfangen kannst, lösch sie einfach. Du wirst keine weitere Post mehr von uns bekommen.\n";
                $msg .= "\n";
                $msg .= "Dein ".$this->configuration->getTitle()."-Team";
                mail($mail, "Bestätige deine E-Mail-Adresse", $msg, "From: ".$this->configuration->getTitle()."<".$this->configuration->sysMail().">");
            }
        }
    }

    /*
     * Send a mail when a new news article was posted to the news correcture system.
     */
    public function sendNewArticleMail(int $userID): void
    {
        $mail = $this->userBase->getMailbyID($userID);
        $nickname = $this->userBase->getNickbyID($userID);
        $link = $this->configuration->getDomain().$this->configuration->getBasePath()."/admin/index.php?var=module&module=news&action=queue";
        $msg = "Hallo ".$nickname.",\n";
        $msg .= "\n";
        $msg .= "es wurde eine neue Nachricht ins System eingestellt. Da Du einer unserer Lektoren bist wurdest du ausgewählt diese Nachricht freizuschalten.\n";
        $msg .= "Bitte logg dich ins Administrationssystem ein und besuche folgende Seite: ".$link."\n";
        $msg .= "\n";
        $msg .= "Bitte lies den Artikel gegen und schalte ihn ggf. frei. Sollten noch andere Artikel vorhanden sein, lies diese bitte auch gegen und schalte sie ggf. frei.\n";
        $msg .= "\n";
        $msg .= "Dein ".$this->configuration->getTitle()."-Team";
        mail($mail, "Neuer Nachrichtenartikel auf ".$this->configuration->getTitle(), $msg, "From: ".$this->configuration->getTitle()."<".$this->configuration->sysMail().">");
    }

    /*
     * Send out a password reset mail.
     * @param int $page Set to -1 for administration page.
     */
    public function sendPasswordMail(int $page, string $nickname): bool
    {
        $id = $this->userBase->getIDbyName($nickname);
        $mail = $this->userBase->getMailbyID($id);
        if (!empty($id) && !empty($mail)) {
            $password = $this->userBase->getPassbyID($id);
            $time = time();
            $auth_code = md5($page.$id.$time.$password);
            $link = "";
            if ($page == -1) {
                $link = $this->configuration->getDomain().$this->configuration->getBasePath()."/admin/index.php?var=forgot&action=recover&uid=".$id."&time=".$time."&auth=".$auth_code;
            } else {
                $uri = $this->navigation->getRelativeURI($page, null, true);
                $link = $this->configuration->getDomain().$this->configuration->getBasePath()."/".$uri."action2=recover&uid=".$id."&time=".$time."&auth=".$auth_code;
            }
            $msg = "Hallo ".$nickname.",\n";
            $msg .= "\n";
            $msg .= "diese E-Mail bekommst du, weil du dein Passwort auf ".$this->configuration->getTitle()." angefordert hast.\n";
            $msg .= "\n";
            $msg .= "Aus Sicherheitsgründen speichern wir die Passwörter nur mit einer Einweg-Verschlüsselung ab. Du kannst aber ein neues Passwort setzen, um dich wieder einloggen zu können.\n";
            $msg .= "\n";
            $msg .= "Klicke auf folgenden Link um dein Passwort neu zu setzen: ".$link."\n";
            $msg .= "\n";
            $msg .= "Der Link ist 48 Stunden lang gültig. Sollte der Link nicht funktionieren, kopiere ihn bitte in deinen Browser.\n";
            $msg .= "\n";
            $msg .= "Wir wünschen dir noch viel Spaß auf unserer Internetseite.\n";
            $msg .= "Dein ".$this->configuration->getTitle()."-Team";
            $msg .= "\n";
            $msg .= "\n";
            $msg .= "--\n";
            $msg .= "Um zum gültigen Impressum zu gelangen besuchst du bitte ".$this->configuration->getDomain().$this->configuration->getBasePath()." und klickst dort unten auf der Seite auf Impressum.\n";
            $msg .= "Sollte dir die E-Mail fälschlicherweise zugesandt worden sein, so schick uns eine Kopie dieser E-Mail an ".$this->configuration->errMail().". Wir kümmern uns dann um den Fehler.";
            mail($mail, "Erinnerungsmail: Dein Passwort bei ".$this->configuration->getTitle(), $msg, "From: ".$this->configuration->getTitle()."<".$this->configuration->sysMail().">");
            return true;
        } else {
            return false;
        }
    }

    /*
     * Send out a mail with the user name.
     */
    public function sendNicknameMail(string $mail): bool
    {
        $nickname = $this->userBase->getNickbyMail($mail);
        if (!empty($nickname)) {
            $msg = "Hallo ".$nickname.",\n";
            $msg .= "\n";
            $msg .= "diese E-Mail bekommst du, weil du deinen Benutzernamen auf ".$this->configuration->getTitle()." angefordert hast.\n";
            $msg .= "\n";
            $msg .= "Dein Benutzername lautet ".$nickname.".\n";
            $msg .= "\n";
            $msg .= "Wir wünschen dir noch viel Spaß auf unserer Internetseite.\n";
            $msg .= "Dein ".$this->configuration->getTitle()."-Team";
            $msg .= "\n";
            $msg .= "\n";
            $msg .= "--\n";
            $msg .= "Um zum gültigen Impressum zu gelangen besuchst du bitte ".$this->configuration->getDomain().$this->configuration->getBasePath()." und klickst dort unten auf der Seite auf Impressum.\n";
            $msg .= "Sollte dir die E-Mail fälschlicherweise zugesandt worden sein, so schick uns eine Kopie dieser E-Mail an ".$this->configuration->errMail().". Wir kümmern uns dann um den Fehler.";
            mail($mail, "Erinnerungsmail: Dein Benutzername bei ".$this->configuration->getTitle(), $msg, "From: ".$this->configuration->getTitle()."<".$this->configuration->sysMail().">");
            return true;
        } else {
            return false;
        }
    }
}
