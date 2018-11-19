
## Docker
up:
	docker-compose up

down:
	docker-compose down

ps:
	docker-compose ps

ssh:
	docker exec -it --user=dev etlbundle-docker-php bash

# ====================
# Qualimetry rules

cs: checkstyle
checkstyle:
	vendor/bin/phpcs --ignore=/vendor/*,/Tests/Resources/app/* --extensions=php --encoding=utf-8 --standard=PSR2 -np .

lint.php:
	find tests src -type f -name "*.php" -exec php -l {} \;

composer.validate:
	composer validate composer.json

qa: qualimetry
qualimetry: checkstyle lint.php composer.validate

# ====================
## Testing
phpunit:
	vendor/bin/phpunit -c phpunit.xml.dist --coverage-text
