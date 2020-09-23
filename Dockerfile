FROM phusion/baseimage:0.11
ARG DEBIAN_FRONTEND=noninteractive


# Update & install dependencies and do cleanup
RUN apt-get update && \
    apt-get dist-upgrade -y && \
    apt-get install -y \
        composer \
        apache2 \
        libapache2-mod-php \
        php-mysql \
        php-curl \
        php-cli \
        php-mbstring \
        php-json \
        php-bcmath \
        php-bz2 \
        php-zip \
        php-intl \
        php-xml \
        curl \
        git && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Enable rewrite support for apache2
RUN a2enmod rewrite && \
    a2dissite 000-default

# Configure virtual host
COPY ./docker/cryptoprice-apache2.conf /etc/apache2/sites-available/cryptoprice.conf
RUN a2ensite cryptoprice

# add cryptoprice cronjobs
COPY ./docker/cryptoprice.cron /etc/cron.d/cryptoprice

RUN mkdir /app && \
    chown -R www-data:www-data /app && \
    chown -R www-data:www-data /var/www && \
    chown -R root:root /etc/cron.d/cryptoprice && \
    chmod 644 /etc/cron.d/cryptoprice

# Copy our app into docker
COPY ./app /app/app
COPY ./bootstrap /app/bootstrap
COPY ./config /app/config
COPY ./database /app/database
COPY ./public /app/public
COPY ./resources /app/resources
COPY ./routes /app/routes
COPY ./storage /app/storage
COPY ./tests /app/tests
COPY ./artisan /app/artisan
COPY ./composer.json /app/composer.json
COPY ./composer.lock /app/composer.lock

# set correct access rights for copied files
RUN chown -R www-data:www-data /app/

# install composer dependencies
USER www-data
RUN cd /app && \
    COMPOSER_HOME=/var/www composer global require hirak/prestissimo && \
    COMPOSER_HOME=/var/www composer install

USER root
# Add our startup script
RUN mkdir /etc/service/cryptoprice
COPY docker/cryptoprice.sh /etc/service/cryptoprice/run
RUN chmod +x /etc/service/cryptoprice/run

RUN chmod 755 /etc/container_environment.sh

EXPOSE 80

CMD ["/sbin/my_init"]
HEALTHCHECK --interval=30s --timeout=10s --retries=3 CMD http://127.0.0.1/api/documentation || exit 1