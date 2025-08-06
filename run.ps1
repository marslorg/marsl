$phpstan = Start-Process -FilePath "vendor\bin\phpstan" -ArgumentList "analyze --memory-limit=-1 --fail-without-result-cache" -PassThru -Wait -NoNewWindow
$handle = $phpstan.Handle
$phpstan.WaitForExit();

if ($phpstan.ExitCode -eq 0) {
    docker-compose up -d --build --force-recreate --remove-orphans
}