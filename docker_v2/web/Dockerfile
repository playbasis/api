FROM romeoz/docker-nginx-php:5.6
MAINTAINER Impressius-PDTEAM

RUN apt-get update \
    && apt-get install -y php-mongo \
    && rm -rf /var/lib/apt/lists/*

RUN rm /etc/nginx/sites-enabled/app.conf
ADD nginx.conf /etc/nginx/conf.d/default.conf

RUN rm /etc/php/5.6/fpm/php.ini
RUN rm /etc/php/5.6/cli/php.ini

ADD php.ini etc/php/5.6/fpm/php.ini
ADD php.ini etc/php/5.6/cli/php.ini

CMD ["/usr/bin/supervisord"]