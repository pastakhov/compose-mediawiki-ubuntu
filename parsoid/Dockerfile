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

ENV PARSOID_HOME=/var/lib/parsoid

# parsoid setup
RUN set -x; \
    # Core
    mkdir -p $PARSOID_HOME \
    && git clone \
        --depth 1 \
        https://gerrit.wikimedia.org/r/p/mediawiki/services/parsoid \
        $PARSOID_HOME \
    && cd $PARSOID_HOME \
    && npm install \
    && useradd -U -r -s /bin/bash parsoid

COPY run-parsoid.sh /run-parsoid.sh
RUN chmod -v +x /run-parsoid.sh

COPY config.yaml /var/lib/parsoid/config.yaml

USER parsoid
EXPOSE 8000
CMD ["/run-parsoid.sh"]
