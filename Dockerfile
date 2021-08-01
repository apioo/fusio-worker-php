FROM php:7.4-alpine
RUN apk add --no-cache git
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
WORKDIR /app
COPY . .
RUN composer install
EXPOSE 9092
CMD ["php", "./worker.php"]
