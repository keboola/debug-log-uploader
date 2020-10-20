FROM php:7.1
ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update -q \
  && apt-get install unzip git -y --no-install-recommends \
  && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php \
  && mv composer.phar /usr/local/bin/composer

RUN pecl install xdebug-2.9.8 && docker-php-ext-enable xdebug

WORKDIR /code
