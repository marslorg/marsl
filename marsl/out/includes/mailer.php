<?php
include_once(dirname(__FILE__)."/errorHandler.php");
include_once(dirname(__FILE__)."/../user/user.php");
include_once(dirname(__FILE__)."/config.inc.php");

class Mailer {
	public function sendConfirmationMail($userID, $mail) {
		
	}
	
	/*
	 * Send a mail when a new news article was posted to the news correcture system.
	 */
	public function sendNewArticleMail($userID) {
		$user = new User();
		$config = new Configuration();
		$mail = $user->getMailbyID($userID);
		$nickname = $user->getNickbyID($userID);
		$link = $config->getDomain()."/admin/index.php?var=module&module=news&action=queue";
		$msg = "Hallo ".$nickname.",\n";
		$msg .= "\n";
		$msg .= "es wurde eine neue Nachricht ins System eingestellt. Da Du einer unserer Lektoren bist wurdest du ausgewählt diese Nachricht freizuschalten.\n";
		$msg .= "Bitte logg dich ins Administrationssystem ein und besuche folgende Seite: ".$link."\n";
		$msg .= "\n";
		$msg .= "Bitte lies den Artikel gegen und schalte ihn ggf. frei. Sollten noch andere Artikel vorhanden sein, lies diese bitte auch gegen und schalte sie ggf. frei.\n";
		$msg .= "\n";
		$msg .= "Dein ".$config->getTitle()."-Team";
		mail($mail, "Neuer Nachrichtenartikel auf ".$config->getTitle(), $msg, "From: ".$config->getTitle()."<".$config->sysMail().">");
	}
	
	/*
	 * Send out a password reset mail.
	 */
	public function sendPasswordMail($page, $nickname) {
		$user = new User();
		$id = $user->getIDbyName($nickname);
		$mail = $user->getMailbyID($id);
		if (!empty($id)&&!empty($mail)) {
			$password = $user->getPassbyID($id);
			$time = time();
			$auth_code = md5($page.$id.$time.$password);
			$config = new Configuration();
			$link = "";
			if ($page == "admin") {
				$link = $config->getDomain()."/admin/index.php?var=forgot&action=recover&uid=".$id."&time=".$time."&auth=".$auth_code;
			}
			else {
				$link = $config->getDomain()."/index.php?id=".$page."&action=recover&uid=".$id."&time=".$time."&auth=".$auth_code;
			}
			$msg = "Hallo ".$nickname.",\n";
			$msg .= "\n";
			$msg .= "diese E-Mail bekommst du, weil du dein Passwort auf ".$config->getTitle()." angefordert hast.\n";
			$msg .= "\n";
			$msg .= "Aus Sicherheitsgründen speichern wir die Passwörter nur mit einer Einweg-Verschlüsselung ab. Du kannst aber ein neues Passwort setzen, um dich wieder einloggen zu können.\n";
			$msg .= "\n";
			$msg .= "Klicke auf folgenden Link um dein Passwort neu zu setzen: ".$link."\n";
			$msg .= "\n";
			$msg .= "Der Link ist 48 Stunden lang gültig. Sollte der Link nicht funktionieren, kopiere ihn bitte in deinen Browser.\n";
			$msg .= "\n";
			$msg .= "Wir wünschen dir noch viel Spaß auf unserer Internetseite.\n";
			$msg .= "Dein ".$config->getTitle()."-Team";
			$msg .= "\n";
			$msg .= "\n";
			$msg .= "--\n";
			$msg .= "Um zum gültigen Impressum zu gelangen besuchst du bitte ".$config->getDomain()." und klickst dort unten auf der Seite auf Impressum.\n";
			$msg .= "Sollte dir die E-Mail fälschlicherweise zugesandt worden sein, so schick uns eine Kopie dieser E-Mail an ".$config->errMail().". Wir kümmern uns dann um den Fehler.";
			mail($mail, "Erinnerungsmail: Dein Passwort bei ".$config->getTitle(), $msg, "From: ".$config->getTitle()."<".$config->sysMail().">");
			return true;
		}
		else {
			return false;
		}
	}
	
	/*
	 * Send out a mail with the user name.
	 */
	public function sendNicknameMail($mail) {
		$user = new User();
		$nickname = $user->getNickbyMail($mail);
		if (!empty($nickname)) {
			$config = new Configuration();
			$msg = "Hallo ".$nickname.",\n";
			$msg .= "\n";
			$msg .= "diese E-Mail bekommst du, weil du deinen Benutzernamen auf ".$config->getTitle()." angefordert hast.\n";
			$msg .= "\n";
			$msg .= "Dein Benutzername lautet ".$nickname.".\n";
			$msg .= "\n";
			$msg .= "Wir wünschen dir noch viel Spaß auf unserer Internetseite.\n";
			$msg .= "Dein ".$config->getTitle()."-Team";
			$msg .= "\n";
			$msg .= "\n";
			$msg .= "--\n";
			$msg .= "Um zum gültigen Impressum zu gelangen besuchst du bitte ".$config->getDomain()." und klickst dort unten auf der Seite auf Impressum.\n";
			$msg .= "Sollte dir die E-Mail fälschlicherweise zugesandt worden sein, so schick uns eine Kopie dieser E-Mail an ".$config->errMail().". Wir kümmern uns dann um den Fehler.";
			mail($mail, "Erinnerungsmail: Dein Benutzername bei ".$config->getTitle(), $msg, "From: ".$config->getTitle()."<".$config->sysMail().">");
			return true;
		}
		else {
			return false;
		}
	}
}

?>