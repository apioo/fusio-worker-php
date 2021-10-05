FROM php:7.4-alpine
RUN apk add --no-cache git postgresql-dev libxml2-dev curl-dev libzip-dev libpng-dev libmemcached-dev oniguruma-dev openssl libuv-dev autoconf gcc g++ make
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
# install php extensions
RUN docker-php-ext-install \
    pgsql \
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
RUN pecl install uv-0.2.4
RUN docker-php-ext-enable uv
WORKDIR /worker
COPY . .
RUN composer install
EXPOSE 9092
VOLUME /worker/actions
CMD ["php", "./worker.php"]
