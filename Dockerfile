FROM php:7.2
RUN apt-get update -y && apt-get install -y openssl zip unzip git
RUN docker-php-ext-install mysqli && \
    docker-php-ext-install pdo_mysql
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN docker-php-ext-install pdo mbstring
WORKDIR /laravel
COPY . /laravel
RUN composer install

RUN php artisan migrate

CMD php artisan serve --host=0.0.0.0 --port=8000