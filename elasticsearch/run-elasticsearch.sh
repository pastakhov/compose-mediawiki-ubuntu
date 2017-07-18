#!/bin/bash
set -e

mkdir -p $ES_LOG_DIR
mkdir -p $ES_LOG_DIR

OPTS="$OPTS -Des.default.path.logs=$ES_LOG_DIR -Des.default.path.data=$ES_DATA_DIR -Des.default.network.host=0.0.0.0"

echo "Starting Elasticsearch with the options $OPTS"
CMD="$ES_HOME/bin/elasticsearch $OPTS"
if [ `id -u` = 0 ]; then
  echo "Running as non-root..."
  chown -R $ES_USER:$ES_USER $ES_VOLUME
  su -c "$CMD" $ES_USER
else
  $CMD
fi
