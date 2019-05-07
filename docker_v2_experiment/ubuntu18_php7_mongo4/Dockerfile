FROM ubuntu:18.04
MAINTAINER Vorachet Jaroensawas <vorachet@gmail.com>

ENV DEBIAN_FRONTEND=noninteractive

RUN export LANG=en_US.UTF-8
RUN export LC_ALL=en_US.UTF-8

RUN apt-get update && apt-get install -y --no-install-recommends \
        gnupg2 \
        software-properties-common

RUN apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv 9DA31620334BD75D9DCB49F368818C72E52529D4
RUN echo "deb [ arch=amd64 ] https://repo.mongodb.org/apt/ubuntu bionic/mongodb-org/4.0 multiverse" | tee /etc/apt/sources.list.d/mongodb-org-4.0.list
RUN LC_ALL=en_US.UTF-8 add-apt-repository -y ppa:ondrej/php

RUN apt-get update && apt-get install -y --no-install-recommends \
        make \
        nginx \
        wget \
        nmap \
        mongodb-org \
        php7.2-fpm \
        php7.2-common \
        php7.2-mongodb \
        php-pear \
        php7.2-dev

RUN pecl install mongodb
RUN bash
RUN echo "extension=mongodb.so" >> /etc/php/7.2/fpm/php.ini
RUN echo "extension=mongodb.so" >> /etc/php/7.2/cli/php.ini

ADD config/nginx/api.pbapp.net.conf /etc/nginx/sites-enabled/api.pbapp.net.conf

RUN mkdir -p /data
RUN mkdir -p /run/php

COPY startNginx.sh startNginx.sh
COPY startPhpFpm.sh startPhpFpm.sh
COPY startMongoDB.sh startMongoDB.sh
COPY start.sh start.sh

CMD ./start.sh



