# marsl CMS

The marsl CMS is an easy to use CMS, which is still in development.

Main user right now is [Music2Web.de](https://www.music2web.de)

## Usage with Docker

Docker container build:
docker build -t marsl:latest .

Docker container run:
docker run -d -p 80:80 -v [absolute path to config.inc.php]:/var/www/html/includes/config.inc.php -v [absolute path to albums]:/var/www/html/albums -v [absolute path to files]:/var/www/html/files -v [absolute path to news]:/var/www/html/news -v [absolute path to shared]:/var/www/html/shared marsl:latest

For proper health checks the mounted volumes albums, files, news and shared must contain an (empty) file with the name "health".
