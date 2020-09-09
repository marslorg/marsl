<?php
include_once(dirname(__FILE__)."/config.inc.php");
set_error_handler("sendErrorMail");

function sendErrorMail($errno, $errmsg, $filename, $linenum) {
	$config = new Configuration();
	$dateTime = new DateTime("now", new DateTimeZone($config->getTimezone()));
	$error = "Zeit: ".$dateTime->format("Y-m-d H:i:s")."\n";
	$error .= "Meldung: ".$errmsg."\n";
	$error .= "Datei: ".$filename."\n";
	$error .= "Zeile: ".$linenum."\n";
	if (isset($_SERVER['HTTP_REFERER'])) {
		$error .= "Referer: ".$_SERVER['HTTP_REFERER']."\n";
	}
	$error .= "\n";
	$error .= "Adresse: ".$_SERVER['REQUEST_URI']."\n";
	$error .= "\n";
	$error .= "IP: ".$_SERVER['REMOTE_ADDR']."\n";
	$error .= "\n";
	$error .= "GET-Werte:\n";
	foreach($_GET as $key=>$value) {
		$error .= $key."->".$value."\n";
	}
	$error .= "\n";
	$error .= "POST-Werte:\n";
	foreach($_POST as $key=>$value) {
		$error .= $key."->".$value."\n";
	}
	$error .= "\n";
	$error .= "COOKIE-Werte:\n";
	foreach($_COOKIE as $key=>$value) {
		$error .= $key."->".$value."\n";
	}
	$error .= "\n";
	$error .= "FILES-Werte:\n";
	foreach($_FILES as $key=>$value) {
		$error .= $key."->".$value."\n";
	}
	//mail($config->errMail(), "Fehler auf ".$config->getDomain(), $error, "From: ".$config->getTitle()."<".$config->sysMail().">");
	//echo "<b>Ein Fehler ist aufgetreten. Wir arbeiten daran.</b>";
	echo nl2br($error);
}
?>
