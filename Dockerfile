FROM php:8.4-apache
ENV COMPOSER_VERSION "2.8.10"
ENV COMPOSER_SHA256 "28dbb6bd8bef31479c7985b774c130a8bda37dbe63c35b56f6cb6bc377427573"

# install default packages
RUN apt-get update && apt-get -y install \
    wget \
    git \
    unzip \
    default-mysql-client \
    libpq-dev \
    libxml2-dev \
    libcurl3-dev \
    libzip-dev \
    libonig-dev \
    libpng-dev \
    openssl \
    libssl-dev \
    libcurl4-openssl-dev \
    libaio1

# install php extensions
RUN docker-php-ext-install \
    pgsql \
    mysqli \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    simplexml \
    dom \
    bcmath \
    curl \
    zip \
    mbstring \
    intl \
    xml \
    gd \
    soap

# install pecl
RUN pecl install mongodb-2.1.1

RUN docker-php-ext-enable \
    mongodb

# install composer
RUN wget -O /usr/bin/composer https://getcomposer.org/download/${COMPOSER_VERSION}/composer.phar
RUN echo "${COMPOSER_SHA256} */usr/bin/composer" | sha256sum -c -
RUN chmod +x /usr/bin/composer

# install worker
COPY . /var/www/html/worker
RUN cd /var/www/html/worker && /usr/bin/composer install && mkdir /var/www/html/worker/actions
RUN chown -R www-data: /var/www/html/worker

# apache config
RUN sed -i 's/80/9092/g' /etc/apache2/ports.conf
RUN rm /etc/apache2/sites-available/*.conf
RUN rm /etc/apache2/sites-enabled/*.conf
COPY ./apache/worker.conf /etc/apache2/sites-available/worker.conf
RUN a2enmod rewrite
RUN a2ensite worker

# php config
RUN mv "${PHP_INI_DIR}/php.ini-production" "${PHP_INI_DIR}/php.ini"

VOLUME /var/www/html/worker/actions

EXPOSE 9092
