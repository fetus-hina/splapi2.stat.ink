all: vendor
.PHONY: all

vendor: composer.lock
	./composer.phar install
	touch vendor

composer.lock: composer.json composer.phar
	./composer.phar update
	touch composer.lock

composer.phar:
	curl -sS https://getcomposer.org/installer | php
	touch composer.phar
