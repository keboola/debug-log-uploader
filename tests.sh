#!/bin/bash

php --version \
  && composer --version \
  && composer install --prefer-dist --no-interaction \
  && ./vendor/bin/phpcs --standard=psr2 -n --ignore=vendor --extensions=php . \
  && ./vendor/bin/phpunit
