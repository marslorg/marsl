<?php

spl_autoload_register("psr4Autoloader");

function psr4Autoloader(string $classPath) : void {
    $relativeFilePath = str_replace("marsl", "", str_replace("\\", "/", $classPath));

    $absoluteFilePath = dirname(__FILE__).$relativeFilePath.".php";

    if (!file_exists($absoluteFilePath)) {
        $absoluteFilePath = dirname(__FILE__).strtolower($relativeFilePath).".php";
    }

    include_once($absoluteFilePath);
}

?>