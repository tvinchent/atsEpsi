FROM php:8.3-apache

RUN apt-get update && apt-get install -y default-jre curl \
    && rm -rf /var/lib/apt/lists/*

# PHP PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Tika
RUN mkdir -p /opt/tika \
    && curl -L -o /opt/tika/tika-app.jar https://archive.apache.org/dist/tika/2.9.2/tika-app-2.9.2.jar

RUN a2enmod rewrite
COPY ./src /var/www/html
WORKDIR /var/www/html
