FROM centos:7

# Import EPEL
RUN yum --assumeyes install epel-release && \
    yum --assumeyes install yum-utils

# Import REMI
RUN rpm -Uvh http://rpms.famillecollet.com/enterprise/remi-release-7.rpm && \
    yum-config-manager -q --enable remi && \
    yum-config-manager -q --enable remi-php56 \
    && yum clean all

RUN yum --assumeyes install \
    php-fpm php-cli php-xml \
    nginx \
    rsyslog \

    && yum clean all

################################################################################

EXPOSE 80

################################################################################

RUN sed -i 's/;date.timezone =.*/date.timezone = "UTC"/'    /etc/php.ini
RUN sed -i 's/daemonize = yes/daemonize = no/'              /etc/php-fpm.conf
RUN sed -i 's@listen = .*@listen = /var/run/php5-fpm.sock@' /etc/php-fpm.d/www.conf
RUN sed -i 's/;listen.owner = .*/listen.owner = nginx/'     /etc/php-fpm.d/www.conf
RUN sed -i 's/;listen.group = .*/listen.group = nginx/'     /etc/php-fpm.d/www.conf
RUN sed -i 's/;listen.mode = .*/listen.mode = 0660/'        /etc/php-fpm.d/www.conf

ADD nginx/nginx.conf    /etc/nginx/nginx.conf
ADD nginx/vhost.conf    /etc/nginx/conf.d/vhost.conf
ADD nginx/upstream.conf /etc/nginx/conf.d/upstream.conf

ADD syslog/rsyslog.conf /etc/rsyslog.conf
RUN rm /etc/rsyslog.d/listen.conf

ADD start.sh            /bin/start.sh

################################################################################

CMD ["/bin/start.sh"]
