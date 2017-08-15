.PHONY: all
all: app

.PHONY: app
app: vendor config/cookie.php config/params-session.php
	./yii migrate/up

vendor: composer.lock
	./composer.phar install
	touch vendor

composer.lock: composer.json composer.phar
	./composer.phar update
	touch composer.lock

composer.phar:
	curl -sS https://getcomposer.org/installer | php
	touch composer.phar

config/cookie.php:
	php standalone/cookie.php > $@

config/params-session.php:
	cp config/params-session.sample.php $@
