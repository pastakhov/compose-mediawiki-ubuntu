FROM ubuntu:16.04

MAINTAINER pastakhov@yandex.ru

# Install requered packages
# Install requered packages
RUN set -x; \
    apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        varnish \
    && rm -rf /var/lib/apt/lists/* \
    && rm -rf /var/cache/apt/archives/*

ENV VARNISH_PORT 80

EXPOSE $VARNISH_PORT

COPY run-varnish.sh /run-varnish.sh
RUN chmod -v +x /run-varnish.sh

COPY default.vcl /etc/varnish/default.vcl

CMD ["/run-varnish.sh"]
