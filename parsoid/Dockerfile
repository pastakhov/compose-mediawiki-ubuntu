FROM ubuntu:16.04

MAINTAINER pastakhov@yandex.ru

RUN set -x; \
    useradd -U -r -s /bin/bash parsoid

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

# parsoid setup
RUN set -x; \
    # Core
    mkdir -p /var/lib/parsoid \
    && git clone \
        --depth 1 \
        https://gerrit.wikimedia.org/r/p/mediawiki/services/parsoid \
        /var/lib/parsoid \
    && cd /var/lib/parsoid \
    && npm install

ADD run-parsoid.sh /run-parsoid.sh
RUN chmod -v +x /run-parsoid.sh

ADD config.yaml /var/lib/parsoid/config.yaml

USER parsoid
EXPOSE 8000
CMD ["/run-parsoid.sh"]
