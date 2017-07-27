#!/bin/bash
set -e

# Start varnish and log
varnishd \
    -j unix,user=varnish \
    -a :${VARNISH_PORT} \
    -f /etc/varnish/default.vcl \
    -s malloc,256m

varnishncsa
