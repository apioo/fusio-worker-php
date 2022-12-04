FROM php:8.1-apache
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
RUN pecl install mongodb-1.12.0

RUN docker-php-ext-enable \
    mongodb

# Install Oracle Instantclient
RUN mkdir /opt/oracle \
    && cd /opt/oracle \
    && wget https://download.oracle.com/otn_software/linux/instantclient/instantclient-basic-linuxx64.zip \
    && wget https://download.oracle.com/otn_software/linux/instantclient/instantclient-sdk-linuxx64.zip \
    && unzip /opt/oracle/instantclient-basic-linuxx64.zip -d /opt/oracle \
    && unzip /opt/oracle/instantclient-sdk-linuxx64.zip -d /opt/oracle \
    && rm -rf /opt/oracle/*.zip \
    && DIR_ORACLE_INSTANT_CLIENT=$(ls -d /opt/oracle/*/ | sed 's:/$::g') \
    && echo $DIR_ORACLE_INSTANT_CLIENT > /etc/ld.so.conf.d/oracle-instantclient.conf \
    && ldconfig

# Install Oracle extensions
RUN DIR_ORACLE_INSTANT_CLIENT=$(ls -d /opt/oracle/*/ | sed 's:/$::g') \
    && VER_MAY_ORACLE_INSTANT_CLIENT=$(echo $DIR_ORACLE_INSTANT_CLIENT | cut -d "_" -f 2) \
    && VER_MIN_ORACLE_INSTANT_CLIENT=$(echo $DIR_ORACLE_INSTANT_CLIENT | cut -d "_" -f 3) \
    && echo "instantclient,$DIR_ORACLE_INSTANT_CLIENT" | pecl install oci8 \
    && docker-php-ext-enable \
           oci8
RUN DIR_ORACLE_INSTANT_CLIENT=$(ls -d /opt/oracle/*/ | sed 's:/$::g') \
    && VER_MAY_ORACLE_INSTANT_CLIENT=$(echo $DIR_ORACLE_INSTANT_CLIENT | cut -d "_" -f 2) \
    && VER_MIN_ORACLE_INSTANT_CLIENT=$(echo $DIR_ORACLE_INSTANT_CLIENT | cut -d "_" -f 3) \
    && docker-php-ext-configure pdo_oci --with-pdo-oci=instantclient,$DIR_ORACLE_INSTANT_CLIENT,${VER_MAY_ORACLE_INSTANT_CLIENT}.${VER_MIN_ORACLE_INSTANT_CLIENT} \
    && docker-php-ext-install pdo_oci

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
