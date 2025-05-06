<?php

spl_autoload_register("psr4Autoloader");

function psr4Autoloader(string $classPath): void
{
    $relativeFilePath = str_replace("marsl", "", str_replace("\\", "/", $classPath));

    $absoluteFilePath = dirname(__FILE__).$relativeFilePath.".php";

    if (!file_exists($absoluteFilePath)) {
        $lowerFilePath = strtolower($absoluteFilePath);

        $filePathes = glob(dirname($absoluteFilePath) . '/*');

        if (is_array($filePathes)) {
            foreach ($filePathes as $filePath) {
                if (strtolower($filePath) == $lowerFilePath) {
                    $absoluteFilePath = $filePath;
                    break;
                }
            }
        }
    }

    include_once($absoluteFilePath);
}
