FROM php:8.3-apache
RUN docker-php-ext-install pdo_mysql && a2enmod rewrite headers
COPY apache.conf /etc/apache2/conf-available/ia-lab.conf
RUN a2enconf ia-lab
