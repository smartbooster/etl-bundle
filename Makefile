
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

qa: qualimetry
qualimetry: checkstyle lint.php composer.validate metrics phpstan

cs: checkstyle
checkstyle:
	vendor/bin/phpcs --extensions=php -n --standard=PSR12 --report=full src

lint.php:
	find src -type f -name "*.php" -exec php -l {} \;

composer.validate:
	composer validate composer.json

cb: code-beautifier
code-beautifier:
	vendor/bin/phpcbf --extensions=php --standard=PSR12 src tests

cpd:
	vendor/bin/phpcpd --fuzzy src

metrics:
	vendor/bin/phpmetrics --report-html=build/phpmetrics.html src

phpstan:
	vendor/bin/phpstan analyse src --level=1 -c phpstan.neon

# ====================
## Testing
phpunit:
	vendor/bin/phpunit -c phpunit.xml.dist --coverage-text
