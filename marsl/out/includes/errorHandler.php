<?php

namespace marsl\includes;

include_once(dirname(__FILE__)."/../autoload.php");

use DateTime;
use DateTimeZone;
use marsl\includes\Configuration;

set_error_handler("\marsl\includes\sendErrorMail");

function sendErrorMail(int $errno, string $errmsg, string $filename, int $linenum): bool
{
    $config = new Configuration();
    $dateTime = new DateTime("now", new DateTimeZone($config->getTimezone()));
    $error = "Zeit: ".$dateTime->format("Y-m-d H:i:s")."\n";
    $error .= "Meldung: ".$errmsg."\n";
    $error .= "Datei: ".$filename."\n";
    $error .= "Zeile: ".$linenum."\n";
    if (isset($_SERVER['HTTP_REFERER']) && is_string($_SERVER['HTTP_REFERER'])) {
        $error .= "Referer: ".strval($_SERVER['HTTP_REFERER'])."\n";
    }
    $error .= "\n";
    if (is_string($_SERVER['REQUEST_URI'])) {
        $error .= "Adresse: ".strval($_SERVER['REQUEST_URI'])."\n";
    }
    $error .= "\n";
    if (is_string($_SERVER[$config->getRemoteIPFieldName()])) {
        $error .= "IP: ".strval($_SERVER[$config->getRemoteIPFieldName()])."\n";
    }
    $error .= "\n";
    $error .= "GET-Werte:\n";
    foreach ($_GET as $key => $value) {
        if (is_string($key) && is_string($value)) {
            $error .= strval($key)."->".strval($value)."\n";
        }
    }
    $error .= "\n";
    $error .= "POST-Werte:\n";
    foreach ($_POST as $key => $value) {
        if (is_string($key) && is_string($value)) {
            $error .= strval($key)."->".strval($value)."\n";
        }
    }
    $error .= "\n";
    $error .= "COOKIE-Werte:\n";
    foreach ($_COOKIE as $key => $value) {
        if (is_string($key) && is_string($value)) {
            $error .= strval($key)."->".strval($value)."\n";
        }
    }
    $error .= "\n";
    $error .= "FILES-Werte:\n";
    foreach ($_FILES as $key => $value) {
        if (is_string($key) && is_string($value)) {
            $error .= strval($key)."->".strval($value)."\n";
        }
    }

    $error .= "STACKTRACE:\n\n";

    $backtrace = debug_backtrace();
    array_shift($backtrace);

    foreach ($backtrace as $traceLine) {
        if (isset($traceLine['file']) && isset($traceLine['line'])) {
            $error .= "In ".$traceLine['file']." on line ".$traceLine['line']." called function ".$traceLine['function'];
            if (array_key_exists("args", $traceLine) && count($traceLine['args']) > 0) {
                $error .= " with arguments";
                foreach ($traceLine['args'] as $argument) {
                    if (is_string($argument)) {
                        $error .= " ".$argument.",";
                    } else {
                        try {
                            $error .= " ".serialize($argument).",";
                        } catch (\Exception $e) {
                            $error .= " ".gettype($argument).",";
                        }
                    }
                }
                $error = substr($error, 0, -1);
            }
            $error .= ".\n=> ";
        }
    }
    $error = substr($error, 0, -3);

    //mail($config->errMail(), "Fehler auf ".$config->getDomain(), $error, "From: ".$config->getTitle()."<".$config->sysMail().">");
    //echo "<b>Ein Fehler ist aufgetreten. Wir arbeiten daran.</b>";
    echo nl2br($error);

    return true;
}
