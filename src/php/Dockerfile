FROM php:fpm-alpine3.17
RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-enable pdo pdo_mysql
RUN docker-php-ext-install mysqli
RUN docker-php-ext-enable mysqli