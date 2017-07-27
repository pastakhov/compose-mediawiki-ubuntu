FROM ubuntu:16.04

MAINTAINER pastakhov@yandex.ru

# Install requered packages
RUN set -x; \
    apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        git \
        nodejs \
        npm \
    && rm -rf /var/lib/apt/lists/* \
    && rm -rf /var/cache/apt/archives/*

# Update npm
RUN set -x; \
    npm install -g npm \
    && ln -s /usr/bin/nodejs /usr/bin/node

ENV RB_HOME=/var/lib/restbase \
    RB_DATA=/data

# restbase setup
RUN set -x; \
    # Core
    mkdir -p $RB_HOME \
    && git clone \
        --depth 1 \
        https://github.com/wikimedia/restbase.git \
        /var/lib/restbase \
    && cd $RB_HOME \
    && npm install \
    && useradd -U -r -s /bin/bash restbase \
    && mkdir -p $RB_DATA \
    && chown -R restbase:restbase $RB_DATA

COPY run-restbase.sh /run-restbase.sh
RUN chmod -v +x /run-restbase.sh

COPY projects_example.yaml $RB_HOME/projects/example.yaml
COPY config.yaml $RB_HOME/config.yaml

USER restbase
EXPOSE 7231
CMD ["/run-restbase.sh"]
VOLUME $RB_DATA
