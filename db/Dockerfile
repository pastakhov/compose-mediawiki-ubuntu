# see https://github.com/mysql/mysql-docker
FROM ubuntu:16.04

MAINTAINER pastakhov@yandex.ru 

# Install requered packages
RUN set -x; \
    apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
        ca-certificates \
        mysql-server \
        libpwquality-tools \
    && rm -rf /var/lib/apt/lists/* \
    && rm -rf /var/cache/apt/archives/* \
    && rm -rf /var/lib/mysql/*

RUN set -x; \
    mkdir -p /var/run/mysqld \
    && chown -R mysql:mysql /var/run/mysqld

COPY docker.cnf /etc/mysql/conf.d/docker.cnf
COPY mysqld.cnf /etc/mysql/mysql.conf.d/mysqld.cnf

RUN set -x; \
    mkdir -p /docker-entrypoint-initdb.d

VOLUME /var/lib/mysql

COPY docker-entrypoint.sh /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]
RUN chmod -v +x /entrypoint.sh

EXPOSE 3306

CMD ["mysqld"]
