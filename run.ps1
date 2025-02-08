.\vendor\bin\phpstan analyze --memory-limit=-1 --fail-without-result-cache
if ($LASTEXITCODE -eq 0) {
    docker-compose up -d --build --force-recreate --remove-orphans
}