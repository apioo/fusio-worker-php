FROM php:7.4-apache
ENV COMPOSER_VERSION "2.1.3"
ENV COMPOSER_SHA256 "f8a72e98dec8da736d8dac66761ca0a8fbde913753e9a43f34112367f5174d11"
ENV APACHE_DOCUMENT_ROOT /worker/public
ENV PORT 9092
RUN apt-get update && apt-get -y install wget git

# apache config
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# install composer
RUN wget -O /usr/bin/composer https://getcomposer.org/download/${COMPOSER_VERSION}/composer.phar
RUN echo "${COMPOSER_SHA256} */usr/bin/composer" | sha256sum -c -
RUN chmod +x /usr/bin/composer

WORKDIR /worker
VOLUME /worker/actions

COPY . .
RUN composer install
RUN chown -R www-data: ${APACHE_DOCUMENT_ROOT}

EXPOSE 9092
