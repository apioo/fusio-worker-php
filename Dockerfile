FROM php:7.4-apache
ENV COMPOSER_VERSION "2.1.9"
ENV COMPOSER_SHA256 "4d00b70e146c17d663ad2f9a21ebb4c9d52b021b1ac15f648b4d371c04d648ba"

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
    libcurl4-openssl-dev

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
RUN pecl install mongodb-1.12.0

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
RUN sed -i 's/80/9092/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf
RUN sed -i 's!/var/www/html!/var/www/html/worker/public!g' /etc/apache2/sites-available/*.conf
RUN sed -i 's!/var/www/!/var/www/html/worker/public!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

VOLUME /var/www/html/worker/actions

EXPOSE 9092
