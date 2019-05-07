FROM romeoz/docker-nginx-php:5.6
MAINTAINER Impressius-PDTEAM

RUN apt-get update \
    && apt-get install -y php-mongo \
    && rm -rf /var/lib/apt/lists/*

RUN rm /etc/nginx/sites-enabled/app.conf
ADD nginx.conf /etc/nginx/conf.d/default.conf

CMD ["/usr/bin/supervisord"]